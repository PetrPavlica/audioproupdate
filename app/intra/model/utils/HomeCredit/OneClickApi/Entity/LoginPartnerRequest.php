<?php

namespace HomeCredit\OneClickApi\Entity;

use HomeCredit\OneClickApi\AEntity;

class LoginPartnerRequest extends AEntity
{

	/**
	 * Partner username
	 *
	 * @var string
	 * @required
	 */
	private $username;

	/**
	 * Partner secret password
	 *
	 * @var string
	 * @required
	 */
	private $password;

	/**
	 * @param string $username
	 * @param string $password
	 */
	public function __construct(
		$username,
		$password
	)
	{
		$this->setUsername($username);
		$this->setPassword($password);
	}

	/**
	 * @return string
	 */
	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * @param string $username
	 * @return $this
	 */
	public function setUsername($username)
	{
		$this->assertNotNull($username);
		$this->username = $username;
		return $this;
	}

	/**
	 * @param string $password
	 * @return $this
	 */
	public function setPassword($password)
	{
		$this->assertNotNull($password);
		$this->password = $password;
		return $this;
	}

}
