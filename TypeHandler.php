<?php
class TypeHandler
{
    private static array $alias = [
        "int"       => "integer",
        "integer"   => "integer",
        "str"       => "string",
        "string"    => "string",
        "bool"      => "boolean",
        "boolean"   => "boolean",
        "empty"     => "null",
        "null"      => "null",
    ];

    /**
     * check if a type is sanitable
     * @param string $value // input type name
     * @return bool
     */
    final public static function typeValid(string $value): bool
    {
        return \array_key_exists($value, self::$alias);
    }

    /**
     * get all valid types
     * @return array
     */
    final public static function getValid(): array
    {
        return self::$alias;
    }

    final public static function normalizeType(string $type): string
    {
        return \array_key_exists($type, self::$alias) ? self::$alias[$type] : 'null';
    }
}
