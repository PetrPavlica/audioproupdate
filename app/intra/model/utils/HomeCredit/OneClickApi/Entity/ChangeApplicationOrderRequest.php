<?php

namespace HomeCredit\OneClickApi\Entity;

use HomeCredit\OneClickApi\AEntity;

class ChangeApplicationOrderRequest extends AEntity
{

	protected static $associations = [
		'order' => \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderRequest\Order::class,
	];

	/**
	 * Changed order data.
	 *
	 * @var \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderRequest\Order
	 * @required
	 */
	private $order;

	/**
	 * Reason of change.
	 *
	 * @var string|null
	 */
	private $reason;

	/**
	 * @param \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderRequest\Order $order
	 * @param string|null $reason
	 */
	public function __construct(
		\HomeCredit\OneClickApi\Entity\ChangeApplicationOrderRequest\Order $order,
		$reason = null
	)
	{
		$this->setOrder($order);
		$this->setReason($reason);
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderRequest\Order
	 */
	public function getOrder()
	{
		return $this->order;
	}

	/**
	 * @return string|null
	 */
	public function getReason()
	{
		return $this->reason;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderRequest\Order $order
	 * @return $this
	 */
	public function setOrder($order)
	{
		$this->assertNotNull($order);
		$this->order = $order;
		return $this;
	}

	/**
	 * @param string|null $reason
	 * @return $this
	 */
	public function setReason($reason)
	{
		$this->reason = $reason;
		return $this;
	}

}
