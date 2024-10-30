<?php

/**
 * BIG FISH Payment Gateway QPAY provider
 * 
 */
class BigFishPaymentGatewayQPAY extends BigFishPaymentGatewayProvider {

	protected $providerName = 'QPAY';
	
	protected $providerLongName = 'Wirecard QPAY';
	
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
			'autoCommit' => array(
				'title' => __('Authorization', BF_PMGW_PLUGIN),
				'type' => 'select',
				'options' => array(
					'1' => __('Immediate', BF_PMGW_PLUGIN),
					'0' => __('Later', BF_PMGW_PLUGIN),
				),
				'default' => '1',
			),
			'QpayPaymentType' => array(
				'title' => __('Available payment types', BF_PMGW_PLUGIN),
				'type' => 'select',
				'options' => array(
					'' => '',
					'SELECT' => __('Select payment type on Wirecard side', BF_PMGW_PLUGIN),
					'BANCONTACT_MISTERCASH' => __('Bancontact/Mister Cash', BF_PMGW_PLUGIN),
					'CCARD' => __('Credit Card, Maestro SecureCode', BF_PMGW_PLUGIN),
					'CCARD-MOTO' => __('Credit Card - Mail Order and Telephone Order', BF_PMGW_PLUGIN),
					'EKONTO' => __('eKonto', BF_PMGW_PLUGIN),
					'EPAY_BG' => __('ePay.bg', BF_PMGW_PLUGIN),
					'EPS' => __('eps Online-wire', BF_PMGW_PLUGIN),
					'GIROPAY' => __('giropay', BF_PMGW_PLUGIN),
					'IDL' => __('iDEAL', BF_PMGW_PLUGIN),
					'MONETA' => __('moneta.ru', BF_PMGW_PLUGIN),
					'MPASS' => __('mpass', BF_PMGW_PLUGIN),
					'PRZELEWY24' => __('Przelewy24', BF_PMGW_PLUGIN),
					'PAYPAL' => __('PayPal', BF_PMGW_PLUGIN),
					'PBX' => __('paybox', BF_PMGW_PLUGIN),
					'POLI' => __('POLi', BF_PMGW_PLUGIN),
					'PSC' => __('paysafecard', BF_PMGW_PLUGIN),
					'QUICK' => __('@Quick', BF_PMGW_PLUGIN),
					'SEPA-DD' => __('SEPA Direct Debit', BF_PMGW_PLUGIN),
					'SKRILLDIRECT' => __('Skrill Direct', BF_PMGW_PLUGIN),
					'SKRILLWALLET' => __('Skrill Digital Wallet', BF_PMGW_PLUGIN),
					'SOFORTUEBERWEISUNG' => __('SOFORT Banking', BF_PMGW_PLUGIN),
					'TATRAPAY' => __('TatraPay', BF_PMGW_PLUGIN),
					'TRUSTLY' => __('Trustly', BF_PMGW_PLUGIN),
					'TRUSTPAY' => __('TrustPay', BF_PMGW_PLUGIN),
					'VOUCHER' => __('My Voucher', BF_PMGW_PLUGIN),
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

		if (isset($this->settings['QpayPaymentType']) && !empty($this->settings['QpayPaymentType'])) {
			$this->pmgwData['extra']['QpayPaymentType'] = $this->settings['QpayPaymentType'];
		}

		return parent::process_payment($order_id);
	}
}
