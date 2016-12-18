<?php

/*
 * This file is part of the ApiDocBundle package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Bundle\ApiDocBundle;

use EXSyst\Bundle\ApiDocBundle\Describer\DescriberInterface;
use EXSyst\Bundle\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use EXSyst\Bundle\ApiDocBundle\Model\ModelRegistry;
use EXSyst\Component\Swagger\Swagger;
use Psr\Cache\CacheItemPoolInterface;

final class ApiDocGenerator
{
    private $swagger;
    private $describers;
    private $modelRegistry;
    private $cacheItemPool;

    /**
     * @param DescriberInterface[] $describers
     */
    public function __construct(array $describers, ModelRegistry $modelRegistry, CacheItemPoolInterface $cacheItemPool = null)
    {
        $this->describers = $describers;
        $this->modelRegistry = $modelRegistry;
        $this->cacheItemPool = $cacheItemPool;
    }

    public function generate(): Swagger
    {
        if (null !== $this->swagger) {
            return $this->swagger;
        }

        if ($this->cacheItemPool) {
            $item = $this->cacheItemPool->getItem('swagger_doc');
            if ($item->isHit()) {
                return $this->swagger = $item->get();
            }
        }

        $this->swagger = new Swagger();
        $modelRegistry = clone $this->modelRegistry;
        foreach ($this->describers as $describer) {
            if ($describer instanceof ModelRegistryAwareInterface) {
                $describer->setModelRegistry($modelRegistry);
            }

            $describer->describe($this->swagger);
        }
        $modelRegistry->registerModelsIn($this->swagger);

        if (isset($item)) {
            $this->cacheItemPool->save($item->set($this->swagger));
        }

        return $this->swagger;
    }
}
