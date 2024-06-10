<?php

namespace HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse;

use HomeCredit\OneClickApi\AEntity;

class Items extends AEntity
{

	const TYPE_PHYSICAL = 'PHYSICAL';
	const TYPE_DISCOUNT = 'DISCOUNT';
	const TYPE_SHIPPING_FEE = 'SHIPPING_FEE';
	const TYPE_SALES_TAX = 'SALES_TAX';
	const TYPE_DIGITAL = 'DIGITAL';
	const TYPE_GIFT_CARD = 'GIFT_CARD';
	const TYPE_STORE_CREDIT = 'STORE_CREDIT';
	const TYPE_FEE = 'FEE';
	const TYPE_INSURANCE = 'INSURANCE';
	const STATE_PROCESSING = 'PROCESSING';
	const STATE_SENT = 'SENT';
	const STATE_DELIVERED = 'DELIVERED';
	const STATE_RETURNED = 'RETURNED';
	const STATE_CANCELLED = 'CANCELLED';

	protected static $associations = [
		'unitPrice' => \HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\UnitPrice::class,
		'unitVat' => \HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\UnitVat::class,
		'totalPrice' => \HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\TotalPrice::class,
		'totalVat' => \HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\TotalVat::class,
		'image' => \HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\Image::class,
	];

	/**
	 * Internal code for item (internal to e-shop). Used to better identify the item for future changes
	 *
	 * @var string
	 * @required
	 */
	private $code;

	/**
	 * EAN code.
	 *
	 * @var string|null
	 */
	private $ean;

	/**
	 * Item name
	 *
	 * @var string
	 * @required
	 */
	private $name;

	/**
	 * Item quantity. If empty, considered as 1.
	 *
	 * @var float|null
	 */
	private $quantity;

	/**
	 * Item type
	 *
	 * @var string|null
	 */
	private $type;

	/**
	 * @var string|null
	 */
	private $producer;

	/**
	 * Price per one piece. If empty, automatically considered that `unitPrice` is `totalPrice` / `quantity`.
	 *
	 * @var \HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\UnitPrice
	 */
	private $unitPrice;

	/**
	 * VAT amount per one piece. If empty, automatically considered that `unitVat` is `totalVat` / `quantity`.
	 *
	 * @var \HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\UnitVat
	 */
	private $unitVat;

	/**
	 * Total price for all pieces, VAT inclusive
	 *
	 * @var \HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\TotalPrice
	 * @required
	 */
	private $totalPrice;

	/**
	 * Total VAT amount for all pieces
	 *
	 * @var \HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\TotalVat
	 * @required
	 */
	private $totalVat;

	/**
	 * Item image
	 *
	 * @var \HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\Image
	 */
	private $image;

	/**
	 * Order state.
	 *
	 * @var string|null
	 */
	private $state;

	/**
	 * @param string $code
	 * @param string $name
	 * @param \HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\TotalPrice $totalPrice
	 * @param \HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\TotalVat $totalVat
	 * @param string|null $ean
	 * @param float|null $quantity
	 * @param string|null $type
	 * @param string|null $producer
	 * @param \HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\UnitPrice $unitPrice
	 * @param \HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\UnitVat $unitVat
	 * @param \HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\Image $image
	 * @param string|null $state
	 */
	public function __construct(
		$code,
		$name,
		\HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\TotalPrice $totalPrice,
		\HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\TotalVat $totalVat,
		$ean = null,
		$quantity = null,
		$type = null,
		$producer = null,
		\HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\UnitPrice $unitPrice = null,
		\HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\UnitVat $unitVat = null,
		\HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\Image $image = null,
		$state = null
	)
	{
		$this->setCode($code);
		$this->setName($name);
		$this->setTotalPrice($totalPrice);
		$this->setTotalVat($totalVat);
		$this->setEan($ean);
		$this->setQuantity($quantity);
		$this->setType($type);
		$this->setProducer($producer);
		$this->setUnitPrice($unitPrice);
		$this->setUnitVat($unitVat);
		$this->setImage($image);
		$this->setState($state);
	}

	/**
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @return string|null
	 */
	public function getEan()
	{
		return $this->ean;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return float|null
	 */
	public function getQuantity()
	{
		return $this->quantity;
	}

	/**
	 * @return string|null
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @return string|null
	 */
	public function getProducer()
	{
		return $this->producer;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\UnitPrice
	 */
	public function getUnitPrice()
	{
		return $this->unitPrice;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\UnitVat
	 */
	public function getUnitVat()
	{
		return $this->unitVat;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\TotalPrice
	 */
	public function getTotalPrice()
	{
		return $this->totalPrice;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\TotalVat
	 */
	public function getTotalVat()
	{
		return $this->totalVat;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\Image
	 */
	public function getImage()
	{
		return $this->image;
	}

	/**
	 * @return string|null
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * @param string $code
	 * @return $this
	 */
	public function setCode($code)
	{
		$this->assertNotNull($code);
		$this->code = $code;
		return $this;
	}

	/**
	 * @param string|null $ean
	 * @return $this
	 */
	public function setEan($ean)
	{
		$this->ean = $ean;
		return $this;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function setName($name)
	{
		$this->assertNotNull($name);
		$this->name = $name;
		return $this;
	}

	/**
	 * @param float|null $quantity
	 * @return $this
	 */
	public function setQuantity($quantity)
	{
		$this->quantity = $quantity;
		return $this;
	}

	/**
	 * @param string|null $type
	 * @return $this
	 */
	public function setType($type)
	{
		if (!is_null($type)) {
			$this->assertInArray($type, [self::TYPE_PHYSICAL, self::TYPE_DISCOUNT, self::TYPE_SHIPPING_FEE, self::TYPE_SALES_TAX, self::TYPE_DIGITAL, self::TYPE_GIFT_CARD, self::TYPE_STORE_CREDIT, self::TYPE_FEE, self::TYPE_INSURANCE]);
		}
		$this->type = $type;
		return $this;
	}

	/**
	 * @param string|null $producer
	 * @return $this
	 */
	public function setProducer($producer)
	{
		$this->producer = $producer;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\UnitPrice $unitPrice
	 * @return $this
	 */
	public function setUnitPrice($unitPrice)
	{
		$this->unitPrice = $unitPrice;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\UnitVat $unitVat
	 * @return $this
	 */
	public function setUnitVat($unitVat)
	{
		$this->unitVat = $unitVat;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\TotalPrice $totalPrice
	 * @return $this
	 */
	public function setTotalPrice($totalPrice)
	{
		$this->assertNotNull($totalPrice);
		$this->totalPrice = $totalPrice;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\TotalVat $totalVat
	 * @return $this
	 */
	public function setTotalVat($totalVat)
	{
		$this->assertNotNull($totalVat);
		$this->totalVat = $totalVat;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items\Image $image
	 * @return $this
	 */
	public function setImage($image)
	{
		$this->image = $image;
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

}
