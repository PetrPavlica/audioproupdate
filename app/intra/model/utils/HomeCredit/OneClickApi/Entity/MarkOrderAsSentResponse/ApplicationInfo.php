<?php

namespace HomeCredit\OneClickApi\Entity\MarkOrderAsSentResponse;

use HomeCredit\OneClickApi\AEntity;

class ApplicationInfo extends AEntity
{

	const STATE_PROCESSING = 'PROCESSING';
	const STATE_READY = 'READY';
	const STATE_REJECTED = 'REJECTED';
	const STATE_CANCELLED = 'CANCELLED';
	const STATEREASON_PROCESSING_REDIRECT_NEEDED = 'PROCESSING_REDIRECT_NEEDED';
	const STATEREASON_PROCESSING_PREAPPROVED = 'PROCESSING_PREAPPROVED';
	const STATEREASON_REJECTED = 'REJECTED';
	const STATEREASON_PROCESSING_APPROVED = 'PROCESSING_APPROVED';
	const STATEREASON_PROCESSING_REVIEW = 'PROCESSING_REVIEW';
	const STATEREASON_PROCESSING_ALT_OFFER = 'PROCESSING_ALT_OFFER';
	const STATEREASON_PROCESSING_SIGNED = 'PROCESSING_SIGNED';
	const STATEREASON_CANCELLED_NOT_PAID = 'CANCELLED_NOT_PAID';
	const STATEREASON_READY_TO_SHIP = 'READY_TO_SHIP';
	const STATEREASON_READY_SHIPPED = 'READY_SHIPPED';
	const STATEREASON_READY_DELIVERING = 'READY_DELIVERING';
	const STATEREASON_READY_DELIVERED = 'READY_DELIVERED';
	const STATEREASON_READY_PAID = 'READY_PAID';
	const STATEREASON_CANCELLED_RETURNED = 'CANCELLED_RETURNED';

	/**
	 * Unique identifier in HCO
	 *
	 * @var string
	 * @required
	 */
	private $id;

	/**
	 * Application state.
	 *
	 * @var string
	 * @required
	 */
	private $state;

	/**
	 * Describes internal state of application, e.g. when state is PROCESSING - reason why application remains in processing state
	 *
	 * @var string
	 * @required
	 */
	private $stateReason;

	/**
	 * @param string $id
	 * @param string $state
	 * @param string $stateReason
	 */
	public function __construct(
		$id,
		$state,
		$stateReason
	)
	{
		$this->setId($id);
		$this->setState($state);
		$this->setStateReason($stateReason);
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * @return string
	 */
	public function getStateReason()
	{
		return $this->stateReason;
	}

	/**
	 * @param string $id
	 * @return $this
	 */
	public function setId($id)
	{
		$this->assertNotNull($id);
		$this->id = $id;
		return $this;
	}

	/**
	 * @param string $state
	 * @return $this
	 */
	public function setState($state)
	{
		$this->assertNotNull($state);
		$this->assertInArray($state, [self::STATE_PROCESSING, self::STATE_READY, self::STATE_REJECTED, self::STATE_CANCELLED]);
		$this->state = $state;
		return $this;
	}

	/**
	 * @param string $stateReason
	 * @return $this
	 */
	public function setStateReason($stateReason)
	{
		$this->assertNotNull($stateReason);
		$this->assertInArray($stateReason, [self::STATEREASON_PROCESSING_REDIRECT_NEEDED, self::STATEREASON_PROCESSING_PREAPPROVED, self::STATEREASON_REJECTED, self::STATEREASON_PROCESSING_APPROVED, self::STATEREASON_PROCESSING_REVIEW, self::STATEREASON_PROCESSING_ALT_OFFER, self::STATEREASON_PROCESSING_SIGNED, self::STATEREASON_CANCELLED_NOT_PAID, self::STATEREASON_READY_TO_SHIP, self::STATEREASON_READY_SHIPPED, self::STATEREASON_READY_DELIVERING, self::STATEREASON_READY_DELIVERED, self::STATEREASON_READY_PAID, self::STATEREASON_CANCELLED_RETURNED]);
		$this->stateReason = $stateReason;
		return $this;
	}

}
