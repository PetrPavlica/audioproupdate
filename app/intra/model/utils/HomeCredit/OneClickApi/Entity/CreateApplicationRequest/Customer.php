<?php

namespace HomeCredit\OneClickApi\Entity\CreateApplicationRequest;

use HomeCredit\OneClickApi\AEntity;

class Customer extends AEntity
{

	protected static $associations = [
		'addresses[]' => \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\Addresses::class,
		'fingerprintComponents[]' => \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\FingerprintComponents::class,
		'extraData' => \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\ExtraData::class,
	];

	/**
	 * Customer first (given) name. Must be paired with `lastName`. Required if `fullName` is empty.
	 *
	 * @var string|null
	 */
	private $firstName;

	/**
	 * Customer last (family) name. Must be paired with `firstName`. Required if `fullName` is empty.
	 *
	 * @var string|null
	 */
	private $lastName;

	/**
	 * Customer full name, including academical degrees and salutation. Required only if `firstName` and `lastName` are empty.
	 *
	 * @var string|null
	 */
	private $fullName;

	/**
	 * E-mail address of customer
	 *
	 * @var string
	 * @required
	 */
	private $email;

	/**
	 * Phone number with country code (including leading `+`)
	 *
	 * @var string
	 * @required
	 */
	private $phone;

	/**
	 * Addresses. Only `BILLING` and `DELIVERY` types are allowed.
	 *
	 * @var \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\Addresses[]
	 * @required
	 */
	private $addresses;

	/**
	 * Tax identification number (ICO)
	 *
	 * @var string|null
	 */
	private $tin;

	/**
	 * IPv4 or IPv6 address of a customer.
	 *
	 * @var string|null
	 */
	private $ipAddress;

	/**
	 * Fingerprints components.  Fill this property with data created with HCO JS library.
	 *
	 * @var \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\FingerprintComponents[]
	 */
	private $fingerprintComponents;

	/**
	 * Additional data, that may improve the approval probability.
	 *
	 * @var \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\ExtraData
	 */
	private $extraData;

	/**
	 * @param string $email
	 * @param string $phone
	 * @param \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\Addresses[] $addresses
	 * @param string|null $firstName
	 * @param string|null $lastName
	 * @param string|null $fullName
	 * @param string|null $tin
	 * @param string|null $ipAddress
	 * @param \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\FingerprintComponents[] $fingerprintComponents
	 * @param \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\ExtraData $extraData
	 */
	public function __construct(
		$email,
		$phone,
		array $addresses,
		$firstName = null,
		$lastName = null,
		$fullName = null,
		$tin = null,
		$ipAddress = null,
		array $fingerprintComponents = null,
		\HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\ExtraData $extraData = null
	)
	{
		$this->setEmail($email);
		$this->setPhone($phone);
		$this->setAddresses($addresses);
		$this->setFirstName($firstName);
		$this->setLastName($lastName);
		$this->setFullName($fullName);
		$this->setTin($tin);
		$this->setIpAddress($ipAddress);
		$this->setFingerprintComponents($fingerprintComponents);
		$this->setExtraData($extraData);
	}

	/**
	 * @return string|null
	 */
	public function getFirstName()
	{
		return $this->firstName;
	}

	/**
	 * @return string|null
	 */
	public function getLastName()
	{
		return $this->lastName;
	}

	/**
	 * @return string|null
	 */
	public function getFullName()
	{
		return $this->fullName;
	}

	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @return string
	 */
	public function getPhone()
	{
		return $this->phone;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\Addresses[]
	 */
	public function getAddresses()
	{
		return $this->addresses;
	}

	/**
	 * @return string|null
	 */
	public function getTin()
	{
		return $this->tin;
	}

	/**
	 * @return string|null
	 */
	public function getIpAddress()
	{
		return $this->ipAddress;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\FingerprintComponents[]
	 */
	public function getFingerprintComponents()
	{
		return $this->fingerprintComponents;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\ExtraData
	 */
	public function getExtraData()
	{
		return $this->extraData;
	}

	/**
	 * @param string|null $firstName
	 * @return $this
	 */
	public function setFirstName($firstName)
	{
		$this->firstName = $firstName;
		return $this;
	}

	/**
	 * @param string|null $lastName
	 * @return $this
	 */
	public function setLastName($lastName)
	{
		$this->lastName = $lastName;
		return $this;
	}

	/**
	 * @param string|null $fullName
	 * @return $this
	 */
	public function setFullName($fullName)
	{
		$this->fullName = $fullName;
		return $this;
	}

	/**
	 * @param string $email
	 * @return $this
	 */
	public function setEmail($email)
	{
		$this->assertNotNull($email);
		$this->email = $email;
		return $this;
	}

	/**
	 * @param string $phone
	 * @return $this
	 */
	public function setPhone($phone)
	{
		$this->assertNotNull($phone);
		$this->phone = $phone;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\Addresses[] $addresses
	 * @return $this
	 */
	public function setAddresses($addresses)
	{
		$this->assertNotNull($addresses);
		$this->addresses = $addresses;
		return $this;
	}

	/**
	 * @param string|null $tin
	 * @return $this
	 */
	public function setTin($tin)
	{
		$this->tin = $tin;
		return $this;
	}

	/**
	 * @param string|null $ipAddress
	 * @return $this
	 */
	public function setIpAddress($ipAddress)
	{
		$this->ipAddress = $ipAddress;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\FingerprintComponents[] $fingerprintComponents
	 * @return $this
	 */
	public function setFingerprintComponents($fingerprintComponents)
	{
		$this->fingerprintComponents = $fingerprintComponents;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\ExtraData $extraData
	 * @return $this
	 */
	public function setExtraData($extraData)
	{
		$this->extraData = $extraData;
		return $this;
	}

}
