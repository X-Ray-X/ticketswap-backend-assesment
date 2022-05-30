<?php

namespace TicketSwap\Assessment;

use Money\Money;
use TicketSwap\Assessment\Exceptions\BarcodeAlreadyExistsException;
use TicketSwap\Assessment\Roles\Admin;
use TicketSwap\Assessment\Roles\Seller;

final class Listing
{
    private ?string $verifiedByAdmin = null;

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
     * @return Admin|null
     */
    public function getVerifiedByAdmin(): ?string
    {
        return $this->verifiedByAdmin;
    }

    /**
     * @param Admin|null $verifiedByAdmin
     * @return Listing
     */
    public function setVerifiedByAdmin(?Admin $verifiedByAdmin): self
    {
        $this->verifiedByAdmin = $verifiedByAdmin;

        return $this;
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

            // Convert array of Barcode objects into an array of their string representation
            $barcodes = array_map('strval', $ticket->getBarcodes());

            foreach ($tickets as $subsequentTicket) {
                $subsequentBarcodes = array_map('strval', $subsequentTicket->getBarcodes());

                if (empty(array_intersect($barcodes, $subsequentBarcodes))) continue;

                throw BarcodeAlreadyExistsException::withTicketsInListing($ticket, $subsequentTicket);
            }
        }
    }
}
