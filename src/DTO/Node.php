<?php

namespace Ptolemy\DTO;

class Node
{
    /** @var string|null */
    private $class;

    /** @var string|null */
    private $type;

    /** @var string */
    private $function;

    public function __construct(string $function, string $class = null, string $type = null)
    {
        $this->class = $class;
        $this->type = $type;
        $this->function = $function;
    }

    public function toArray(): array
    {
        return [
            'class' => $this->class,
            'type' => $this->type,
            'function' => $this->function,
        ];
    }
}
