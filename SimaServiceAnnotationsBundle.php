<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations;


use SimonMarx\Symfony\Bundles\ServiceAnnotations\DependencyInjection\Compiler\NoServicePass;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\DependencyInjection\Compiler\ServiceAliasPass;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\DependencyInjection\Compiler\ServiceTagArgumentPass;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\DependencyInjection\Compiler\ServiceTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SimaServiceAnnotationsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container
            ->addCompilerPass(new ServiceTagPass())
            ->addCompilerPass(new ServiceAliasPass())
            ->addCompilerPass(new ServiceTagArgumentPass())
            ->addCompilerPass(new NoServicePass())
        ;
    }
}