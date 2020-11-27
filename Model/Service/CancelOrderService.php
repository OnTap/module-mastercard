<?php
/**
 * Copyright (c) 2016-2020 Mastercard
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

declare(strict_types=1);

namespace OnTap\MasterCard\Model\Service;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\InputException;
use OnTap\MasterCard\Model\Ui\Hpf\ConfigProvider;

class CancelOrderService
{

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * CancelOrderService constructor.
     * @param Session $checkoutSession
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Session $checkoutSession,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param string|int $orderId
     * @return OrderInterface
     * @throws InputException
     * @throws LocalizedException
     */
    public function execute($orderId): OrderInterface
    {
        $orderId = (string)$orderId;

        $order = $this->checkoutSession->getLastRealOrder();

        if ((string)$order->getId() !== $orderId && $order->getIncrementId() !== $orderId) {
            throw new LocalizedException(__('You are not permitted to cancel order'));
        }

        $payment = $order->getPayment();

        if ($payment->getMethod() !== ConfigProvider::METHOD_CODE) {
            throw new InputException(__('Payment method is invalid'));
        }

        if (!$order->canCancel()) {
            throw new LocalizedException(__('You are not permitted to cancel order'));
        }
        $order->cancel();
        $this->orderRepository->save($order);

        return $order;
    }
}
