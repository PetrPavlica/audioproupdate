<?php

namespace HomeCredit\OneClickApi\RestClient\AuthorizationProcess;

use GuzzleHttp\Client;
use HomeCredit\OneClickApi\Entity\LoginPartnerRequest;
use Nette\Utils\Json;

class AuthTokenAuthorizationProcess implements IAuthorizationProcess
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
	 * @var string|null
	 */
	private $accessToken = null;

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
	 * @throws \Nette\Utils\JsonException
	 * @throws \Exception
	 */
	public function login(Client $client)
	{
		$result = Json::decode((string) $client->post('authentication/v1/partner', [
			'json' => LoginPartnerRequest::fromArray([
				'username' => $this->userName,
				'password' => $this->password,
			])
		])->getBody());
		$this->accessToken = $result->accessToken;
	}

	public function reset()
	{
		$this->accessToken = null;
	}

	/**
	 * @return bool
	 */
	public function isLoggedIn()
	{
		return !is_null($this->accessToken);
	}

	/**
	 * @return string[][]
	 */
	public function getAuthHeaders()
	{
		return [[
			'name' => 'Authorization',
			'value' => 'Bearer ' . $this->accessToken
		]];
	}

}
