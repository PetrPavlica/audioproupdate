<?php

namespace HomeCredit\OneClickApi\Entity\CreateApplicationResponse\Customer;

use HomeCredit\OneClickApi\AEntity;

class Addresses extends AEntity
{

	const ADDRESSTYPE_PERMANENT = 'PERMANENT';
	const ADDRESSTYPE_CONTACT = 'CONTACT';
	const ADDRESSTYPE_DELIVERY = 'DELIVERY';
	const ADDRESSTYPE_BILLING = 'BILLING';

	/**
	 * Name on address
	 *
	 * @var string|null
	 */
	private $name;

	/**
	 * Country.
(see [ISO 3166 alpha-2](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2))
	 *
	 * @var string|null
	 */
	private $country;

	/**
	 * City.
	 *
	 * @var string
	 * @required
	 */
	private $city;

	/**
	 * Street/city part.
	 *
	 * @var string
	 * @required
	 */
	private $streetAddress;

	/**
	 * Street number
	 *
	 * @var string
	 * @required
	 */
	private $streetNumber;

	/**
	 * Postal code
	 *
	 * @var string
	 * @required
	 */
	private $zip;

	/**
	 * Type of the address. Only some of the types are allowed in each context.
	 *
	 * @var string|null
	 */
	private $addressType;

	/**
	 * @param string $city
	 * @param string $streetAddress
	 * @param string $streetNumber
	 * @param string $zip
	 * @param string|null $name
	 * @param string|null $country
	 * @param string|null $addressType
	 */
	public function __construct(
		$city,
		$streetAddress,
		$streetNumber,
		$zip,
		$name = null,
		$country = null,
		$addressType = null
	)
	{
		$this->setCity($city);
		$this->setStreetAddress($streetAddress);
		$this->setStreetNumber($streetNumber);
		$this->setZip($zip);
		$this->setName($name);
		$this->setCountry($country);
		$this->setAddressType($addressType);
	}

	/**
	 * @return string|null
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string|null
	 */
	public function getCountry()
	{
		return $this->country;
	}

	/**
	 * @return string
	 */
	public function getCity()
	{
		return $this->city;
	}

	/**
	 * @return string
	 */
	public function getStreetAddress()
	{
		return $this->streetAddress;
	}

	/**
	 * @return string
	 */
	public function getStreetNumber()
	{
		return $this->streetNumber;
	}

	/**
	 * @return string
	 */
	public function getZip()
	{
		return $this->zip;
	}

	/**
	 * @return string|null
	 */
	public function getAddressType()
	{
		return $this->addressType;
	}

	/**
	 * @param string|null $name
	 * @return $this
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @param string|null $country
	 * @return $this
	 */
	public function setCountry($country)
	{
		$this->country = $country;
		return $this;
	}

	/**
	 * @param string $city
	 * @return $this
	 */
	public function setCity($city)
	{
		$this->assertNotNull($city);
		$this->city = $city;
		return $this;
	}

	/**
	 * @param string $streetAddress
	 * @return $this
	 */
	public function setStreetAddress($streetAddress)
	{
		$this->assertNotNull($streetAddress);
		$this->streetAddress = $streetAddress;
		return $this;
	}

	/**
	 * @param string $streetNumber
	 * @return $this
	 */
	public function setStreetNumber($streetNumber)
	{
		$this->assertNotNull($streetNumber);
		$this->streetNumber = $streetNumber;
		return $this;
	}

	/**
	 * @param string $zip
	 * @return $this
	 */
	public function setZip($zip)
	{
		$this->assertNotNull($zip);
		$this->zip = $zip;
		return $this;
	}

	/**
	 * @param string|null $addressType
	 * @return $this
	 */
	public function setAddressType($addressType)
	{
		if (!is_null($addressType)) {
			$this->assertInArray($addressType, [self::ADDRESSTYPE_PERMANENT, self::ADDRESSTYPE_CONTACT, self::ADDRESSTYPE_DELIVERY, self::ADDRESSTYPE_BILLING]);
		}
		$this->addressType = $addressType;
		return $this;
	}

}
