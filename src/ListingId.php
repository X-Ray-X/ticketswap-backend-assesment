<?php

namespace TicketSwap\Assessment;

final class ListingId implements \Stringable
{
    /**
     * @param string $id
     */
    public function __construct(private string $id)
    {
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->id;
    }
}
