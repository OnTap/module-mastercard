<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\OrderRepositoryInterface;

class UpdateOrderHandler implements HandlerInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * UpdateOrderHandler constructor.
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $payment = SubjectReader::readPayment($handlingSubject);

        /* @var \Magento\Sales\Model\Order\Payment $paymentO */
        $paymentO = $payment->getPayment();
        PaymentHandler::importPaymentResponse($paymentO, $response);

        $orderO = $payment->getOrder();

        /* @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get($orderO->getId());
        $order->addStatusHistoryComment(__("Order updated by Webhook"));
        $order->save();
    }
}
