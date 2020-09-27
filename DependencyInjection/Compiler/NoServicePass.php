<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations\DependencyInjection\Compiler;


use SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation\NoService;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils\CompilerPassServiceAnnotationTrait;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils\ReflectionCacheTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class NoServicePass implements CompilerPassInterface
{
    use ReflectionCacheTrait;
    use CompilerPassServiceAnnotationTrait;

    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $serviceId => $definition) {
            if ($this->definitionHasValidClass($definition)) {
                continue;
            }

            if ($this->definitionHasAnnotation($definition, NoService::class)) {
                $container->removeDefinition($serviceId);
            }
        }
    }
}