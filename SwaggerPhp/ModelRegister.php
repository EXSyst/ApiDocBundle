<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\SwaggerPhp;

use EXSyst\Component\Swagger\Swagger;
use Nelmio\ApiDocBundle\Util\ControllerReflector;
use Swagger\Analysis;
use Swagger\Annotations as SWG;
use Swagger\Context;
use Swagger\Annotations\AbstractAnnotation;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\PropertyInfo\Type;

/**
 * Resolves the path in SwaggerPhp annotation when needed.
 *
 * @internal
 */
final class PathResolver
{
    public function __invoke(Analysis $analysis)
    {
        $this->createImplicitOperations($analysis);
        $this->completeOperations($analysis);
    }

    private function analyseAnnotation(AbstractAnnotation $annotation)
    {
        $analysedProperties = ['paths', 'get', 'post', 'put','delete', 'patch', 'head', 'options', 'responses', 'parameters'];
        foreach ($analysedProperties as $property) {
            if (!property_exists($annotation->$property)) {
                continue;
            }

            $value = $annotation->$property;
            if (!$property instanceof AbstractAnnotation) {
                continue;
            }

            if ($value instanceof Model) {
                $value->validate();

                $this->modelRegistry->register($this->createType($value->type);
            }

            $this->analyseAnnotation($annotation);
        }
    }

    private function createType(string $type): Type
    {
        return new Type(Type::BUILTIN_TYPE_OBJECT, false, $type);
    }
}
