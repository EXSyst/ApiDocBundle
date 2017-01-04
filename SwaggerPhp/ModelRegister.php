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
use Nelmio\ApiDocBundle\Annotation\Model as ModelAnnotation;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\Model\ModelRegistry;
use Nelmio\ApiDocBundle\Util\ControllerReflector;
use Swagger\Analysis;
use Swagger\Annotations as SWG;
use Swagger\Context;
use Swagger\Annotations\Schema;
use Swagger\Annotations\Response;
use Swagger\Annotations\Parameter;
use Swagger\Annotations\AbstractAnnotation;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\PropertyInfo\Type;

/**
 * Resolves the path in SwaggerPhp annotation when needed.
 *
 * @internal
 */
final class ModelRegister
{
    private $modelRegistry;

    public function __construct(ModelRegistry $modelRegistry)
    {
        $this->modelRegistry = $modelRegistry;
    }

    public function __invoke(Analysis $analysis)
    {
        foreach ($analysis->annotations as $annotation) {
            if (!$annotation instanceof ModelAnnotation || $annotation->_context->not('nested')) {
                continue;
            }

            if (!is_string($annotation->type)) {
                // Ignore invalid annotations, they are validated later
                continue;
            }

            $parent = $annotation->_context->nested;
            if (!$parent instanceof Response && !$parent instanceof Parameter) {
                throw new \InvalidArgumentException(sprintf('Annotation @%s is not compatible with @%s. It is only compatible with @%s and @%s.', ModelAnnotation::class, get_class($parent), Response::class, Parameter::class));
            }
            $parent->merge([new Schema([
                'ref' => $this->modelRegistry->register(new Model($this->createType($annotation->type)))
            ])]);

            $analysis->annotations->detach($annotation);
        }
    }

    private function createType(string $type): Type
    {
        return new Type(Type::BUILTIN_TYPE_OBJECT, false, $type);
    }
}
