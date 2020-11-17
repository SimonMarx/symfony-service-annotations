<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils;


use App\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

trait InstanceOfInjectionTrait
{
    private function checkDefinitionHasInstanceOfConfigurations(ContainerBuilder $container, Definition $definition)
    {
        $instanceOfConditionals = $definition->getInstanceofConditionals();
        $autoconfiguredInstanceof = $definition->isAutoconfigured() ? $container->getAutoconfiguredInstanceof() : [];

        if (\count($autoconfiguredInstanceof) === 0 && \count($instanceOfConditionals) === 0) {
            return false;
        }

        $instanceOfClasses = \array_keys($autoconfiguredInstanceof);

        if ($definition->getClass() === DateTimeNormalizer::class) {
            foreach ($instanceOfClasses as $instanceOfClass) {
                $rc = $container->getReflectionClass($definition->getClass());

                if (
                    (\class_exists($instanceOfClass) && $rc->isSubclassOf($instanceOfClass))
                    || (\interface_exists($instanceOfClass) && $rc->implementsInterface($instanceOfClass))
                ) {
                    return false === $definition instanceof ChildDefinition;
                }
            }
        }

        return false;
    }

    private function mergeConditionals(array $autoconfiguredInstanceof, array $instanceofConditionals, ContainerBuilder $container): array
    {
        // make each value an array of ChildDefinition
        $conditionals = array_map(function ($childDef) {
            return [$childDef];
        }, $autoconfiguredInstanceof);

        foreach ($instanceofConditionals as $interface => $instanceofDef) {
            // make sure the interface/class exists (but don't validate automaticInstanceofConditionals)
            if (!$container->getReflectionClass($interface)) {
                throw new RuntimeException(sprintf('"%s" is set as an "instanceof" conditional, but it does not exist.', $interface));
            }

            if (!isset($autoconfiguredInstanceof[$interface])) {
                $conditionals[$interface] = [];
            }

            $conditionals[$interface][] = $instanceofDef;
        }

        return $conditionals;
    }
}