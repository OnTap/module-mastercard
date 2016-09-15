<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Test\Unit\Gateway\Request\Direct;

use OnTap\MasterCard\Gateway\Request\TransactionDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;

class TransactionDataBuilderTest extends \PHPUnit_Framework_TestCase
{
    const AMOUNT = 199.566;
    const AMOUNT_FORMATTED = 199.57;
    const CURRENCY = 'GBP';

    /**
     * @var TransactionDataBuilder
     */
    private $transactionBuilder;

    /**
     * setUp
     */
    public function setUp()
    {
        $this->transactionBuilder = new TransactionDataBuilder();
    }

    /**
     * testBuildSuccess
     */
    public function testBuildSuccess()
    {
        $expected = [
            'transaction' => [
                'amount' => static::AMOUNT_FORMATTED,
                'currency' => static::CURRENCY
            ]
        ];

        $result = $this->transactionBuilder->build([
            'payment' => $this->getPaymentMock(),
            'amount' => static::AMOUNT
        ]);

        static::assertEquals($expected, $result);
    }

    /**
     * Run test for build method (throw Exception)
     *
     * @expectedException \InvalidArgumentException
     */
    public function testBuildFailure()
    {
        $this->transactionBuilder->build([
            'payment' => null,
            'amount' => null
        ]);
    }

    /**
     * @return PaymentDataObjectInterface
     */
    private function getPaymentMock()
    {
        $orderMock = $this->getMockBuilder(OrderAdapterInterface::class)
            ->getMock();

        $orderMock->expects($this->once())
            ->method('getCurrencyCode')
            ->willReturn(static::CURRENCY);

        $subject = $this->getMockBuilder(PaymentDataObjectInterface::class)
            ->getMock();

        $subject->expects($this->once())
            ->method('getOrder')
            ->willReturn($orderMock);

        return $subject;
    }
}
