<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations\DependencyInjection\Compiler;


use SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation\ServiceTag;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation\ServiceTagArgument;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Exception\InvalidServiceAnnotationException;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils\CompilerPassServiceAnnotationTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ServiceTagArgumentPass implements CompilerPassInterface
{
    use CompilerPassServiceAnnotationTrait;

    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $serviceId => $definition) {
            if (false === $this->definitionHasValidClass($definition) || false === $this->definitionHasAnnotation($definition, ServiceTagArgument::class)) {
                continue;
            }

            /** @var ServiceTagArgument[] $arguments */
            $arguments = $this->getDefinitionAnnotation($definition, ServiceTagArgument::class);
            /** @var ServiceTag[] $serviceTags */
            $serviceTags = $this->getDefinitionAnnotation($definition, ServiceTag::class);

            $attributesByTag = [];

            foreach ($arguments as $tagArgument) {
                if (null === $tagArgument->getTag() && \count($serviceTags) > 1) {
                    throw new InvalidServiceAnnotationException(\sprintf('Please define in your class "%s" in annotation "@ServiceTagArgument" the property "tag", since you have more than one "@ServiceTag" definitions in your class or parents of your class the system cannot autodetect the service tag for your arguments', $definition->getClass()));
                }

                $tagName = $tagArgument->getTag() ?: $serviceTags[0]->getName();
                $tagArgument->setTag($tagName);

                $attributesByTag[$tagName][] = $tagArgument;
            }

            $definedAttributesByTag = [];

            foreach ($attributesByTag as $tag => $attributes) {
                foreach ($definition->getTag($tag) as $tagDefinition) {
                    $definedAttributesByTag[$tag] = \array_replace($definedAttributesByTag[$tag] ?? [], \array_keys($tagDefinition));
                }
            }

            $usedAttributes = [];

            /** @var ServiceTagArgument $attribute */
            foreach ($attributesByTag as $tag => $attributes) {
                foreach ($attributes as $attribute) {
                    if (true === \in_array($attribute->getArgument(), $definedAttributesByTag[$tag] ?? []) && true === $attribute->getIgnoreWhenDefined() && true === $attribute->getExceptionWhenDefined()) {
                        throw new InvalidServiceAnnotationException(\sprintf('Attribute "%s" defined in class "%s" was already defined for tag "%s". Add options "ignoreWhenDefined" to false to overwrite attribute or set option "exceptionWhenDefined" to false to hide the exception and ignore the attribute', $attribute->getArgument(), $definition->getClass(), $attribute->getTag()));
                    }

                    if (
                        false === \in_array($attribute->getArgument(), $definedAttributesByTag[$tag])
                        || (
                            true === \in_array($attribute->getArgument(), $definedAttributesByTag[$tag])
                            && false === $attribute->getIgnoreWhenDefined()
                        )
                    ) {
                        $usedAttributes[$tag][$attribute->getArgument()] = $attribute->getValue();
                    }
                }
            }


            foreach($usedAttributes as $tag => $attributes) {
                $definition->addTag($tag, $attributes);
            }
        }
    }
}