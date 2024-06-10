<?php

namespace HomeCredit\OneClickApi\RestClient;

use HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailRequest;
use HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailResponse;
use HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsRequest;
use HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsResponse;
use HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferRequest;
use HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferResponse;
use HomeCredit\OneClickApi\RestClient;
use Nette\Utils\Json;

class InstallmentsCalculator extends RestClient
{

	/**
	 * @param CalculateInstallmentProgramsOfferRequest $calculateInstallmentProgramsOfferRequest
	 * @return CalculateInstallmentProgramsOfferResponse
	 * @throws \Exception
	 */
	public function calculateInstallmentProgramsOffer(CalculateInstallmentProgramsOfferRequest $calculateInstallmentProgramsOfferRequest)
	{
		$response = $this->getHttpClient()->post('financing/v1/installmentProgramsOffers', ['json' => $calculateInstallmentProgramsOfferRequest]);

		/** @var CalculateInstallmentProgramsOfferResponse $result */
		$result = CalculateInstallmentProgramsOfferResponse::fromArray(Json::decode((string) $response->getBody(), Json::FORCE_ARRAY));
		return $result;
	}

	/**
	 * @param CalculateInstallmentProgramDetailRequest $calculateInstallmentProgramDetailRequest
	 * @return CalculateInstallmentProgramDetailResponse
	 * @throws \Exception
	 */
	public function calculateInstallmentProgramDetail(CalculateInstallmentProgramDetailRequest $calculateInstallmentProgramDetailRequest)
	{
		$response = $this->getHttpClient()->post('financing/v1/installmentPrograms', ['json' => $calculateInstallmentProgramDetailRequest]);

		/** @var CalculateInstallmentProgramDetailResponse $result */
		$result = CalculateInstallmentProgramDetailResponse::fromArray(Json::decode((string) $response->getBody(), Json::FORCE_ARRAY));
		return $result;
	}

	/**
	 * @param CalculateInstallmentProgramsDownPaymentsRequest $calculateInstallmentProgramsDownPaymentsRequest
	 * @return CalculateInstallmentProgramsDownPaymentsResponse
	 * @throws \Exception
	 */
	public function calculateInstallmentProgramDownPayment(CalculateInstallmentProgramsDownPaymentsRequest $calculateInstallmentProgramsDownPaymentsRequest)
	{
		$response = $this->getHttpClient()->post('financing/v1/installmentProgramsDownPayments', ['json' => $calculateInstallmentProgramsDownPaymentsRequest]);

		/** @var CalculateInstallmentProgramsDownPaymentsResponse $result */
		$result = CalculateInstallmentProgramsDownPaymentsResponse::fromArray(Json::decode((string) $response->getBody(), Json::FORCE_ARRAY));
		return $result;
	}

}
