<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils;

class ObjectClonerContext
{
    public const IGNORE_INHERITANCE_CHECK = 'ignoreInheritanceCheck';
    public const CONSTRUCT = 'construct';

    private bool $ignoreInheritanceCheck = false;

    private array $construct = [];

    public function __construct(array $data = [])
    {
        $this->ignoreInheritanceCheck = $data[self::IGNORE_INHERITANCE_CHECK] ?? $this->ignoreInheritanceCheck;
        $this->construct = $data[self::CONSTRUCT] ?? $this->construct;
    }

    public function isIgnoreInheritanceCheck(): bool
    {
        return $this->ignoreInheritanceCheck;
    }

    public function getConstruct(): array
    {
        return $this->construct;
    }
}