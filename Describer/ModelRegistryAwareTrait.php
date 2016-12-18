<?php

/*
 * This file is part of the ApiDocBundle package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Bundle\ApiDocBundle\Describer;

use EXSyst\Bundle\ApiDocBundle\Model\ModelRegistry;

trait ModelRegistryAwareTrait
{
    private $modelRegistry;

    public function setModelRegistry(ModelRegistry $modelRegistry)
    {
        $this->modelRegistry = $modelRegistry;
    }
}
