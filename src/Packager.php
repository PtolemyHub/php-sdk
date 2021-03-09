<?php

namespace Ptolemy;

use Ptolemy\DTO\Package;
use Ptolemy\DTO\Relationship;

class Packager
{
    /**
     * @param Relationship[] $relationships
     * @param int $packageSize Positive integer
     *
     * @return Package[]
     */
    public function package(array $relationships, int $packageSize): array
    {
        if ($packageSize <= 0) {
            throw new \Exception('Package size must be greater than 0');
        }

        $packages = [];
        $relationshipsChunks = array_chunk($relationships, $packageSize);
        foreach ($relationshipsChunks as $relationshipsChunk) {
            $package = new Package();

            /** @var Relationship $relationship */
            foreach ($relationshipsChunk as $relationship) {
                $package->addSerializableRelationship($relationship->toArray());
            }

            $packages[] = $package;
        }

        PtolemySdk::log(sprintf(
            'Packaged %s relationship(s) into %s package(s)',
            count($relationships),
            count($packages)
        ));

        return $packages;
    }
}