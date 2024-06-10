<?php

namespace HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailResponse\Detail;

use HomeCredit\OneClickApi\AEntity;

class PaidBack extends AEntity
{

	/**
	 * Amount in minor units (250000 represents 2500 CZK) [ISO 4217](https://en.wikipedia.org/wiki/ISO_4217)
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
