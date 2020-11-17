<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("CLASS")
 */
class ParentService
{
    private string $parent;

    public function __construct(array $values = [])
    {
        $this->parent = $values['parent'] ?? $values['value'];
    }

    public function getParent(): string
    {
        return $this->parent;
    }
}