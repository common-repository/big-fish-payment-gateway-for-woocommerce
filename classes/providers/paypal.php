<?php

/**
 * BIG FISH Payment Gateway PayPal provider
 * 
 */
class BigFishPaymentGatewayPayPal extends BigFishPaymentGatewayProvider {

	protected $providerName = 'PayPal';
	
	protected $providerLongName = 'PayPal';
	
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
		
		if (
				isset($this->settings['recurringPaymentEnable']) && (int)$this->settings['recurringPaymentEnable'] && 
				isset($this->settings['recurringPaymentBillingPeriods']) && !empty($this->settings['recurringPaymentBillingPeriods'])
		) {
			$this->description .= '<div>' . __('Enable recurring payment', BF_PMGW_PLUGIN).': <input type="checkbox" name="recurringPaymentEnable" value="1">';

			if (count($this->settings['recurringPaymentBillingPeriods']) > 1) {
				$this->description .= '<br />' . __('Billing period', BF_PMGW_PLUGIN).': <select name="recurringPaymentBillingPeriod" size="1">';

				foreach ($this->settings['recurringPaymentBillingPeriods'] as $recurringPaymentBillingPeriod) {
					$this->description .= '<option value="' . $recurringPaymentBillingPeriod . '">' . __($recurringPaymentBillingPeriod, BF_PMGW_PLUGIN) . '</option>';
				}

				$this->description .= '</select>';
			} else {
				$this->description .= '<br />' . __('Billing period', BF_PMGW_PLUGIN).': ' . $this->settings['recurringPaymentBillingPeriods'][0] . '<input type="hidden" name="recurringPaymentBillingPeriod" value="' . $this->settings['recurringPaymentBillingPeriods'][0] . '">';
			}
			
			$this->description .= '</div>';
		}
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
			'recurringPaymentEnable' => array(
				'title' => __('Enable recurring payment', BF_PMGW_PLUGIN),
				'type' => 'select',
				'options' => array(
					'0' => __('No', BF_PMGW_PLUGIN),
					'1' => __('Yes', BF_PMGW_PLUGIN),
				),
				'default' => '0',
			),
			'recurringPaymentBillingPeriods' => array(
				'title' => __('Available billing periods to recurring payment', BF_PMGW_PLUGIN),
				'type' => 'multiselect',
				'css' => 'min-height: 100px;',
				'options' => array(
					'Day' => __('Day', BF_PMGW_PLUGIN),
					'Week' => __('Week', BF_PMGW_PLUGIN),
					'SemiMonth' => __('SemiMonth', BF_PMGW_PLUGIN),
					'Month' => __('Month', BF_PMGW_PLUGIN),
					'Year' => __('Year', BF_PMGW_PLUGIN),
				),
				'description' => __('Select one to default. If you select more customer also can choose from.', BF_PMGW_PLUGIN),
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
}
