<?php

/**
 * BIG FISH Payment Gateway OTP2 provider
 * 
 */
class BigFishPaymentGatewayOTP2 extends BigFishPaymentGatewayProvider {

	protected $providerName = 'OTP2';
	
	protected $providerLongName = 'OTP Bank (two participants)';
	
	public $supports = array('products', 'refunds');
	
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
		parent::__construct();

		$this->description .= '<div>';
		$this->description .= __('Card number', BF_PMGW_PLUGIN).':<br /><input type="text" name="OtpCardNumber" value=""><br />';
		$this->description .= __('Card expiration (mmyy)', BF_PMGW_PLUGIN).':<br /><input type="text" name="OtpExpiration" value="" style="width: 70px"><br />';
		$this->description .= __('CVC', BF_PMGW_PLUGIN).':<br /><input type="text" name="OtpCvc" value="" style="width: 60px">';
		$this->description .= '</div>';
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
			'autoCommit' => array(
				'title' => __('Authorization', BF_PMGW_PLUGIN),
				'type' => 'select',
				'options' => array(
					'1' => __('Immediate', BF_PMGW_PLUGIN),
					'0' => __('Later', BF_PMGW_PLUGIN),
				),
				'default' => '1',
			),
			'encryptPublicKey' => array(
				'title' => __('Encrypt public key', BF_PMGW_PLUGIN),
				'type' => 'textarea',
				'css' => 'min-height: 200px;',
				'default' => '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCpRN6hb8pQaDen9Qjt18P2FqSc
F2uhjKfd0DZ1t0HWtvYMmJGfM6+wgjQGDHHc4LAcLIHF1TQVLCYdbyLzsOTRUhi4
UFsW18IBznoEAx2wxiTCyzxtONpIkr5HD2E273UbXvVKA2hig2BgpOA2Poil9xtO
XIm63iVw6gjP2qDnNwIDAQAB
-----END PUBLIC KEY-----',
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

	/**
	 * Process payment
	 *
	 * @param int $order_id
	 * @param false $isOneClickPaymentPSD2
	 * @access public
	 * @return array|boolean
	 */			
	public function process_payment($order_id, $isOneClickPaymentPSD2 = false) {
		$this->pmgwData['autoCommit'] = !isset($this->settings['autoCommit']) ? true : (boolean)$this->get_option('autoCommit');
		
		return parent::process_payment($order_id);
	}	
}
