<?php

namespace HomeCredit\OneClickApi\Entity;

use HomeCredit\OneClickApi\AEntity;

class CalculateInstallmentProgramsDownPaymentsRequest extends AEntity
{

	protected static $associations = [
		'price' => \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsRequest\Price::class,
	];

	/**
	 * Product set code, this will limit further calculations
	 *
	 * @var string
	 * @required
	 */
	private $productSetCode;

	/**
	 * Goods price
	 *
	 * @var \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsRequest\Price
	 * @required
	 */
	private $price;

	/**
	 * @param string $productSetCode
	 * @param \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsRequest\Price $price
	 */
	public function __construct(
		$productSetCode,
		\HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsRequest\Price $price
	)
	{
		$this->setProductSetCode($productSetCode);
		$this->setPrice($price);
	}

	/**
	 * @return string
	 */
	public function getProductSetCode()
	{
		return $this->productSetCode;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsRequest\Price
	 */
	public function getPrice()
	{
		return $this->price;
	}

	/**
	 * @param string $productSetCode
	 * @return $this
	 */
	public function setProductSetCode($productSetCode)
	{
		$this->assertNotNull($productSetCode);
		$this->productSetCode = $productSetCode;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsRequest\Price $price
	 * @return $this
	 */
	public function setPrice($price)
	{
		$this->assertNotNull($price);
		$this->price = $price;
		return $this;
	}

}
