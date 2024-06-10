<?php

namespace HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferResponse;

use HomeCredit\OneClickApi\AEntity;

class InstallmentPrograms extends AEntity
{

	const TYPE_MINIMAL = 'MINIMAL';
	const TYPE_FAVOURITE = 'FAVOURITE';
	const TYPE_OTHER = 'OTHER';

	protected static $associations = [
		'installment' => \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferResponse\InstallmentPrograms\Installment::class,
	];

	/**
	 * Installment program product code
	 *
	 * @var string
	 * @required
	 */
	private $productCode;

	/**
	 * Program type
	 *
	 * @var string|null
	 */
	private $type;

	/**
	 * monthly installment amount
	 *
	 * @var \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferResponse\InstallmentPrograms\Installment
	 */
	private $installment;

	/**
	 * installment count
	 *
	 * @var float|null
	 */
	private $numberOfInstallments;

	/**
	 * @param string $productCode
	 * @param string|null $type
	 * @param \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferResponse\InstallmentPrograms\Installment $installment
	 * @param float|null $numberOfInstallments
	 */
	public function __construct(
		$productCode,
		$type = null,
		\HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferResponse\InstallmentPrograms\Installment $installment = null,
		$numberOfInstallments = null
	)
	{
		$this->setProductCode($productCode);
		$this->setType($type);
		$this->setInstallment($installment);
		$this->setNumberOfInstallments($numberOfInstallments);
	}

	/**
	 * @return string
	 */
	public function getProductCode()
	{
		return $this->productCode;
	}

	/**
	 * @return string|null
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferResponse\InstallmentPrograms\Installment
	 */
	public function getInstallment()
	{
		return $this->installment;
	}

	/**
	 * @return float|null
	 */
	public function getNumberOfInstallments()
	{
		return $this->numberOfInstallments;
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
	 * @param string|null $type
	 * @return $this
	 */
	public function setType($type)
	{
		if (!is_null($type)) {
			$this->assertInArray($type, [self::TYPE_MINIMAL, self::TYPE_FAVOURITE, self::TYPE_OTHER]);
		}
		$this->type = $type;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferResponse\InstallmentPrograms\Installment $installment
	 * @return $this
	 */
	public function setInstallment($installment)
	{
		$this->installment = $installment;
		return $this;
	}

	/**
	 * @param float|null $numberOfInstallments
	 * @return $this
	 */
	public function setNumberOfInstallments($numberOfInstallments)
	{
		$this->numberOfInstallments = $numberOfInstallments;
		return $this;
	}

}
