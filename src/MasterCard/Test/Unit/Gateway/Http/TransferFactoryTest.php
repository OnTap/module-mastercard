<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Test\Unit\Gateway\Http;

use OnTap\MasterCard\Gateway\Http\TransferFactory;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;

class TransferFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TransferBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transferBuilder;

    /**
     * @var TransferFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transferMock;

    /**
     * @var TransferFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transferFactory;

    /**
     * @var PaymentDataObjectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $payment;

    /**
     * @var OrderAdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderAdapter;

    /**
     * @var ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * Setup
     */
    public function setUp()
    {
        $this->config = $this->getMockBuilder(ConfigInterface::class)
            ->setMethods(['getMerchantId', 'getMerchantPassword', 'getApiUrl'])
            ->getMockForAbstractClass();

        $this->payment = $this->getMock(PaymentDataObjectInterface::class);

        $this->orderAdapter = $this->getMockBuilder(OrderAdapterInterface::class)
            ->setMethods(['getOrderIncrementId'])
            ->getMockForAbstractClass();

        $this->transferBuilder = $this->getMock(TransferBuilder::class);

        $this->transferMock = $this->getMock(TransferInterface::class);

        $this->transferFactory = new TransferFactory(
            $this->config,
            $this->transferBuilder
        );
    }

    /**
     * Test
     */
    public function testCreate()
    {
        $request = ['data1', 'data2'];

        $this->payment->expects($this->atLeastOnce())
            ->method('getOrder')
            ->willReturn($this->orderAdapter);

        $this->transferBuilder->expects($this->once())
            ->method('setBody')
            ->with($request)
            ->willReturnSelf();

        $this->transferBuilder->expects($this->once())
            ->method('setMethod')
            ->with('PUT')
            ->willReturnSelf();

        $this->transferBuilder->expects($this->once())
            ->method('setHeaders')
            ->with(['Content-Type' => 'application/json;charset=UTF-8'])
            ->willReturnSelf();

        $this->transferBuilder->expects($this->once())
            ->method('setAuthUsername')
            ->willReturnSelf();

        $this->transferBuilder->expects($this->once())
            ->method('setAuthPassword')
            ->willReturnSelf();

        $this->transferBuilder->expects($this->once())
            ->method('setUri')
            ->willReturnSelf();

        $this->transferBuilder->expects($this->once())
            ->method('build')
            ->willReturn($this->transferMock);

        $this->assertEquals($this->transferMock, $this->transferFactory->create($request, $this->payment));
    }
}
