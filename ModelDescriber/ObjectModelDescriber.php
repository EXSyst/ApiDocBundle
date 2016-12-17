<?php

/*
 * This file is part of the ApiDocBundle package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Bundle\ApiDocBundle\ModelDescriber;

use EXSyst\Bundle\ApiDocBundle\Model\ModelOptions;
use EXSyst\Bundle\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use EXSyst\Bundle\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use EXSyst\Component\Swagger\Schema;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;

class ObjectModelDescriber implements ModelDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    public function __construct(PropertyInfoExtractorInterface $propertyInfo)
    {
        $this->propertyInfo = $propertyInfo;
    }

    public function describe(Schema $schema, ModelOptions $options)
    {
        $schema->setType('object');
        $properties = $schema->getProperties();

        $class = $options->getType()->getClassName();
        foreach ($this->propertyInfo->getProperties($class) as $propertyName) {
            $types = $this->propertyInfo->getTypes($class, $propertyName);
            if (0 === count($types)) {
                throw new \LogicException(sprintf('The PropertyInfo component was not able to guess the type of %s::$%s', $class, $propertyName));
            }
            if (count($types) > 1) {
                throw new \LogicException(sprintf('Property %s::$%s defines more than one type.', $class, $propertyName));
            }

            $this->modelRegistry->register($properties->get($propertyName))
                ->setType($types[0]);
        }
    }

    public function supports(ModelOptions $options)
    {
        return Type::BUILTIN_TYPE_OBJECT === $options->getType()->getBuiltinType();
    }
}
