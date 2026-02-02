<?php
use TypeHandler;

class Sanitation
{
    /**
     * result of the sanitation, always an array
     * @var array
     */
    public array $result = [];

    /**
     * the dirty data of the input
     * includes its sanitation data
     * @var DirtyItem[] $dirty
     */
    public array $dirty = [];

    public function __construct() {}

    public function sanitate(string $name, string $type, $value = null, ?int $maxLength = null, bool $allowNull = true, $defaultValue = null)
    {
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
                    'wysiwyg'   => $this->strTag($treated, ['br', 'span', 'ul', 'li', 'strong', 'i', 'u']),
                    'strQuote'  => $this->strQuote($treated),
                    'boolean'   => $this->boolean($treated),
                    'nullify'   => $this->nullify()
                };
            }
        }

        $this->result[$name] = $treated;
    }

    /**
     * change the value into integer
     * @param mixed $value
     * @return int
     */
    private function numeric($value): int
    {
        return (int)$value;
    }

    /**
     * strip html tags from string
     * @param mixed $value dirty value
     * @param array $allowed whitelisted tags
     * @return string
     */
    private function strTag($value, array $allowed = []): string
    {
        return strip_tags($value, $allowed);
    }

    /**
     * sanitate inserted string for commonly used symbols for injections
     * changes ` into '
     * changes " into '
     * changes \\ into /
     * changes _ into [space]
     * @param mixed $value
     * @return string
     */
    private function strQuote($value): string
    {
        $value = str_replace('`', "'", (string)$value);
        $value = str_replace('"', "'", (string)$value);
        $value = str_replace('\\', "/", (string)$value);
        $value = str_replace('_', " ", (string)$value);
        return $value;
    }

    /**
     * validate for boolean value
     * @param mixed $value raw value
     * @return bool sanitated boolean. return false if not === 1 and not === "true" and not === true
     */
    private function boolean($value): bool
    {
        $result = \is_bool($value) ? $value : $value === "true" || $value === 1;
        return $result;
    }

    /**
     * nullify the value
     * @return null
     */
    private function nullify(): null
    {
        return null;
    }

    /**
     * get the objectified result
     * @return object of the result
     */
    public function toObject()
    {
        return (object)$this->result;
    }

    /**
     * get the value of a result
     * @param string $name of the variable
     * @return mixed the sanitated value of the variable
     */
    public function get(string $name): mixed{
        $res = \array_key_exists($name,$this->result) ? $this->result[$name] : null;
        return $res;
    }
}
