<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations\DependencyInjection\Compiler;


use SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation\DependencyInjection;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Exception\InvalidServiceAnnotationException;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Struct\ClassAnnotationScan;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils\CompilerPassServiceAnnotationTrait;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils\ReflectionCacheTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

class DependencyInjectionPass implements CompilerPassInterface
{
    use CompilerPassServiceAnnotationTrait;
    use ReflectionCacheTrait;

    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $definition) {
            if (false === $this->definitionHasValidClass($definition)) {
                continue;
            }

            $classAnnotationScan = $this->fullAnnotationScan($definition);

            $this->handleMethodAnnotations($classAnnotationScan, $definition);
            $this->handlePropertyAnnotations($classAnnotationScan, $definition);
        }
    }

    private function handlePropertyAnnotations(ClassAnnotationScan $annotationScan, Definition $definition)
    {
        /**
         * @var string $propertyName
         * @var DependencyInjection[] $propertyAnnotations
         */
        foreach ($annotationScan->findPropertyAnnotations(DependencyInjection::class) as $propertyName => $propertyAnnotations) {
            $property = $this->getPropertyReflection($definition->getClass(), $propertyName);

            if (false === $property->isPublic()) {
                throw new InvalidServiceAnnotationException(\sprintf('Property %s::%s cannot be configured with @DependencyInjection cause it is not a public property', $property->getDeclaringClass()->getName(), $property->getName()));
            }

            if (\count($propertyAnnotations) > 1) {
                throw new InvalidServiceAnnotationException(\sprintf('Property %s::%s has more than one annotation of type @DependencyInjection which is not possible to inject multiple services to property', $property->getDeclaringClass()->getName(), $property->getName()));
            }

            $propertyAnnotation = $propertyAnnotations[0];

            if (null === $propertyAnnotation->getServiceId() && null === $propertyAnnotation->getTagged()) {
                throw new InvalidServiceAnnotationException(\sprintf('Property %s::%s has @DependencyInjection without "serviceId" and "tagged" option. One of them must be defined'));
            }


            if (null !== $propertyAnnotation->getServiceId()) {
                $definition->setProperty($property->getName(), service($propertyAnnotation->getServiceId()));
            } elseif (null !== $propertyAnnotation->getTagged()) {
                $definition->setProperty($property->getName(), tagged_iterator($propertyAnnotation->getTagged()));
            }
        }
    }

    private function handleMethodAnnotations(ClassAnnotationScan $annotationScan, Definition $definition)
    {
        /**
         * @var string $methodName
         * @var DependencyInjection[] $methodAnnotations
         */
        foreach ($annotationScan->findMethodAnnotations(DependencyInjection::class) as $methodName => $methodAnnotations) {
            $method = $this->getMethodReflection($definition->getClass(), $methodName);

            if (false === $method->isConstructor()) {
                // Currently only constructor is supported, @TODO: Add setter injection support
                continue;
            }

            $targetless = \array_filter($methodAnnotations, fn(DependencyInjection $dependencyInjection) => $dependencyInjection->getTarget() === null);

            if (\count($targetless) > 0 && $method->getNumberOfParameters() > 1) {
                throw new InvalidServiceAnnotationException(\sprintf('Method %s::%s has a @DependencyInjection annotation without a target defined, this is only possible for methods with one parameter or properties', $method->getDeclaringClass()->getName(), $method->getName()));
            }

            foreach ($method->getParameters() as $parameter) {
                $foundTargetAnnotation = \array_values(\array_filter(
                        $methodAnnotations,
                        fn(DependencyInjection $dependencyInjection) => $dependencyInjection->getTarget() === $parameter->getName()
                    ))[0] ?? null;

                if ($foundTargetAnnotation instanceof DependencyInjection) {
                    if (null !== $foundTargetAnnotation->getServiceId()) {
                        $definition->setArgument(\sprintf('$%s', $parameter->getName()), new Reference($foundTargetAnnotation->getServiceId()));
                    } elseif (null !== $foundTargetAnnotation->getTagged()) {
                        $definition->setArgument(\sprintf('$%s', $parameter->getName()), tagged_iterator($foundTargetAnnotation->getTagged()));
                    }
                }
            }
        }
    }
}