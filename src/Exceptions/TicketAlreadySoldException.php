<?php

namespace TicketSwap\Assessment\Exceptions;

use TicketSwap\Assessment\Ticket;

final class TicketAlreadySoldException extends \Exception
{
    public static function withTicket(Ticket $ticket) : self
    {
        return new self(
            sprintf(
                'Ticket (%s) has already been sold',
                $ticket->getId()
            )
        );
    }
}
