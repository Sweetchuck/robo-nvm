<?php

namespace Sweetchuck\Robo\Nvm\Comparer;

class ArrayComparer
{
    /**
     * @var array
     */
    protected $keys = [];

    public function getKeys(): array
    {
        return $this->keys;
    }

    /**
     * @return $this
     */
    public function setKeys(array $value)
    {
        $this->keys = $value;

        return $this;
    }

    public function __construct(array $keys = [])
    {
        $this->setKeys($keys);
    }

    public function __invoke()
    {
        return $this->compare(...func_get_args());
    }

    public function compare(array $a, array $b): int
    {
        foreach ($this->getKeys() as $key => $defaultValue) {
            $aValue = $a[$key] ?? $defaultValue;
            $bValue = $b[$key] ?? $defaultValue;

            $result = $aValue <=> $bValue;

            if ($result !== 0) {
                return $result;
            }
        }

        return 0;
    }
}
