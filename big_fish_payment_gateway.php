<?php

/**
 * Plugin Name: BIG FISH Payment Gateway for WooCommerce
 * Plugin URI: https://www.paymentgateway.hu/fejlesztoknek/platformok/woocommerce
 * Description: BIG FISH Payment Gateway system provides more different payment solutions for webshops, where all the payment methods of the webshop can be managed in one place.
 * Version: 3.0.1
 * Requires at least: 4.9
 * Requires PHP: 7.2
 * Author: BIG FISH Payment Services Ltd.
 * Author URI: https://www.paymentgateway.hu/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

/**
 * Include autoloader to BIG FISH Payment Gateway
 * 
 */
require(realpath(dirname(__FILE__)) . '/api/BigFish/PaymentGateway/Autoload.php');

add_action('plugins_loaded', 'BigFishPaymentGatewayWoo');

function BigFishPaymentGatewayWoo() {
	if (!class_exists('WC_Payment_Gateway')) {
		return;
	}
	
	/**
	 * Register BIG FISH Payment Gateway autoloader to use namespace
	 * 
	 */
	\BigFish\PaymentGateway\Autoload::register();

	/**
	 * BIG FISH Payment Gateway WooCommerce ID
	 * 
	 */
	define('BF_PMGW_ID' , 'BigFishPaymentGateway');
	
	/**
	 * BIG FISH Payment Gateway title
	 * 
	 */
	define('BF_PMGW_TITLE' , 'BIG FISH Payment Gateway');
	
	/**
	 * BIG FISH Payment Gateway WooCommerce plugin
	 * 
	 */
	define('BF_PMGW_PLUGIN' , 'big_fish_payment_gateway');
	
	/**
	 * BIG FISH Payment Gateway WooCommerce version
	 * 
	 */
	define('BF_PMGW_VERSION' , '3.0.1');

	require(realpath(dirname(__FILE__)) . '/classes/BigFishPaymentGatewayProvider.php');

	/**
	 * BIG FISH Payment Gateway for WooCommerce
	 * 
	 */
	class BigFishPaymentGateway extends WC_Payment_Gateway
	{
		const PLUGIN_CONFIG_PREFIX = 'woocommerce_';

		protected $email;

		/**
		 * Contructor
		 *
		 * @access public
		 */
		public function __construct() {
			/**
			 *  Load BIG FISH Payment Gateway translations
			 * 
			 */
			load_plugin_textdomain(BF_PMGW_PLUGIN, false, dirname(plugin_basename(__FILE__)) . '/languages/');

			$this->id = BF_PMGW_ID;
			$this->title = BF_PMGW_TITLE;
			$this->method_title = $this->title;

			/**
			 * Set BIG FISH Payment Gateway plugin admin form fields
			 * 
			 */
			$this->set_form();

			$this->init_settings();
			
			if (is_admin()) {
				/**
				 * Check installed option
				 * 
				 */
				if (!get_option(BF_PMGW_ID . '_Installed_' . BF_PMGW_VERSION, false)) {
					/**
					 * Set BIG FISH Payment Gateway tables to database
					 * 
					 */
					$this->install();
				}
			}

			/**
			 * Add provider's classes
			 * 
			 */
			add_filter('woocommerce_payment_gateways', array($this, 'add_providers'));
			
			/**
			 * Process transaction result
			 * 
			 */
			add_action('woocommerce_api_' . strtolower($this->id), array($this, 'result'));
			
			add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
			
			/**
			 * Close transaction functions
			 * 
			 */
			add_action( 'woocommerce_order_status_completed', array($this, 'close_transaction_approved'));
			
			add_action( 'woocommerce_order_status_cancelled', array($this, 'close_transaction_declined'));

			add_action('wp', array($this, 'delete_payment_method_action'), 10);
		}

		/**
		 * set form
		 *
		 * @access private
		 * @return void
		 */
		private function set_form() {
			$this->form_fields = array(
				'configuration' => array(
					'title' => __('Settings', BF_PMGW_PLUGIN),
					'type' => 'title'
				),
				'storeName' => array(
					'title' => __('Store name', BF_PMGW_PLUGIN),
					'type' => 'text',
					'default' => 'sdk_test',
					'desc_tip' => false
				),
				'apiKey' => array(
					'title' => __('API key', BF_PMGW_PLUGIN),
					'type' => 'text',
					'default' => '86af3-80e4f-f8228-9498f-910ad',
					'desc_tip' => false
				),
				'testMode' => array(
					'title' => __('Test mode', BF_PMGW_PLUGIN),
					'type' => 'select',
					'options' => array(
						'1' => __('Yes', BF_PMGW_PLUGIN),
						'0' => __('No', BF_PMGW_PLUGIN),
					),
					'default' => '1',
				),
				'providers' => array(
					'title' => __('Available providers', BF_PMGW_PLUGIN),
					'type' => 'multiselect',
					'css' => 'min-height: 430px;',
					'options' => array(
						\BigFish\PaymentGateway::PROVIDER_BARION2 => __('Barion Smart Gateway', BF_PMGW_PLUGIN),
						\BigFish\PaymentGateway::PROVIDER_BORGUN => __('Borgun SecurePay', BF_PMGW_PLUGIN),
						\BigFish\PaymentGateway::PROVIDER_BORGUN2 => __('Borgun RPG', BF_PMGW_PLUGIN),
						\BigFish\PaymentGateway::PROVIDER_BBARUHITEL => __('MBH online trade loan', BF_PMGW_PLUGIN),
						\BigFish\PaymentGateway::PROVIDER_CIB => __('CIB Bank', BF_PMGW_PLUGIN),
						\BigFish\PaymentGateway::PROVIDER_GP => __('Global Payments', BF_PMGW_PLUGIN),
						\BigFish\PaymentGateway::PROVIDER_KHB => __('K&H Bank', BF_PMGW_PLUGIN),
						\BigFish\PaymentGateway::PROVIDER_KHB_SZEP => __('K&H SZÉP Card', BF_PMGW_PLUGIN),
						\BigFish\PaymentGateway::PROVIDER_MKB_SZEP => __('MBH SZÉP Card', BF_PMGW_PLUGIN),
						\BigFish\PaymentGateway::PROVIDER_OTP => __('OTP Bank', BF_PMGW_PLUGIN),
						\BigFish\PaymentGateway::PROVIDER_OTPARUHITEL => __('OTP Bank trade loan', BF_PMGW_PLUGIN),
						'OTPSZEP' => __('OTP SZÉP Card', BF_PMGW_PLUGIN),
						\BigFish\PaymentGateway::PROVIDER_PAYPAL => __('PayPal (Classic)', BF_PMGW_PLUGIN),
						\BigFish\PaymentGateway::PROVIDER_PAYPALREST => __('PayPal (Rest)', BF_PMGW_PLUGIN),
						\BigFish\PaymentGateway::PROVIDER_PAYSAFECARD => __('paysafecard', BF_PMGW_PLUGIN),
						\BigFish\PaymentGateway::PROVIDER_PAYU2 => __('PayU (Classic)', BF_PMGW_PLUGIN),
						\BigFish\PaymentGateway::PROVIDER_PAYUREST => __('PayU (Rest)', BF_PMGW_PLUGIN),
						\BigFish\PaymentGateway::PROVIDER_OTP_SIMPLE => __('SimplePay', BF_PMGW_PLUGIN),
						\BigFish\PaymentGateway::PROVIDER_OTP_SIMPLE_WIRE => __('SimplePay Wire', BF_PMGW_PLUGIN),
						\BigFish\PaymentGateway::PROVIDER_SOFORT => __('Sofort Banking', BF_PMGW_PLUGIN),
						\BigFish\PaymentGateway::PROVIDER_UNICREDIT => __('UniCredit Bank', BF_PMGW_PLUGIN),
						\BigFish\PaymentGateway::PROVIDER_WIRECARD => __('Wirecard', BF_PMGW_PLUGIN),
						\BigFish\PaymentGateway::PROVIDER_SAFERPAY => __('Worldline', BF_PMGW_PLUGIN),
					),
				),				
			);
		}

		/**
		 * install database tables
		 *
		 * @access private
		 * @return void
		 */		
		private function install() {
			global $wpdb;

			$sql = "CREATE TABLE `" . BigFishPaymentGatewayProvider::getTransactionsTableName() . "` (
				`bigfishpaymentgateway_id` int(11) NOT NULL AUTO_INCREMENT,
				`order_id` int(11) NOT NULL,
				`transaction_id` varchar(255) DEFAULT NULL,
				`provider_name` varchar(255) NOT NULL,
				`auto_commit` tinyint(1) NOT NULL DEFAULT '1',
				`response_message` text,
				`user_id` bigint(20) UNSIGNED DEFAULT NULL,
				`amount` double DEFAULT NULL,
				`card_registration` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
				`success_payment` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
				`created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`bigfishpaymentgateway_id`),
				UNIQUE KEY `transaction_id` (`transaction_id`)
			);
			CREATE TABLE `" . BigFishPaymentGatewayProvider::getLogsTableName() . "` (
				`bigfishpaymentgateway_log_id` int(11) NOT NULL AUTO_INCREMENT,
				`bigfishpaymentgateway_id` int(11) NOT NULL,
				`status` varchar(255) NOT NULL,
				`message` text NOT NULL,
				`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`bigfishpaymentgateway_log_id`)
			);";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);

			/**
			 * Set Installed option
			 * 
			 */
			update_option(BF_PMGW_ID . '_Installed_' . BF_PMGW_VERSION, true);
		}

		/**
		 * add providers
		 *
		 * @param array $methods
		 * @access public
		 * @return array
		 */
		public function add_providers($methods) {
			$methods[] = BF_PMGW_ID;
			/**
			 * Add providers which were selected in BIG FISH Payment Gateway checkout admin
			 *
			 */
			if (is_array($this->settings['providers']) && !empty($this->settings['providers'])) {
				foreach ($this->settings['providers'] as $providers) {
					$methods[] = $this->id . $providers;
				}
			}

			return $methods;
		}

		/**
		 * @param string $providerName
		 * @return array
		 */
		public static function getApiSettingsByProvider($providerName) {
			$pmgwSettings = self::getPmgwSettings();
			$providerSettings = self::getProviderSettings($providerName);
			
			return array_merge($pmgwSettings, $providerSettings);
		}

		/**
		 * @return array
		 */
		public static function getPmgwSettings() {
			return get_option(self::PLUGIN_CONFIG_PREFIX . BF_PMGW_ID . '_settings', null);
		}

		/**
		 * @param string $providerName
		 * @return array
		 */
		public static function getProviderSettings($providerName) {
			return get_option(self::PLUGIN_CONFIG_PREFIX . BF_PMGW_ID . $providerName . '_settings', null);
		}

		/**
		 * @param array $data
		 * @return \BigFish\PaymentGateway
		 */
		public static function setApiConfig(array $data)
		{
			$config = new \BigFish\PaymentGateway\Config();

			$config->testMode = filter_var($data['testMode'], FILTER_VALIDATE_BOOL);
			$config->storeName = $data['storeName'];
			$config->apiKey = $data['apiKey'];
			$config->encryptPublicKey = null;

			return new \BigFish\PaymentGateway($config);
		}

		/**
		 * process result
		 *
		 * @access public
		 * @return void
		 */		
		public function result() {
			global $wpdb, $wp_version, $woocommerce;

			try {
				if (!array_key_exists("TransactionId", $_GET)) {
					throw new Exception(__('No transaction ID!', BF_PMGW_PLUGIN));
				}
				
				/**
				 * Get transaction's data
				 * 
				 */
				$transaction = self::getTransaction($_GET["TransactionId"]);

				$order = new WC_Order($transaction->order_id);

				/**
				 * Set BIG FISH Payment Gateway config
				 * 
				 */
				$api = self::setApiConfig(self::getApiSettingsByProvider($transaction->provider_name));

				$details = $api->send((new \BigFish\PaymentGateway\Request\Details())
					->setTransactionId($_GET["TransactionId"])
					->setGetRelatedTransactions(false)
				);

				$providerLongName = $this->form_fields['providers']['options'][$transaction->provider_name];

				/**
				 * Get result from BIG FISH Payment Gateway server
				 * 
				 */
				$response = $api->send((new \BigFish\PaymentGateway\Request\Result())->setTransactionId($_GET["TransactionId"]));

				if ($response->ResultCode == "SUCCESSFUL") {
					$cardRegistration = false;

					$responseMessage = sprintf(
						__('successful_transaction_notification', BF_PMGW_PLUGIN),
						self::getProviderTransactionIdMessage($transaction->provider_name, $providerLongName),
						$response->ProviderTransactionId,
						$response->OrderId,
						$details->ProviderSpecificData['Created']
					);

					if (!empty($response->Anum)) {
						$responseMessage .= "<br/>" . __('Authorization number', BF_PMGW_PLUGIN) . ": <b>" . $response->Anum . "</b>";
					}

					if (in_array($transaction->provider_name, array(
						\BigFish\PaymentGateway::PROVIDER_KHB_SZEP,
						\BigFish\PaymentGateway::PROVIDER_MKB_SZEP,
						\BigFishPaymentGatewayOTPSZEP::CLASS_NAME
					))
					) {
						$pocket = null;
						if (
							$transaction->provider_name === \BigFish\PaymentGateway::PROVIDER_MKB_SZEP &&
							array_key_exists($details->ProviderSpecificData['CafeteriaCode'], BigFishPaymentGatewayMKBSZEP::pocketList())
						) {
							$pocketList = BigFishPaymentGatewayMKBSZEP::pocketList();
							$pocket = $pocketList[$details->ProviderSpecificData['CafeteriaCode']];
						}

						if (
							$transaction->provider_name === \BigFishPaymentGatewayOTPSZEP::CLASS_NAME &&
							array_key_exists($details->ProviderSpecificData['CardPocketId'], BigFishPaymentGatewayOTPSZEP::pocketList())
						) {
							$pocketList = BigFishPaymentGatewayOTPSZEP::pocketList();
							$pocket = $pocketList[$details->ProviderSpecificData['CardPocketId']];
						}

						if (
							$transaction->provider_name === \BigFish\PaymentGateway::PROVIDER_KHB_SZEP &&
							array_key_exists($details->ProviderSpecificData['CardPocketId'], BigFishPaymentGatewayKHBSZEP::pocketList())
						) {
							$pocketList = BigFishPaymentGatewayKHBSZEP::pocketList();
							$pocket = $pocketList[$details->ProviderSpecificData['CardPocketId']];
						}

						if (!empty($pocket)) {
							$responseMessage .= "<br/>" . __('Pocket', BF_PMGW_PLUGIN) . ": <b>" . $pocket . "</b>";
						}
					}

					if (in_array($transaction->provider_name, array(
						\BigFish\PaymentGateway::PROVIDER_PAYPAL,
						\BigFish\PaymentGateway::PROVIDER_BORGUN2,
						\BigFish\PaymentGateway::PROVIDER_SAFERPAY,
						\BigFish\PaymentGateway::PROVIDER_OTP_SIMPLE,
						\BigFish\PaymentGateway::PROVIDER_BARION2,
						\BigFish\PaymentGateway::PROVIDER_VIRPAY,
						\BigFish\PaymentGateway::PROVIDER_GP,
						\BigFish\PaymentGateway::PROVIDER_PAYPALREST,
						\BigFish\PaymentGateway::PROVIDER_PAYUREST,
						\BigFish\PaymentGateway::PROVIDER_KHB,
					))) {

						$wc_token = new WC_Payment_Token_CC();
						$wc_token->set_token($details->CommonData['TransactionId']);
						$wc_token->set_gateway_id(sprintf('%1s%2s', BF_PMGW_ID, $transaction->provider_name));
						$wc_token->set_user_id($order->get_customer_id());
						$wc_token->set_card_type(__('Card', BF_PMGW_PLUGIN));
						$wc_token->set_expiry_year('----'); //default
						$wc_token->set_expiry_month('--'); //default
						$wc_token->set_last4('n/a'); //default

						// For KHB provider, the PSD2 card registration logic is a bit different.
						// The OneClickPayment field is false for the registration transaction.
						// We have to query the payment registrations by a dedicated API endpoint.
						if ($transaction->provider_name === \BigFish\PaymentGateway::PROVIDER_KHB) {
							$paymentRegistrations = $api->send((new \BigFish\PaymentGateway\Request\GetPaymentRegistrations())
								->setProviderName(\BigFish\PaymentGateway::PROVIDER_KHB)
								->setUserId((string)$order->get_customer_id())
								->setPaymentRegistrationType(\BigFish\PaymentGateway::PAYMENT_REGISTRATION_TYPE_CUSTOMER_INITIATED)
							);

							if ($paymentRegistrations->ResultCode == \BigFish\PaymentGateway::RESULT_CODE_SUCCESS && !empty($paymentRegistrations->Data['CIT'])) {
								foreach ($paymentRegistrations->Data['CIT'] as $citPaymentRegistrationData) {
									if ($citPaymentRegistrationData->ReferenceTransactionId == $transaction->transaction_id) {
										$wc_token->set_expiry_year(date("Y", strtotime($citPaymentRegistrationData->PaymentDeviceExpiration)));
										$wc_token->set_expiry_month(date("m", strtotime($citPaymentRegistrationData->PaymentDeviceExpiration)));
										$wc_token->set_last4(substr($citPaymentRegistrationData->PaymentDeviceNumber, -4));
										$cardRegistration = true;
										break;
									}
								}
							}
						} else if (isset($details->ProviderSpecificData['OneClickPayment']) && !empty($details->ProviderSpecificData['OneClickPayment'])) {
							$wc_token->set_card_type($details->ProviderSpecificData['CardType']);
							if (!empty($details->ProviderSpecificData['CardExpDate'])) {
								$wc_token->set_expiry_year(date("Y", strtotime($details->ProviderSpecificData['CardExpDate'])));
								$wc_token->set_expiry_month(date("m", strtotime($details->ProviderSpecificData['CardExpDate'])));
							}

							if ($transaction->provider_name == \BigFish\PaymentGateway::PROVIDER_PAYPAL) {
								$responseMessage .= "<br/>" . __('Recurring payment period', BF_PMGW_PLUGIN) . ": <b>" . __($details->ProviderSpecificData['Extra']->REFERENCE->BILLINGPERIOD, BF_PMGW_PLUGIN) . "</b>";
							}

							if (
								$transaction->provider_name == \BigFish\PaymentGateway::PROVIDER_BORGUN2 ||
								$transaction->provider_name == \BigFish\PaymentGateway::PROVIDER_VIRPAY
							) {
								if (!isset($details->ProviderSpecificData['ParentBorgunTransactionId']) || empty($details->ProviderSpecificData['ParentBorgunTransactionId'])) {
									$cardRegistration = true;
									$wc_token->set_last4(substr($details->ProviderSpecificData['CardPan'], -4));
								}
							}

							if ($transaction->provider_name == \BigFish\PaymentGateway::PROVIDER_OTP_SIMPLE) {
								if (!isset($details->ProviderSpecificData['OneClickPayment']) || empty($details->ProviderSpecificData['ParentPayrefno'])) {
									$cardRegistration = true;
									if (empty($details->ProviderSpecificData['CardLastFourNumber'])) {
										sleep(2);
										$details = $api->send((new \BigFish\PaymentGateway\Request\Details())
											->setTransactionId($details->CommonData['TransactionId'])
											->setGetRelatedTransactions(false)
										);
									}

									$wc_token->set_last4($details->ProviderSpecificData['CardLastFourNumber']);
									$wc_token->set_card_type(__('Card', BF_PMGW_PLUGIN));
								}
							}

							if ($transaction->provider_name == \BigFish\PaymentGateway::PROVIDER_BARION2) {
								if (!isset($details->ProviderSpecificData['RecurrenceId']) || empty($details->ProviderSpecificData['RecurrenceId'])) {
									$cardRegistration = true;
									if (!empty($details->ProviderSpecificData['CardLastFourNumber'])) {
										$wc_token->set_last4($details->ProviderSpecificData['CardLastFourNumber']);
									} else {
										$wc_token->set_card_type($details->ProviderSpecificData['FundingSource']);
									}
								}
							}

							if ($transaction->provider_name == \BigFish\PaymentGateway::PROVIDER_GP) {
								if (!isset($details->ProviderSpecificData['ParentOrdernumber']) || empty($details->ProviderSpecificData['ParentOrdernumber'])) {
									$cardRegistration = true;
									$wc_token->set_last4(substr($details->ProviderSpecificData['CardPan'], -4));
								}
							}

							if ($transaction->provider_name == \BigFish\PaymentGateway::PROVIDER_SAFERPAY) {
								if (!isset($details->ProviderSpecificData['ParentSaferpayTransactionId']) || empty($details->ProviderSpecificData['ParentSaferpayTransactionId'])) {
									$cardRegistration = true;
									$wc_token->set_last4($details->ProviderSpecificData['CardLastFourNumber']);
								}
							}

							if ($transaction->provider_name == \BigFish\PaymentGateway::PROVIDER_PAYPALREST) {
								if (!isset($details->ProviderSpecificData['ParentAgreementId']) || empty($details->ProviderSpecificData['ParentAgreementId'])) {
									$cardRegistration = true;
									$wc_token->set_last4(substr($details->ProviderSpecificData['AgreementId'], -4));
									$wc_token->set_card_type(__('Wallet', BF_PMGW_PLUGIN));
								}
							}

							if ($transaction->provider_name == \BigFish\PaymentGateway::PROVIDER_PAYUREST) {
								if (!isset($details->ProviderSpecificData['ParentPayuPaymentId']) || empty($details->ProviderSpecificData['ParentPayuPaymentId'])) {
									$cardRegistration = true;
									$wc_token->set_last4(substr($details->ProviderSpecificData['CardPan'], -4));
								}
							}
						}

						if ($cardRegistration && !empty($order->get_customer_id()) && $wc_token->validate()) {
							$wc_token->save();
						}
					}

					if ($transaction->provider_name != \BigFish\PaymentGateway::PROVIDER_OTP_SIMPLE) {
						$responseMessage .= "<br /><br />" . $response->ResultMessage;
					}

					$dbData = array(
						'response_message' => $responseMessage, 
						'card_registration' => (int)$cardRegistration,
						'success_payment' => 1,
					);

					/**
					 * Set transaction result in email
					 * 
					 */
					$this->email = $responseMessage;
					
					add_action('woocommerce_email_after_order_table', array($this, 'email_content'));
					
					$order->add_order_note($responseMessage);
					
					/**
					 * Set order status
					 * 
					 */
					$order->payment_complete();
					
					/**
					 * Log result response
					 * 
					 */
					$wpdb->insert(BigFishPaymentGatewayProvider::getLogsTableName(), array('bigfishpaymentgateway_id' => $transaction->bigfishpaymentgateway_id, 'status' => $order->get_status(), 'message' => 'Result: ' . print_r($response, true)));

					/**
					 * Save response message
					 * 
					 */
					$wpdb->update(BigFishPaymentGatewayProvider::getTransactionsTableName(), $dbData, array('bigfishpaymentgateway_id' => $transaction->bigfishpaymentgateway_id));

					$woocommerce->cart->empty_cart();

					$location = $this->get_return_url($order);

					wp_safe_redirect($location);
				} elseif ($response->ResultCode == "PENDING" || $response->ResultCode == "OPEN") {
					$resultMessage = sprintf(
						__('pending_transaction_notification', BF_PMGW_PLUGIN),
						self::getProviderTransactionIdMessage($transaction->provider_name, $providerLongName),
						$response->ProviderTransactionId,
						$response->OrderId,
						$details->ProviderSpecificData['Created']
					);

					if (isset($transaction)) {
						/**
						 * Log result response
						 *
						 */
						$wpdb->insert(BigFishPaymentGatewayProvider::getLogsTableName(), array('bigfishpaymentgateway_id' => $transaction->bigfishpaymentgateway_id, 'status' => $order->get_status(), 'message' => 'Result: ' . $resultMessage));
					}

					/**
					 * Show message on site
					 *
					 */
					wc_add_notice($resultMessage, 'notice');

					$order->add_order_note($resultMessage);

					$woocommerce->cart->empty_cart();

					/**
					 * Go to cancel url
					 *
					 */
					$location = $order->get_view_order_url();
					wp_safe_redirect($location);
				} elseif ($response->ResultCode == "CANCELED" || $response->ResultCode == "TIMEOUT") {
					$message = sprintf(
						__('canceled_transaction_notification', BF_PMGW_PLUGIN),
						$response->OrderId,
						$details->ProviderSpecificData['Created']
					);

					if ($transaction->provider_name == \BigFish\PaymentGateway::PROVIDER_CIB) {
						$message .= sprintf("<br />%s: <b>%s</b>", self::getProviderTransactionIdMessage($transaction->provider_name, $providerLongName), $response->ProviderTransactionId);
					}

					throw new Exception($message);
				} else {
					throw new Exception(sprintf(
						__('error_transaction_notification', BF_PMGW_PLUGIN),
							self::getProviderTransactionIdMessage($transaction->provider_name, $providerLongName),
							$response->ProviderTransactionId,
							$response->OrderId,
							$details->ProviderSpecificData['Created']
						)
					);
				}
			} catch (Exception $e) {
				$resultMessage = $e->getMessage();

				if (isset($transaction)) {
					if (isset($response)) {
						if ($transaction->provider_name != \BigFish\PaymentGateway::PROVIDER_OTP_SIMPLE) {
							$resultMessage .= "<br /><br />" . $response->ResultMessage;
						}
					}

					/**
					 * Log result response
					 * 
					 */
					$wpdb->insert(BigFishPaymentGatewayProvider::getLogsTableName(), array('bigfishpaymentgateway_id' => $transaction->bigfishpaymentgateway_id, 'status' => $order->get_status(), 'message' => 'Result: ' . $resultMessage));
				}

				/**
				 * Show error on site
				 * 
				 */
				wc_add_notice($resultMessage, 'error');

				$order->add_order_note($resultMessage);

				/**
				 * Go to cancel url
				 * 
				 */
				$location = $order->get_cancel_order_url();
				wp_safe_redirect($location);
			}

			exit;
		}

		/**
		 * @param string $providerName
		 * @param string $providerLongName
		 * @return string
		 */
		public static function getProviderTransactionIdMessage($providerName, $providerLongName) {
			switch ($providerName) {
				case \BigFish\PaymentGateway::PROVIDER_CIB:
					return __('provider_transaction_id_message_cib', BF_PMGW_PLUGIN);
				default:
					return sprintf(__('provider_transaction_id_message_default', BF_PMGW_PLUGIN), $providerLongName);
			}
		}

		public static function delete_payment_method_action() {
			global $wp;

			if (isset($wp->query_vars['delete-payment-method'])) {
				wc_nocache_headers();

				$token_id = absint($wp->query_vars['delete-payment-method']);
				$token = WC_Payment_Tokens::get($token_id);

				if (is_null($token) || get_current_user_id() !== $token->get_user_id() || ! isset($_REQUEST['_wpnonce']) || false === wp_verify_nonce(wp_unslash($_REQUEST['_wpnonce']), 'delete-payment-method-' . $token_id)) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					wc_add_notice(__('Invalid payment token.', BF_PMGW_PLUGIN), 'error');
				} else {
					/** @var BigFishPaymentGatewayProvider $providerClass */
					$className = $token->get_gateway_id();
					$providerClass = new $className();
					if (!in_array('tokencancel', $providerClass->supports) || self::callCancelApiByTransactionId($token->get_token())) {
						WC_Payment_Tokens::delete($token_id);
						wc_add_notice(__('Payment token deleted.', BF_PMGW_PLUGIN));
					} else {
						wc_add_notice(__('Error at token delete.', BF_PMGW_PLUGIN), 'error');
					}
				}
				wp_safe_redirect(wc_get_account_endpoint_url('payment-methods'));
				exit();
			}
		}

		/**
		 * @param string $transactionId
		 * @return bool
		 */
		public static function callCancelApiByTransactionId($transactionId) {
			global $wpdb;

			$transaction = self::getTransaction($transactionId);

			$api = self::setApiConfig(self::getApiSettingsByProvider($transaction->provider_name));

			try {
				$cancelToken = $api->send((new \BigFish\PaymentGateway\Request\CancelPaymentRegistration())->setTransactionId($transactionId));
				$wpdb->insert(BigFishPaymentGatewayProvider::getLogsTableName(), array('bigfishpaymentgateway_id' => $transaction->bigfishpaymentgateway_id, 'message' => 'CancelToken: ' . print_r($cancelToken, true)));

				if ($cancelToken->ResultCode == "SUCCESSFUL") {
					return true;
				} else {
					return false;
				}
			} catch (\Exception $e) {
				return false;
			}
		}

		/**
		 * close transaction approved
		 *
		 * @param integer $order_id
		 * @access public
		 * @return void
		 */
		public function close_transaction_approved($order_id) {
			$this->close_transaction($order_id, true);
		}
		
		/**
		 * close transaction declined
		 *
		 * @param integer $order_id
		 * @access public
		 * @return void
		 */		
		public function close_transaction_declined($order_id) {
			$this->close_transaction($order_id, false);
		}

		/**
		 * close transaction
		 *
		 * @param integer $order_id
		 * @param boolean $approved
		 * @access public
		 * @return void
		 */		
		public function close_transaction($order_id, $approved) {
			global $wpdb;

			$order = new WC_Order($order_id);

			/**
			 * Get transaction's data
			 * 
			 */
			$transaction = $wpdb->get_row("SELECT * FROM " . BigFishPaymentGatewayProvider::getTransactionsTableName() . " WHERE order_id=" . (int)$order_id . " ORDER BY bigfishpaymentgateway_id DESC LIMIT 1");

			if (!empty($transaction->transaction_id) && !(int)$transaction->auto_commit) {

				/**
				 * Set BIG FISH Payment Gateway config
				 * 
				 */
				$api = self::setApiConfig(self::getApiSettingsByProvider($transaction->provider_name));
				
				$getResult = $api->send((new \BigFish\PaymentGateway\Request\Result())->setTransactionId($transaction->transaction_id));

				if ($getResult->CommitState != "PENDING") {
					return;
				}
				
				/**
				 * Close transaction request from BIG FISH Payment Gateway server
				 * 
				 */
				$response = $api->send((new \BigFish\PaymentGateway\Request\Close())
					->setTransactionId($transaction->transaction_id)
					->setApprove($approved)
				);

				/**
				 * Log close response
				 * 
				 */
				$wpdb->insert(BigFishPaymentGatewayProvider::getLogsTableName(), array('bigfishpaymentgateway_id' => $transaction->bigfishpaymentgateway_id, 'status' => $order->get_status(), 'message' => 'Close: ' . print_r($response, true)));
				
				if ($response->ResultCode == "SUCCESSFUL") {
					$order->add_order_note(__('Transaction closed', BF_PMGW_PLUGIN) . ': ' . __('SUCCESSFUL', BF_PMGW_PLUGIN) . ' (' . __(($approved ? 'Approved' : 'Declined'), BF_PMGW_PLUGIN) . ')');
				} else {
					$order->add_order_note(__('Transaction closed', BF_PMGW_PLUGIN) . ': ' . __('FAILED', BF_PMGW_PLUGIN) . '<br />' . $response->ResultMessage);
				}
			}
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
		 * @param string $transactionId
		 * @return mixed
		 */
		public static function getTransaction($transactionId)
		{
			global $wpdb;

			return $wpdb->get_row("SELECT * FROM " . BigFishPaymentGatewayProvider::getTransactionsTableName() . " WHERE transaction_id='" . $transactionId . "'");
		}
	}

	/**
	 * Include providers classes
	 * 
	 */
	$files = scandir(realpath(dirname(__FILE__)) . "/classes/providers/");

	if (is_array($files) && !empty($files)) {
		foreach ($files as $file) {
			if (!in_array($file, array('.', '..'))) {
				require(realpath(dirname(__FILE__)) . "/classes/providers/" . $file);
			}
		}
	}

	new BigFishPaymentGateway();
}
