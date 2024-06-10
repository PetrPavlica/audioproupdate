<?php

namespace HomeCredit\OneClickApi\Entity;

use HomeCredit\OneClickApi\AEntity;

class CancelApplicationRequest extends AEntity
{

	const REASON_APPLICATION_CANCELLED_CARRIER_CHANGED = 'APPLICATION_CANCELLED_CARRIER_CHANGED';
	const REASON_APPLICATION_CANCELLED_CART_CONTENT_CHANGED = 'APPLICATION_CANCELLED_CART_CONTENT_CHANGED';
	const REASON_APPLICATION_CANCELLED_BY_CUSTOMER = 'APPLICATION_CANCELLED_BY_CUSTOMER';
	const REASON_APPLICATION_CANCELLED_BY_ERP = 'APPLICATION_CANCELLED_BY_ERP';
	const REASON_APPLICATION_CANCELLED_EXPIRED = 'APPLICATION_CANCELLED_EXPIRED';
	const REASON_APPLICATION_CANCELLED_UNFINISHED = 'APPLICATION_CANCELLED_UNFINISHED';
	const REASON_APPLICATION_CANCELLED_BY_ESHOP_RULES = 'APPLICATION_CANCELLED_BY_ESHOP_RULES';
	const REASON_APPLICATION_CANCELLED_OTHER = 'APPLICATION_CANCELLED_OTHER';

	/**
	 * Reason of cancellation. Considered as `APPLICATION_CANCELLED_OTHER` if not set.
	 *
	 * @var string|null
	 */
	private $reason;

	/**
	 * Specification of `APPLICATION_CANCELLED_OTHER` reason
	 *
	 * @var string|null
	 */
	private $customReason;

	/**
	 * @param string|null $reason
	 * @param string|null $customReason
	 */
	public function __construct(
		$reason = null,
		$customReason = null
	)
	{
		$this->setReason($reason);
		$this->setCustomReason($customReason);
	}

	/**
	 * @return string|null
	 */
	public function getReason()
	{
		return $this->reason;
	}

	/**
	 * @return string|null
	 */
	public function getCustomReason()
	{
		return $this->customReason;
	}

	/**
	 * @param string|null $reason
	 * @return $this
	 */
	public function setReason($reason)
	{
		if (!is_null($reason)) {
			$this->assertInArray($reason, [self::REASON_APPLICATION_CANCELLED_CARRIER_CHANGED, self::REASON_APPLICATION_CANCELLED_CART_CONTENT_CHANGED, self::REASON_APPLICATION_CANCELLED_BY_CUSTOMER, self::REASON_APPLICATION_CANCELLED_BY_ERP, self::REASON_APPLICATION_CANCELLED_EXPIRED, self::REASON_APPLICATION_CANCELLED_UNFINISHED, self::REASON_APPLICATION_CANCELLED_BY_ESHOP_RULES, self::REASON_APPLICATION_CANCELLED_OTHER]);
		}
		$this->reason = $reason;
		return $this;
	}

	/**
	 * @param string|null $customReason
	 * @return $this
	 */
	public function setCustomReason($customReason)
	{
		$this->customReason = $customReason;
		return $this;
	}

}
