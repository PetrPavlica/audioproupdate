<?php

namespace HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailResponse;

use HomeCredit\OneClickApi\AEntity;

class Detail extends AEntity
{

	protected static $associations = [
		'paidBack' => \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailResponse\Detail\PaidBack::class,
	];

	/**
	 * Installment date (day in month)
	 *
	 * @var float|null
	 */
	private $installmentDay;

	/**
	 * Date of the first installment
	 *
	 * @var string|null
	 */
	private $installmentFirstDate;

	/**
	 * Date of the last installment
	 *
	 * @var string|null
	 */
	private $installmentLastDate;

	/**
	 * Annual interest rate
	 *
	 * @var float|null
	 */
	private $annualInterestRate;

	/**
	 * Predicted provision date
	 *
	 * @var string|null
	 */
	private $predictedProvisionDate;

	/**
	 * Total amount paid by customer
	 *
	 * @var \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailResponse\Detail\PaidBack
	 */
	private $paidBack;

	/**
	 * RPSN
	 *
	 * @var float|null
	 */
	private $rpsn;

	/**
	 * Text interpretation of the offer
	 *
	 * @var string|null
	 */
	private $legalline;

	/**
	 * @param float|null $installmentDay
	 * @param string|null $installmentFirstDate
	 * @param string|null $installmentLastDate
	 * @param float|null $annualInterestRate
	 * @param string|null $predictedProvisionDate
	 * @param \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailResponse\Detail\PaidBack $paidBack
	 * @param float|null $rpsn
	 * @param string|null $legalline
	 */
	public function __construct(
		$installmentDay = null,
		$installmentFirstDate = null,
		$installmentLastDate = null,
		$annualInterestRate = null,
		$predictedProvisionDate = null,
		\HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailResponse\Detail\PaidBack $paidBack = null,
		$rpsn = null,
		$legalline = null
	)
	{
		$this->setInstallmentDay($installmentDay);
		$this->setInstallmentFirstDate($installmentFirstDate);
		$this->setInstallmentLastDate($installmentLastDate);
		$this->setAnnualInterestRate($annualInterestRate);
		$this->setPredictedProvisionDate($predictedProvisionDate);
		$this->setPaidBack($paidBack);
		$this->setRpsn($rpsn);
		$this->setLegalline($legalline);
	}

	/**
	 * @return float|null
	 */
	public function getInstallmentDay()
	{
		return $this->installmentDay;
	}

	/**
	 * @return string|null
	 */
	public function getInstallmentFirstDate()
	{
		return $this->installmentFirstDate;
	}

	/**
	 * @return string|null
	 */
	public function getInstallmentLastDate()
	{
		return $this->installmentLastDate;
	}

	/**
	 * @return float|null
	 */
	public function getAnnualInterestRate()
	{
		return $this->annualInterestRate;
	}

	/**
	 * @return string|null
	 */
	public function getPredictedProvisionDate()
	{
		return $this->predictedProvisionDate;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailResponse\Detail\PaidBack
	 */
	public function getPaidBack()
	{
		return $this->paidBack;
	}

	/**
	 * @return float|null
	 */
	public function getRpsn()
	{
		return $this->rpsn;
	}

	/**
	 * @return string|null
	 */
	public function getLegalline()
	{
		return $this->legalline;
	}

	/**
	 * @param float|null $installmentDay
	 * @return $this
	 */
	public function setInstallmentDay($installmentDay)
	{
		$this->installmentDay = $installmentDay;
		return $this;
	}

	/**
	 * @param string|null $installmentFirstDate
	 * @return $this
	 */
	public function setInstallmentFirstDate($installmentFirstDate)
	{
		$this->installmentFirstDate = $installmentFirstDate;
		return $this;
	}

	/**
	 * @param string|null $installmentLastDate
	 * @return $this
	 */
	public function setInstallmentLastDate($installmentLastDate)
	{
		$this->installmentLastDate = $installmentLastDate;
		return $this;
	}

	/**
	 * @param float|null $annualInterestRate
	 * @return $this
	 */
	public function setAnnualInterestRate($annualInterestRate)
	{
		$this->annualInterestRate = $annualInterestRate;
		return $this;
	}

	/**
	 * @param string|null $predictedProvisionDate
	 * @return $this
	 */
	public function setPredictedProvisionDate($predictedProvisionDate)
	{
		$this->predictedProvisionDate = $predictedProvisionDate;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailResponse\Detail\PaidBack $paidBack
	 * @return $this
	 */
	public function setPaidBack($paidBack)
	{
		$this->paidBack = $paidBack;
		return $this;
	}

	/**
	 * @param float|null $rpsn
	 * @return $this
	 */
	public function setRpsn($rpsn)
	{
		$this->rpsn = $rpsn;
		return $this;
	}

	/**
	 * @param string|null $legalline
	 * @return $this
	 */
	public function setLegalline($legalline)
	{
		$this->legalline = $legalline;
		return $this;
	}

}
