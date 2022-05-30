<?php

namespace TicketSwap\Assessment;

final class Marketplace
{
    /**
     * @param array<Listing> $listingsForSale
     */
    public function __construct(private array $listingsForSale = [])
    {
    }

    /**
     * @return array<Listing>
     */
    public function getListingsForSale() : array
    {
        return $this->listingsForSale;
    }

    /**
     * @param Buyer $buyer
     * @param TicketId $ticketId
     * @return Ticket
     * @throws TicketAlreadySoldException
     */
    public function buyTicket(Buyer $buyer, TicketId $ticketId) : Ticket
    {
        foreach($this->listingsForSale as $listing) {
            foreach($listing->getTickets() as $ticket) {
                if ($ticket->getId()->equals($ticketId)) {
                   return $ticket->buyTicket($buyer); 
                }
            }
        }
    }

    /**
     * @throws BarcodeAlreadyExistsException
     */
    public function setListingForSale(Listing $listing) : void
    {
        if (!empty($this->listingsForSale)) {
            $this->verifyBarcodesAgainstOtherListings($listing);
        }

        $this->listingsForSale[(string) $listing->getId()] = $listing;
    }


    /**
     * @param Listing $listing
     * @return void
     * @throws BarcodeAlreadyExistsException
     */
    private function verifyBarcodesAgainstOtherListings(Listing $listing) : void
    {
        $ticketsFromNewListing = $listing->getTickets();

        foreach ($ticketsFromNewListing as $ticket) {
            foreach ($this->listingsForSale as $existingListing) {
                $duplicatedTicket = $this->findDuplicateBarcodeInListing($ticket, $existingListing);

                if (!$duplicatedTicket || $this->isTicketResell($duplicatedTicket, $listing)) continue;

                throw BarcodeAlreadyExistsException::withTicket($ticket);
            }
        }
    }

    /**
     * @param Ticket $ticket
     * @param Listing $listing
     * @return bool|Ticket
     */
    private function findDuplicateBarcodeInListing(Ticket $ticket, Listing $listing) : bool | Ticket
    {
        /** @var Ticket $ticketFromExistingListing */
        foreach ($listing->getTickets() as $ticketFromExistingListing) {
            foreach ($ticketFromExistingListing->getBarcodes() as $barcodeFromExistingListing) {
                foreach ($ticket->getBarcodes() as $barcodeFromNewTicket) {
                    if ((string) $barcodeFromNewTicket !== (string) $barcodeFromExistingListing) continue;

                    return $ticketFromExistingListing;
                }
            }
        }

        return false;
    }

    /**
     * @param Ticket $existingTicket
     * @param Listing $newTicketListing
     * @return bool
     */
    private function isTicketResell(Ticket $existingTicket, Listing $newTicketListing) : bool
    {
        if ($existingTicket->isBought()) {
            return (string) $existingTicket->getBuyer() === (string) $newTicketListing->getSeller();
        }

        return false;
    }
}
