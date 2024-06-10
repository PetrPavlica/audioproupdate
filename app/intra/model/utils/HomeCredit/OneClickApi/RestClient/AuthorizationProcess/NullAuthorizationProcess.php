<?php

namespace HomeCredit\OneClickApi\RestClient\AuthorizationProcess;

use GuzzleHttp\Client;

class NullAuthorizationProcess implements IAuthorizationProcess
{

	private $isLoggedIn = false;

	/**
	 * @param Client $client
	 * @throws \Exception
	 */
	public function login(Client $client)
	{
		$this->isLoggedIn = true;
	}

	public function reset()
	{
		$this->isLoggedIn = false;
	}

	/**
	 * @return bool
	 */
	public function isLoggedIn()
	{
		return $this->isLoggedIn;
	}

	public function getAuthHeaders()
	{
		return [];
	}

}
