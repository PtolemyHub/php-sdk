<?php

namespace Ptolemy\DTO;

class Relationship
{
    /** @var Node */
    private $callee;

    /** @var Node */
    private $caller;

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
