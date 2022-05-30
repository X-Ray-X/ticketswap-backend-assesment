<?php

namespace TicketSwap\Assessment\Roles;

final class Seller implements \Stringable
{
    /**
     * @param string $name
     */
    public function __construct(private string $name)
    {
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->name;
    }
}
