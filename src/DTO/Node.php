<?php

namespace Ptolemy\DTO;

class Node
{
    private ?string $class;
    private ?string $type;
    private ?string $function;

    public function __construct(string $class = null, string $type = null, string $function = null)
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
