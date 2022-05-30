<?php

namespace TicketSwap\Assessment;

final class Barcode implements \Stringable
{
    /**
     * @param string $type
     * @param string $value
     */
    public function __construct(private string $type, private string $value)
    {
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return sprintf('%s:%s', $this->type, $this->value);
    }
}
