<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils;


use ReflectionClass;
use ReflectionProperty;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Exception\InvalidCloneConfigurationException;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Definition;

class ObjectCloner
{
    use ReflectionCacheTrait;

    private object $source;
    private ?object $target = null;

    private ObjectClonerContext $context;

    private ReflectionClass $sourceReflection;
    private ReflectionClass $targetReflection;

    /**
     * @throws \ReflectionException
     */
    public function __construct(object $source, string $targetClass, ObjectClonerContext $context = null)
    {
        if (false === \class_exists($targetClass)) {
            throw new InvalidCloneConfigurationException(\sprintf('ObjectCloner requires a valid $targetClass, given: "%s" does not exist as class', $targetClass));
        }

        $this->source = $source;
        $this->context = $context ?? new ObjectClonerContext();

        $this->sourceReflection = $this->getClassReflection(\get_class($source), true);
        $this->targetReflection = $this->getClassReflection($targetClass, true);
    }

    public static function clone(object $source, string $targetClass, ObjectClonerContext $context = null): object
    {
        $instance = new self($source, $targetClass, $context);
        return $instance->cloneObject();
    }

    public function cloneObject(): object
    {
        if (false === $this->checkIfTargetIsValid()) {
            throw new InvalidCloneConfigurationException(\sprintf('"%s" is an invalid target to clone from "%s" because its not a child of the source class', $this->targetReflection->getName(), $this->sourceReflection->getName()));
        }

        $sortedConstructorArgs = $this->getSortedConstructorParams($this->targetReflection);

        if ($this->targetReflection->getConstructor()->getNumberOfParameters() !== \count($sortedConstructorArgs)) {
            throw new InvalidCloneConfigurationException(\sprintf('Your target "%s" requires "%s" constructor parameters but only "%d" are given.', $this->targetReflection->getName(), $this->targetReflection->getConstructor()->getNumberOfParameters(), \count($sortedConstructorArgs)));
        }

        if (null === $this->target) {
            if ($this->targetReflection->getConstructor()->getNumberOfRequiredParameters() > 0 || \count($this->context->getConstruct()) > 0) {
                $this->target = $this->targetReflection->newInstanceArgs($this->getSortedConstructorParams($this->targetReflection));
            } else {
                $this->target = $this->targetReflection->newInstance();
            }
        }

        $properties = $this->getPropertyReflections($this->targetReflection->getName(), true);

        foreach ($properties as $property) {
            if (null !== $sourceProperty = $this->findSourceProperty($property->getName())) {
                if ($sourceProperty->isPrivate() || $sourceProperty->isProtected()) {
                    $sourceProperty->setAccessible(true);
                }

                $value = $sourceProperty->getValue($this->source);

                $sourceProperty->setValue($this->target, $value);
            }
        }

        return $this->target;
    }

    private function findSourceProperty(string $name): ?ReflectionProperty
    {
        return $this->getPropertyReflection($this->sourceReflection->getName(), $name, true);
    }

    private function getSortedConstructorParams(ReflectionClass $reflectionClass): array
    {
        $sorted = [];

        foreach ($reflectionClass->getConstructor()->getParameters() as $parameter) {
            $sorted[$parameter->getName()] = $this->context->getConstruct()[$parameter->getName()] ?? $parameter->getDefaultValue();
        }

        return $sorted;
    }

    private function checkIfTargetIsValid(): bool
    {
        // check if target class is a child of source class
        if (true === $this->targetReflection->isSubclassOf($this->sourceReflection->getName())) {
            return true;
        }

        // check if source class is a child of the target class
        if (true === $this->sourceReflection->isSubclassOf($this->targetReflection->getName())) {
            return true;
        }

        return false;
    }
}