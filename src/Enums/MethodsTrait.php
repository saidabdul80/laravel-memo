<?php

namespace Saidabdulsalam\LaravelMemo\Enums;
trait MethodsTrait
{
    public static function getKey($value)
    {
        $constants = self::toArray();
        return array_search($value, $constants, true);
    }

    public static function getValue(string $key): ?int
    {
        $constants = self::toArray();
        return $constants[$key] ?? null;
    }

    private static function toArray(): array
    {
        $reflection = new \ReflectionClass(self::class);
        return $reflection->getConstants();
    }

    public static function getKeys(){
        return self::toArray();
    }
    
}
