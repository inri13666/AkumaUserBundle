<?php
/**
 * User  : Nikita Makarov
 * Date  : 1/5/16
 * E-Mail: mesaverde228@gmail.com
 * 
 * @file 
 * Description
 */

namespace Akuma\Bundle\UserBundle\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class FosUserCompilerPass  implements CompilerPassInterface{

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        /** @var Definition $definition */
        $definition = $container->getDefinition('fos_user.user_manager.default');

        if('FOS\UserBundle\Doctrine\UserManager' === $definition->getClass()){
            $definition->setClass('Akuma\Bundle\UserBundle\Doctrine\UserManager');
        }

    }
}