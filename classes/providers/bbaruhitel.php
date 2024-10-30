<?php

/**
 * BIG FISH Payment Gateway MBH Online Aruhitel provider
 * 
 */
class BigFishPaymentGatewayBBAruhitel extends BigFishPaymentGatewayProvider {

	protected $providerName = 'BBAruhitel';
	
	protected $providerLongName = 'MBH Online Áruhitel';
	
	public $supports = array('products');
	
	protected $pmgwData = array(
		'autoCommit' => true,
	);

	/**
	 * Contructor
	 * 
	 * @access public
	 */		
	public function __construct() {
		parent::__construct();
		$this->method_description = __('This payment available only in Hungarian forint', BF_PMGW_PLUGIN);
		$this->max_amount = $this->get_option('max_amount');
		$this->min_amount = $this->get_option('min_amount');
	}

	/**
	 * Check if the gateway is available for use.
	 *
	 * @return bool
	 */
	public function is_available() {
		$is_available = ( 'yes' === $this->enabled );

		if (get_woocommerce_currency() !== 'HUF') {
			$is_available = false;
		}

		if ( !WC()->cart ||
			$this->get_order_total() < $this->min_amount ||
			$this->get_order_total() > $this->max_amount
		) {
			$is_available = false;
		}

		return $is_available;
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
			'min_amount' => array(
				'title' => __('Minimum amount (only HUF)', BF_PMGW_PLUGIN),
				'default' => 25000
			),
			'max_amount' => array(
				'title' => __('Maximum amount (only HUF)', BF_PMGW_PLUGIN),
				'default' => 2000000
			),
		);
	}

}
