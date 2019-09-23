<?php
/**
 * Copyright (c) 2016-2019 Mastercard
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OnTap\MasterCard\Test\Unit\Gateway\Request\Direct;

use OnTap\MasterCard\Gateway\Request\VoidDataBuilder;
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
