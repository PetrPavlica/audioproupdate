<?php

namespace HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer;

use HomeCredit\OneClickApi\AEntity;

class FingerprintComponents extends AEntity
{

	/**
	 * Key
	 *
	 * @var string
	 * @required
	 */
	private $key;

	/**
	 * Value
	 *
	 * @var string
	 * @required
	 */
	private $value;

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function __construct(
		$key,
		$value
	)
	{
		$this->setKey($key);
		$this->setValue($value);
	}

	/**
	 * @return string
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param string $key
	 * @return $this
	 */
	public function setKey($key)
	{
		$this->assertNotNull($key);
		$this->key = $key;
		return $this;
	}

	/**
	 * @param string $value
	 * @return $this
	 */
	public function setValue($value)
	{
		$this->assertNotNull($value);
		$this->value = $value;
		return $this;
	}

}
