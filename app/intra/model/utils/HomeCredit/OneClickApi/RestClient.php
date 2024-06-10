<?php

namespace HomeCredit\OneClickApi;

use GuzzleHttp\Client;
use HomeCredit\OneClickApi\RestClient\AuthorizationProcess\IAuthorizationProcess;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RestClient
{

	/**
	 * @var IAuthorizationProcess
	 */
	private $authorizationProcess;

	/**
	 * @var HttpClientFactory
	 */
	private $httpClientFactory;

	/**
	 * @param HttpClientFactory $httpClientFactory
	 * @param IAuthorizationProcess $authorizationProcess
	 */
	public function __construct(HttpClientFactory $httpClientFactory, IAuthorizationProcess $authorizationProcess)
	{
		$this->authorizationProcess = $authorizationProcess;
		$this->httpClientFactory = $httpClientFactory;
	}

	public function login()
	{
		$this->authorizationProcess->login($this->httpClientFactory->getClient([
			'http_errors' => true,
			'synchronous' => true,
		]));
	}

	/**
	 * Metoda vraci callback, ktery z authorizacniho procesu vytahne potrebne a nastavi do requestu
	 *
	 * @return \Closure
	 */
	private function handleAuthorizationHeader()
	{
		return function (callable  $handler) {
			return function (RequestInterface $request, array $options) use ($handler) {
				if ($this->authorizationProcess->isLoggedIn()) {
					foreach ($this->authorizationProcess->getAuthHeaders() as $authHeader) {
						if (isset($authHeader['name']) && isset($authHeader['value'])) {
							$request = $request->withHeader($authHeader['name'], $authHeader['value']);
						}
					}
				}
				return $handler($request, $options);
			};
		};
	}

	/**
	 * Metoda vraci callback pro testovani, zda bude pri pristim volani nutne zopakovat prihlaseni
	 *
	 * @return \Closure
	 */
	private function handleObtainNewKey()
	{
		return function (callable $handler) {
			return function (RequestInterface $request, array $options) use ($handler) {
				return $handler($request, $options)->then(
					function (ResponseInterface $response) {
						$code = $response->getStatusCode();
						if ($code == 403) {
							$this->authorizationProcess->reset();
						}
						return $response;
					}
				);
			};
		};
	}

	/**
	 * @return Client
	 */
	protected function getHttpClient()
	{
		if (!$this->authorizationProcess->isLoggedIn()) {
			$this->authorizationProcess->login($this->httpClientFactory->getClient([
				'http_errors' => true,
				'synchronous' => true,
			]));
		}
		return $this->httpClientFactory->getClient([
			'http_errors' => true,
			'synchronous' => true,
		], [
			$this->handleAuthorizationHeader(),
			$this->handleObtainNewKey(),
		]);
	}

}
