<?php
use TypeHandler;

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

        if ($value === null) {
            $treated = $allowNull ? $defaultValue ?? null : TypeHandler::getDefault($type);
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
