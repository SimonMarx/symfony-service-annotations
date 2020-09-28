<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations\Struct;


use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class ClassAnnotationScan
{
    private ReflectionClass $classReflection;
    private AnnotationReader $annotationReader;

    /** @var array<object> */
    private array $classAnnotations = [];

    /** @var array<object> */
    private array $propertyAnnotations = [];

    /** @var array<object> */
    private array $methodAnnotations = [];

    public function __construct(string $className)
    {
        $this->classReflection = new ReflectionClass($className);
        $this->annotationReader = new AnnotationReader();

        $this->init();
    }

    public static function newByReflection(ReflectionClass $reflectionClass): self
    {
        return new self($reflectionClass->getName());
    }

    public static function newByObject(object $object): self
    {
        return new self(\get_class($object));
    }

    private function init()
    {
        $this->getPropertyAnnotations();
        $this->getMethodAnnotations();
        $this->getClassAnnotations();
    }

    private function getClassAnnotations(): void
    {
        foreach ($this->annotationReader->getClassAnnotations($this->classReflection) as $classAnnotation) {
            $this->addClassAnnotation($classAnnotation);
        }
    }

    private function getMethodAnnotations(): void
    {
        foreach ($this->classReflection->getMethods() as $method) {
            foreach ($this->annotationReader->getMethodAnnotations($method) as $methodAnnotation) {
                $this->addMethodAnnotation($method, $methodAnnotation);
            }
        }
    }

    private function getPropertyAnnotations(): void
    {
        foreach ($this->classReflection->getProperties() as $property) {
            foreach ($this->annotationReader->getPropertyAnnotations($property) as $propertyAnnotation) {
                $this->addPropertyAnnotation($property, $propertyAnnotation);
            }
        }
    }

    public function addClassAnnotation(object $annotation): self
    {
        $this->classAnnotations[] = $annotation;

        return $this;
    }

    public function addMethodAnnotation(ReflectionMethod $method, object $annotation): self
    {
        $this->methodAnnotations[$method->getName()][] = $annotation;

        return $this;
    }

    public function addPropertyAnnotation(ReflectionProperty $property, object $annotation): self
    {
        $this->propertyAnnotations[$property->getName()][] = $annotation;

        return $this;
    }

    public function findMethodAnnotations(string $annotationClass): array
    {
        $found = [];

        foreach ($this->methodAnnotations as $methodName => $annotations) {
            $found[$methodName] = \array_filter(
                $annotations,
                fn(object $annotation) => \get_class($annotation) === $annotationClass
            );

            if (0 === \count($found[$methodName])) {
                unset($found[$methodName]);
            }
        }

        return $found;
    }

    public function findPropertyAnnotations(string $annotationClass): array
    {
        $found = [];

        foreach ($this->propertyAnnotations as $propertyName => $annotations) {
            $found[$propertyName] = \array_filter(
                $annotations,
                fn(object $annotation) => \get_class($annotation) === $annotationClass
            );

            if (0 === \count($found[$propertyName])) {
                unset($found[$propertyName]);
            }
        }

        return $found;
    }
}