<?php

use Pace\Type;
use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
    public function testCamelize()
    {
        $this->assertEquals('csr', Type::camelize('CSR'));
        $this->assertEquals('jobPart', Type::camelize('JobPart'));
    }

    public function testModelify()
    {
        $this->assertEquals('GLBatch', Type::modelify('glBatch'));
        $this->assertEquals('SalesPerson', Type::modelify('salesPerson'));
    }

    public function testSingularize()
    {
        $this->assertEquals('jobStatus', Type::singularize('jobStatus'));
        $this->assertEquals('jobStatus', Type::singularize('jobStatuses'));
    }

    public function testKeyName()
    {
        $this->assertEquals('attachment', Type::keyName('FileAttachment'));
        $this->assertNull(Type::keyName('Estimate'));
    }

    public function testResolveKeyValue()
    {
        $this->assertEquals(1, Type::resolveKeyValue('CSR', ['primaryKey' => 1]));
        $this->assertEquals(2248386, Type::resolveKeyValue('EstimatePaper', ['id' => 2248386]));
        $this->assertEquals(99, Type::resolveKeyValue('FileAttachment', ['attachment' => 99]));
        $this->assertNull(Type::resolveKeyValue('EstimatePaper', ['primaryKey' => null, 'id' => null]));
    }
}
