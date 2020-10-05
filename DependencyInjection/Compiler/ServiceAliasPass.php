<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations\DependencyInjection\Compiler;


use SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation\ServiceAlias;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils\CompilerPassServiceAnnotationTrait;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils\ReflectionCacheTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ServiceAliasPass implements CompilerPassInterface
{
    use ReflectionCacheTrait;
    use CompilerPassServiceAnnotationTrait;

    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $serviceId => $definition) {
            if (false === $this->definitionHasValidClass($definition)) {
                continue;
            }

            if (true === $this->definitionHasAnnotation($definition, ServiceAlias::class)) {
                /** @var ServiceAlias[] $serviceAliases */
                $serviceAliases = $this->getDefinitionAnnotations($definition, ServiceAlias::class);

                foreach ($serviceAliases as $alias) {
                    $container->setAlias($alias->getName(), $serviceId);
                }
            }
        }
    }
}