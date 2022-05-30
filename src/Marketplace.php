<?php

namespace TicketSwap\Assessment;

use TicketSwap\Assessment\Exceptions\BarcodeAlreadyExistsException;
use TicketSwap\Assessment\Exceptions\TicketAlreadySoldException;
use TicketSwap\Assessment\Exceptions\TicketNotFoundException;
use TicketSwap\Assessment\Roles\Admin;
use TicketSwap\Assessment\Roles\Buyer;

final class Marketplace
{
    /**
     * @param array<Listing> $listingsUnverified
     * @param array<Listing> $listingsForSale
     * @param array<Listing> $listingsSoldOut
     */
    public function __construct(
        private array $listingsUnverified = [],
        private array $listingsForSale = [],
        private array $listingsSoldOut = []
    ) {
    }

    /**
     * @return Listing[]
     */
    public function getListingsUnverified(): array
    {
        return $this->listingsUnverified;
    }

    /**
     * @return Listing[]
     */
    public function getListingsForSale() : array
    {
        return $this->listingsForSale;
    }

    /**
     * @return Listing[]
     */
    public function getListingsSoldOut() : array
    {
        return $this->listingsSoldOut;
    }

    /**
     * @param Buyer $buyer
     * @param TicketId $ticketId
     * @return Ticket
     * @throws TicketAlreadySoldException
     * @throws TicketNotFoundException
     */
    public function buyTicket(Buyer $buyer, TicketId $ticketId) : Ticket
    {
        foreach($this->listingsForSale as $listing) {
            foreach($listing->getTickets() as $ticket) {
                if ($ticket->getId()->equals($ticketId) && $listing->getVerifiedByAdmin()) {
                   $boughtTicket = $ticket->buyTicket($buyer);

                   $this->refreshListingsForSale();

                   return $boughtTicket;
                }
            }
        }

        throw new TicketNotFoundException('Ticket with ID ' . $ticketId . ' does not exist.');
    }

    /**
     * @throws BarcodeAlreadyExistsException
     */
    public function setListingForSale(Listing $listing) : void
    {
        $this->verifyBarcodesAgainstOtherListings($listing);

        $this->listingsUnverified[(string) $listing->getId()] = $listing;
    }

    /**
     * @param Listing $listing
     * @param Admin $admin
     * @param bool $decisionVerified
     * @return Listing
     */
    public function verifyListingByAdmin(Listing $listing, Admin $admin, bool $decisionVerified = true) : Listing
    {
        $currentVerificationState = $listing->getVerifiedByAdmin();
        if (!$currentVerificationState && $decisionVerified) {
            $this->listingsForSale[(string) $listing->getId()] = $listing;
            unset($this->listingsUnverified[(string) $listing->getId()]);
        }

        if ($currentVerificationState && !$decisionVerified) {
            $this->listingsUnverified[(string) $listing->getId()] = $listing;
            unset($this->listingsForSale[(string) $listing->getId()]);
        }

        return $listing->setVerifiedByAdmin($decisionVerified ? $admin : null);
    }

    /**
     * @return void
     */
    public function refreshListingsForSale() : void
    {
        foreach ($this->listingsForSale as $key => $listing) {
            if (0 === count($listing->getTickets(true))) {
                $this->listingsSoldOut[$key] = $listing;
                unset($this->listingsForSale[$key]);
            }
        }
    }

    /**
     * @param Listing $listing
     * @return void
     * @throws BarcodeAlreadyExistsException
     */
    private function verifyBarcodesAgainstOtherListings(Listing $listing) : void
    {
        // Make sure to verify with both currently active and archived listings
        $existingListings = array_merge($this->listingsForSale, $this->listingsSoldOut);

        if (empty($existingListings)) return;

        $ticketsFromNewListing = $listing->getTickets();

        foreach ($ticketsFromNewListing as $ticket) {
            foreach ($existingListings as $existingListing) {
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
