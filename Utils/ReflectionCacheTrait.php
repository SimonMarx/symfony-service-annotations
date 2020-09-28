<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils;


use App\Command\PermissionPersistCommand;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

trait ReflectionCacheTrait
{
    private array $classReflectionCache = [];

    private function getClassReflection(string $className): ?ReflectionClass
    {
        $cacheKey = \md5($className);

        if (false === \array_key_exists($cacheKey, $this->classReflectionCache)) {
            try {
                $this->classReflectionCache[$cacheKey] = new ReflectionClass($className);
            } catch (ReflectionException $reflectionException) {
                return null;
            }
        }

        return $this->classReflectionCache[$cacheKey] ?? null;
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