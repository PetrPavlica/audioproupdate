<?php

namespace HomeCredit\OneClickApi\Entity\CreateApplicationResponse;

use HomeCredit\OneClickApi\AEntity;

class SettingsInstallment extends AEntity
{

	protected static $associations = [
		'preferredInstallment' => \HomeCredit\OneClickApi\Entity\CreateApplicationResponse\SettingsInstallment\PreferredInstallment::class,
		'preferredDownPayment' => \HomeCredit\OneClickApi\Entity\CreateApplicationResponse\SettingsInstallment\PreferredDownPayment::class,
	];

	/**
	 * Preferred number of installments (in months)
	 *
	 * @var float|null
	 */
	private $preferredMonths;

	/**
	 * Preferred repayment amount
	 *
	 * @var \HomeCredit\OneClickApi\Entity\CreateApplicationResponse\SettingsInstallment\PreferredInstallment
	 */
	private $preferredInstallment;

	/**
	 * Preferred down payment amount (amount should be rounded to hundreds)
	 *
	 * @var \HomeCredit\OneClickApi\Entity\CreateApplicationResponse\SettingsInstallment\PreferredDownPayment
	 */
	private $preferredDownPayment;

	/**
	 * Product code, this indicates previous calculation result
	 *
	 * @var string|null
	 */
	private $productCode;

	/**
	 * Product set code, this will limit further calculations
	 *
	 * @var string|null
	 */
	private $productSetCode;

	/**
	 * @param float|null $preferredMonths
	 * @param \HomeCredit\OneClickApi\Entity\CreateApplicationResponse\SettingsInstallment\PreferredInstallment $preferredInstallment
	 * @param \HomeCredit\OneClickApi\Entity\CreateApplicationResponse\SettingsInstallment\PreferredDownPayment $preferredDownPayment
	 * @param string|null $productCode
	 * @param string|null $productSetCode
	 */
	public function __construct(
		$preferredMonths = null,
		\HomeCredit\OneClickApi\Entity\CreateApplicationResponse\SettingsInstallment\PreferredInstallment $preferredInstallment = null,
		\HomeCredit\OneClickApi\Entity\CreateApplicationResponse\SettingsInstallment\PreferredDownPayment $preferredDownPayment = null,
		$productCode = null,
		$productSetCode = null
	)
	{
		$this->setPreferredMonths($preferredMonths);
		$this->setPreferredInstallment($preferredInstallment);
		$this->setPreferredDownPayment($preferredDownPayment);
		$this->setProductCode($productCode);
		$this->setProductSetCode($productSetCode);
	}

	/**
	 * @return float|null
	 */
	public function getPreferredMonths()
	{
		return $this->preferredMonths;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\CreateApplicationResponse\SettingsInstallment\PreferredInstallment
	 */
	public function getPreferredInstallment()
	{
		return $this->preferredInstallment;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\CreateApplicationResponse\SettingsInstallment\PreferredDownPayment
	 */
	public function getPreferredDownPayment()
	{
		return $this->preferredDownPayment;
	}

	/**
	 * @return string|null
	 */
	public function getProductCode()
	{
		return $this->productCode;
	}

	/**
	 * @return string|null
	 */
	public function getProductSetCode()
	{
		return $this->productSetCode;
	}

	/**
	 * @param float|null $preferredMonths
	 * @return $this
	 */
	public function setPreferredMonths($preferredMonths)
	{
		$this->preferredMonths = $preferredMonths;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\CreateApplicationResponse\SettingsInstallment\PreferredInstallment $preferredInstallment
	 * @return $this
	 */
	public function setPreferredInstallment($preferredInstallment)
	{
		$this->preferredInstallment = $preferredInstallment;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\CreateApplicationResponse\SettingsInstallment\PreferredDownPayment $preferredDownPayment
	 * @return $this
	 */
	public function setPreferredDownPayment($preferredDownPayment)
	{
		$this->preferredDownPayment = $preferredDownPayment;
		return $this;
	}

	/**
	 * @param string|null $productCode
	 * @return $this
	 */
	public function setProductCode($productCode)
	{
		$this->productCode = $productCode;
		return $this;
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

}
