<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations\DependencyInjection\Compiler;

use ReflectionProperty;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation\ParentService;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils\CompilerPassServiceAnnotationTrait;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils\ObjectCloner;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils\ObjectClonerContext;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils\ReflectionCacheTrait;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ParentServicePass implements CompilerPassInterface
{
    use ReflectionCacheTrait;
    use CompilerPassServiceAnnotationTrait;

    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $serviceId => $definition) {
            if (
                false === $this->definitionHasValidClass($definition)
                || false === $this->definitionHasAnnotation($definition, ParentService::class)
            ) {
                continue;
            }

            /** @var ParentService $parentAnnotation */
            $parentAnnotation = $this->findDefinitionAnnotation($definition, ParentService::class);

            if (null === $parentAnnotation) {
                continue;
            }

            if ($definition instanceof ChildDefinition) {
                $definition->setParent($parentAnnotation->getParent());
            } else {
                /** @var ChildDefinition $childDefinition */
                $childDefinition = ObjectCloner::clone(
                    $definition,
                    ChildDefinition::class,
                    new ObjectClonerContext([
                        ObjectClonerContext::CONSTRUCT => ['parent' => $parentAnnotation->getParent()]
                    ])
                );

                $container->removeDefinition($serviceId);
                $container->setDefinition($serviceId, $childDefinition);
            }
        }
    }
}