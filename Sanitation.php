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
        return self::$treatments[$normal];
    }

    final public function getDefault(string $type) {}
}

/**
 * stores the detail of dirty variables to be sanitated
 *
 * should include:
 * @var string $name of the var
 * @var availableTypes $type of the var
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


class Sanitation
{
    public array $result = [];

    /** @var DirtyItem[] $dirty */
    public array $dirty = [];

    public function __construct() {}

    public function sanitate(string $name, string $type, $value = null, ?int $maxLength = null, bool $allowNull = true, $defaultValue = null)
    {
        $treatment = [];
        $treatment = TreatmentHandler::getTreatment($type);
        $this->dirty[$name] = new DirtyItem($name, $type, $maxLength, $allowNull, $defaultValue);

        if (is_null($value)) {

            $treated = null; #fallback
            if (!$allowNull) {
            } else {
                $treated = $defaultValue ?? null;
            }

        } else {
            $treated = $value;
            foreach ($treatment as $key => $tmt) {
                $treated = match ($tmt) {
                    'numeric'   => $this->numeric($treated),
                    'strTag'    => $this->strTag($treated),
                    'strQuote'  => $this->strQuote($treated),
                    'boolean'   => $this->boolean($treated),
                    'nullify'   => $this->nullify($treated)
                };
            }
        }

        $this->result[$name] = $treated;
    }

    private function numeric($value): int
    {
        return (int)$value;
    }

    private function strTag($value): string
    {
        return strip_tags($value, ['br', 'span', 'ul', 'li', 'strong', 'i', 'u']);
    }

    private function strQuote($value): string
    {
        $value = str_replace('`', "'", (string)$value);
        $value = str_replace('"', "'", (string)$value);
        $value = str_replace('\\', "/", (string)$value);
        $value = str_replace('_', " ", (string)$value);
        return $value;
    }

    private function boolean($value): bool
    {
        $result = is_bool($value) ? $value : $value === "true" || $value === 1;
        return $result;
    }

    private function nullify($value): null
    {
        return null;
    }

    public function toObject()
    {
        return (object)$this->result;
    }
}
