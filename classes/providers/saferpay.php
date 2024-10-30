<?php

/**
 * BIG FISH Payment Gateway Saferpay provider
 * 
 */
class BigFishPaymentGatewaySaferpay extends BigFishPaymentGatewayProvider {

	protected $providerName = 'Saferpay';

	protected $providerLongName = 'Worldline';

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
			'SaferpayPaymentMethods' => array(
				'title' => __('Available payment methods', BF_PMGW_PLUGIN),
				'type' => 'multiselect',
                'css' => 'min-height: 100px;',
				'options' => array(
					'AMEX' => __('AMEX', BF_PMGW_PLUGIN),
					'DIRECTDEBIT' => __('DIRECTDEBIT', BF_PMGW_PLUGIN),
					'INVOICE' => __('INVOICE', BF_PMGW_PLUGIN),
					'BONUS' => __('BONUS', BF_PMGW_PLUGIN),
					'DINERS' => __('DINERS', BF_PMGW_PLUGIN),
					'EPRZELEWY' => __('EPRZELEWY', BF_PMGW_PLUGIN),
					'EPS' => __('EPS', BF_PMGW_PLUGIN),
					'GIROPAY' => __('GIROPAY', BF_PMGW_PLUGIN),
					'IDEAL' => __('IDEAL', BF_PMGW_PLUGIN),
					'JCB' => __('JCB', BF_PMGW_PLUGIN),
					'MAESTRO' => __('MAESTRO', BF_PMGW_PLUGIN),
					'MASTERCARD' => __('MASTERCARD', BF_PMGW_PLUGIN),
					'MYONE' => __('MYONE', BF_PMGW_PLUGIN),
					'PAYPAL' => __('PAYPAL', BF_PMGW_PLUGIN),
					'POSTCARD' => __('POSTCARD', BF_PMGW_PLUGIN),
					'POSTFINANCE' => __('POSTFINANCE', BF_PMGW_PLUGIN),
					'SAFERPAYTEST' => __('SAFERPAYTEST', BF_PMGW_PLUGIN),
					'SOFORT' => __('SOFORT', BF_PMGW_PLUGIN),
					'VISA' => __('VISA', BF_PMGW_PLUGIN),
					'VPAY' => __('VPAY', BF_PMGW_PLUGIN),
				),
				'description' => __('The choice is not mandatory', BF_PMGW_PLUGIN),
				'desc_tip'    => true,
			),
			'SaferpayWallets' => array(
				'title' => __('Available wallets', BF_PMGW_PLUGIN),
				'type' => 'multiselect',
                'css' => 'min-height: 50px;',
				'options' => array(
					'MASTERPASS' => __('MASTERPASS', BF_PMGW_PLUGIN),
				),
				'description' => __('The choice is not mandatory', BF_PMGW_PLUGIN),
				'desc_tip'    => true,
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
		
		if (isset($this->settings['SaferpayPaymentMethods']) && !empty($this->settings['SaferpayPaymentMethods'])) {
			$this->pmgwData['extra']['SaferpayPaymentMethods'] = $this->settings['SaferpayPaymentMethods'];
		}

		if (isset($this->settings['SaferpayWallets']) && !empty($this->settings['SaferpayWallets'])) {
			$this->pmgwData['extra']['SaferpayWallets'] = $this->settings['SaferpayWallets'];
		}

		return parent::process_payment($order_id);
	}
}
