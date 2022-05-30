<?php

namespace TicketSwap\Assessment;

final class TicketId implements \Stringable
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

    /**
     * @param TicketId $otherId
     * @return bool
     */
    public function equals(TicketId $otherId) : bool
    {
        return $this->id === $otherId->id;
    }
}
