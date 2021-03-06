<?php

/*
 * This file is part of the ApiDocBundle package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Bundle\ApiDocBundle\Tests\Describer;

use EXSyst\Bundle\ApiDocBundle\Describer\RouteDescriber;
use EXSyst\Bundle\ApiDocBundle\RouteDescriber\RouteDescriberInterface;
use EXSyst\Component\Swagger\Swagger;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteDescriberTest extends AbstractDescriberTest
{
    private $routes;
    private $routeDescriber;

    public function testIgnoreWhenNoController()
    {
        $this->routes->add('foo', new Route('foo'));
        $this->routeDescriber->expects($this->never())
            ->method('describe');

        $this->assertEquals((new Swagger())->toArray(), $this->getSwaggerDoc()->toArray());
    }

    protected function setUp()
    {
        $this->routeDescriber = $this->createMock(RouteDescriberInterface::class);
        $this->routes = new RouteCollection();
        $this->describer = new RouteDescriber(
            new Container(),
            $this->routes,
            $this->createMock(ControllerNameParser::class),
            [$this->routeDescriber]
        );
    }
}
