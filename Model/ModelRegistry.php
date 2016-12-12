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

use EXSyst\Bundle\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
use EXSyst\Component\Swagger\Schema;
use EXSyst\Component\Swagger\Swagger;
use Symfony\Component\PropertyInfo\Type;

final class ModelRegistry
{
    private $modelDescribers = [];
    private $options;
    private $unregistered = [];
    private $hashes = [];

    /**
     * @param ModelDescriberInterface[] $modelDescribers
     */
    public function __construct(array $modelDescribers = [])
    {
        $this->options = new \SplObjectStorage();
        $this->modelDescribers = $modelDescribers;
    }

    /**
     * @param Schema    $schema
     * @param Type|null $type    null if not known yet
     * @param array     $options
     */
    public function register(Schema $schema)
    {
        if (!isset($this->options[$schema])) {
            $this->unregistered[] = $schema;
            $this->options[$schema] = new ModelOptions();
        }

        return $this->options[$schema];
    }

    /**
     * @internal
     */
    public function registerModelsIn(Swagger $api)
    {
        while (count($this->unregistered)) {
            $tmp = [];
            foreach ($this->unregistered as $schema) {
                $options = $this->options[$schema];
                $options->validate();

                $hash = $options->getHash();
                if (isset($this->hashes[$hash])) {
                    $schema->setRef('#/definitions/'.$this->hashes[$hash]);

                    continue;
                }

                if (!isset($tmp[$hash])) {
                    $tmp[$hash] = [$options, [/* schemas */]];
                }
                $tmp[$hash][1][] = $schema;
            }
            $this->unregistered = [];

            foreach ($tmp as $hash => list($options, $schemas)) {
                $name = $this->generateModelName($api, $options);
                $this->hashes[$hash] = $name;

                $baseSchema = $api->getDefinitions()->get($name);

                foreach ($schemas as $schema) {
                    $schema->setRef('#/definitions/'.$name);
                }

                foreach ($this->modelDescribers as $modelDescriber) {
                    $modelDescriber->describe($baseSchema, $options);
                }
            }
        }
    }

    public function __clone()
    {
        $this->options = new \SplObjectStorage();
        $this->unregistered = [];
        $this->hashes = [];
    }

    private function generateModelName(Swagger $api, ModelOptions $options): string
    {
        $type = $options->getType();
        if ($type->isCollection()) {
            $base = $this->getTypeShortName($type->getCollectionValueType()).'[]';
        } else {
            $base = (new \ReflectionClass($type->getClassName()))->getShortName();
        }

        $definitions = $api->getDefinitions();
        $name = $base;
        $i = 1;
        while ($definitions->has($name)) {
            ++$i;
            $name = $base.$i;
        }

        return $name;
    }
}
