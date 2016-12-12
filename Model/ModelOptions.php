<?php

/*
 * This file is part of the ApiDocBundle package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Bundle\ApiDocBundle\Model;

use Symfony\Component\PropertyInfo\Type;

final class ModelOptions
{
    private $locked = false;
    private $type;

    /**
     * @return Type|null
     */
    public function getType()
    {
        return $this->type;
    }

    public function setType(Type $type)
    {
        if ($this->locked) {
            throw new \LogicException('Options are locked, you can\'t update them.');
        }

        $this->type = $type;
    }

    public function getHash()
    {
        return md5(serialize($this->type));
    }

    public function validate()
    {
        $this->locked = true;

        if (null === $this->type) {
            throw new \LogicException('The model type must be specified.');
        }

        if (Type::BUILTIN_TYPE_OBJECT !== $this->type->getBuiltinType() && !$this->type->isCollection() && Type::BUILTIN_TYPE_OBJECT === $this->type->getCollectionValueType()->getBuiltinType()) {
            throw new \LogicException('Only schemas of objects and collection of objects are supported.');
        }
    }
}
