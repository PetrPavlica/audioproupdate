<?php

namespace HomeCredit\OneClickApi\Entity;

use HomeCredit\OneClickApi\AEntity;

class GetOrderResponse extends AEntity
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
		'totalPrice' => \HomeCredit\OneClickApi\Entity\GetOrderResponse\TotalPrice::class,
		'totalVat[]' => \HomeCredit\OneClickApi\Entity\GetOrderResponse\TotalVat::class,
		'addresses[]' => \HomeCredit\OneClickApi\Entity\GetOrderResponse\Addresses::class,
		'items[]' => \HomeCredit\OneClickApi\Entity\GetOrderResponse\Items::class,
		'applicationInfo' => \HomeCredit\OneClickApi\Entity\GetOrderResponse\ApplicationInfo::class,
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
	 * @var \HomeCredit\OneClickApi\Entity\GetOrderResponse\TotalPrice
	 * @required
	 */
	private $totalPrice;

	/**
	 * Total VAT amounts split by their VAT rates
	 *
	 * @var \HomeCredit\OneClickApi\Entity\GetOrderResponse\TotalVat[]
	 * @required
	 */
	private $totalVat;

	/**
	 * Addresses. Only `BILLING` and `DELIVERY` types are allowed.
	 *
	 * @var \HomeCredit\OneClickApi\Entity\GetOrderResponse\Addresses[]
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
	 * @var \HomeCredit\OneClickApi\Entity\GetOrderResponse\Items[]
	 * @required
	 */
	private $items;

	/**
	 * Order state.
	 *
	 * @var string
	 * @required
	 */
	private $state;

	/**
	 * Base information about respective application
	 *
	 * @var \HomeCredit\OneClickApi\Entity\GetOrderResponse\ApplicationInfo
	 * @required
	 */
	private $applicationInfo;

	/**
	 * @param string $number
	 * @param \HomeCredit\OneClickApi\Entity\GetOrderResponse\TotalPrice $totalPrice
	 * @param \HomeCredit\OneClickApi\Entity\GetOrderResponse\TotalVat[] $totalVat
	 * @param \HomeCredit\OneClickApi\Entity\GetOrderResponse\Addresses[] $addresses
	 * @param \HomeCredit\OneClickApi\Entity\GetOrderResponse\Items[] $items
	 * @param string $state
	 * @param \HomeCredit\OneClickApi\Entity\GetOrderResponse\ApplicationInfo $applicationInfo
	 * @param array|null $variableSymbols
	 * @param string|null $deliveryType
	 * @param string|null $reservationDate
	 */
	public function __construct(
		$number,
		\HomeCredit\OneClickApi\Entity\GetOrderResponse\TotalPrice $totalPrice,
		array $totalVat,
		array $addresses,
		array $items,
		$state,
		\HomeCredit\OneClickApi\Entity\GetOrderResponse\ApplicationInfo $applicationInfo,
		$variableSymbols = null,
		$deliveryType = null,
		$reservationDate = null
	)
	{
		$this->setNumber($number);
		$this->setTotalPrice($totalPrice);
		$this->setTotalVat($totalVat);
		$this->setAddresses($addresses);
		$this->setItems($items);
		$this->setState($state);
		$this->setApplicationInfo($applicationInfo);
		$this->setVariableSymbols($variableSymbols);
		$this->setDeliveryType($deliveryType);
		$this->setReservationDate($reservationDate);
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
	 * @return \HomeCredit\OneClickApi\Entity\GetOrderResponse\TotalPrice
	 */
	public function getTotalPrice()
	{
		return $this->totalPrice;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\GetOrderResponse\TotalVat[]
	 */
	public function getTotalVat()
	{
		return $this->totalVat;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\GetOrderResponse\Addresses[]
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
	 * @return \HomeCredit\OneClickApi\Entity\GetOrderResponse\Items[]
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * @return string
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\GetOrderResponse\ApplicationInfo
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
	 * @param \HomeCredit\OneClickApi\Entity\GetOrderResponse\TotalPrice $totalPrice
	 * @return $this
	 */
	public function setTotalPrice($totalPrice)
	{
		$this->assertNotNull($totalPrice);
		$this->totalPrice = $totalPrice;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\GetOrderResponse\TotalVat[] $totalVat
	 * @return $this
	 */
	public function setTotalVat($totalVat)
	{
		$this->assertNotNull($totalVat);
		$this->totalVat = $totalVat;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\GetOrderResponse\Addresses[] $addresses
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
	 * @param \HomeCredit\OneClickApi\Entity\GetOrderResponse\Items[] $items
	 * @return $this
	 */
	public function setItems($items)
	{
		$this->assertNotNull($items);
		$this->items = $items;
		return $this;
	}

	/**
	 * @param string $state
	 * @return $this
	 */
	public function setState($state)
	{
		$this->assertNotNull($state);
		$this->assertInArray($state, [self::STATE_PROCESSING, self::STATE_SENT, self::STATE_DELIVERED, self::STATE_RETURNED, self::STATE_CANCELLED]);
		$this->state = $state;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\GetOrderResponse\ApplicationInfo $applicationInfo
	 * @return $this
	 */
	public function setApplicationInfo($applicationInfo)
	{
		$this->assertNotNull($applicationInfo);
		$this->applicationInfo = $applicationInfo;
		return $this;
	}

}
