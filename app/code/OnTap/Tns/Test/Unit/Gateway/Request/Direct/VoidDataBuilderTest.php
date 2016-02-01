<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Test\Unit\Gateway\Request\Direct;

use OnTap\Tns\Gateway\Request\VoidDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;

class VoidDataBuilderTest extends \PHPUnit_Framework_TestCase
{
    const TXN_ID = '123456789';

    /**
     * @var VoidDataBuilder
     */
    private $voidDataBuilder;

    /**
     * Setup
     */
    public function setUp()
    {
        $this->voidDataBuilder = new VoidDataBuilder();
    }

    /**
     * Test if data is set correctly
     */
    public function testBuildSuccess()
    {
        $expected = [
            'transaction' => [
                'targetTransactionId' => static::TXN_ID
            ]
        ];

        $result = $this->voidDataBuilder->build(['payment' => $this->getPaymentMock()]);
        static::assertEquals($expected, $result);
    }

    /**
     * Run test for build method (throw Exception)
     *
     * @expectedException \InvalidArgumentException
     */
    public function testBuildException()
    {
        $this->voidDataBuilder->build(['payment' => null]);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PaymentDataObjectInterface
     */
    private function getPaymentMock()
    {
        $info = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $info->expects($this->once())
            ->method('getParentTransactionId')
            ->willReturn(static::TXN_ID);

        $subject = $this->getMockBuilder(PaymentDataObjectInterface::class)
            ->getMockForAbstractClass();

        $subject->expects($this->once())
            ->method('getPayment')
            ->willReturn($info);

        return $subject;
    }
}
