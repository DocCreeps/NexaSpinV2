<?php

namespace App\Application\Draw\DTOs;

use App\Domain\Draw\Enums\DrawType;
use App\Domain\Draw\Enums\DrawDisplay;
use App\Domain\Draw\Collections\Participants;
use App\Domain\Draw\ValueObjects\Participant;

final readonly class DrawData
{
    /**
     * @param array<int,string> $participants
     * @param array<string,mixed> $options
     */
    public function __construct(
        public array $participants,
        public DrawType $type,
        public DrawDisplay $display,
        public array $options = [],
    ) {}


    public function participantsCollection(): Participants
    {
        return new Participants(
            array_map(
                fn(string $name) => new Participant($name),
                $this->participants
            )
        );
    }
}
