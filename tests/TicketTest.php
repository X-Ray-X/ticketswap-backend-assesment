<?php

namespace TicketSwap\Assessment\tests;

use PHPUnit\Framework\TestCase;
use TicketSwap\Assessment\Barcode;
use TicketSwap\Assessment\Ticket;
use TicketSwap\Assessment\TicketId;
use TicketSwap\Assessment\UnexpectedValueException;

class TicketTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_be_possible_to_create_ticket_with_multiple_barcodes()
    {
        $ticket = new Ticket(
            new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
            [
                new Barcode('EAN-13', '38974312923'),
                new Barcode('UPC-A', '72527273070'),
            ],
        );

        $this->assertCount(2, $ticket->getBarcodes());
    }

    /**
     * @test
     */
    public function it_should_not_be_possible_to_create_ticket_with_incorrect_barcode_parameter()
    {
        $this->expectException(UnexpectedValueException::class);

        new Ticket(
            new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
            [
                null
            ],
        );
    }

    /**
     * @test
     */
    public function it_should_not_be_possible_to_create_ticket_without_barcode()
    {
        $this->expectException(UnexpectedValueException::class);

        new Ticket(
            new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
            [],
        );
    }
}