<?php

/**
 * BIG FISH Payment Gateway OTP Simple provider
 * 
 */
class BigFishPaymentGatewayOTPSimple extends BigFishPaymentGatewayProvider {

	protected $providerName = 'OTPSimple';

	protected $providerLongName = 'SimplePay';

	public $supports = array('products', 'refunds', 'tokenization', 'tokencancel');

	protected $pmgwData = array(
		'autoCommit' => true,
	);
	
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
			$this->providerName . 'CardRegistrationFunctions' => array(
				'title' => __('Card registration functions', BF_PMGW_PLUGIN),
				'type' => 'select',
				'options' => array(
					'0' => __('No', BF_PMGW_PLUGIN),
					'1' => __('Only card registration', BF_PMGW_PLUGIN),
					'2' => __('Card registration and One Click Payment', BF_PMGW_PLUGIN),
				),
				'default' => '0',
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
