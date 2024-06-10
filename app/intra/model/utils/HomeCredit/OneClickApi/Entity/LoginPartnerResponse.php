<?php

namespace HomeCredit\OneClickApi\Entity;

use HomeCredit\OneClickApi\AEntity;

class LoginPartnerResponse extends AEntity
{

	/**
	 * Access token
	 *
	 * @var string|null
	 */
	private $accessToken;

	/**
	 * Token validity remaining time (in seconds)
	 *
	 * @var float|null
	 */
	private $expiresIn;

	/**
	 * @param string|null $accessToken
	 * @param float|null $expiresIn
	 */
	public function __construct(
		$accessToken = null,
		$expiresIn = null
	)
	{
		$this->setAccessToken($accessToken);
		$this->setExpiresIn($expiresIn);
	}

	/**
	 * @return string|null
	 */
	public function getAccessToken()
	{
		return $this->accessToken;
	}

	/**
	 * @return float|null
	 */
	public function getExpiresIn()
	{
		return $this->expiresIn;
	}

	/**
	 * @param string|null $accessToken
	 * @return $this
	 */
	public function setAccessToken($accessToken)
	{
		$this->accessToken = $accessToken;
		return $this;
	}

	/**
	 * @param float|null $expiresIn
	 * @return $this
	 */
	public function setExpiresIn($expiresIn)
	{
		$this->expiresIn = $expiresIn;
		return $this;
	}

}
