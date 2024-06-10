<?php

namespace HomeCredit\OneClickApi\Entity\CreateApplicationRequest;

use HomeCredit\OneClickApi\AEntity;

class MerchantUrls extends AEntity
{

	/**
	 * URL of the partner used for redirection of the customer back to partner website (from HCO gateway) after application approval. Do not implement any business logic on accessing this URL by customer. Implement business logic to READY notification via `notificationEndpoint`.
	 *
	 * @var string
	 * @required
	 */
	private $approvedRedirect;

	/**
	 * URL of the partner used for redirection of the customer back to partner website (from HCO gateway) after application rejection.  Do not implement any business logic on accessing this URL by customer. Implement business logic to REJECTED notification via `notificationEndpoint`.
	 *
	 * @var string
	 * @required
	 */
	private $rejectedRedirect;

	/**
	 * URL of the partner used as a notification endpoint for obtaining important updates about application (approval, rejection, storno, etc.).
	 *
	 * @var string
	 * @required
	 */
	private $notificationEndpoint;

	/**
	 * @param string $approvedRedirect
	 * @param string $rejectedRedirect
	 * @param string $notificationEndpoint
	 */
	public function __construct(
		$approvedRedirect,
		$rejectedRedirect,
		$notificationEndpoint
	)
	{
		$this->setApprovedRedirect($approvedRedirect);
		$this->setRejectedRedirect($rejectedRedirect);
		$this->setNotificationEndpoint($notificationEndpoint);
	}

	/**
	 * @return string
	 */
	public function getApprovedRedirect()
	{
		return $this->approvedRedirect;
	}

	/**
	 * @return string
	 */
	public function getRejectedRedirect()
	{
		return $this->rejectedRedirect;
	}

	/**
	 * @return string
	 */
	public function getNotificationEndpoint()
	{
		return $this->notificationEndpoint;
	}

	/**
	 * @param string $approvedRedirect
	 * @return $this
	 */
	public function setApprovedRedirect($approvedRedirect)
	{
		$this->assertNotNull($approvedRedirect);
		$this->approvedRedirect = $approvedRedirect;
		return $this;
	}

	/**
	 * @param string $rejectedRedirect
	 * @return $this
	 */
	public function setRejectedRedirect($rejectedRedirect)
	{
		$this->assertNotNull($rejectedRedirect);
		$this->rejectedRedirect = $rejectedRedirect;
		return $this;
	}

	/**
	 * @param string $notificationEndpoint
	 * @return $this
	 */
	public function setNotificationEndpoint($notificationEndpoint)
	{
		$this->assertNotNull($notificationEndpoint);
		$this->notificationEndpoint = $notificationEndpoint;
		return $this;
	}

}
