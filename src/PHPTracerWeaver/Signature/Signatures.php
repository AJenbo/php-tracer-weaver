<?php namespace PHPTracerWeaver\Signature;

use PHPTracerWeaver\Reflector\ClassCollatorInterface;

class Signatures
{
    /** @var FunctionSignature[] */
    protected $signaturesArray = [];
    /** @var ClassCollatorInterface */
    protected $collator;

    /**
     * @param ClassCollatorInterface $collator
     */
    public function __construct(ClassCollatorInterface $collator)
    {
        $this->collator = $collator;
    }

    /**
     * @param string $func
     * @param string $class
     *
     * @return bool
     */
    public function has(string $func, string $class = ''): bool
    {
        $name = strtolower($class ? ($class . '->' . $func) : $func);

        return isset($this->signatures_array[$name]);
    }

    /**
     * @param string $func
     * @param string $class
     *
     * @return FunctionSignature
     */
    public function get(string $func, string $class = ''): FunctionSignature
    {
        if (!$func) {
            throw new Exception('Illegal identifier: {' . "$func, $class" . '}');
        }
        $name = strtolower($class ? ($class . '->' . $func) : $func);
        if (!isset($this->signatures_array[$name])) {
            $this->signatures_array[$name] = new FunctionSignature($this->collator);
        }

        return $this->signatures_array[$name];
    }

    /**
     * @return array[]
     */
    public function export(): array
    {
        $out = [];
        foreach ($this->signatures_array as $name => $functionSignature) {
            $out[$name] = $functionSignature->export();
        }

        return $out;
    }
}
