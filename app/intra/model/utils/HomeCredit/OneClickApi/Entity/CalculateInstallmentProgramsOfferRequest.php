<?php

namespace HomeCredit\OneClickApi\Entity;

use HomeCredit\OneClickApi\AEntity;

class CalculateInstallmentProgramsOfferRequest extends AEntity
{

	protected static $associations = [
		'price' => \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferRequest\Price::class,
		'downPayment' => \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferRequest\DownPayment::class,
	];

	/**
	 * Product set code, this will limit further calculations. If not provided, default productSet will be used.
	 *
	 * @var string|null
	 */
	private $productSetCode;

	/**
	 * Order price
	 *
	 * @var \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferRequest\Price
	 * @required
	 */
	private $price;

	/**
	 * Down payment amount (default is 0, amount should be rounded to hundreds)
	 *
	 * @var \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferRequest\DownPayment
	 */
	private $downPayment;

	/**
	 * @param \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferRequest\Price $price
	 * @param string|null $productSetCode
	 * @param \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferRequest\DownPayment $downPayment
	 */
	public function __construct(
		\HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferRequest\Price $price,
		$productSetCode = null,
		\HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferRequest\DownPayment $downPayment = null
	)
	{
		$this->setPrice($price);
		$this->setProductSetCode($productSetCode);
		$this->setDownPayment($downPayment);
	}

	/**
	 * @return string|null
	 */
	public function getProductSetCode()
	{
		return $this->productSetCode;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferRequest\Price
	 */
	public function getPrice()
	{
		return $this->price;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferRequest\DownPayment
	 */
	public function getDownPayment()
	{
		return $this->downPayment;
	}

	/**
	 * @param string|null $productSetCode
	 * @return $this
	 */
	public function setProductSetCode($productSetCode)
	{
		$this->productSetCode = $productSetCode;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferRequest\Price $price
	 * @return $this
	 */
	public function setPrice($price)
	{
		$this->assertNotNull($price);
		$this->price = $price;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferRequest\DownPayment $downPayment
	 * @return $this
	 */
	public function setDownPayment($downPayment)
	{
		$this->downPayment = $downPayment;
		return $this;
	}

}
