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
use Swagger\Annotations\Operation;
use Symfony\Component\Routing\RouteCollection;

/**
 * Resolves the path in SwaggerPhp annotation when needed.
 *
 * @internal
 */
final class PathResolver
{
    private $routeCollection;
    private $controllerReflector;

    private $controllerMap;

    public function __construct(RouteCollection $routeCollection, ControllerReflector $controllerReflector)
    {
        $this->routeCollection = $routeCollection;
        $this->controllerReflector = $controllerReflector;
    }

    public function __invoke(Analysis $analysis)
    {
        $operations = $analysis->getAnnotationsOfType(Operation::class);
        foreach ($operations as $operation) {
            if (null !== $operation->path || $operation->_context->not('method')) {
                continue;
            }

            if (null === $this->controllerMap) {
                $this->buildMap();
            }

            $context = $operation->_context;
            $class = ltrim($context->namespace.'\\'.$context->class, '\\');
            $method = $context->method;
            $httpMethod = strtoupper($operation->method);

            // Checks if a route corresponds to this method
            if (!isset($this->controllerMap[$class][$method][$httpMethod])) {
                continue;
            }

            $paths = array_keys($this->controllerMap[$class][$method][$httpMethod]);
            // Define the path of the first annotation
            $operation->path = array_pop($paths);

            // If there are other paths, clone the annotation
            foreach ($paths as $path) {
                $alias = clone $operation;
                $alias->path = $path;

                $analysis->addAnnotation($alias, $alias->_context);
            }
        }
    }

    private function buildMap()
    {
        $this->controllerMap = [];
        foreach ($this->routeCollection->all() as $route) {
            if (!$route->hasDefault('_controller')) {
                continue;
            }

            $controller = $route->getDefault('_controller');
            if ($callable = $this->controllerReflector->getReflectionClassAndMethod($controller)) {
                list($class, $method) = $callable;
                $class = $class->name;
                $method = $method->name;

                if (!isset($this->controllerMap[$class])) {
                    $this->controllerMap[$class] = [];
                }
                if (!isset($this->controllerMap[$class][$method])) {
                    $this->controllerMap[$class][$method] = [];
                }

                $httpMethods = $route->getMethods() ?: Swagger::$METHODS;
                foreach ($httpMethods as $httpMethod) {
                    if (!isset($this->controllerMap[$class][$method][$httpMethod])) {
                        $this->controllerMap[$class][$method][$httpMethod] = [];
                    }

                    $path = $this->normalizePath($route->getPath());
                    $this->controllerMap[$class][$method][$httpMethod][$path] = true;
                }
            }
        }
    }

    private function normalizePath(string $path)
    {
        if (substr($path, -10) === '.{_format}') {
            $path = substr($path, 0, -10);
        }

        return $path;
    }
}
