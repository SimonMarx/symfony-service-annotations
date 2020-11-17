<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations;


use SimonMarx\Symfony\Bundles\ServiceAnnotations\DependencyInjection\Compiler\DefinitionManipulationPass;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\DependencyInjection\Compiler\DependencyInjectionPass;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\DependencyInjection\Compiler\NoServicePass;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\DependencyInjection\Compiler\ParentServicePass;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\DependencyInjection\Compiler\ServiceAliasPass;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\DependencyInjection\Compiler\ServiceCallPass;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\DependencyInjection\Compiler\ServiceTagArgumentPass;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\DependencyInjection\Compiler\ServiceTagPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SimaServiceAnnotationsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container
            ->addCompilerPass(new DefinitionManipulationPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1)
            ->addCompilerPass(new ServiceTagPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1)
            ->addCompilerPass(new ServiceAliasPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1)
            ->addCompilerPass(new DependencyInjectionPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1)
            ->addCompilerPass(new ServiceTagArgumentPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1)
            ->addCompilerPass(new ParentServicePass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1)
            ->addCompilerPass(new ServiceCallPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1)

            ->addCompilerPass(new NoServicePass(), PassConfig::TYPE_REMOVE);
        ;
    }
}