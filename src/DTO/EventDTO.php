<?php

namespace Ptolemy\DTO;

class EventDTO
{
    private string $date;
    private ?NodeDTO $caller;
    private NodeDTO $callee;

    public function __construct(\DateTime $date, NodeDTO $callee, NodeDTO $caller = null)
    {
        $date->setTimezone(new \DateTimeZone('UTC'));

        $this->date = $date->format('Y-m-d\TH:i:s\Z');
        $this->caller = $caller;
        $this->callee = $callee;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getCaller(): ?NodeDTO
    {
        return $this->caller;
    }

    public function getCallee(): NodeDTO
    {
        return $this->callee;
    }
}
