<?php

/**
 * BIG FISH Payment Gateway OTP Simple Wire provider
 * 
 */
class BigFishPaymentGatewayOTPSimpleWire extends BigFishPaymentGatewayProvider {

	protected $providerName = 'OTPSimpleWire';
	
	protected $providerLongName = 'SimplePay Wire';

	public $supports = array('products', 'refunds');
	
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
