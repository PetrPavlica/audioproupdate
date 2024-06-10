<?php

namespace HomeCredit\OneClickApi\EntityGenerator;

class ClassGenerator
{

	/**
	 * @var PropertyGenerator[]
	 */
	private $properties;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $nameSpace;

	/**
	 * @var string
	 */
	private $extends;

	/**
	 * @var string[]
	 */
	private $use;

	public function __construct($name)
	{
		$this->name = $name;
	}

	/**
	 * @param PropertyGenerator $propertyGenerator
	 * @return PropertyGenerator
	 */
	public function addProperty(PropertyGenerator $propertyGenerator)
	{
		$this->properties[$propertyGenerator->getName()] = $propertyGenerator;

		return $propertyGenerator;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return PropertyGenerator[]
	 */
	public function getProperties()
	{
		return $this->properties;
	}

	/**
	 * @param PropertyGenerator[] $properties
	 * @return ClassGenerator
	 */
	public function setProperties($properties)
	{
		$this->properties = $properties;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getNameSpace()
	{
		return $this->nameSpace;
	}

	/**
	 * @param string $nameSpace
	 * @return ClassGenerator
	 */
	public function setNameSpace($nameSpace)
	{
		$this->nameSpace = $nameSpace;
		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getUse()
	{
		return $this->use;
	}

	/**
	 * @param string[] $use
	 * @return ClassGenerator
	 */
	public function setUse($use)
	{
		$this->use = $use;
		return $this;
	}

	/**
	 * @param string $extends
	 * @return ClassGenerator
	 */
	public function setExtends($extends)
	{
		$this->extends = $extends;
		return $this;
	}

	public function __toString()
	{
		$phpCode = '<?php' . PHP_EOL . PHP_EOL;
		if ($this->name) {
			$phpCode .= 'namespace ' . $this->getNameSpace() . ';' . PHP_EOL . PHP_EOL;
		}
		if ($this->getUse()) {
			$uses = array_map(function ($value) {
				return 'use ' . $value . ';';
			}, $this->getUse());
			$uses = implode(PHP_EOL, $uses);
			$phpCode .= $uses . PHP_EOL . PHP_EOL;
		}
		$phpCode .= 'class ' . $this->getName();
		if ($this->extends) {
			$phpCode .= ' extends ' . $this->getExtends();
		}
		$phpCode .= PHP_EOL . '{' . PHP_EOL;

		$phpCode .= PHP_EOL;
		$phpCode .= $this->generateConstants();
		$phpCode .= $this->generateAssociations();

		foreach ($this->getProperties() as $property) {
			$phpCode .= $property . PHP_EOL . PHP_EOL;
		}

		$phpCode .= $this->generateConstructor();
		$phpCode .= PHP_EOL;
		$phpCode .= $this->generateGetters();
		$phpCode .= $this->generateSetters();
		$phpCode .= '}' . PHP_EOL;

		return $phpCode;
	}

	private function generateConstructor()
	{
		$required = [];
		$notRequired = [];

		foreach ($this->getProperties() as $property) {
			/** @var PropertyGenerator $property */
			if ($property->isRequired()) {
				array_push($required, $property);
				continue;
			}
			array_push($notRequired, $property);
		}
		$properties = array_merge($required, $notRequired);
		$str = '';
		$str .= "\t" . '/**' . PHP_EOL;
		foreach ($properties as $property) {
			$str .= "\t" . ' * @param ' . $property->getType() . ' $' . $property->getName() . PHP_EOL;
		}
		$str .= "\t" . ' */' . PHP_EOL;
		$str .= "\t" . 'public function __construct(' . PHP_EOL;
		foreach ($properties as $property) {
			$cType = '';
			if (!$property->isPrimitive()) {
				if (strpos($property->getType(), '[]')) {
					$cType = 'array ';
				} else {
					$cType = $property->getType() . ' ';
				}
			}
			$cParams[] = "\t\t" . $cType . '$' . $property->getName() . (!$property->isRequired() ? ' = null' : '');
		}
		$str .= implode(',' . PHP_EOL, $cParams) . PHP_EOL . "\t" . ')' . PHP_EOL;
		$str .= "\t" . '{' . PHP_EOL;
		foreach ($properties as $property) {
			$str .= "\t\t" . '$this->set' . ucfirst($property->getName()) . '($' . $property->getName() . ');' . PHP_EOL;
		}
		$str .= "\t" . '}' . PHP_EOL;

		return $str;
	}

	private function generateGetters()
	{
		$getters = '';
		foreach ($this->getProperties() as $property) {
			$getters .= "\t" . '/**' . PHP_EOL;
			$getters .= "\t" . ' * @return ' . $property->getType() . PHP_EOL;
			$getters .= "\t" . ' */' . PHP_EOL;
			$getters .= "\t" . 'public function get' . ucfirst($property->getName()) . '()' . PHP_EOL;
			$getters .= "\t" . '{' . PHP_EOL;
			$getters .= "\t\t" . 'return $this->' . $property->getName() . ';' . PHP_EOL;
			$getters .= "\t" . '}' . PHP_EOL . PHP_EOL;
		}
		return $getters;
	}

	private function generateSetters()
	{
		$setters = '';
		foreach ($this->getProperties() as $property) {
			$setters .= "\t" . '/**' . PHP_EOL;
			$setters .= "\t" . ' * @param ' . $property->getType() . ' $' . $property->getName() . PHP_EOL;
			$setters .= "\t" . ' * @return $this' . PHP_EOL;
			$setters .= "\t" . ' */' . PHP_EOL;
			$setters .= "\t" . 'public function set' . ucfirst($property->getName()) . '($' . $property->getName() . ')' . PHP_EOL;
			$setters .= "\t" . '{' . PHP_EOL;
			if ($property->isRequired()) {
				$setters .= "\t\t" . '$this->assertNotNull($' . $property->getName() . ');' . PHP_EOL;
			}
			if (count($property->getEnums()) > 0) {
				$names = [];
				foreach ($property->getEnums() as $enum) {
					$names[$enum] = $this->getConstantName($property, $enum);
				}
				$validValues = 'self::' . implode(', self::', $names);
				$setter = '$this->assertInArray($' . $property->getName() . ', [' . $validValues . ']);';
				if (!$property->isRequired()) {
					$setters .= "\t\tif (!is_null($" . $property->getName() . ')) {' . PHP_EOL;
					$setters .= "\t\t\t" . $setter . PHP_EOL;
					$setters .= "\t\t}" . PHP_EOL;
				} else {
					$setters .= "\t\t" . $setter . PHP_EOL;
				}
			}
			$setters .= "\t\t" . '$this->' . $property->getName() . ' = $' . $property->getName() . ';' . PHP_EOL;
			$setters .= "\t\t" . 'return $this;' . PHP_EOL;
			$setters .= "\t" . '}' . PHP_EOL . PHP_EOL;
		}
		return $setters;
	}

	private function generateConstants()
	{
		$constants = '';
		if ($this->hasEnums()) {
			foreach ($this->getProperties() as $property) {
				if ($property->hasEnums()) {
					foreach ($property->getEnums() as $enum) {
						$contsName = $this->getConstantName($property, $enum);
						$constants .= "\t" . 'const ' . $contsName . ' = \'' . $enum . '\';' . PHP_EOL;
					}
				}
			}
			$constants .= PHP_EOL;
		}

		return $constants;
	}

	private function generateAssociations()
	{
		$associations = '';
		if (count($this->getProperties()) > 0) {

			$parts = [];
			foreach ($this->getProperties() as $property) {
				if ($property->isPrimitive()) {
					continue;
				}
				$name = $property->getName();
				$type = $property->getType();
				if (strpos($type, '[]')) {
					$type = rtrim($type, '[]');
					$name = $name . '[]';
				}
				$parts[] = "\t\t" . '\'' . $name . '\' => ' . $type . '::class,';
			}
			if (count($parts) > 0) {
				$associations = "\t" . 'protected static $associations = [' . PHP_EOL;
				$associations .= implode(PHP_EOL, $parts) . PHP_EOL;
				$associations .= "\t" . '];' . PHP_EOL . PHP_EOL;
			}
		}

		return $associations;
	}

	private function hasEnums()
	{
		$haveEnums = false;
		foreach ($this->getProperties() as $property) {
			if ($property->hasEnums()) {
				$haveEnums = true;
				break;
			}
		}

		return $haveEnums;
	}

	/**
	 * @return string
	 */
	public function getExtends()
	{
		return $this->extends;
	}

	/**
	 * @return string
	 */
	public function getSavePath()
	{
		return str_replace('\\', DIRECTORY_SEPARATOR, $this->getNameSpace() . DIRECTORY_SEPARATOR . str_replace($this->getNameSpace(), '', $this->getName()) . '.php');
	}

	/**
	 * @param string $baseDir
	 * @throws \Exception
	 */
	public function saveToFs($baseDir)
	{
		if (!is_dir($baseDir) || !is_writable($baseDir)) {
			throw new \Exception($baseDir . ' is not writeable');
		}
		$savePath = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $baseDir . DIRECTORY_SEPARATOR . $this->getSavePath());
		$dirName = dirname($savePath);
		if (!is_dir($dirName)) {
			if (!mkdir($dirName, 0777, true) && !is_writable($dirName)) {
				throw new \Exception('Unable to create directory ' . $dirName);
			}
		}
		if (!file_put_contents($savePath, (string) $this)) {
			throw new \Exception('Unabel to save file ' . $savePath);
		}
	}

	/**
	 * @param PropertyGenerator $propertyGenerator
	 * @param string $enumValue
	 * @return string
	 */
	private function getConstantName(PropertyGenerator $propertyGenerator, $enumValue)
	{
		return strtoupper($propertyGenerator->getName()) . '_' . strtoupper($enumValue);
	}

}
