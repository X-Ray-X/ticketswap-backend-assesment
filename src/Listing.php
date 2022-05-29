<?php

namespace TicketSwap\Assessment;

use Money\Money;

final class Listing
{
    /**
     * @param array<Ticket> $tickets
     * @throws BarcodeAlreadyExistsException
     */
    public function __construct(
        private ListingId $id,
        private Seller $seller,
        private array $tickets,
        private Money $price
    ) {
        $this->verifyIfBarcodesAreUnique($tickets);
    }

    public function getId() : ListingId
    {
        return $this->id;
    }

    public function getSeller() : Seller
    {
        return $this->seller;
    }

    public function getPrice() : Money
    {
        return $this->price;
    }

    /**
     * @return array<Ticket>
     */
    public function getTickets(?bool $forSale = null) : array
    {
        if (true === $forSale) {
            $forSaleTickets = [];
            foreach ($this->tickets as $ticket) {
                if (!$ticket->isBought()) {
                    $forSaleTickets[] = $ticket;
                }
            }

            return $forSaleTickets;
        } else if (false === $forSale) {
            $notForSaleTickets = [];
            foreach ($this->tickets as $ticket) {
                if ($ticket->isBought()) {
                    $notForSaleTickets[] = $ticket;
                }
            }

            return $notForSaleTickets;
        } else {
            return $this->tickets;
        }
    }

    /**
     * @param array<Ticket> $tickets
     * @throws BarcodeAlreadyExistsException
     */
    private function verifyIfBarcodesAreUnique(array $tickets) : void
    {
        /** @var Ticket $ticket */
        foreach ($tickets as $ticket) {
            array_shift($tickets);
            foreach ($tickets as $subsequentTicket) {
                if ((string) $ticket->getBarcode() !== (string) $subsequentTicket->getBarcode())  continue;

                throw BarcodeAlreadyExistsException::withTicketsInListing($ticket, $subsequentTicket);
            }
        }
    }
}
