<?php

namespace HomeCredit\OneClickApi\EntityGenerator;

class PropertyGenerator
{

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $visibility;

	/**
	 * @var string[]
	 */
	private $docBlock;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var bool
	 */
	private $isRequired;

	/**
	 * @var string[]|null
	 */
	private $enums;

	/**
	 * PropertyGenerator constructor.
	 * @param string $name
	 */
	public function __construct($name)
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getVisibility()
	{
		return $this->visibility;
	}

	/**
	 * @param string $visibility
	 * @return PropertyGenerator
	 */
	public function setVisibility($visibility)
	{
		$this->visibility = $visibility;
		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getDocBlock()
	{
		return $this->docBlock;
	}

	/**
	 * @param string[] $docBlock
	 * @return PropertyGenerator
	 */
	public function setDocBlock($docBlock)
	{
		$this->docBlock = $docBlock;
		return $this;
	}

	public function addDocBlock($value)
	{
		$this->docBlock[] = $value;
		return $this;
	}

	public function __toString()
	{
		$property = "\t" . '/**' . PHP_EOL;
		if ($this->getDocBlock()) {
			foreach ($this->getDocBlock() as $name => $value) {
				if (trim($value) == '') {
					continue;
				}
				$property .= "\t" . ' * ' . $value . PHP_EOL;
				if (substr($value, 0, 1) != '@') {
					$property .= "\t" . ' *' . PHP_EOL;
				}
			}
		}
		if ($this->isRequired()) {
			$property .= "\t" . ' * @required' . PHP_EOL;
		}
		$property .= "\t" . ' */' . PHP_EOL;
		$property .= "\t" . $this->getVisibility() . ' $' . $this->getName() . ';';

		return $property;
	}

	/**
	 * @param string $type
	 * @return PropertyGenerator
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @return boolean
	 */
	public function isRequired()
	{
		return $this->isRequired;
	}

	/**
	 * @param boolean $isRequired
	 * @return PropertyGenerator
	 */
	public function setIsRequired($isRequired)
	{
		$this->isRequired = $isRequired;
		return $this;
	}

	/**
	 * @param string[] $enums
	 * @return PropertyGenerator
	 */
	public function setEnums($enums)
	{
		$this->enums = $enums;
		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getEnums()
	{
		return $this->enums;
	}

	/**
	 * @return bool
	 */
	public function hasEnums()
	{
		return is_array($this->getEnums()) && count($this->getEnums()) > 0;
	}

	public function isPrimitive()
	{
		return in_array(str_replace(['|', 'null'], '', $this->getType()), ['int', 'integer', 'string', 'float', 'mixed', 'array']);
	}

}
