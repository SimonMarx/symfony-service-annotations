<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations\DependencyInjection\Compiler;


use SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation\ServiceTag;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils\CompilerPassServiceAnnotationTrait;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils\ReflectionCacheTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ServiceTagPass implements CompilerPassInterface
{
    use ReflectionCacheTrait;
    use CompilerPassServiceAnnotationTrait;

    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $serviceId => $definition) {
            if (false === $this->definitionHasValidClass($definition) || false === $this->definitionHasAnnotation($definition, ServiceTag::class)) {
                continue;
            }

            /** @var ServiceTag[] $tags */
            $tags = $this->getDefinitionAnnotation($definition, ServiceTag::class);

            foreach ($tags as $tag) {
                $definition->addTag($tag->getName(), $tag->getAttributes());
            }
        }
    }
}