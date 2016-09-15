<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Test\Unit\Gateway\Request\Direct;

use OnTap\MasterCard\Gateway\Request\Direct\CardDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;

class CardDataBuilderTest extends \PHPUnit_Framework_TestCase
{
    const CC_EXP_MONTH = '5';
    const FIXED_CC_EXP_MONTH = '05';

    const CC_EXP_YEAR = '2024';
    const FIXED_CC_EXP_YEAR = '24';

    const CC_NUMBER = '4111111111111111';
    const CC_CID = '123';
    const CC_TYPE = 'CARD';

    /**
     * @var CardDataBuilder
     */
    private $cardDataBuilder;

    /**
     * setUp
     */
    public function setUp()
    {
        $this->cardDataBuilder = new CardDataBuilder();
    }

    /**
     * testBuildSuccess
     */
    public function testBuildSuccess()
    {
        $expected = [
            'sourceOfFunds' => [
                'provided' => [
                    'card' => [
                        'expiry' => [
                            'month' => self::FIXED_CC_EXP_MONTH,
                            'year' => self::FIXED_CC_EXP_YEAR,
                        ],
                        'number' => self::CC_NUMBER,
                        'securityCode' => self::CC_CID,
                    ],
                ],
                'type' => static::CC_TYPE,
            ]
        ];

        $result = $this->cardDataBuilder->build(['payment' => $this->getPaymentMock()]);
        static::assertEquals($expected, $result);
    }

    /**
     * Run test for build method (throw Exception)
     *
     * @expectedException \InvalidArgumentException
     */
    public function testBuildFailure()
    {
        $this->cardDataBuilder->build(['payment' => null]);
    }

    /**
     * @return PaymentDataObjectInterface
     */
    private function getPaymentMock()
    {
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paymentMock->expects($this->exactly(4))
            ->method('getAdditionalInformation')
            ->willReturnMap([
                [CardDataBuilder::CC_EXP_MONTH, static::CC_EXP_MONTH],
                [CardDataBuilder::CC_EXP_YEAR, static::CC_EXP_YEAR],
                [CardDataBuilder::CC_NUMBER, static::CC_NUMBER],
                [CardDataBuilder::CC_CID, static::CC_CID]
            ]);

        $subject = $this->getMockBuilder(PaymentDataObjectInterface::class)
            ->getMock();

        $subject->expects($this->once())
            ->method('getPayment')
            ->willReturn($paymentMock);

        return $subject;
    }
}
