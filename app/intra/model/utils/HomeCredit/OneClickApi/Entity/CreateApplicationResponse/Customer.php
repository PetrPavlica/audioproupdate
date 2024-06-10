<?php

namespace HomeCredit\OneClickApi\Entity\CreateApplicationResponse;

use HomeCredit\OneClickApi\AEntity;

class Customer extends AEntity
{

	protected static $associations = [
		'addresses[]' => \HomeCredit\OneClickApi\Entity\CreateApplicationResponse\Customer\Addresses::class,
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
	 * Addresses. All types are allowed.
	 *
	 * @var \HomeCredit\OneClickApi\Entity\CreateApplicationResponse\Customer\Addresses[]
	 */
	private $addresses;

	/**
	 * Tax identification number (ICO)
	 *
	 * @var string|null
	 */
	private $tin;

	/**
	 * @param string $email
	 * @param string $phone
	 * @param string|null $firstName
	 * @param string|null $lastName
	 * @param string|null $fullName
	 * @param \HomeCredit\OneClickApi\Entity\CreateApplicationResponse\Customer\Addresses[] $addresses
	 * @param string|null $tin
	 */
	public function __construct(
		$email,
		$phone,
		$firstName = null,
		$lastName = null,
		$fullName = null,
		array $addresses = null,
		$tin = null
	)
	{
		$this->setEmail($email);
		$this->setPhone($phone);
		$this->setFirstName($firstName);
		$this->setLastName($lastName);
		$this->setFullName($fullName);
		$this->setAddresses($addresses);
		$this->setTin($tin);
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
	 * @return \HomeCredit\OneClickApi\Entity\CreateApplicationResponse\Customer\Addresses[]
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
	 * @param \HomeCredit\OneClickApi\Entity\CreateApplicationResponse\Customer\Addresses[] $addresses
	 * @return $this
	 */
	public function setAddresses($addresses)
	{
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

}
