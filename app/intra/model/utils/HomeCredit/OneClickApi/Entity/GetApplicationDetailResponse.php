<?php

namespace HomeCredit\OneClickApi\Entity;

use HomeCredit\OneClickApi\AEntity;

class GetApplicationDetailResponse extends AEntity
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
	const TYPE_INSTALLMENT = 'INSTALLMENT';

	protected static $associations = [
		'customer' => \HomeCredit\OneClickApi\Entity\GetApplicationDetailResponse\Customer::class,
		'order' => \HomeCredit\OneClickApi\Entity\GetApplicationDetailResponse\Order::class,
		'settingsInstallment' => \HomeCredit\OneClickApi\Entity\GetApplicationDetailResponse\SettingsInstallment::class,
	];

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
	 * Customer data
	 *
	 * @var \HomeCredit\OneClickApi\Entity\GetApplicationDetailResponse\Customer
	 */
	private $customer;

	/**
	 * Order data
	 *
	 * @var \HomeCredit\OneClickApi\Entity\GetApplicationDetailResponse\Order
	 * @required
	 */
	private $order;

	/**
	 * Financing type
	 *
	 * @var string
	 * @required
	 */
	private $type;

	/**
	 * Settings for INSTALLMENT Application type.
	 *
	 * @var \HomeCredit\OneClickApi\Entity\GetApplicationDetailResponse\SettingsInstallment
	 */
	private $settingsInstallment;

	/**
	 * Gateway redirect URL. Redirect user to this URL if state is `PROCESSING` and stateReason is `PROCESSING_REDIRECT_NEEDED`.
	 *
	 * @var string|null
	 */
	private $gatewayRedirectUrl;

	/**
	 * @param string $id
	 * @param string $state
	 * @param string $stateReason
	 * @param \HomeCredit\OneClickApi\Entity\GetApplicationDetailResponse\Order $order
	 * @param string $type
	 * @param \HomeCredit\OneClickApi\Entity\GetApplicationDetailResponse\Customer $customer
	 * @param \HomeCredit\OneClickApi\Entity\GetApplicationDetailResponse\SettingsInstallment $settingsInstallment
	 * @param string|null $gatewayRedirectUrl
	 */
	public function __construct(
		$id,
		$state,
		$stateReason,
		\HomeCredit\OneClickApi\Entity\GetApplicationDetailResponse\Order $order,
		$type,
		\HomeCredit\OneClickApi\Entity\GetApplicationDetailResponse\Customer $customer = null,
		\HomeCredit\OneClickApi\Entity\GetApplicationDetailResponse\SettingsInstallment $settingsInstallment = null,
		$gatewayRedirectUrl = null
	)
	{
		$this->setId($id);
		$this->setState($state);
		$this->setStateReason($stateReason);
		$this->setOrder($order);
		$this->setType($type);
		$this->setCustomer($customer);
		$this->setSettingsInstallment($settingsInstallment);
		$this->setGatewayRedirectUrl($gatewayRedirectUrl);
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
	 * @return \HomeCredit\OneClickApi\Entity\GetApplicationDetailResponse\Customer
	 */
	public function getCustomer()
	{
		return $this->customer;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\GetApplicationDetailResponse\Order
	 */
	public function getOrder()
	{
		return $this->order;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\GetApplicationDetailResponse\SettingsInstallment
	 */
	public function getSettingsInstallment()
	{
		return $this->settingsInstallment;
	}

	/**
	 * @return string|null
	 */
	public function getGatewayRedirectUrl()
	{
		return $this->gatewayRedirectUrl;
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

	/**
	 * @param \HomeCredit\OneClickApi\Entity\GetApplicationDetailResponse\Customer $customer
	 * @return $this
	 */
	public function setCustomer($customer)
	{
		$this->customer = $customer;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\GetApplicationDetailResponse\Order $order
	 * @return $this
	 */
	public function setOrder($order)
	{
		$this->assertNotNull($order);
		$this->order = $order;
		return $this;
	}

	/**
	 * @param string $type
	 * @return $this
	 */
	public function setType($type)
	{
		$this->assertNotNull($type);
		$this->assertInArray($type, [self::TYPE_INSTALLMENT]);
		$this->type = $type;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\GetApplicationDetailResponse\SettingsInstallment $settingsInstallment
	 * @return $this
	 */
	public function setSettingsInstallment($settingsInstallment)
	{
		$this->settingsInstallment = $settingsInstallment;
		return $this;
	}

	/**
	 * @param string|null $gatewayRedirectUrl
	 * @return $this
	 */
	public function setGatewayRedirectUrl($gatewayRedirectUrl)
	{
		$this->gatewayRedirectUrl = $gatewayRedirectUrl;
		return $this;
	}

}
