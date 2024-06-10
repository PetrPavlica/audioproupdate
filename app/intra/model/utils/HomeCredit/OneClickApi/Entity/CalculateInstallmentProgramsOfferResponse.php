<?php

namespace HomeCredit\OneClickApi\Entity;

use HomeCredit\OneClickApi\AEntity;

class CalculateInstallmentProgramsOfferResponse extends AEntity
{

	protected static $associations = [
		'installmentPrograms[]' => \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferResponse\InstallmentPrograms::class,
	];

	/**
	 * Array with installment programs offer
	 *
	 * @var \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferResponse\InstallmentPrograms[]
	 * @required
	 */
	private $installmentPrograms;

	/**
	 * @param \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferResponse\InstallmentPrograms[] $installmentPrograms
	 */
	public function __construct(
		array $installmentPrograms
	)
	{
		$this->setInstallmentPrograms($installmentPrograms);
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferResponse\InstallmentPrograms[]
	 */
	public function getInstallmentPrograms()
	{
		return $this->installmentPrograms;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferResponse\InstallmentPrograms[] $installmentPrograms
	 * @return $this
	 */
	public function setInstallmentPrograms($installmentPrograms)
	{
		$this->assertNotNull($installmentPrograms);
		$this->installmentPrograms = $installmentPrograms;
		return $this;
	}

}
