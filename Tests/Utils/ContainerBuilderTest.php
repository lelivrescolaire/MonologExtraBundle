<?php

namespace LLS\Bundle\MonologExtraBundle\Tests\Utils;

use \mageekguy\atoum\test;

/**
 * Test class for Extension classes
 *
 * @author Jérémy Jourdin <jeremy.jourdin@lelivrescolaire.fr>
 */
abstract class ContainerBuilderTest extends test
{
    /**
     * @var ContainerBuilder
     */
    protected $container;

    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);

        $this->container = new \mock\Symfony\Component\DependencyInjection\ContainerBuilder();
        
    }
}