<?php

namespace TicketSwap\Assessment;

class BarcodeAlreadyExistsException extends \Exception
{
    public static function withTicket(Ticket $ticket) : self
    {
        return new self(
            sprintf(
                'Ticket %s contains a barcode that is already for sale in another listing.',
                $ticket->getId(),
            )
        );
    }

    public static function withTicketsInListing(Ticket $ticket, Ticket $ticketWithDuplicateBarcode) : self
    {
        return new self(
            sprintf(
                'Tickets with IDs: %s, %s contain a duplicated barcode: %s.',
                $ticket->getId(),
                $ticketWithDuplicateBarcode->getId(),
                $ticketWithDuplicateBarcode->getBarcode(),
            )
        );
    }
}