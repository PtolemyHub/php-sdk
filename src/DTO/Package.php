<?php

namespace Ptolemy\DTO;

class Package
{
    /** @var array */
    private $serializableRelationships = [];

    public function addSerializableRelationship(array $serializableRelationship): void
    {
        $this->serializableRelationships[] = $serializableRelationship;
    }

    public function getSerializableRelationships(): array
    {
        return $this->serializableRelationships;
    }
}
