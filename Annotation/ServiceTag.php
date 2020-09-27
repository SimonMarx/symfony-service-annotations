<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation;


use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("CLASS")
 */
class ServiceTag
{
    private string $name;
    private ?int $priority = null;
    private array $attributes = [];

    public function __construct(array $values = [])
    {
        $this->name = $values['name'] ?? $values['value'];
        $this->priority = $values['priority'] ?? null;

        if (\array_key_exists('value', $values)) {
            unset($values['value']);
        } else {
            unset($values['name']);
        }

        $this->attributes = $values;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPriority(): ?int
    {
        return $this->priority ?: $this->getAttributes()['priority'] ?? null;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}