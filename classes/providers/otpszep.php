<?php

/**
 * BIG FISH Payment Gateway OTP SZÉP Card provider
 * 
 */
class BigFishPaymentGatewayOTPSZEP extends BigFishPaymentGatewayProvider {

	protected $className = self::CLASS_NAME;
	
	protected $providerName = 'OTP';
	
	protected $providerLongName = 'OTP SZÉP Card';

	const CLASS_NAME = 'OTPSZEP';

	const POCKET_ID = 'OtpCardPocketId';

	protected $pmgwData = array(
		'autoCommit' => true,
	);
	
	/**
	 * Contructor
	 * 
	 * @access public
	 * @return void
	 */		
	public function __construct() {
		$this->setPockets();

		parent::__construct();

		if (is_array($this->settings[static::POCKET_ID]) && count($this->settings[static::POCKET_ID]) > 1 && !empty($this->settings[static::POCKET_ID])) {
			$this->description .= $this->getPacketHtmlSelector($this->settings[static::POCKET_ID]);
		}
	}

	/**
	 * Check if the gateway is available for use.
	 *
	 * @return bool
	 */
	public function is_available() {
		$is_available = ( 'yes' === $this->enabled );

		if (!isset($this->settings[static::POCKET_ID]) || empty($this->settings[static::POCKET_ID])) {
			$is_available = false;
		}

		return $is_available;
	}

	protected function setPockets()
	{
		$this->pockets = self::pocketList();
	}

	/**
	 * @return array
	 */
	public static function pocketList()
	{
		return array(
			'09' => __('Accommodation', BF_PMGW_PLUGIN),
			'07' => __('Hospitality', BF_PMGW_PLUGIN),
			'08' => __('Leisure', BF_PMGW_PLUGIN),
			'01' => __('Food voucher', BF_PMGW_PLUGIN),
			'02' => __('Hot meals voucher', BF_PMGW_PLUGIN),
			'03' => __('Back-to-school voucher', BF_PMGW_PLUGIN),
			'04' => __('Culture voucher', BF_PMGW_PLUGIN),
			'05' => __('Gift voucher', BF_PMGW_PLUGIN),
			'06' => __('Sport voucher', BF_PMGW_PLUGIN),
		);
	}
	
	/**
	 * set form
	 *
	 * @access protected
	 * @return void
	 */
	protected function set_form() {
		$this->form_fields = array(
			'enabled' => array(
				'title' => __('Active', BF_PMGW_PLUGIN),
				'type' => 'checkbox',
			),
			'displayname' => array(
				'title' => __('Display name', BF_PMGW_PLUGIN),
				'type' => 'text',
				'default' => $this->providerLongName,
			),
			'description' => array(
				'title' => __('Description', BF_PMGW_PLUGIN),
				'type' => 'textarea',
			),
			'storeName' => array(
					'title' => __('SZÉP Store name', BF_PMGW_PLUGIN),
					'type' => 'text',
					'default' => 'sdk_test',
					'desc_tip' => false
			),
			'apiKey' => array(
				'title' => __('SZÉP API key', BF_PMGW_PLUGIN),
				'type' => 'text',
				'default' => '86af3-80e4f-f8228-9498f-910ad',
				'desc_tip' => false
			),
			'OtpCardPocketId' => array(
				'title' => __('Available pocket', BF_PMGW_PLUGIN),
				'type' => 'multiselect',
				'css' => 'min-height: 180px;',
				'options' => $this->pockets,
				'description' => __('Please, select a pocket', BF_PMGW_PLUGIN) . ' ' . __( 'You can also select one or more pocket', BF_PMGW_PLUGIN),
			),
			'translate_enabled' => array(
				'title'   => __( 'Use translation function', BF_PMGW_PLUGIN),
				'type'    => 'checkbox',
				'label'   => __( 'Enable to use system translation from .po file', BF_PMGW_PLUGIN ),
				'description' => sprintf(
					__('You find with this tokens/keys: %s, %s', BF_PMGW_PLUGIN),
					$this->getDisplayNameToken(),
					$this->getDisplayDescriptionToken()
				)
			)
		);
	}	
}
