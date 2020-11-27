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

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\InputException;
use Magento\Sales\Model\Reorder\Reorder;

class ReorderService
{

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var Reorder
     */
    private $reorder;

    /**
     * CancelOrderService constructor.
     * @param Session $checkoutSession
     * @param Reorder $reorder
     */
    public function __construct(
        Session $checkoutSession,
        Reorder $reorder
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->reorder = $reorder;
    }

    /**
     * @param OrderInterface $order
     * @return CartInterface
     * @throws InputException
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function execute(OrderInterface $order): CartInterface
    {
        $reorderOutput = $this->reorder->execute($order->getIncrementId(), (string)$order->getStoreId());

        if (!empty($reorderOutput->getErrors())) {
            throw new LocalizedException(__($reorderOutput->getErrors()[0]));
        }

        $this->checkoutSession->setQuoteId($reorderOutput->getCart()->getId());

        return $reorderOutput->getCart();
    }
}
