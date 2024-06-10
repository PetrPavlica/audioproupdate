<?php

namespace HomeCredit\OneClickApi\RestClient\AuthorizationProcess;

use GuzzleHttp\Client;

class BasicHttpAuthorizationProcess implements IAuthorizationProcess
{

	/**
	 * @var string
	 */
	private $userName;

	/**
	 * @var string
	 */
	private $password;

	/**
	 * BasicHttpAuthorizationProcess constructor.
	 * @param string $userName
	 * @param string $password
	 */
	public function __construct($userName, $password)
	{
		$this->userName = $userName;
		$this->password = $password;
	}

	/**
	 * @param Client $client
	 * @throws \Exception
	 */
	public function login(Client $client)
	{
	}

	public function reset()
	{
	}

	/**
	 * @return bool
	 */
	public function isLoggedIn()
	{
		return true;
	}

	public function getAuthHeaders()
	{
		return [[
			'name' => 'Authorization',
			'value' => base64_encode($this->userName . ':' . $this->password)
		]];
	}

}
