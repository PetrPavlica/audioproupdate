<?php

namespace HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer;

use HomeCredit\OneClickApi\AEntity;

class ExtraData extends AEntity
{

	protected static $associations = [
		'transactionsSum' => \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\ExtraData\TransactionsSum::class,
		'cashlessTransactionsSum' => \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\ExtraData\CashlessTransactionsSum::class,
	];

	/**
	 * Historical count of customer's cash transactions made in partner's e-shop
	 *
	 * @var float|null
	 */
	private $transactionsNumber;

	/**
	 * Historical count of customer's cashless transactions made in partner's e-shop
	 *
	 * @var float|null
	 */
	private $cashlessTransactionsNumber;

	/**
	 * Sum of all cash transactions made in partner's e-shop
	 *
	 * @var \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\ExtraData\TransactionsSum
	 */
	private $transactionsSum;

	/**
	 * Sum of all cashless transactions made in partner's e-shop
	 *
	 * @var \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\ExtraData\CashlessTransactionsSum
	 */
	private $cashlessTransactionsSum;

	/**
	 * Newest transaction date on partner e-shop.
	 *
	 * @var string|null
	 */
	private $latestTransactionDate;

	/**
	 * Oldest transaction date on partner e-shop
	 *
	 * @var string|null
	 */
	private $earliestTransactionDate;

	/**
	 * Total time spent on partners website (in seconds)
	 *
	 * @var float|null
	 */
	private $pageTotalTime;

	/**
	 * Total time spent on partners website in review and comments sections (in seconds)
	 *
	 * @var float|null
	 */
	private $pageReviewsTime;

	/**
	 * Total count of removed items form shopping basket during current shopping
	 *
	 * @var float|null
	 */
	private $cartItemsRemoved;

	/**
	 * Number of viewed pages with products
	 *
	 * @var float|null
	 */
	private $itemsViewed;

	/**
	 * @param float|null $transactionsNumber
	 * @param float|null $cashlessTransactionsNumber
	 * @param \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\ExtraData\TransactionsSum $transactionsSum
	 * @param \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\ExtraData\CashlessTransactionsSum $cashlessTransactionsSum
	 * @param string|null $latestTransactionDate
	 * @param string|null $earliestTransactionDate
	 * @param float|null $pageTotalTime
	 * @param float|null $pageReviewsTime
	 * @param float|null $cartItemsRemoved
	 * @param float|null $itemsViewed
	 */
	public function __construct(
		$transactionsNumber = null,
		$cashlessTransactionsNumber = null,
		\HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\ExtraData\TransactionsSum $transactionsSum = null,
		\HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\ExtraData\CashlessTransactionsSum $cashlessTransactionsSum = null,
		$latestTransactionDate = null,
		$earliestTransactionDate = null,
		$pageTotalTime = null,
		$pageReviewsTime = null,
		$cartItemsRemoved = null,
		$itemsViewed = null
	)
	{
		$this->setTransactionsNumber($transactionsNumber);
		$this->setCashlessTransactionsNumber($cashlessTransactionsNumber);
		$this->setTransactionsSum($transactionsSum);
		$this->setCashlessTransactionsSum($cashlessTransactionsSum);
		$this->setLatestTransactionDate($latestTransactionDate);
		$this->setEarliestTransactionDate($earliestTransactionDate);
		$this->setPageTotalTime($pageTotalTime);
		$this->setPageReviewsTime($pageReviewsTime);
		$this->setCartItemsRemoved($cartItemsRemoved);
		$this->setItemsViewed($itemsViewed);
	}

	/**
	 * @return float|null
	 */
	public function getTransactionsNumber()
	{
		return $this->transactionsNumber;
	}

	/**
	 * @return float|null
	 */
	public function getCashlessTransactionsNumber()
	{
		return $this->cashlessTransactionsNumber;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\ExtraData\TransactionsSum
	 */
	public function getTransactionsSum()
	{
		return $this->transactionsSum;
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\ExtraData\CashlessTransactionsSum
	 */
	public function getCashlessTransactionsSum()
	{
		return $this->cashlessTransactionsSum;
	}

	/**
	 * @return string|null
	 */
	public function getLatestTransactionDate()
	{
		return $this->latestTransactionDate;
	}

	/**
	 * @return string|null
	 */
	public function getEarliestTransactionDate()
	{
		return $this->earliestTransactionDate;
	}

	/**
	 * @return float|null
	 */
	public function getPageTotalTime()
	{
		return $this->pageTotalTime;
	}

	/**
	 * @return float|null
	 */
	public function getPageReviewsTime()
	{
		return $this->pageReviewsTime;
	}

	/**
	 * @return float|null
	 */
	public function getCartItemsRemoved()
	{
		return $this->cartItemsRemoved;
	}

	/**
	 * @return float|null
	 */
	public function getItemsViewed()
	{
		return $this->itemsViewed;
	}

	/**
	 * @param float|null $transactionsNumber
	 * @return $this
	 */
	public function setTransactionsNumber($transactionsNumber)
	{
		$this->transactionsNumber = $transactionsNumber;
		return $this;
	}

	/**
	 * @param float|null $cashlessTransactionsNumber
	 * @return $this
	 */
	public function setCashlessTransactionsNumber($cashlessTransactionsNumber)
	{
		$this->cashlessTransactionsNumber = $cashlessTransactionsNumber;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\ExtraData\TransactionsSum $transactionsSum
	 * @return $this
	 */
	public function setTransactionsSum($transactionsSum)
	{
		$this->transactionsSum = $transactionsSum;
		return $this;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\CreateApplicationRequest\Customer\ExtraData\CashlessTransactionsSum $cashlessTransactionsSum
	 * @return $this
	 */
	public function setCashlessTransactionsSum($cashlessTransactionsSum)
	{
		$this->cashlessTransactionsSum = $cashlessTransactionsSum;
		return $this;
	}

	/**
	 * @param string|null $latestTransactionDate
	 * @return $this
	 */
	public function setLatestTransactionDate($latestTransactionDate)
	{
		$this->latestTransactionDate = $latestTransactionDate;
		return $this;
	}

	/**
	 * @param string|null $earliestTransactionDate
	 * @return $this
	 */
	public function setEarliestTransactionDate($earliestTransactionDate)
	{
		$this->earliestTransactionDate = $earliestTransactionDate;
		return $this;
	}

	/**
	 * @param float|null $pageTotalTime
	 * @return $this
	 */
	public function setPageTotalTime($pageTotalTime)
	{
		$this->pageTotalTime = $pageTotalTime;
		return $this;
	}

	/**
	 * @param float|null $pageReviewsTime
	 * @return $this
	 */
	public function setPageReviewsTime($pageReviewsTime)
	{
		$this->pageReviewsTime = $pageReviewsTime;
		return $this;
	}

	/**
	 * @param float|null $cartItemsRemoved
	 * @return $this
	 */
	public function setCartItemsRemoved($cartItemsRemoved)
	{
		$this->cartItemsRemoved = $cartItemsRemoved;
		return $this;
	}

	/**
	 * @param float|null $itemsViewed
	 * @return $this
	 */
	public function setItemsViewed($itemsViewed)
	{
		$this->itemsViewed = $itemsViewed;
		return $this;
	}

}
