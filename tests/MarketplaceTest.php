<?php

namespace TicketSwap\Assessment\tests;

use PHPUnit\Framework\TestCase;
use Money\Currency;
use Money\Money;
use TicketSwap\Assessment\Barcode;
use TicketSwap\Assessment\BarcodeAlreadyExistsException;
use TicketSwap\Assessment\Buyer;
use TicketSwap\Assessment\Listing;
use TicketSwap\Assessment\ListingId;
use TicketSwap\Assessment\Marketplace;
use TicketSwap\Assessment\Seller;
use TicketSwap\Assessment\Ticket;
use TicketSwap\Assessment\TicketAlreadySoldException;
use TicketSwap\Assessment\TicketId;

class MarketplaceTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_list_all_the_tickets_for_sale()
    {
        $marketplace = new Marketplace(
            listingsForSale: [
                new Listing(
                    id: new ListingId('D59FDCCC-7713-45EE-A050-8A553A0F1169'),
                    seller: new Seller('Pascal'),
                    tickets: [
                        new Ticket(
                            new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
                            [
                                new Barcode('EAN-13', '38974312923'),
                            ],
                        ),
                    ],
                    price: new Money(4950, new Currency('EUR')),
                ),
            ]
        );

        $listingsForSale = $marketplace->getListingsForSale();

        $this->assertCount(1, $listingsForSale);
    }

    /**
     * @test
     */
    public function it_should_be_possible_to_buy_a_ticket()
    {
        $marketplace = new Marketplace(
            listingsForSale: [
                new Listing(
                    id: new ListingId('D59FDCCC-7713-45EE-A050-8A553A0F1169'),
                    seller: new Seller('Pascal'),
                    tickets: [
                        new Ticket(
                            new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
                            [
                                new Barcode('EAN-13', '38974312923'),
                            ],
                        ),
                    ],
                    price: new Money(4950, new Currency('EUR')),
                ),
            ]
        );

        $boughtTicket = $marketplace->buyTicket(
            buyer: new Buyer('Sarah'),
            ticketId: new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B')
        );

        $this->assertNotNull($boughtTicket);
        $this->assertSame('EAN-13:38974312923', (string) $boughtTicket->getBarcodes()[0]);
    }

    /**
     * @test
     */
    public function it_should_not_be_possible_to_buy_the_same_ticket_twice()
    {
        $marketplace = new Marketplace(
            listingsForSale: [
                new Listing(
                    id: new ListingId('D59FDCCC-7713-45EE-A050-8A553A0F1169'),
                    seller: new Seller('Pascal'),
                    tickets: [
                        new Ticket(
                            new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
                            [
                                new Barcode('EAN-13', '38974312923'),
                            ],
                        ),
                    ],
                    price: new Money(4950, new Currency('EUR')),
                ),
            ]
        );

        $this->expectException(TicketAlreadySoldException::class);

        $marketplace->buyTicket(
            buyer: new Buyer('Sarah'),
            ticketId: new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B')
        );

        // Second attempt at buying the same ticket, which should trigger the exception class.
        $marketplace->buyTicket(
            buyer: new Buyer('John'),
            ticketId: new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B')
        );
    }

    /**
     * @test
     */
    public function it_should_be_possible_to_put_a_listing_for_sale()
    {
        $marketplace = new Marketplace(
            listingsForSale: [
                new Listing(
                    id: new ListingId('D59FDCCC-7713-45EE-A050-8A553A0F1169'),
                    seller: new Seller('Pascal'),
                    tickets: [
                        new Ticket(
                            new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
                            [
                                new Barcode('EAN-13', '38974312923'),
                            ],
                        ),
                    ],
                    price: new Money(4950, new Currency('EUR')),
                ),
            ]
        );

        $marketplace->setListingForSale(
            new Listing(
                id: new ListingId('26A7E5C4-3F59-4B3C-B5EB-6F2718BC31AD'),
                seller: new Seller('Tom'),
                tickets: [
                    new Ticket(
                        new TicketId('45B96761-E533-4925-859F-3CA62182848E'),
                        [
                            new Barcode('EAN-13', '893759834'),
                        ],
                    ),
                ],
                price: new Money(4950, new Currency('EUR')),
            )
        );

        $listingsForSale = $marketplace->getListingsForSale();

        $this->assertCount(2, $listingsForSale);
    }

    /**
     * @test
     */
    public function it_should_not_be_possible_to_sell_a_ticket_with_a_barcode_that_is_already_for_sale()
    {
        $marketplace = new Marketplace(
            listingsForSale: [
                new Listing(
                    id: new ListingId('D59FDCCC-7713-45EE-A050-8A553A0F1169'),
                    seller: new Seller('Pascal'),
                    tickets: [
                        new Ticket(
                            new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
                            [
                                new Barcode('EAN-13', '38974312923'),
                            ],
                        ),
                    ],
                    price: new Money(4950, new Currency('EUR')),
                ),
                new Listing(
                    id: new ListingId('26A7E5C4-3F59-4B3C-B5EB-6F2718BC31AD'),
                    seller: new Seller('Tom'),
                    tickets: [
                        new Ticket(
                            new TicketId('45B96761-E533-4925-859F-3CA62182848E'),
                            [
                                new Barcode('EAN-13', '893759834'),
                            ],
                        ),
                    ],
                    price: new Money(4950, new Currency('EUR')),
                ),
            ]
        );

        $this->expectException(BarcodeAlreadyExistsException::class);

        $marketplace->setListingForSale(
            new Listing(
                id: new ListingId('8CC6C370-DD3E-11EC-BA7D-0800200C9A66'),
                seller: new Seller('Alice'),
                tickets: [
                    new Ticket(
                        new TicketId('45B96761-E533-4925-859F-3CA62182848E'),
                        [
                            new Barcode('EAN-13', '893759834'),
                        ],
                    ),
                ],
                price: new Money(4950, new Currency('EUR')),
            )
        );
    }

    /**
     * @test
     */
    public function it_should_be_possible_for_a_buyer_of_a_ticket_to_sell_it_again()
    {
        $marketplace = new Marketplace(
            listingsForSale: [
                new Listing(
                    id: new ListingId('D59FDCCC-7713-45EE-A050-8A553A0F1169'),
                    seller: new Seller('Pascal'),
                    tickets: [
                        new Ticket(
                            new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
                            [
                                new Barcode('EAN-13', '38974312923')
                            ],
                        ),
                    ],
                    price: new Money(4950, new Currency('EUR')),
                ),
            ]
        );

        $marketplace->buyTicket(
            buyer: new Buyer('Tom'),
            ticketId: new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B')
        );

        $marketplace->setListingForSale(
            new Listing(
                id: new ListingId('26A7E5C4-3F59-4B3C-B5EB-6F2718BC31AD'),
                seller: new Seller('Tom'),
                tickets: [
                    new Ticket(
                        new TicketId('6293BB44-2F5F-4E2A-ACA8-8CDF01AF401B'),
                        [
                            new Barcode('EAN-13', '38974312923'),
                        ],
                    ),
                ],
                price: new Money(4950, new Currency('EUR')),
            )
        );

        $listingsForSale = $marketplace->getListingsForSale();

        $this->assertCount(2, $listingsForSale);
    }
}
