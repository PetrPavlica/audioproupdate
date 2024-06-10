<?php

namespace HomeCredit\OneClickApi\Entity;

use HomeCredit\OneClickApi\AEntity;

class CalculateInstallmentProgramDetailRequest extends AEntity
{

	protected static $associations = [
		'price' => \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailRequest\Price::class,
		'downPayment' => \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailRequest\DownPayment::class,
	];

	/**
	 * Product code, this indicates previous calculation result
	 *
	 * @var string
	 * @required
	 */
	private $productCode;

	/**
	 * Goods price
	 *
	 * @var \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailRequest\Price
	 * @required
	 */
	private $price;

	/**
	 * Down payment amount (default is 0, amount should be rounded to hundreds)
	 *
	 * @var \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailRequest\DownPayment
	 */
	private $downPayment;

	/**
	 * @param string $productCode
	 * @param \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailRequest\Price $price
	 * @param \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailRequest\DownPayment $downPayment
	 */
	public function __construct(
		$productCode,
		\HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailRequest\Price $price,
		\HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailRequest\DownPayment $downPayment = null
	)
	{
		$this->setProductCode($productCode);
		$this->setPrice($price);
		$this->setDownPayment($downPayment);
	}

	/**
	 * @return string
	 */
	public function getProductCode()
	{
		return $this->productCode;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailRequest\Price
	 */
	public function getPrice()
	{
		return $this->price;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailRequest\DownPayment
	 */
	public function getDownPayment()
	{
		return $this->downPayment;
	}

	/**
	 * @param string $productCode
	 * @return $this
	 */
	public function setProductCode($productCode)
	{
		$this->assertNotNull($productCode);
		$this->productCode = $productCode;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailRequest\Price $price
	 * @return $this
	 */
	public function setPrice($price)
	{
		$this->assertNotNull($price);
		$this->price = $price;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailRequest\DownPayment $downPayment
	 * @return $this
	 */
	public function setDownPayment($downPayment)
	{
		$this->downPayment = $downPayment;
		return $this;
	}

}
