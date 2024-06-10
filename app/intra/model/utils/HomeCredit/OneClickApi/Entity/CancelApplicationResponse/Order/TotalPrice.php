<?php

namespace HomeCredit\OneClickApi\Entity\CancelApplicationResponse\Order;

use HomeCredit\OneClickApi\AEntity;

class TotalPrice extends AEntity
{

	/**
	 * Amount in minor units (1259950 represents 12599,50 CZK) [ISO 4217](https://en.wikipedia.org/wiki/ISO_4217)
	 *
	 * @var float
	 * @required
	 */
	private $amount;

	/**
	 * Amount currency. [ISO 4217](https://en.wikipedia.org/wiki/ISO_4217). Currenty only CZK is allowed.
	 *
	 * @var string
	 * @required
	 */
	private $currency;

	/**
	 * @param float $amount
	 * @param string $currency
	 */
	public function __construct(
		$amount,
		$currency
	)
	{
		$this->setAmount($amount);
		$this->setCurrency($currency);
	}

	/**
	 * @return float
	 */
	public function getAmount()
	{
		return $this->amount;
	}

	/**
	 * @return string
	 */
	public function getCurrency()
	{
		return $this->currency;
	}

	/**
	 * @param float $amount
	 * @return $this
	 */
	public function setAmount($amount)
	{
		$this->assertNotNull($amount);
		$this->amount = $amount;
		return $this;
	}

	/**
	 * @param string $currency
	 * @return $this
	 */
	public function setCurrency($currency)
	{
		$this->assertNotNull($currency);
		$this->currency = $currency;
		return $this;
	}

}
