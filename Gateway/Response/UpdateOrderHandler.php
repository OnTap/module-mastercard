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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function handle(array $handlingSubject, array $response)
    {
        $payment = SubjectReader::readPayment($handlingSubject);

        /** @var \Magento\Sales\Model\Order\Payment $paymentO */
        $paymentO = $payment->getPayment();
        PaymentHandler::importPaymentResponse($paymentO, $response);

        $orderO = $payment->getOrder();

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get($orderO->getId());
        $order->addCommentToStatusHistory(__("Order updated by Webhook"));
        $this->orderRepository->save($order);
    }
}
