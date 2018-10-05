<?php
namespace Numerique1\Components\Restresources\RestresourcesBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class Numerique1RestresourcesBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

    }
}