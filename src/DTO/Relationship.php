<?php

namespace Ptolemy\DTO;

class Relationship
{
    private Node $caller;
    private Node $callee;

    public function __construct(Node $caller, Node $callee)
    {
        $this->caller = $caller;
        $this->callee = $callee;
    }

    public function toArray()
    {
        return [
            'caller' => $this->caller->toArray(),
            'callee' => $this->callee->toArray(),
        ];
    }
}
