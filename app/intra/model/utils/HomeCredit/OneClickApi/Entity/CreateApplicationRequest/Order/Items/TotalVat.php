<?php

namespace HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Order\Items;

use HomeCredit\OneClickApi\AEntity;

class TotalVat extends AEntity
{

	/**
	 * Amount in minor units (188992 represents 1889,92 CZK) [ISO 4217](https://en.wikipedia.org/wiki/ISO_4217)
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
	 * VAT rate as natural number (15 represents 15% rate)
	 *
	 * @var float
	 * @required
	 */
	private $vatRate;

	/**
	 * @param float $amount
	 * @param string $currency
	 * @param float $vatRate
	 */
	public function __construct(
		$amount,
		$currency,
		$vatRate
	)
	{
		$this->setAmount($amount);
		$this->setCurrency($currency);
		$this->setVatRate($vatRate);
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
	 * @return float
	 */
	public function getVatRate()
	{
		return $this->vatRate;
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

	/**
	 * @param float $vatRate
	 * @return $this
	 */
	public function setVatRate($vatRate)
	{
		$this->assertNotNull($vatRate);
		$this->vatRate = $vatRate;
		return $this;
	}

}
