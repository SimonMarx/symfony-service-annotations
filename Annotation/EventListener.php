<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("CLASS")
 */
class EventListener
{
    private string $eventTag;
    private string $event;
    private array $attributes = [];

    public function __construct(array $values = [])
    {
        $this->event = $values['event'] ?? $values['value'];
        $this->eventTag = $values['eventTag'] ?? 'kernel.event_listener';

        unset($values['value']);
        unset($values['name']);

        $this->attributes['event'] = $this->event;
        $this->attributes = $values;
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    public function getEventTag(): string
    {
        return $this->eventTag;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}