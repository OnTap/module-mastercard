<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Test\Unit\Gateway\Request\Direct;

use OnTap\Tns\Gateway\Request\OperationBuilder;

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
