<?php

namespace TicketSwap\Assessment\Exceptions;

use TicketSwap\Assessment\Ticket;

class BarcodeAlreadyExistsException extends \Exception
{
    /**
     * @param Ticket $ticket
     * @return static
     */
    public static function withTicket(Ticket $ticket) : self
    {
        return new self(
            sprintf(
                'Ticket %s contains a barcode that is already for sale in another listing.',
                $ticket->getId(),
            )
        );
    }

    /**
     * @param Ticket $ticket
     * @param Ticket $ticketWithDuplicateBarcode
     * @return static
     */
    public static function withTicketsInListing(Ticket $ticket, Ticket $ticketWithDuplicateBarcode) : self
    {
        return new self(
            sprintf(
                'Tickets with IDs: %s, %s contain a duplicated barcode.',
                $ticket->getId(),
                $ticketWithDuplicateBarcode->getId(),
            )
        );
    }
}