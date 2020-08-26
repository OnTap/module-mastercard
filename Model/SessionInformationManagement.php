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

namespace OnTap\MasterCard\Model;

use OnTap\MasterCard\Api\SessionInformationManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Api\BillingAddressManagementInterface;

class SessionInformationManagement implements SessionInformationManagementInterface
{
    const CREATE_HOSTED_SESSION = 'create_session';

    /**
     * @var CommandPoolInterface
     */
    protected $commandPool;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var PaymentDataObjectFactory
     */
    protected $paymentDataObjectFactory;

    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var BillingAddressManagementInterface
     */
    protected $billingAddressManagement;

    /**
     * SessionInformationManagement constructor.
     * @param CommandPoolInterface $commandPool
     * @param CartRepositoryInterface $quoteRepository
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param BillingAddressManagementInterface $billingAddressManagement
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        CartRepositoryInterface $quoteRepository,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        BillingAddressManagementInterface $billingAddressManagement
    ) {
        $this->commandPool = $commandPool;
        $this->quoteRepository = $quoteRepository;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->billingAddressManagement = $billingAddressManagement;
    }

    /**
     * @inheritDoc
     */
    public function createNewPaymentSession(
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        $cartId = (int) $cartId;

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        $quote->getPayment()->setQuote($quote);
        $quote->getPayment()->importData(
            $paymentMethod->getData()
        );

        $this->commandPool
            ->get(static::CREATE_HOSTED_SESSION)
            ->execute([
                'payment' => $this->paymentDataObjectFactory->create($quote->getPayment())
            ]);

        $this->quoteRepository->save($quote);
        $session = $quote->getPayment()->getAdditionalInformation('session');

        return [
            'id' => (string) $session['id'],
            'version' => (string) $session['version']
        ];
    }

    /**
     * @inheritDoc
     */
    public function createNewGuestPaymentSession(
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        $cartId = (int) $cartId;

        $quoteIdMask = $this->quoteIdMaskFactory
            ->create()
            ->load($cartId, 'masked_id');

        $billingAddress->setEmail($email);
        return $this->createNewPaymentSession($quoteIdMask->getQuoteId(), $paymentMethod, $billingAddress);
    }
}
