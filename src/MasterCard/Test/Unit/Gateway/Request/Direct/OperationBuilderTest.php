<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Test\Unit\Gateway\Request\Direct;

use OnTap\MasterCard\Gateway\Request\OperationBuilder;

class OperationBuilderTest extends \PHPUnit_Framework_TestCase
{
    const OPERATION = 'OPERATION';

    /**
     * @var OperationBuilder
     */
    private $operationBuilder;

    /**
     * setUp
     */
    public function setUp()
    {
        $this->operationBuilder = new OperationBuilder(static::OPERATION);
    }

    /**
     * testBuildSuccess
     */
    public function testBuildSuccess()
    {
        $expected = [
            'apiOperation' => static::OPERATION
        ];

        $result = $this->operationBuilder->build([]);
        static::assertEquals($expected, $result);
    }
}
