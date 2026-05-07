<?php

namespace App\Domain\Draw\Contracts;

interface DrawStrategy
{
    public function draw(array $participants, array $options = []): mixed;
}
