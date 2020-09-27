<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation;


use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("CLASS")
 */
class ServiceTagArgument
{
    private ?string $tag = null;
    private string $argument;
    private bool $ignoreWhenDefined = true;
    private bool $exceptionWhenDefined = true;

    /** @var string|bool|int|float */
    private $value;

    public function __construct(array $values = [])
    {
        $this->tag = $values['tag'] ?? null;
        $this->argument = $values['argument'];
        $this->value = $values['value'];
        $this->ignoreWhenDefined = $values['ignoreWhenDefined'] ?? true;
        $this->exceptionWhenDefined = $values['exceptionWhenDefined'] ?? true;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function getArgument(): string
    {
        return $this->argument;
    }

    /**
     * @return bool|float|int|string
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getExceptionWhenDefined(): bool
    {
        return $this->exceptionWhenDefined;
    }

    public function getIgnoreWhenDefined(): bool
    {
        return $this->ignoreWhenDefined;
    }

    public function setTag(string $tag): self
    {
        if (null !== $this->tag) {
            throw new \RuntimeException(\sprintf('the tag cannot be redefined when once set'));
        }

        $this->tag = $tag;

        return $this;
    }
}