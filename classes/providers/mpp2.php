<?php

/**
 * BIG FISH Payment Gateway MPP2 provider
 * 
 */
class BigFishPaymentGatewayMPP2 extends BigFishPaymentGatewayProvider {

	protected $providerName = 'MPP2';
	
	protected $providerLongName = 'MasterCard® Mobile';
	
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
