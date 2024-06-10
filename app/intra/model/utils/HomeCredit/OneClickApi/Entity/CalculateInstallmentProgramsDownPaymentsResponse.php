<?php

namespace HomeCredit\OneClickApi\Entity;

use HomeCredit\OneClickApi\AEntity;

class CalculateInstallmentProgramsDownPaymentsResponse extends AEntity
{

	protected static $associations = [
		'minimum' => \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsResponse\Minimum::class,
		'maximum' => \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsResponse\Maximum::class,
		'step' => \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsResponse\Step::class,
	];

	/**
	 * The lowest down payment amount, e.g. 0 (0 Kč)
	 *
	 * @var \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsResponse\Minimum
	 */
	private $minimum;

	/**
	 * Maximum down payment amount, e.g. 2500 (2 500 Kč)
	 *
	 * @var \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsResponse\Maximum
	 */
	private $maximum;

	/**
	 * Size of one step between the lowest and maximum down payment amounts, e.g. 500 (500 Kč). Available down payments could be (min 0, max 2500): 0, 500, 1000, 1500, 2000, 2500
	 *
	 * @var \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsResponse\Step
	 */
	private $step;

	/**
	 * @param \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsResponse\Minimum $minimum
	 * @param \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsResponse\Maximum $maximum
	 * @param \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsResponse\Step $step
	 */
	public function __construct(
		\HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsResponse\Minimum $minimum = null,
		\HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsResponse\Maximum $maximum = null,
		\HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsResponse\Step $step = null
	)
	{
		$this->setMinimum($minimum);
		$this->setMaximum($maximum);
		$this->setStep($step);
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsResponse\Minimum
	 */
	public function getMinimum()
	{
		return $this->minimum;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsResponse\Maximum
	 */
	public function getMaximum()
	{
		return $this->maximum;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsResponse\Step
	 */
	public function getStep()
	{
		return $this->step;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsResponse\Minimum $minimum
	 * @return $this
	 */
	public function setMinimum($minimum)
	{
		$this->minimum = $minimum;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsResponse\Maximum $maximum
	 * @return $this
	 */
	public function setMaximum($maximum)
	{
		$this->maximum = $maximum;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsResponse\Step $step
	 * @return $this
	 */
	public function setStep($step)
	{
		$this->step = $step;
		return $this;
	}

}
