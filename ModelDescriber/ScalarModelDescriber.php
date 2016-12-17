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

class ScalarModelDescriber implements ModelDescriberInterface
{
    private static $supportedTypes = [
        Type::BUILTIN_TYPE_INT => 'integer',
        Type::BUILTIN_TYPE_FLOAT => 'float',
        Type::BUILTIN_TYPE_STRING => 'string',
    ];

    public function describe(Schema $schema, ModelOptions $options)
    {
        $type = self::$supportedTypes[$options->getType()->getBuiltinType()];
        $schema->setType($type);
    }

    public function supports(ModelOptions $options)
    {
        return isset(self::$supportedTypes[$options->getType()->getBuiltinType()]);
    }
}
