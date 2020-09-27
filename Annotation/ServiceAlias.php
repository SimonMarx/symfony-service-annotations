<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation;


use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("CLASS")
 */
class ServiceAlias
{
    private string $name;

    public function __construct(array $values)
    {
        $this->name = $values['name'] ?? $values['value'];
    }

    public function getName(): string
    {
        return $this->name;
    }
}