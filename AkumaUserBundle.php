<?php

namespace Akuma\Bundle\UserBundle;

use Akuma\Bundle\UserBundle\DependencyInjection\Compiler\FosUserCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AkumaUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new FosUserCompilerPass());
    }
}
