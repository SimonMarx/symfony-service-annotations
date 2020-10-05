<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

trait ReflectionCacheTrait
{
    private array $classReflectionCache = [];

    private function getClassReflection(string $className, bool $recursive = false): ?ReflectionClass
    {
        $cacheKey = \md5($className);

        if (false === \array_key_exists($cacheKey, $this->classReflectionCache)) {
            try {
                $this->classReflectionCache[$cacheKey] = new ReflectionClass($className);
            } catch (ReflectionException $reflectionException) {
                return null;
            }
        }

        if (
            true === $recursive
            && false !== $this->classReflectionCache[$cacheKey]->getParentClass()
            && false === \array_key_exists($cacheKey, $this->classReflectionCache)
        ) {
            $this->getClassReflection($this->classReflectionCache[$cacheKey]->getParentClass());
        }

        return $this->classReflectionCache[$cacheKey] ?? null;
    }

    /**
     * @return ReflectionProperty[]
     */
    public function getPropertyReflections(string $className, bool $recursive = false, int $filter = null): array
    {
        $classReflection = $this->getClassReflection($className, $recursive);

        $properties = $classReflection->getProperties();

        if (false !== $classReflection->getParentClass() && true === $recursive) {
            $parentProperties = $this->getPropertyReflections($classReflection->getParentClass()->getName(), $recursive, $filter);

            $filtered = \array_filter($parentProperties, function (ReflectionProperty $parentProperty) use ($properties) {
                $childDefinition = \array_filter($properties, function (ReflectionProperty $childProperty) use ($parentProperty) {
                    return $childProperty->getName() === $parentProperty->getName()
                        && $childProperty->getDeclaringClass()->getName() === $parentProperty->getDeclaringClass()->getName();
                });
                return \count($childDefinition) === 0;
            });

            $properties = \array_merge($filtered, $properties);
        }

        return $properties;
    }

    public function getMethodReflection(string $className, string $methodName): ?ReflectionMethod
    {
        $rc = $this->getClassReflection($className);

        if (null === $rc) {
            return null;
        }

        try {
            return $rc->getMethod($methodName);
        } catch (ReflectionException $exception) {
            return null;
        }
    }

    public function getPropertyReflection(string $className, string $propertyName): ?ReflectionProperty
    {
        $rc = $this->getClassReflection($className);

        if (null === $rc) {
            return null;
        }

        try {
            return $rc->getProperty($propertyName);
        } catch (ReflectionException $exception) {
            return null;
        }
    }
}