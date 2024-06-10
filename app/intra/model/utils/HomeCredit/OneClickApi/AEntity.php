<?php

namespace HomeCredit\OneClickApi;

use Respect\Validation\Validator as v;

abstract class AEntity implements \JsonSerializable
{

	const INDEX_ENTRYCLASS = 0;
	const INDEX_MULTIPLICITY = 1;
	const INDEX_EMBEDDING = 2;

	/**
	 * @var array
	 */
	protected static $associations = [];

	/**
	 * @var array entryClass => [INDEX_ENTRYCLASS => relatedEntryClass, INDEX_MULTIPLICITY => multiplicity, INDEX_EMBEDDING => embedding]
	 */
	private static $parsedAssociations = [];

	private static function initParsedAssociations()
	{
		if (!array_key_exists($calledClass = static::class, self::$parsedAssociations)) {
			self::parseAssociations($calledClass);
		}
	}

	/**
	 * @param string $class
	 */
	private static function parseAssociations($class)
	{
		self::$parsedAssociations[$class] = [];
		foreach (static::$associations as $association => $entryClass) {
			$matches = [];
			$result = preg_match('#^([^.[\]]+)(\.[^.[\]]*)?(\[\])?$#', $association, $matches);
			if ($result === 0 || (!empty($matches[2]) && !empty($matches[3]))) {
				throw new \InvalidArgumentException('Invalid association definition given: ' . $association);
			}
			self::$parsedAssociations[$class][$matches[1]] = [
				self::INDEX_ENTRYCLASS => $entryClass,
				self::INDEX_MULTIPLICITY => !empty($matches[3]),
				self::INDEX_EMBEDDING => !empty($matches[2]) ?
					($matches[2] === '.' ? $matches[1] . '_' : substr($matches[2], 1)) :
					false,
			];
		}
	}

	/**
	 * @param mixed $value
	 */
	protected function assertNotNull($value)
	{
		v::not(v::nullType())->assert($value);
	}

	/**
	 * @param mixed $value
	 * @param array $validValues
	 */
	protected function assertInArray($value, $validValues)
	{
		v::in($validValues)->assert($value);
	}

	/**
	 * @param mixed[] $data
	 * @return AEntity
	 * @throws \Exception
	 */
	public static function fromArray(array $data)
	{
		self::initParsedAssociations();
		$args = [];

		foreach ($data as $oldKey => $v) {
			$newKey = lcfirst(implode('', array_map('ucfirst', explode('_', $oldKey))));
			if ($newKey !== $oldKey) {
				$data[$newKey] = $v;
				unset($data[$oldKey]);
			}
		}

		foreach (static::requiredArgs() as $param) {
			$paramName = $param->getName();
			if (!array_key_exists($paramName, $data)) {
				if ($param->allowsNull()) {
					$args[] = null;
				} elseif ($param->isDefaultValueAvailable()) {
					$args[] = $param->getDefaultValue();
				} else {
					throw new \InvalidArgumentException('Param ' . $paramName . ' is missing');
				}
			} else {
				$value = $data[$paramName];
				if (isset(self::$parsedAssociations[static::class][$paramName])) {
					$association = self::$parsedAssociations[static::class][$paramName];
					$className = $association[self::INDEX_ENTRYCLASS];
					if ($association[self::INDEX_MULTIPLICITY]) {
						if (count($value) > 0) {
							$arrOfObjects = [];
							foreach ($value as $item) {
								array_push($arrOfObjects, $className::fromArray($item));
							}
							$value = $arrOfObjects;
						}
					} else {
					    $value = $className::fromArray($data[$paramName]);
					}
				}
				$args[] = $value;
			}
		}

		return new static(...$args);
	}

	/**
	 * @return \ReflectionParameter[]
	 * @throws \ReflectionException
	 */
	private static function requiredArgs()
	{
		return (new \ReflectionClass(static::class))
			->getConstructor()
			->getParameters();
	}

	/**
	 * @return mixed[]
	 * @throws \ReflectionException
	 */
	public function jsonSerialize()
	{
		$properties = [];
		foreach ((new \ReflectionClass(static::class))->getProperties() as $property) {
			if ($property->isStatic()) {
				continue;
			}

			if ($property->isPublic()) {
				$value = $this->{$property->getName()};
			} else {
				$getterName = 'get' . ucfirst($property->getName());
				if (($property->isPrivate() || $property->isProtected()) && method_exists($this, $getterName)) {
					$method = new \ReflectionMethod(static::class, $getterName);
					if (!$method->isPublic()) {
						continue;
					}
					$value = $this->{$getterName}();
				} else {
					continue;
				}
			}

			$required = !(strpos($property->getDocComment(), '@required') === false);
			if (!$required && is_null($value)) {
				continue;
			}
			$properties[$property->getName()] = $value;
		}

		if (is_array($properties) && count($properties) == 0) {
		    $properties = (object)$properties;
        }

		return $properties;
	}

}
