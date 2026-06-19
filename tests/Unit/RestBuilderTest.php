<?php

use Pace\RestBuilder;
use PHPUnit\Framework\TestCase;

class RestBuilderTest extends TestCase
{
    public function testNumericStringForeignKeysAreFormattedAsIntegers()
    {
        $builder = new RestBuilder();

        $builder->filter('@estimateQuantity', '2248405');

        $this->assertEquals('@estimateQuantity = 2248405', $builder->toXPath());
    }

    public function testStringTypedAttributesRemainQuotedWhenNumeric()
    {
        $builder = new RestBuilder();

        $builder->filter('@job', '99999');

        $this->assertEquals('@job = "99999"', $builder->toXPath());
    }
}
