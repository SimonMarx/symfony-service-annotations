<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations\DependencyInjection\Compiler;


use App\Serializer\Normalizer\DateTimeNormalizer;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation\ServiceTag;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation\ServiceTagArgument;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Exception\InvalidServiceAnnotationException;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils\CompilerPassServiceAnnotationTrait;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils\InstanceOfInjectionTrait;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils\ObjectCloner;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils\ObjectClonerContext;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

class ServiceTagArgumentPass implements CompilerPassInterface
{
    use CompilerPassServiceAnnotationTrait;
    use InstanceOfInjectionTrait;

    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $serviceId => $definition) {
            if ($this->checkDefinitionHasInstanceOfConfigurations($container, $definition)) {
                continue;
            }

            if (false === $this->definitionHasValidClass($definition) || false === $this->definitionHasAnnotation($definition, ServiceTagArgument::class)) {
                continue;
            }

            /** @var ServiceTagArgument[] $arguments */
            $arguments = $this->getDefinitionAnnotations($definition, ServiceTagArgument::class);
            $serviceTags = $definition->getTags();

            $attributesByTag = [];

            foreach ($arguments as $tagArgument) {
                if (null === $tagArgument->getTag() && \count($serviceTags) > 1) {
                    throw new InvalidServiceAnnotationException(\sprintf('Please define in your class "%s" in annotation "@ServiceTagArgument" the property "tag", since you have more than one "@ServiceTag" definitions in your class or parents of your class the system cannot autodetect the service tag for your arguments', $definition->getClass()));
                }

                $tagName = $tagArgument->getTag() ?: \array_keys($serviceTags)[0] ?? null;

                if (null === $tagName) {
                    throw new InvalidServiceAnnotationException(\sprintf('Using annotation "%s" requires that the service definition has tags defined. Your service "%s" does not have any tag defined', ServiceTagArgument::class, $serviceId));
                }

                if (null === $tagArgument->getTag()) {
                    $tagArgument->setTag($tagName);
                }

                $attributesByTag[$tagName][] = $tagArgument;
            }

            $existingDefinitionTags = $definition->getTags();

            /**
             * @var string $tag
             * @var ServiceTagArgument[] $serviceTagArguments
             */
            foreach ($attributesByTag as $tag => $serviceTagArguments) {
                foreach ($serviceTagArguments as $serviceTagArgument) {
                    $tagAttributes = $existingDefinitionTags[$serviceTagArgument->getTag()] ?? [];

                    if (\count($tagAttributes) > 0) {
                        foreach ($tagAttributes as $index => $attributes) {
                            $tagAttributes[$index][$serviceTagArgument->getArgument()] = $serviceTagArgument->getValue();
                        }
                    } else {
                        $tagAttributes[0][$serviceTagArgument->getArgument()] = $serviceTagArgument->getValue();
                    }

                    $existingDefinitionTags[$serviceTagArgument->getTag()] = $tagAttributes;
                }
            }

            $definition->setTags($existingDefinitionTags);
        }
    }
}