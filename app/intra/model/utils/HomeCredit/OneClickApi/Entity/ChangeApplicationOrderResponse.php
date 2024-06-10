<?php

namespace HomeCredit\OneClickApi\Entity;

use HomeCredit\OneClickApi\AEntity;

class ChangeApplicationOrderResponse extends AEntity
{

	const DELIVERYTYPE_DELIVERY_CARRIER = 'DELIVERY_CARRIER';
	const DELIVERYTYPE_PERSONAL_BRANCH = 'PERSONAL_BRANCH';
	const DELIVERYTYPE_PERSONAL_PARTNER = 'PERSONAL_PARTNER';
	const DELIVERYTYPE_ONLINE = 'ONLINE';
	const STATE_PROCESSING = 'PROCESSING';
	const STATE_SENT = 'SENT';
	const STATE_DELIVERED = 'DELIVERED';
	const STATE_RETURNED = 'RETURNED';
	const STATE_CANCELLED = 'CANCELLED';

	protected static $associations = [
		'totalPrice' => \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse\TotalPrice::class,
		'totalVat[]' => \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse\TotalVat::class,
		'addresses[]' => \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse\Addresses::class,
		'items[]' => \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse\Items::class,
		'applicationInfo' => \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse\ApplicationInfo::class,
	];

	/**
	 * Order number (internal for e-shop)
	 *
	 * @var string
	 * @required
	 */
	private $number;

	/**
	 * Variable symbols (internal for e-shop). For the financing type `INSTALLMENT` the first VS in this array is used as the VS of the reconciliation payment.
	 *
	 * @var array|null
	 */
	private $variableSymbols;

	/**
	 * Total order amount, including VAT
	 *
	 * @var \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse\TotalPrice
	 * @required
	 */
	private $totalPrice;

	/**
	 * Total VAT amounts split by their VAT rates
	 *
	 * @var \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse\TotalVat[]
	 * @required
	 */
	private $totalVat;

	/**
	 * Addresses. Only `BILLING` and `DELIVERY` types are allowed.
	 *
	 * @var \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse\Addresses[]
	 * @required
	 */
	private $addresses;

	/**
	 * Delivery type.
	 *
	 * @var string|null
	 */
	private $deliveryType;

	/**
	 * Date and time until order is reserved. After this date and time, e-shop does not guarantee items availability (e.g. if application processing is longer, it may endanger order fullfillment).
	 *
	 * @var string|null
	 */
	private $reservationDate;

	/**
	 * Order items
	 *
	 * @var \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse\Items[]
	 * @required
	 */
	private $items;

	/**
	 * Order state.
	 *
	 * @var string|null
	 */
	private $state;

	/**
	 * Base information about respective application
	 *
	 * @var \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse\ApplicationInfo
	 * @required
	 */
	private $applicationInfo;

	/**
	 * @param string $number
	 * @param \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse\TotalPrice $totalPrice
	 * @param \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse\TotalVat[] $totalVat
	 * @param \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse\Addresses[] $addresses
	 * @param \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse\Items[] $items
	 * @param \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse\ApplicationInfo $applicationInfo
	 * @param array|null $variableSymbols
	 * @param string|null $deliveryType
	 * @param string|null $reservationDate
	 * @param string|null $state
	 */
	public function __construct(
		$number,
		\HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse\TotalPrice $totalPrice,
		array $totalVat,
		array $addresses,
		array $items,
		\HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse\ApplicationInfo $applicationInfo,
		$variableSymbols = null,
		$deliveryType = null,
		$reservationDate = null,
		$state = null
	)
	{
		$this->setNumber($number);
		$this->setTotalPrice($totalPrice);
		$this->setTotalVat($totalVat);
		$this->setAddresses($addresses);
		$this->setItems($items);
		$this->setApplicationInfo($applicationInfo);
		$this->setVariableSymbols($variableSymbols);
		$this->setDeliveryType($deliveryType);
		$this->setReservationDate($reservationDate);
		$this->setState($state);
	}

	/**
	 * @return string
	 */
	public function getNumber()
	{
		return $this->number;
	}

	/**
	 * @return array|null
	 */
	public function getVariableSymbols()
	{
		return $this->variableSymbols;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse\TotalPrice
	 */
	public function getTotalPrice()
	{
		return $this->totalPrice;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse\TotalVat[]
	 */
	public function getTotalVat()
	{
		return $this->totalVat;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse\Addresses[]
	 */
	public function getAddresses()
	{
		return $this->addresses;
	}

	/**
	 * @return string|null
	 */
	public function getDeliveryType()
	{
		return $this->deliveryType;
	}

	/**
	 * @return string|null
	 */
	public function getReservationDate()
	{
		return $this->reservationDate;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse\Items[]
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * @return string|null
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse\ApplicationInfo
	 */
	public function getApplicationInfo()
	{
		return $this->applicationInfo;
	}

	/**
	 * @param string $number
	 * @return $this
	 */
	public function setNumber($number)
	{
		$this->assertNotNull($number);
		$this->number = $number;
		return $this;
	}

	/**
	 * @param array|null $variableSymbols
	 * @return $this
	 */
	public function setVariableSymbols($variableSymbols)
	{
		$this->variableSymbols = $variableSymbols;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse\TotalPrice $totalPrice
	 * @return $this
	 */
	public function setTotalPrice($totalPrice)
	{
		$this->assertNotNull($totalPrice);
		$this->totalPrice = $totalPrice;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse\TotalVat[] $totalVat
	 * @return $this
	 */
	public function setTotalVat($totalVat)
	{
		$this->assertNotNull($totalVat);
		$this->totalVat = $totalVat;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse\Addresses[] $addresses
	 * @return $this
	 */
	public function setAddresses($addresses)
	{
		$this->assertNotNull($addresses);
		$this->addresses = $addresses;
		return $this;
	}

	/**
	 * @param string|null $deliveryType
	 * @return $this
	 */
	public function setDeliveryType($deliveryType)
	{
		if (!is_null($deliveryType)) {
			$this->assertInArray($deliveryType, [self::DELIVERYTYPE_DELIVERY_CARRIER, self::DELIVERYTYPE_PERSONAL_BRANCH, self::DELIVERYTYPE_PERSONAL_PARTNER, self::DELIVERYTYPE_ONLINE]);
		}
		$this->deliveryType = $deliveryType;
		return $this;
	}

	/**
	 * @param string|null $reservationDate
	 * @return $this
	 */
	public function setReservationDate($reservationDate)
	{
		$this->reservationDate = $reservationDate;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse\Items[] $items
	 * @return $this
	 */
	public function setItems($items)
	{
		$this->assertNotNull($items);
		$this->items = $items;
		return $this;
	}

	/**
	 * @param string|null $state
	 * @return $this
	 */
	public function setState($state)
	{
		if (!is_null($state)) {
			$this->assertInArray($state, [self::STATE_PROCESSING, self::STATE_SENT, self::STATE_DELIVERED, self::STATE_RETURNED, self::STATE_CANCELLED]);
		}
		$this->state = $state;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse\ApplicationInfo $applicationInfo
	 * @return $this
	 */
	public function setApplicationInfo($applicationInfo)
	{
		$this->assertNotNull($applicationInfo);
		$this->applicationInfo = $applicationInfo;
		return $this;
	}

}
