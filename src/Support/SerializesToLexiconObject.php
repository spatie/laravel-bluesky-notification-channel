<?php

namespace Spatie\BlueskyNotificationChannel\Support;

use ReflectionClass;
use ReflectionProperty;

trait SerializesToLexiconObject
{
    public function toArray(): array
    {
        return [
            '$type' => $this->getType(),
            ...$this->serializeProperties(),
        ];
    }

    abstract public function getType(): string;

    protected function serializeProperties(): array
    {
        return collect((new ReflectionClass($this))->getProperties(ReflectionProperty::IS_PUBLIC))
            ->filter(function (ReflectionProperty $property) {
                return \count($property->getAttributes(IgnoreProperty::class)) === 0;
            })
            ->flatMap(fn (ReflectionProperty $property) => [
                $property->getName() => $this->{$property->getName()},
            ])
            ->map(function (mixed $value) {
                if (\is_object($value) && method_exists($value, 'toArray')) {
                    return $value->toArray();
                }

                return $value;
            })
            ->filter()
            ->toArray();
    }
}
