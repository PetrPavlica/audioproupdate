<?php

namespace HomeCredit\OneClickApi\RestClient;

use HomeCredit\OneClickApi\Entity\CancelApplicationRequest;
use HomeCredit\OneClickApi\Entity\CancelApplicationResponse;
use HomeCredit\OneClickApi\Entity\ChangeApplicationOrderRequest;
use HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse;
use HomeCredit\OneClickApi\Entity\CreateApplicationRequest;
use HomeCredit\OneClickApi\Entity\CreateApplicationResponse;
use HomeCredit\OneClickApi\Entity\GetApplicationDetail;
use HomeCredit\OneClickApi\Entity\GetApplicationDetailResponse;
use HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse;
use HomeCredit\OneClickApi\Entity\MarkOrderAsSentResponse;
use HomeCredit\OneClickApi\RestClient;
use Nette\Utils\Json;

class Application extends RestClient
{

	/**
	 * @param CreateApplicationRequest $createApplicationRequest
	 * @return CreateApplicationResponse
	 * @throws \Nette\Utils\JsonException
	 * @throws \Exception
	 */
	public function create(CreateApplicationRequest $createApplicationRequest)
	{
	    $response = $this->getHttpClient()->post('financing/v1/applications', ['json' => $createApplicationRequest]);

		/** @var CreateApplicationResponse $result */
		$result = CreateApplicationResponse::fromArray(Json::decode((string) $response->getBody(), Json::FORCE_ARRAY));
		return $result;
	}

	/**
	 * @param string $applicationId
	 * @return GetApplicationDetailResponse
	 * @throws \Nette\Utils\JsonException
	 * @throws \Exception
	 */
	public function getDetail($applicationId)
	{
		$response = $this->getHttpClient()->get('financing/v1/applications/' . (string) $applicationId);

		/** @var GetApplicationDetailResponse $result */
		$result = GetApplicationDetailResponse::fromArray(Json::decode((string) $response->getBody(), Json::FORCE_ARRAY));
		return $result;
	}

	/**
	 * @param string $applicationId
	 * @param CancelApplicationRequest $cancelApplicationRequest
	 * @return CancelApplicationResponse
	 * @throws \Nette\Utils\JsonException
	 * @throws \Exception
	 */
	public function cancel($applicationId, CancelApplicationRequest $cancelApplicationRequest)
	{
		$response = $this->getHttpClient()->put('financing/v1/applications/' . (string) $applicationId . '/cancel', [
			'json' => $cancelApplicationRequest
		]);

		/** @var CancelApplicationResponse $result */
		$result = CancelApplicationResponse::fromArray(Json::decode((string) $response->getBody(), Json::FORCE_ARRAY));
		return $result;
	}

	/**
	 * @param string $applicationId
	 * @param ChangeApplicationOrderRequest $changeApplicationOrderRequest
	 * @return ChangeApplicationOrderResponse
	 * @throws \Nette\Utils\JsonException
	 * @throws \Exception
	 */
	public function changeOrder($applicationId, ChangeApplicationOrderRequest $changeApplicationOrderRequest)
	{
		$response = $this->getHttpClient()->put('financing/v1/applications/' . (string) $applicationId . '/order', [
			'json' => $changeApplicationOrderRequest
		]);

		/** @var ChangeApplicationOrderResponse $result */
		$result = ChangeApplicationOrderResponse::fromArray(Json::decode((string) $response->getBody(), Json::FORCE_ARRAY));
		return $result;
	}

	/**
	 * @param string $applicationId
	 * @return MarkOrderAsSentResponse
	 * @throws \Nette\Utils\JsonException
	 * @throws \Exception
	 */
	public function markOrderAsSent($applicationId)
	{
		$response = $this->getHttpClient()->put('financing/v1/applications/' . (string) $applicationId . '/order/send');

		/** @var MarkOrderAsSentResponse $result */
		$result = MarkOrderAsSentResponse::fromArray(Json::decode((string) $response->getBody(), Json::FORCE_ARRAY));
		return $result;
	}

	/**
	 * @param string $applicationId
	 * @return MarkOrderAsDeliveredResponse
	 * @throws \Nette\Utils\JsonException
	 * @throws \Exception
	 */
	public function markOrderAsDelivered($applicationId)
	{
		$response = $this->getHttpClient()->put('financing/v1/applications/' . (string) $applicationId . '/order/deliver');

		/** @var MarkOrderAsDeliveredResponse $result */
		$result = MarkOrderAsDeliveredResponse::fromArray(Json::decode((string) $response->getBody(), Json::FORCE_ARRAY));
		return $result;
	}

}
