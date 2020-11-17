<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations\DependencyInjection\Compiler;


use SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation\NotShared;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils\CompilerPassServiceAnnotationTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DefinitionManipulationPass implements CompilerPassInterface
{
    use CompilerPassServiceAnnotationTrait;

    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $definition) {
            if (
                false === $this->definitionHasValidClass($definition)
                || false === $this->definitionHasAnnotation($definition, NotShared::class)
            ) {
                continue;
            }

            $definition->setShared(false);
        }
    }
}