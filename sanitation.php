<?php
trait availableTypes
{
    /**
     * ["typeName" => ["treatment"]]
     * @var array
     */
    private array $availableTypes = [
        "int"       => ["numeric"],
        "integer"   => ["numeric"],
        "str"       => ["strTag", "strQuote"],
        "string"    => ["strTag", "strQuote"],
        "bool"      => ["boolean"],
        "boolean"   => ["boolean"]
    ];
    /**
     * check if a type is sanitable
     * @param string $value // input type name
     * @return bool
     */
    final public function typeValid(string $value): bool
    {
        return array_key_exists($value, $this->availableTypes);
    }

    /**
     * get all valid types
     * @return array
     */
    final public function getValid(): array
    {
        return $this->availableTypes;
    }

    /**
     * get treatment related to data type
     * @param string $type
     * @return array
     */
    final public function getTreatment(string $type): array
    {
        return $this->typeValid($type) ? $this->availableTypes[$type] : [];
    }
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
 * @var $devaultValue of the var to be used if the value is empty() and $allowNull is false 
 */
class dirtyItem
{
    use availableTypes;

    public string $name;
    public string $type;

    private ?int $maxLength;
    private bool $allowNull = true;
    private $devaultValue = null;

    public function __construct(string $name, string $type, array $treatment, ?int $maxLength = null, bool $allowNull = true, $devaultValue = null)
    {
        if ($this->typeValid($type)) {
            $this->name = $name;
            $this->type = $type;
            $this->maxLength = $maxLength;
            $this->allowNull = $allowNull;
            $this->devaultValue = $devaultValue;
        }
    }
}

class Sanitation
{
    use availableTypes;
    public array $result = [];

    /** @var dirtyItem[] $dirty */
    public array $dirty = [];

    public function __construct() {}

    public function sanitate(string $name, string $type, $value = null, ?int $maxLength = null, bool $allowNull = true, $devaultValue = null)
    {
        $treatment = [];
        if ($this->typeValid($type)) {
            $treatment = $this->getTreatment($type);
            $this->dirty[$name] = new dirtyItem($name, $type, $treatment, $maxLength, $allowNull, $devaultValue);
        }
        $treated = $value;
        foreach ($treatment as $key => $name) {
            $treated = match ($name) {
                'numeric' => $this->numeric($treated),
                'strTag' => $this->strTag($treated),
                'strQuote' => $this->strQuote($treated),
                'boolean' => $this->boolean($treated),
            };
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
        return $value;
    }

    private function boolean($value): bool
    {
        $result = is_bool($value) ? $value : $value == "true" || $value === 1;
        return $result;
    }
}