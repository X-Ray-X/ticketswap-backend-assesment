<?php

namespace TicketSwap\Assessment;

use TicketSwap\Assessment\Exceptions\TicketAlreadySoldException;
use TicketSwap\Assessment\Exceptions\UnexpectedValueException;
use TicketSwap\Assessment\Roles\Buyer;

final class Ticket
{
    /**
     * @param TicketId $id
     * @param array<Barcode> $barcodes
     * @param Buyer|null $buyer
     * @throws UnexpectedValueException
     */
    public function __construct(
        private TicketId $id,
        private array $barcodes,
        private ?Buyer $buyer = null
    ) {
        if (
            empty($this->barcodes)
            || !empty(array_filter($this->barcodes, function ($item) { return !is_a($item, Barcode::class); }))
        ) {
            throw new UnexpectedValueException(
                'Cannot create new ticket - either no barcodes provided or one of the parameters is not an instance of ' . Barcode::class
            );
        }
    }

    public function getId() : TicketId
    {
        return $this->id;
    }

    /**
     * @return Barcode[]
     */
    public function getBarcodes() : array
    {
        return $this->barcodes;
    }

    public function getBuyer() : Buyer
    {
        return $this->buyer;
    }

    public function isBought() : bool
    {
        return $this->buyer !== null;
    }

    /**
     * @throws TicketAlreadySoldException
     */
    public function buyTicket(Buyer $buyer) : self
    {
        if (!$this->isBought()) {
            $this->buyer = $buyer;

            return $this;
        }

        throw TicketAlreadySoldException::withTicket($this);
    }
}
