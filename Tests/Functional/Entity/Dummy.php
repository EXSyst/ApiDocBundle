<?php

/*
 * This file is part of the ApiDocBundle package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Bundle\ApiDocBundle\Tests\Functional\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Guilhem N. <egetick@gmail.com>
 *
 * @ApiResource(
 *   collectionOperations={
 *     "get"={"method"="GET"},
 *     "custom2"={"path"="/foo", "method"="GET"},
 *     "custom"={"path"="/foo", "method"="POST"},
 *   },
 *   itemOperations={"get"={"method"="GET"}})
 * )
 */
class Dummy
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @ApiProperty(iri="http://schema.org/name")
     */
    private $name;

    /**
     * @var array
     */
    private $foo;

    public function getId(): int
    {
        return $this->id;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function hasRole(string $role)
    {
    }

    public function setFoo(array $foo = null)
    {
    }
}
