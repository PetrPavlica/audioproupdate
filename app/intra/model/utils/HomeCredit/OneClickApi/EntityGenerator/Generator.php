<?php

namespace HomeCredit\OneClickApi\EntityGenerator;

use RoundingWell\Schematic\Schema;

class Generator
{

	/**
	 * @param Schema\ObjectSchema $schema
	 * @param string $className
	 * @param string $baseClass
	 * @return ClassGenerator[]
	 */
	public function generate(Schema\ObjectSchema $schema, $className, $baseClass)
	{
		$classes = [];

		$spaces = explode('\\', $className);
		$nameSpace = implode('\\', array_slice($spaces, 0, count($spaces) - 1));
		$class = (
			(new ClassGenerator(array_slice($spaces, -1, 1)[0]))
				->setNameSpace($nameSpace)
				->setExtends($baseClass)
				->setUse(['HomeCredit\OneClickApi\AEntity'])
		);

		foreach ($schema->properties() as $name => $property) {
			$typeHint = $property->phpType();

			if ($property->isObject()) {
				// Create a new class for this property
				$nextClass = $className . '\\' . ucfirst($name);
				$typeHint = '\\' . $nextClass;
				$classes = array_merge($classes, $this->generate(
					$property,
					$nextClass,
					$baseClass
				));
			} elseif ($property->isArray() && $property->hasItems() && $property->items()->isObject()) {
				/** @var Schema\ArraySchema $property */
				$nextClass = $className . '\\' . ucfirst($name);
				$typeHint = '\\' . $nextClass . '[]';
				$classes = array_merge($classes, $this->generate(
					$property->items(),
					$nextClass,
					$baseClass
				));
			} elseif (!$schema->isRequired($name) && !$property->isNull()) {
				$typeHint = $typeHint . '|null';
			}

			$prop = $class->addProperty(
				(new PropertyGenerator($name))
					->addDocBlock($property->getDescription())
					->addDocBlock('@var ' . $typeHint)
					->setVisibility('private')
					->setType($typeHint)
					->setIsRequired($schema->isRequired($name))
			);
			if ($property->isEnum()) {
				$prop->setEnums($property->getEnum());
			}

		}

		$classes[$class->getNameSpace() . '\\' . $class->getName()] = $class;

		return $classes;
	}
}
