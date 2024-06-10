<?php

namespace HomeCredit\OneClickApi\Entity\CancelApplicationResponse;

use HomeCredit\OneClickApi\AEntity;

class Order extends AEntity
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
		'totalPrice' => \HomeCredit\OneClickApi\Entity\CancelApplicationResponse\Order\TotalPrice::class,
		'totalVat[]' => \HomeCredit\OneClickApi\Entity\CancelApplicationResponse\Order\TotalVat::class,
		'addresses[]' => \HomeCredit\OneClickApi\Entity\CancelApplicationResponse\Order\Addresses::class,
		'items[]' => \HomeCredit\OneClickApi\Entity\CancelApplicationResponse\Order\Items::class,
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
	 * @var \HomeCredit\OneClickApi\Entity\CancelApplicationResponse\Order\TotalPrice
	 * @required
	 */
	private $totalPrice;

	/**
	 * Total VAT amounts split by their VAT rates
	 *
	 * @var \HomeCredit\OneClickApi\Entity\CancelApplicationResponse\Order\TotalVat[]
	 * @required
	 */
	private $totalVat;

	/**
	 * Addresses. Only `BILLING` and `DELIVERY` types are allowed.
	 *
	 * @var \HomeCredit\OneClickApi\Entity\CancelApplicationResponse\Order\Addresses[]
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
	 * @var \HomeCredit\OneClickApi\Entity\CancelApplicationResponse\Order\Items[]
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
	 * @param string $number
	 * @param \HomeCredit\OneClickApi\Entity\CancelApplicationResponse\Order\TotalPrice $totalPrice
	 * @param \HomeCredit\OneClickApi\Entity\CancelApplicationResponse\Order\TotalVat[] $totalVat
	 * @param \HomeCredit\OneClickApi\Entity\CancelApplicationResponse\Order\Items[] $items
	 * @param string $state
	 * @param array|null $variableSymbols
	 * @param \HomeCredit\OneClickApi\Entity\CancelApplicationResponse\Order\Addresses[] $addresses
	 * @param string|null $deliveryType
	 * @param string|null $reservationDate
	 */
	public function __construct(
		$number,
		\HomeCredit\OneClickApi\Entity\CancelApplicationResponse\Order\TotalPrice $totalPrice,
		array $totalVat,
		array $items,
		$state,
		$variableSymbols = null,
		array $addresses = null,
		$deliveryType = null,
		$reservationDate = null
	)
	{
		$this->setNumber($number);
		$this->setTotalPrice($totalPrice);
		$this->setTotalVat($totalVat);
		$this->setItems($items);
		$this->setState($state);
		$this->setVariableSymbols($variableSymbols);
		$this->setAddresses($addresses);
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
	 * @return \HomeCredit\OneClickApi\Entity\CancelApplicationResponse\Order\TotalPrice
	 */
	public function getTotalPrice()
	{
		return $this->totalPrice;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\CancelApplicationResponse\Order\TotalVat[]
	 */
	public function getTotalVat()
	{
		return $this->totalVat;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\CancelApplicationResponse\Order\Addresses[]
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
	 * @return \HomeCredit\OneClickApi\Entity\CancelApplicationResponse\Order\Items[]
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
	 * @param \HomeCredit\OneClickApi\Entity\CancelApplicationResponse\Order\TotalPrice $totalPrice
	 * @return $this
	 */
	public function setTotalPrice($totalPrice)
	{
		$this->assertNotNull($totalPrice);
		$this->totalPrice = $totalPrice;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\CancelApplicationResponse\Order\TotalVat[] $totalVat
	 * @return $this
	 */
	public function setTotalVat($totalVat)
	{
		$this->assertNotNull($totalVat);
		$this->totalVat = $totalVat;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\CancelApplicationResponse\Order\Addresses[] $addresses
	 * @return $this
	 */
	public function setAddresses($addresses)
	{
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
	 * @param \HomeCredit\OneClickApi\Entity\CancelApplicationResponse\Order\Items[] $items
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

}
