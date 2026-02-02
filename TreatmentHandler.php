<?php
class TreatmentHandler
{
    /**
     * ["normalType" => ["treatment"]]
     * @var array
     */
    private static array $treatments = [
        "integer"   => ["numeric"],
        "string"    => ["strTag", "strQuote"],
        "wysiwyg"   => ["wysiwyg", "strQuote"],
        "boolean"   => ["boolean"],
        "null"      => ["nullify"] # I doubt this will ever be used, but at least I could use it for fallbacks
    ];

    /**
     * get treatment related to data type
     * @param string $type
     * @return array
     */
    final public static function getTreatment(string $type): array
    {
        $normal = TypeHandler::normalizeType($type);
        return \array_key_exists($normal, self::$treatments) ? self::$treatments[$normal] : [];
    }
}

/**
 * stores the detail of dirty variables to be sanitated
 *
 * must include:
 * @var string $name of the var
 * @var string $type of the var
 *
 * and optional limitations such as:
 * @var int $maxLength of the var
 * @var bool $allowNull to decide whether the value should be left null if it"s empty()
 * @var $defaultValue of the var to be used if the value is empty() and $allowNull is false
 */
class DirtyItem
{
    public string $name;
    public string $type;
    public string $normalType;

    private ?int $maxLength;
    private bool $allowNull = true;
    private $defaultValue = null;

    public function __construct(string $name, string $type, ?int $maxLength = null, bool $allowNull = true, $defaultValue = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->normalType = TypeHandler::normalizeType($type);
        $this->maxLength = $maxLength;
        $this->allowNull = $allowNull;
        $this->defaultValue = $defaultValue;
    }
}
