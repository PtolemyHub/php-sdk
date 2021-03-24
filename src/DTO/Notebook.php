<?php

namespace Ptolemy\DTO;

class Notebook
{
    /** @var array */
    private $relationships = [];

    public function addRelationship(Relationship $relationship): void
    {
        $this->relationships[] = $relationship;
    }

    public function getRelationships(): array
    {
        return $this->relationships;
    }
}
