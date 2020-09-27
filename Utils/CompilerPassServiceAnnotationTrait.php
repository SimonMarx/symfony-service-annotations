<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils;


use App\Response\FooInterface;
use App\Response\ResponseContext;
use App\Response\ResponseFactory;
use App\Serializer\CircularReference\DefaultCircularReferenceHandler;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation\IgnoreParentServiceAnnotations;
use Symfony\Component\DependencyInjection\Definition;

trait CompilerPassServiceAnnotationTrait
{
    use ReflectionCacheTrait;

    private AnnotationReader $annotationReader;

    private array $annotationCache = [];

    public function __construct()
    {
        $this->annotationReader = new AnnotationReader();
    }

    private function definitionHasValidClass(Definition $definition): bool
    {
        if (null === $definition->getClass()) {
            return false;
        }

        return \class_exists($definition->getClass(), false);
    }

    private function definitionHasAnnotation(Definition $definition, string $annotationClass): bool
    {
        if (false === $this->definitionHasValidClass($definition)) {
            return false;
        }


        foreach ($this->getDefinitionAnnotations($definition) as $annotation) {
            if ($annotation instanceof $annotationClass) {
                return true;
            }
        }

        return false;
    }

    private function warmupDefinitionAnnotationCache(Definition $definition): void
    {
        $cacheKey = $this->getDefinitionCacheKey($definition);
        if (true === \array_key_exists($cacheKey, $this->annotationCache)) {
            return;
        }

        $rc = $this->getClassReflection($definition->getClass());

        if (null === $rc) {
            return;
        }

        $this->annotationCache[$cacheKey] = $this->crawlAnnotationsRecursive($rc);
    }

    private function crawlAnnotationsRecursive(ReflectionClass $reflectionClass): array
    {
        $annotations = $this->annotationReader->getClassAnnotations($reflectionClass);
        $ignored = $this->annotationReader->getClassAnnotation($reflectionClass, IgnoreParentServiceAnnotations::class);

        if (
            $reflectionClass->getParentClass() instanceof ReflectionClass
            && (
                $reflectionClass->getParentClass()->isAbstract()
                || $reflectionClass->getParentClass()->isInterface()
            )
        ) {
            $parentAnnotations = $this->crawlAnnotationsRecursive($reflectionClass->getParentClass());

            if ($ignored instanceof IgnoreParentServiceAnnotations) {
                foreach ($parentAnnotations as $parentAnnotation) {
                    if (false === $ignored->ignoreAnnotation(\get_class($parentAnnotation))) {
                        $annotations[] = $parentAnnotation;
                    }
                }
            } else {
                $annotations = \array_merge($annotations, $parentAnnotations);
            }
        }

        foreach ($reflectionClass->getInterfaces() as $interface) {
            $interfaceAnnotations = $this->crawlAnnotationsRecursive($interface);

            if ($ignored instanceof IgnoreParentServiceAnnotations) {
                foreach ($interfaceAnnotations as $interfaceAnnotation) {
                    if (false === $ignored->ignoreAnnotation(\get_class($interfaceAnnotation))) {
                        $annotations[] = $interfaceAnnotation;
                    }
                }
            } else {
                $annotations = \array_merge($annotations, $interfaceAnnotations);
            }
        }

        return $annotations;
    }

    private function getDefinitionCacheKey(Definition $definition): string
    {
        return \md5($definition->getClass());
    }

    private function getDefinitionAnnotation(Definition $definition, string $annotationClass): array
    {
        if (false === $this->definitionHasAnnotation($definition, $annotationClass)) {
            return [];
        }

        $annotations = \array_filter(
            $this->getDefinitionAnnotations($definition),
            fn($annotation) => $annotation instanceof $annotationClass
        );

        return \array_values($annotations);
    }

    private function getDefinitionAnnotations(Definition $definition): array
    {
        $this->warmupDefinitionAnnotationCache($definition);
        $cacheKey = $this->getDefinitionCacheKey($definition);

        return $this->annotationCache[$cacheKey] ?? [];
    }
}