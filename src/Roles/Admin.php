<?php

namespace TicketSwap\Assessment\Roles;

class Admin implements \Stringable
{
    public function __construct(private string $name)
    {
    }

    public function __toString() : string
    {
        return $this->name;
    }
}