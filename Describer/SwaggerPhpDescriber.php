<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Describer;

use Nelmio\ApiDocBundle\SwaggerPhp\AddDefaults;
use Nelmio\ApiDocBundle\SwaggerPhp\ModelRegister;
use Nelmio\ApiDocBundle\SwaggerPhp\PathResolver;
use Swagger\Analysis;
use Swagger\Analyser;

final class SwaggerPhpDescriber extends ExternalDocDescriber implements ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    private $pathResolver;

    public function __construct(string $projectPath, bool $overwrite = false)
    {
        $nelmioNamespace = 'Nelmio\\ApiDocBundle\\';
        if (!in_array($nelmioNamespace, Analyser::$whitelist)) {
            Analyser::$whitelist[] = $nelmioNamespace;
        }

        parent::__construct(function () use ($projectPath) {
            $options = ['processors' => $this->getProcessors()];
            $annotation = \Swagger\scan($projectPath, $options);

            return json_decode(json_encode($annotation));
        }, $overwrite);
    }

    /**
     * If set, the describer will try to complete paths not defined.
     */
    public function setPathResolver(PathResolver $pathResolver)
    {
        $this->pathResolver = $pathResolver;
    }

    private function getProcessors(): array
    {
        $processors = [
            new AddDefaults(),
            new ModelRegister($this->modelRegistry)
        ];
        if (null !== $this->pathResolver) {
            $processors[] = $this->pathResolver;
        }

        return array_merge($processors, Analysis::processors());
    }
}
