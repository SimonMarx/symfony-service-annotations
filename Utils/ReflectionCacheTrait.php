<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils;


use ReflectionClass;
use ReflectionException;

trait ReflectionCacheTrait
{
    private array $classCache = [];

    private function getClassReflection(string $className): ?ReflectionClass
    {
        $hashKey = md5($className);

        if (false === \array_key_exists($hashKey, $this->classCache)) {
            try {
                $this->classCache[$hashKey] = new ReflectionClass($className);
            } catch (ReflectionException $reflectionException) {
                return null;
            }
        }

        return $this->classCache[$hashKey] ?? null;
    }
}