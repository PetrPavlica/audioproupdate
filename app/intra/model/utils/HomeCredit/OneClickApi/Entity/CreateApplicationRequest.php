<?php

namespace HomeCredit\OneClickApi\Entity;

use HomeCredit\OneClickApi\AEntity;

class CreateApplicationRequest extends AEntity
{

	const TYPE_INSTALLMENT = 'INSTALLMENT';

	protected static $associations = [
		'customer' => \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer::class,
		'order' => \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Order::class,
		'settingsInstallment' => \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\SettingsInstallment::class,
		'merchantUrls' => \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\MerchantUrls::class,
	];

	/**
	 * Customer information
	 *
	 * @var \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer
	 * @required
	 */
	private $customer;

	/**
	 * Order information
	 *
	 * @var \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Order
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
	 * @var \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\SettingsInstallment
	 */
	private $settingsInstallment;

	/**
	 * Collection of partner's URLs used for redirection of a customer back to a partner website or as a notification endpoint
	 *
	 * @var \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\MerchantUrls
	 * @required
	 */
	private $merchantUrls;

	/**
	 * @param \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer $customer
	 * @param \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Order $order
	 * @param string $type
	 * @param \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\MerchantUrls $merchantUrls
	 * @param \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\SettingsInstallment $settingsInstallment
	 */
	public function __construct(
		\HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer $customer,
		\HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Order $order,
		$type,
		\HomeCredit\OneClickApi\Entity\CreateApplicationRequest\MerchantUrls $merchantUrls,
		\HomeCredit\OneClickApi\Entity\CreateApplicationRequest\SettingsInstallment $settingsInstallment = null
	)
	{
		$this->setCustomer($customer);
		$this->setOrder($order);
		$this->setType($type);
		$this->setMerchantUrls($merchantUrls);
		$this->setSettingsInstallment($settingsInstallment);
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer
	 */
	public function getCustomer()
	{
		return $this->customer;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Order
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
	 * @return \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\SettingsInstallment
	 */
	public function getSettingsInstallment()
	{
		return $this->settingsInstallment;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\MerchantUrls
	 */
	public function getMerchantUrls()
	{
		return $this->merchantUrls;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer $customer
	 * @return $this
	 */
	public function setCustomer($customer)
	{
		$this->assertNotNull($customer);
		$this->customer = $customer;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Order $order
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
	 * @param \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\SettingsInstallment $settingsInstallment
	 * @return $this
	 */
	public function setSettingsInstallment($settingsInstallment)
	{
		$this->settingsInstallment = $settingsInstallment;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\MerchantUrls $merchantUrls
	 * @return $this
	 */
	public function setMerchantUrls($merchantUrls)
	{
		$this->assertNotNull($merchantUrls);
		$this->merchantUrls = $merchantUrls;
		return $this;
	}

}
