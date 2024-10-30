<?php

namespace BigFish\PaymentGateway\Request;


class Refund extends SimpleRequestAbstract
{
	use ExtraTrait;

	const REQUEST_TYPE = 'Refund';

	public function setAmount(float $amount)
	{
		return $this->setData($amount, 'amount');
	}
}