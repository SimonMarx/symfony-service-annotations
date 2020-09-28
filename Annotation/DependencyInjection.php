<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation;


use Doctrine\Common\Annotations\Annotation\Target;
use RuntimeException;

/**
 * @Annotation
 * @Target({"METHOD", "PROPERTY"})
 */
class DependencyInjection
{
    private ?string $serviceId = null;
    private ?string $target = null;
    private ?string $tagged = null;

    public function __construct(array $values = [])
    {
        $this->serviceId = $values['serviceId'] ?? $values['value'] ?? null;
        $this->target = $values['target'] ?? null;
        $this->tagged = $values['tagged'] ?? null;
    }

    public function setServiceId(string $serviceId): void
    {
        if (null !== $this->serviceId) {
            throw new RuntimeException('Cannot redefine "$serviceId"');
        }

        $this->serviceId = $serviceId;
    }

    public function getServiceId(): ?string
    {
        return $this->serviceId;
    }

    public function setTarget(string $target): void
    {
        if (null !== $this->target) {
            throw new RuntimeException('Cannot redefine "$target"');
        }

        $this->target = $target;
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function getTagged(): ?string
    {
        return $this->tagged;
    }
}