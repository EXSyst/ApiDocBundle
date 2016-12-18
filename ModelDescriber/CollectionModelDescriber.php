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

use EXSyst\Bundle\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use EXSyst\Bundle\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use EXSyst\Bundle\ApiDocBundle\Model\ModelOptions;
use EXSyst\Component\Swagger\Schema;

class CollectionModelDescriber implements ModelDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    public function describe(Schema $schema, ModelOptions $options)
    {
        $schema->setType('array');
        $this->modelRegistry->register($schema->getItems())
            ->setType($options->getType()->getCollectionValueType());
    }

    public function supports(ModelOptions $options)
    {
        return $options->getType()->isCollection() && null !== $options->getType()->getCollectionValueType();
    }
}
