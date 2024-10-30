<?php

/**
 * BIG FISH Payment Gateway Provider
 * 
 */
class BigFishPaymentGatewayProvider extends WC_Payment_Gateway
{
	const POCKET_ID = 'pocket_id';

	const MAX_NAME_LENGTH = 45;
	const MAX_EMAIL_LENGTH = 254;
	const MAX_PHONE_LENGTH = 18;
	const MAX_POSTAL_CODE_LENGTH = 16;
	const MAX_CITY_LENGTH = 50;
	const MAX_ADDRESS_LINE_LENGTH = 50;
	const MAX_COUNTRY_LENGTH = 50;
	const MAX_COUNTRY_CODE_2_LENGTH = 2;
	const MAX_USER_AGENT_LENGTH = 2048;
	const SCA_SHIPPING_METHOD_SERVICE = "05";

	const MB_DEFAULT_ENCODING = 'UTF-8';

	protected $pockets = array();
	protected $min_amount = 0;
	protected $providerName = 'Provider';
	protected $transaction;
	protected $email;
	protected \BigFish\PaymentGateway $api;

	/**
	 * Contructor
	 *
	 * @access public
	 */
	public function __construct() {
		$this->id = BF_PMGW_ID . $this->getProviderSuffix();
		$this->method_title = BF_PMGW_TITLE . ' ' . $this->providerLongName;

		add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

		/**
		 * Set BIG FISH Payment Gateway provider admin form fields
		 *
		 */
		$this->set_form();
		$this->init_settings();
		$this->received_page();
		$this->title = $this->getDisplayName();
		$this->description = $this->getDisplayDescription();
		$this->setCardSelector();
	}

	public function setCardSelector() {
		if (!in_array('tokenization', $this->supports)) {
			return;
		}

		if ((get_current_user_id() > 0) && isset($this->settings[$this->providerName . 'CardRegistrationFunctions']) && (int)$this->settings[$this->providerName . 'CardRegistrationFunctions']) {

			$setting = (int)$this->settings[$this->providerName . 'CardRegistrationFunctions'];

			$this->description .= $this->startCustomBlock();
			//List stored card if has
			if ($setting === 2 && $this->get_tokens()) {
				$description = $this->getHtmlTokenList();

				if (!$description) {
					$this->description .= $this->getPayWithCardRegistrationHtml();
				} else {
					$this->description .= sprintf('%1$s: <br />', __('Pay with', BF_PMGW_PLUGIN));
					$this->description .= $this->getNewPaymentMethodOptionHtml();
					$this->description .= $this->getPayWithCardRegistrationRadioHtml();
					$this->description .= $description;
				}
			}

			if ($setting === 1 || ($setting === 2 && count($this->get_tokens()) == 0)) {
				$this->description .= $this->getPayWithCardRegistrationHtml();
			}

			$this->description .= $this->endCustomBlock();
		}
	}

	/**
	 * @param array $array
	 * @return string
	 */
	protected function getPacketHtmlSelector(array $array) {
		$html = sprintf('<div>%s: <select class="select" name="%s">', __('Select pocket', BF_PMGW_PLUGIN), static::POCKET_ID);

		foreach ($array as $key) {
			$html .= sprintf('<option value="%s">%s</option>', $key, __($this->pockets[$key], BF_PMGW_PLUGIN));
		}

		$html .= '</select></div>';
		return $html;
	}

	/**
	 * @return string
	 */
	protected function getHtmlTokenList() {
		global $wpdb;

		$html = '';
		foreach ($this->get_tokens() as $token) {
			if (!is_numeric($this->get_option('autoCommit'))) {
				continue;
			}

			if (!$wpdb->get_row("SELECT bigfishpaymentgateway_id FROM " . self::getTransactionsTableName() . " WHERE transaction_id='" . $token->get_token() . "' AND auto_commit=" . (int)$this->get_option('autoCommit') . " LIMIT 1")) {
				continue;
			}

			$html .= $this->getSavedPaymentTokenOptionsHtml($token);
		}

		return $html;
	}

	/**
	 * @param WC_Payment_Token_PMGW $token
	 * @return string
	 */
	protected function getSavedPaymentTokenOptionsHtml($token) {
		return sprintf(
			'<li class="woocommerce-SavedPaymentMethods-token"><input id="wc-%1$s-payment-token-%2$s" type="radio" name="%1$s-payment-subselector" value="%2$s" style="width:auto;" class="woocommerce-SavedPaymentMethods-tokenInput" %4$s /><label for="wc-%1$s-payment-token-%2$s">%3$s</label></li>',
			esc_attr($this->id),
			esc_attr($token->get_id()),
			esc_html($token->get_display_name()),
			checked($token->is_default(), true, false)
		);
	}

	/**
	 * @return string
	 */
	protected function getNewPaymentMethodOptionHtml() {
		$label = __('New card', BF_PMGW_PLUGIN);
		$html  = sprintf(
			'<li class="woocommerce-SavedPaymentMethods-new"><input id="pmgw_%2$s_pay_with_new_card" type="radio" name="%1$s-payment-subselector" value="pay_with_new_card" style="width:auto;" class="woocommerce-SavedPaymentMethods-tokenInput" /><label for="pmgw_%2$s_pay_with_new_card">%3$s</label></li>',
			esc_attr($this->id),
			esc_attr($this->providerName),
			esc_html($label)
		);

		return apply_filters('woocommerce_payment_gateway_get_new_payment_method_option_html', $html, $this);
	}

	/**
	 * @return string
	 */
	protected function getPayWithCardRegistrationRadioHtml() {
		$label = __('Card registration', BF_PMGW_PLUGIN);
		$html  = sprintf(
			'<li class="woocommerce-SavedPaymentMethods-new"><input id="pmgw_%2$s_tokenization_card" type="radio" name="%1$s-payment-subselector" value="tokenization_card" style="width:auto;" class="woocommerce-SavedPaymentMethods-tokenInput" /><label for="pmgw_%2$s_tokenization_card">%3$s</label></li>',
			esc_attr($this->id),
			esc_attr($this->providerName),
			esc_html($label)
		);

		return apply_filters('woocommerce_payment_gateway_get_new_payment_method_option_html', $html, $this);
	}

	/**
	 * @return string
	 */
	protected function getPayWithCardRegistrationHtml() {
		$label = __('Pay with card registration', BF_PMGW_PLUGIN);
		$html  = sprintf(
			'<ul class="woocommerce-SavedPaymentMethods wc-saved-payment-methods"><li class="woocommerce-SavedPaymentMethods-new"><input id="%1$s_tokenization_card" type="checkbox" name="%1$s-payment-subselector" value="tokenization_card" style="width:auto;" class="woocommerce-SavedPaymentMethods-tokenInput" /><label for="%1$s_tokenization_card">%2$s</label></li></ul>',
			esc_attr($this->id),
			esc_html($label)
		);

		return apply_filters('woocommerce_payment_gateway_get_new_payment_method_option_html', $html, $this);
	}

	protected function setPockets() {
		$this->pockets = array();
	}

	/**
	 * @return string
	 */
	protected function getProviderSuffix() {
		return (isset($this->className) ? $this->className : $this->providerName);
	}

	/**
	 * @return string
	 */
	protected function getDisplayNameToken() {
		return sprintf('provider_%s_display_name', $this->getProviderSuffix());
	}

	/**
	 * @return string
	 */
	protected function getDisplayDescriptionToken() {
		return sprintf('provider_%s_display_description', $this->getProviderSuffix());
	}

	/**
	 * @return string
	 */
	protected function getDisplayName() {
		if ('yes' === $this->get_option('translate_enabled', 'no')) {
			$displayNameToken = $this->getDisplayNameToken();
			$displayName = __($displayNameToken, BF_PMGW_PLUGIN);

			return $displayName != $displayNameToken ? $displayName : "";
		}

		return __($this->get_option('displayname'), BF_PMGW_PLUGIN);
	}

	/**
	 * @return string
	 */
	protected function getDisplayDescription() {
		if ('yes' === $this->get_option('translate_enabled', 'no')) {
			$displayDescriptionToken = $this->getDisplayDescriptionToken();
			$displayDescription = __($displayDescriptionToken, BF_PMGW_PLUGIN);

			return $displayDescription != $displayDescriptionToken ? $displayDescription : "";
		}

		return __($this->get_option('description'), BF_PMGW_PLUGIN);
	}

	/**
	 * @return string
	 */
	protected function getLocaleSlug() {
		//WPML
		if (defined('ICL_SITEPRESS_VERSION') && defined('ICL_PLUGIN_INACTIVE') && !ICL_PLUGIN_INACTIVE && defined('ICL_LANGUAGE_CODE')) {
			if (isset($_GET['lang'])) {
				return '?lang=' . ICL_LANGUAGE_CODE . '&';
			}

			return '/' . ICL_LANGUAGE_CODE . '/?';
		}
		//WP Multilang
		if (function_exists('wpm_get_language')) {
			return '/' . wpm_get_language() . '/?';
		}

		return '?';
	}

	/**
	 * @return false|mixed
	 */
	protected function getPaymentSubselectorFromPOST()
	{
		$postKey = BF_PMGW_ID . $this->providerName . '-payment-subselector';

		if (!array_key_exists($postKey, $_POST)) {
			return false;
		}

		return $_POST[$postKey];
	}

	/**
	 * @return bool|int
	 */
	protected function hasSelectedTokenizedCard() {
		$data = $this->getPaymentSubselectorFromPOST();

		return $data === false ? false : (($this->getPayWithNewCardCheckBox($data) || $this->getPayWithTokenizationCheckBox($data)) ? false : (int)$data);
	}

	/**
	 * @param mixed $data
	 * @return bool
	 */
	protected function getPayWithTokenizationCheckBox($data) {
		return ($data == 'tokenization_card');
	}

	/**
	 * @param mixed $data
	 * @return bool
	 */
	protected function getPayWithNewCardCheckBox($data) {
		return ($data == 'pay_with_new_card');
	}

	/**
	 * @return string
	 */
	protected function startCustomBlock() {
		return '<ul class="woocommerce-SavedPaymentMethods wc-saved-payment-methods" data-count="' . esc_attr(count($this->get_tokens())) . '">';
	}

	/**
	 * @return string
	 */
	protected function endCustomBlock() {
		return '</ul>';
	}

	/**
	 * @param WC_Order $order
	 * @param WC_Payment_Token_CC $token
	 * @param bool $isOneClickPaymentPSD2
	 * @return array
	 * @throws Exception
	 */
	protected function processOneClickPayment(WC_Order $order, $token, $isOneClickPaymentPSD2 = false) {
		global $wpdb;

		if (!$this->api instanceof \BigFish\PaymentGateway) {
			throw new Exception('API initialization error!');
		}

		/**
		 * InitRP BIG FISH Payment Gateway
		 *
		 */
		$request = $isOneClickPaymentPSD2 ? new \BigFish\PaymentGateway\Request\Init() : new \BigFish\PaymentGateway\Request\InitRP();
		$request
			->setResponseUrl($this->getResponseUrl())
			->setAmount($order->get_total())
			->setCurrency(get_woocommerce_currency())
			->setReferenceTransactionId($token->get_token())
			->setOrderId($order->get_id())
			->setUserId((empty($order->get_customer_id())) ? '' : $order->get_customer_id())
			->setModuleName($this->getModuleName())
			->setModuleVersion(BF_PMGW_VERSION);

		$request->setInfo($this->getInfoByOrder($order));

		if ($isOneClickPaymentPSD2) {
			$request->setAutoCommit($this->pmgwData['autoCommit']);
			$request->setProviderName($this->providerName);
			$request->setPaymentRegistration(false);
			$request->setOneClickPayment(true);
		}

		$response = $this->api->send($request);

		$dbData = array(
			'order_id' => $order->get_id(),
			'provider_name' => (isset($this->className) ? $this->className : $this->providerName),
			'auto_commit' => (int)$this->pmgwData['autoCommit'],
			'amount' => $order->get_total()
		);

		if (!empty($order->get_customer_id())) {
			$dbData['user_id'] = $order->get_customer_id();
		}

		if ((int)$wpdb->insert(self::getTransactionsTableName(), $dbData)) {
			$bigfishpaymentgateway_id = $wpdb->insert_id;
			$wpdb->insert(self::getLogsTableName(), array('bigfishpaymentgateway_id' => $bigfishpaymentgateway_id, 'status' => $order->get_status(), 'message' => 'Init: ' . print_r($response, true)));
		} else {
			throw new Exception(__('Database insert error!', BF_PMGW_PLUGIN));
		}

		if ($response->ResultCode == "SUCCESSFUL" && $response->TransactionId) {
			$wpdb->update(self::getTransactionsTableName(), array('transaction_id' => $response->TransactionId),
				array('bigfishpaymentgateway_id' => $bigfishpaymentgateway_id));

			/**
			 * Start BIG FISH Payment Gateway
			 *
			 */
			if ($isOneClickPaymentPSD2) {
				$url = $this->api->getRedirectUrl((new \BigFish\PaymentGateway\Request\Start())->setTransactionId($response->TransactionId));
			} else {
				$this->api->send((new \BigFish\PaymentGateway\Request\StartRP())->setTransactionId($response->TransactionId));
			}

			return array(
				'result' => 'success',
				'redirect' => $isOneClickPaymentPSD2 ? $url : $this->getResponseUrl() . '&' . http_build_query(array('TransactionId' => $response->TransactionId))
			);
		}

		throw new Exception($response->ResultMessage);
	}

	/**
	 * @param WC_Order $order
	 * @return array
	 * @throws Exception
	 */
	protected function processNormalPayment(WC_Order $order) {
		global $wpdb;

		if (!$this->api instanceof \BigFish\PaymentGateway) {
			throw new Exception('API initialization error!');
		}

		$cardRegistration = false;

		/**
		 * Init BIG FISH Payment Gateway
		 *
		 */
		$request = new \BigFish\PaymentGateway\Request\Init();

		$request->setProviderName($this->providerName)
			->setResponseUrl($this->getResponseUrl())
			->setAmount($order->get_total())
			->setCurrency(get_woocommerce_currency())
			->setOrderId($order->get_id())
			->setUserId((empty($order->get_customer_id())) ? '' : $order->get_customer_id())
			->setLanguage(strtoupper($this->getLang()))
			->setAutoCommit($this->pmgwData['autoCommit'])
			->setModuleName($this->getModuleName())
			->setModuleVersion(BF_PMGW_VERSION);

		/**
		 * Set OTP2 data
		 *
		 */
		if ($this->providerName == \BigFish\PaymentGateway::PROVIDER_OTP_TWO_PARTY) {
			$request->setOtpCardNumber($_POST["OtpCardNumber"])
				->setOtpExpiration($_POST["OtpExpiration"])
				->setOtpCvc($_POST["OtpCvc"]);
		}

		/**
		 * Set OTP SZÉP Card data
		 *
		 */
		if (property_exists($this, 'className') && $this->className == \BigFishPaymentGatewayOTPSZEP::CLASS_NAME) {

			$selectedOtpSzepPocketId = $this->getSelectedPocketId(BigFishPaymentGatewayOTPSZEP::POCKET_ID);

			if (!empty($selectedOtpSzepPocketId)) {
				$request->setOtpCardPocketId($selectedOtpSzepPocketId);
			}
		}

		/**
		 * Set MBH SZÉP Card data
		 *
		 */
		if ($this->providerName == \BigFish\PaymentGateway::PROVIDER_MKB_SZEP) {

			$selectedPocketId = $this->getSelectedPocketId(BigFishPaymentGatewayMKBSZEP::POCKET_ID);

			$request->setMkbSzepCafeteriaId($selectedPocketId)
					->setGatewayPaymentPage(true);
		}

		/**
		 * Set KHB SZÉP Card data
		 *
		 */
		if ($this->providerName == \BigFish\PaymentGateway::PROVIDER_KHB_SZEP) {
			$selectedPocketId = $this->getSelectedPocketId(BigFishPaymentGatewayKHBSZEP::POCKET_ID);
			$this->pmgwData['extra']['KhbCardPocketId'] = $selectedPocketId;
		}

		/**
		 * Set PayPal data
		 *
		 */
		if ($this->providerName == \BigFish\PaymentGateway::PROVIDER_PAYPAL) {
			if (
				isset($this->settings['recurringPaymentEnable']) && (int)$this->settings['recurringPaymentEnable'] &&
				isset($this->settings['recurringPaymentBillingPeriods']) && !empty($this->settings['recurringPaymentBillingPeriods'])
			) {
				if (
					isset($_POST['recurringPaymentEnable']) && (int)$_POST['recurringPaymentEnable'] &&
					isset($_POST['recurringPaymentBillingPeriod']) && !empty($_POST['recurringPaymentBillingPeriod'])
				) {
					$this->pmgwData['extra']['REFERENCE']['BILLINGPERIOD'] = $_POST['recurringPaymentBillingPeriod'];
					$this->pmgwData['extra']['REFERENCE']['BILLINGFREQUENCY'] = 1;
					$this->pmgwData['extra']['REFERENCE']['PROFILESTARTDATE'] = gmdate("Y-m-d\TH:i:s\Z", time());
					$this->pmgwData['extra']['REFERENCE']['DESC'] = __('Recurring payment', BF_PMGW_PLUGIN);

					$request->setOneClickPayment(true);
				}
			}
		}

		/**
		 * Set One Click Payment
		 *
		 */
		if (in_array($this->providerName, array(
			\BigFish\PaymentGateway::PROVIDER_BORGUN2,
			\BigFish\PaymentGateway::PROVIDER_BARION2,
			\BigFish\PaymentGateway::PROVIDER_OTP_SIMPLE,
			\BigFish\PaymentGateway::PROVIDER_SAFERPAY,
			\BigFish\PaymentGateway::PROVIDER_VIRPAY,
			\BigFish\PaymentGateway::PROVIDER_GP,
			\BigFish\PaymentGateway::PROVIDER_PAYPALREST,
			\BigFish\PaymentGateway::PROVIDER_PAYUREST,
			\BigFish\PaymentGateway::PROVIDER_KHB,
		))) {
			if (!empty($order->get_customer_id()) && isset($this->settings[$this->providerName . 'CardRegistrationFunctions']) && (int)$this->settings[$this->providerName . 'CardRegistrationFunctions']) {
				if ($this->getPayWithTokenizationCheckBox($this->getPaymentSubselectorFromPOST())) {
					// If the provider is not KHB proceed as before
					if ($this->providerName != \BigFish\PaymentGateway::PROVIDER_KHB) {
						$cardRegistration = true;
						$request->setOneClickPayment(true);
						$request->setOneClickForcedRegistration(true);
					} else {
						// For KHB provider we create the PSD2 compliant CIT payment
						$cardRegistration = true;
						$request->setPaymentRegistration(true);
						$request->setPaymentRegistrationType(\BigFish\PaymentGateway::PAYMENT_REGISTRATION_TYPE_CUSTOMER_INITIATED);
					}
				}
			}
		}

		/**
		 * Set Info
		 */
		$request->setInfo($this->getInfoByOrder($order));

		/**
		 * Set Extra
		 */
		if (isset($this->pmgwData['extra']) && is_array($this->pmgwData['extra'])) {
			$request->setExtra($this->pmgwData['extra']);
		}

		$response = $this->api->send($request);

		$dbData = array(
			'order_id' => $order->get_id(),
			'provider_name' => (isset($this->className) ? $this->className : $this->providerName),
			'auto_commit' => (int)$this->pmgwData['autoCommit'],
			'amount' => $order->get_total()
		);

		if (!empty($order->get_customer_id())) {
			$dbData['user_id'] = $order->get_customer_id();
		}

		if ((int)$wpdb->insert(self::getTransactionsTableName(), $dbData)) {
			$bigfishpaymentgateway_id = $wpdb->insert_id;
			$wpdb->insert(self::getLogsTableName(), array('bigfishpaymentgateway_id' => $bigfishpaymentgateway_id, 'status' => $order->get_status(), 'message' => 'Init: ' . print_r($response, true)));
		} else {
			throw new Exception(__('Database insert error!', BF_PMGW_PLUGIN));
		}

		if ($response->ResultCode == "SUCCESSFUL" && $response->TransactionId) {
			$wpdb->update(self::getTransactionsTableName(), array('transaction_id' => $response->TransactionId), array('bigfishpaymentgateway_id' => $bigfishpaymentgateway_id));

			/**
			 * Start BIG FISH Payment Gateway
			 *
			 */
			$url = $this->api->getRedirectUrl((new \BigFish\PaymentGateway\Request\Start())->setTransactionId($response->TransactionId));

			return array(
				'result' => 'success',
				'redirect' => $url . ($cardRegistration ? "&normalPayment" : "")
			);

		}

		throw new Exception($response->ResultMessage);
	}

	/**
	 * Process payment
	 *
	 * @param integer $order_id
	 * @param bool $isOneClickPaymentPSD2
	 * @access public
	 * @return array|boolean
	 */
	public function process_payment($order_id, $isOneClickPaymentPSD2 = false) {
		$order = new WC_Order($order_id);

		try {
			/**
			 * Set BIG FISH Payment Gateway config
			 *
			 */
			$providerName = property_exists($this, 'className') ? $this->className : $this->providerName;
			$this->api = BigFishPaymentGateway::setApiConfig(BigFishPaymentGateway::getApiSettingsByProvider($providerName));

			$selectedTokenId = $this->hasSelectedTokenizedCard();
			if ($selectedTokenId) {

				/** @var WC_Payment_Token_CC $token */
				$token = WC_Payment_Tokens::get($selectedTokenId);

				if (is_null($token) || get_current_user_id() !== $token->get_user_id()) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					throw new Exception(__('Invalid payment token.', BF_PMGW_PLUGIN));
				}

				return $this->processOneClickPayment($order, $token, $isOneClickPaymentPSD2);
			}

			return $this->processNormalPayment($order);
		} catch(Exception $e) {
			/**
			 * Show error on site
			 */
			wc_add_notice($e->getMessage(), 'error');
			$order->add_order_note($e->getMessage());

			return false;
		}
	}

	/**
	 * @return string
	 */
	protected function getResponseUrl() {
		return site_url() . $this->getLocaleSlug() . 'wc-api=' . strtolower(BF_PMGW_ID);
	}

	/**
	 * @return string
	 */
	protected function getLang() {
		return strtolower(substr(get_bloginfo('language'), 0, 2));
	}

	/**
	 * @return string
	 */
	protected function getModuleName() {
		global $woocommerce, $wp_version;
		return 'WP (' . $wp_version . ') WC (' . $woocommerce->version . ')';
	}

	/**
	 * @param WC_Order $order
	 * @return \BigFish\PaymentGateway\Data\Info
	 */
	protected function getInfoByOrder($order) {
		$info = new \BigFish\PaymentGateway\Data\Info();

		if (
			!empty($order->get_shipping_first_name()) ||
			!empty($order->get_shipping_last_name()) ||
			!empty($order->get_shipping_address_1()) ||
			!empty($order->get_shipping_address_2()) ||
			!empty($order->get_shipping_city()) ||
			!empty($order->get_shipping_country()) ||
			!empty($order->get_shipping_postcode())
		) {
			$shippingData = new \BigFish\PaymentGateway\Data\Info\Order\InfoOrderShippingData();
			$shippingData
				->setFirstName(mb_substr($order->get_shipping_first_name(), 0, self::MAX_NAME_LENGTH, self::MB_DEFAULT_ENCODING))
				->setLastName(mb_substr($order->get_shipping_last_name(), 0, self::MAX_NAME_LENGTH, self::MB_DEFAULT_ENCODING))
				->setEmail(mb_substr($order->get_billing_email(), 0, self::MAX_EMAIL_LENGTH, self::MB_DEFAULT_ENCODING))
				->setPhone(mb_substr($order->get_billing_phone(), 0, self::MAX_PHONE_LENGTH, self::MB_DEFAULT_ENCODING))
				->setLine1(mb_substr($order->get_shipping_address_1(), 0, self::MAX_ADDRESS_LINE_LENGTH, self::MB_DEFAULT_ENCODING))
				->setLine2(mb_substr($order->get_shipping_address_2(), 0, self::MAX_ADDRESS_LINE_LENGTH, self::MB_DEFAULT_ENCODING))
				->setCity(mb_substr($order->get_shipping_city(), 0, self::MAX_CITY_LENGTH, self::MB_DEFAULT_ENCODING))
				->setCountry(mb_substr($order->get_shipping_country(), 0, self::MAX_COUNTRY_LENGTH, self::MB_DEFAULT_ENCODING))
				->setCountryCode2(mb_substr($order->get_shipping_country(), 0, self::MAX_COUNTRY_CODE_2_LENGTH, self::MB_DEFAULT_ENCODING))
				->setPostalCode(mb_substr($order->get_shipping_postcode(), 0, self::MAX_POSTAL_CODE_LENGTH, self::MB_DEFAULT_ENCODING));
			$info->setObject($shippingData);
		} else {
			$info->setObject((new \BigFish\PaymentGateway\Data\Info\Order\InfoOrderGeneral())->setShippingMethod(self::SCA_SHIPPING_METHOD_SERVICE));
		}

		$billingData = new \BigFish\PaymentGateway\Data\Info\Order\InfoOrderBillingData();
		$billingData
			->setFirstName(mb_substr($order->get_billing_first_name(), 0, self::MAX_NAME_LENGTH, self::MB_DEFAULT_ENCODING))
			->setLastName(mb_substr($order->get_billing_last_name(), 0, self::MAX_NAME_LENGTH, self::MB_DEFAULT_ENCODING))
			->setEmail(mb_substr($order->get_billing_email(), 0, self::MAX_EMAIL_LENGTH, self::MB_DEFAULT_ENCODING))
			->setPhone(mb_substr($order->get_billing_phone(), 0, self::MAX_PHONE_LENGTH, self::MB_DEFAULT_ENCODING))
			->setLine1(mb_substr($order->get_billing_address_1(), 0, self::MAX_ADDRESS_LINE_LENGTH, self::MB_DEFAULT_ENCODING))
			->setLine2(mb_substr($order->get_billing_address_2(), 0, self::MAX_ADDRESS_LINE_LENGTH, self::MB_DEFAULT_ENCODING))
			->setCity(mb_substr($order->get_billing_city(), 0, self::MAX_CITY_LENGTH, self::MB_DEFAULT_ENCODING))
			->setCountry(mb_substr($order->get_billing_country(), 0, self::MAX_COUNTRY_LENGTH, self::MB_DEFAULT_ENCODING))
			->setCountryCode2(mb_substr($order->get_billing_country(), 0, self::MAX_COUNTRY_CODE_2_LENGTH, self::MB_DEFAULT_ENCODING))
			->setPostalCode(mb_substr($order->get_billing_postcode(), 0, self::MAX_POSTAL_CODE_LENGTH, self::MB_DEFAULT_ENCODING));
		$info->setObject($billingData);

		$browser = new \BigFish\PaymentGateway\Data\Info\Customer\InfoCustomerBrowser();
		$browser->setUserAgent(mb_substr($order->get_customer_user_agent(), 0, self::MAX_USER_AGENT_LENGTH, self::MB_DEFAULT_ENCODING));
		$info->setObject($browser);

		$customer = new \BigFish\PaymentGateway\Data\Info\Customer\InfoCustomerGeneral();

		$userData = WP_User::get_data_by('ID', $order->get_customer_id());
		if ($userData !== false) {
			$user = new WP_User;
			$user->init($userData);

			$customer
				->setEmail(mb_substr($user->user_email, 0, self::MAX_EMAIL_LENGTH, self::MB_DEFAULT_ENCODING))
				->setFirstName(mb_substr($user->first_name, 0, self::MAX_NAME_LENGTH, self::MB_DEFAULT_ENCODING))
				->setLastName(mb_substr($user->last_name, 0, self::MAX_NAME_LENGTH, self::MB_DEFAULT_ENCODING));
		} else {
			$customer
				->setEmail(mb_substr($order->get_billing_email(), 0, self::MAX_EMAIL_LENGTH, self::MB_DEFAULT_ENCODING))
				->setFirstName(mb_substr($order->get_billing_first_name(), 0, self::MAX_NAME_LENGTH, self::MB_DEFAULT_ENCODING))
				->setLastName(mb_substr($order->get_billing_last_name(), 0, self::MAX_NAME_LENGTH, self::MB_DEFAULT_ENCODING));
		}

		$customer->setIp($order->get_customer_ip_address());
		$info->setObject($customer);

		return $info;
	}

	/**
	 * @param string $providerPocketId
	 * @return string
	 */
	protected function getSelectedPocketId($providerPocketId) {
		//old settings structure
		$selectedPocketId = $this->settings[$providerPocketId];

		//multi selector settings structure
		if (is_array($this->settings[$providerPocketId])) {

			$selectedPocketId = array_key_exists($providerPocketId, $_POST) ? $_POST[$providerPocketId] : 0;

			if (!empty($selectedPocketId) && !in_array($selectedPocketId, $this->settings[$providerPocketId])) {
				$selectedPocketId = 0;
			}

			// only one pocket enabled
			if (empty($selectedPocketId)) {
				$selectedPocketId = $this->settings[$providerPocketId][0];
			}
		}

		return (string)$selectedPocketId;
	}

	/**
	 * Set received page content
	 *
	 * @access public
	 * @return void
	 */
	public function received_page() {
		global $wp, $wpdb;

		if (is_order_received_page()) {
			/**
			 * Set $order_id by order-received
			 */
			$order_id = apply_filters('woocommerce_thankyou_order_id', absint($wp->query_vars['order-received']));

			/**
			 * Get response message
			 *
			 */
			$this->transaction = $wpdb->get_row("SELECT response_message FROM " . self::getTransactionsTableName() . " WHERE order_id=" . (int)$order_id . " ORDER BY bigfishpaymentgateway_id DESC LIMIT 1");

			add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_content'));
		}
	}

	/**
	 * Output for the order received page
	 *
	 * @access public
	 * @return void
	 */
	public function thankyou_content() {
		echo wpautop(wptexturize(wp_kses_post($this->transaction->response_message)));
	}

	/**
	 * Process refund
	 *
	 * @param integer $order_id
	 * @param float $amount
	 * @param string $reason
	 * @access public
	 * @return boolean
	 */
	public function process_refund($order_id, $amount = null, $reason = '') {
		global $wpdb;

		$transaction = $wpdb->get_row("SELECT * FROM " . self::getTransactionsTableName() . " WHERE order_id=" . (int)$order_id . " ORDER BY bigfishpaymentgateway_id DESC LIMIT 1");

		if (!empty($transaction->transaction_id)) {
			$order = new WC_Order($order_id);

			/**
			 * Set BIG FISH Payment Gateway config
			 *
			 */
			$this->api = BigFishPaymentGateway::setApiConfig(BigFishPaymentGateway::getApiSettingsByProvider($this->providerName));

			/**
			 * Refund transaction request from BIG FISH Payment Gateway server
			 *
			 */
			$response = $this->api->send((new \BigFish\PaymentGateway\Request\Refund())
				->setTransactionId($transaction->transaction_id)
				->setAmount($amount)
			);

			$wpdb->insert(self::getLogsTableName(), array('bigfishpaymentgateway_id' => $transaction->bigfishpaymentgateway_id, 'status' => $order->get_status(), 'message' => 'Refund: ' . print_r($response, true)));

			if ($response->ResultCode == "SUCCESSFUL") {
				$getResult = $this->api->send((new \BigFish\PaymentGateway\Request\Result())->setTransactionId($transaction->transaction_id));

				if ($getResult->ResultCode == "SUCCESSFUL") {
					$this->email = '<div style="margin-top: 16px;">' . __('Provider transaction ID', BF_PMGW_PLUGIN) . ': ' . $getResult->ProviderTransactionId . '</div>';

					add_action('woocommerce_email_order_meta', array($this, 'email_content'));
				}

				$order->add_order_note(__('Refund', BF_PMGW_PLUGIN) . ': ' . __('SUCCESSFUL', BF_PMGW_PLUGIN) . ': ' . $amount . ' ' . get_woocommerce_currency() . '<br />' . $reason);
				return true;
			} else {
				$order->add_order_note(__('Refund', BF_PMGW_PLUGIN) . ': ' . __('FAILED', BF_PMGW_PLUGIN) . ': ' . $amount . ' ' . get_woocommerce_currency() . '<br />' . $response->ResultMessage);
				return new WP_Error('invalid_post', $response->ResultMessage);
			}
		}

		return false;
	}

	/**
	 * Add content to the WC emails
	 *
	 * @access public
	 * @return void
	 */
	public function email_content() {
		echo wpautop(wptexturize($this->email)) . PHP_EOL;
	}

	/**
	 * @return string
	 */
	public static function getTransactionsTableName() {
		global $wpdb;
		return $wpdb->prefix . "woocommerce_" . strtolower(BF_PMGW_ID) . "_transactions";
	}

	/**
	 * @return string
	 */
	public static function getLogsTableName() {
		global $wpdb;
		return $wpdb->prefix . "woocommerce_" . strtolower(BF_PMGW_ID) . "_logs";
	}
}
