<?php

namespace Ptolemy\DTO;

class NodeDTO
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

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getFunction(): ?string
    {
        return $this->function;
    }
}
