<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation;


use Doctrine\Common\Annotations\Annotation\Target;

/**
 * using this annotation on a class ignores all service annotations which are provided by interfaces or extended classes
 *
 * when leave "included" and "excluded" options blank, all parent annotations will be ignored
 * by using "included" you can specify which annotations you want to ignore (using annotation namespace as parameter), all other annotations will be not ignored and interpreted by compiler passes
 * by using "excluded" you can specify which annotations should NOT be ignored from parents (using annotation namespace as parameter), all other annotations will be ignored and NOT interpreted by compiler passes
 *
 *
 * @Annotation
 * @Target("CLASS")
 */
class IgnoreParentServiceAnnotations
{
    private ?array $included = null;
    private ?array $excluded = null;

    public function __construct(array $values = [])
    {
        $this->included = $values['included'] ?? $values['value'] ?? null;
        $this->excluded = $values['excluded'] ?? null;
    }

    public function getExcluded(): ?array
    {
        return $this->excluded;
    }

    public function getIncluded(): ?array
    {
        return $this->included;
    }

    public function ignoreAnnotation(string $annotationNamespace): bool
    {
        if (null === $this->included && null === $this->excluded) {
            return true;
        }

        if (null !== $this->included && true === \in_array($annotationNamespace, $this->included)) {
            return true;
        }

        if (null !== $this->excluded && false === \in_array($annotationNamespace, $this->excluded)) {
            return true;
        }

        return false;
    }
}