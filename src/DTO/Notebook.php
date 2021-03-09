<?php

namespace Ptolemy\DTO;

class Notebook
{
    private array $relationships = [];

    public function addRelationship(Relationship $relationship): void
    {
        $this->relationships[] = $relationship;
    }

    public function getRelationships(): array
    {
        return $this->relationships;
    }
}
