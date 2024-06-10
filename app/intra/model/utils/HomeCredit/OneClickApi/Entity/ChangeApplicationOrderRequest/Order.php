<?php

namespace HomeCredit\OneClickApi\Entity\ChangeApplicationOrderRequest;

use HomeCredit\OneClickApi\AEntity;

class Order extends AEntity
{

	/**
	 * Order number (internal for e-shop)
	 *
	 * @var string|null
	 */
	private $number;

	/**
	 * Variable symbols (internal for e-shop)
	 *
	 * @var array|null
	 */
	private $variableSymbols;

	/**
	 * @param string|null $number
	 * @param array|null $variableSymbols
	 */
	public function __construct(
		$number = null,
		$variableSymbols = null
	)
	{
		$this->setNumber($number);
		$this->setVariableSymbols($variableSymbols);
	}

	/**
	 * @return string|null
	 */
	public function getNumber()
	{
		return $this->number;
	}

	/**
	 * @return array|null
	 */
	public function getVariableSymbols()
	{
		return $this->variableSymbols;
	}

	/**
	 * @param string|null $number
	 * @return $this
	 */
	public function setNumber($number)
	{
		$this->number = $number;
		return $this;
	}

	/**
	 * @param array|null $variableSymbols
	 * @return $this
	 */
	public function setVariableSymbols($variableSymbols)
	{
		$this->variableSymbols = $variableSymbols;
		return $this;
	}

}
