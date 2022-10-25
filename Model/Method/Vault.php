<?php
/**
 * Copyright (c) 2016-2022 Mastercard
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

namespace OnTap\MasterCard\Model\Method;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Gateway\Command\CommandManagerPoolInterface;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\ConfigFactoryInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Vault\Model\Method\Vault as VaultPaymentMethod;
use OnTap\MasterCard\Api\VaultPaymentInterface;
use OnTap\MasterCard\Api\VerifyPaymentFlagInterface;

class Vault extends VaultPaymentMethod
{
    /**
     * @var CommandManagerPoolInterface
     */
    private $commandManagerPool;

    /**
     * @var MethodInterface
     */
    private $vaultProvider;

    /**
     * @var VerifyPaymentFlagInterface
     */
    private $verifyPaymentFlag;

    /**
     * @var PaymentTokenManagementInterface
     */
    private $tokenManagement;

    /**
     * @var OrderPaymentExtensionInterfaceFactory
     */
    private $paymentExtensionFactory;

    /**
     * @var Json|null
     */
    private $jsonSerializer;

    /**
     * @var SerializerInterface
     */
      private $serializer;

    /**
     * @param ConfigInterface $config
     * @param ConfigFactoryInterface $configFactory
     * @param ObjectManagerInterface $objectManager
     * @param MethodInterface $vaultProvider
     * @param ManagerInterface $eventManager
     * @param ValueHandlerPoolInterface $valueHandlerPool
     * @param CommandManagerPoolInterface $commandManagerPool
     * @param PaymentTokenManagementInterface $tokenManagement
     * @param OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
     * @param VerifyPaymentFlagInterface $verifyPaymentFlag
     * @param Json $jsonSerializer
     * @param string $code
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ConfigInterface $config,
        ConfigFactoryInterface $configFactory,
        ObjectManagerInterface $objectManager,
        MethodInterface $vaultProvider,
        ManagerInterface $eventManager,
        ValueHandlerPoolInterface $valueHandlerPool,
        CommandManagerPoolInterface $commandManagerPool,
        PaymentTokenManagementInterface $tokenManagement,
        OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
        VerifyPaymentFlagInterface $verifyPaymentFlag,
        Json $jsonSerializer,
        string $code,
        SerializerInterface $serializer
    ) {
        parent::__construct(
            $config,
            $configFactory,
            $objectManager,
            $vaultProvider,
            $eventManager,
            $valueHandlerPool,
            $commandManagerPool,
            $tokenManagement,
            $paymentExtensionFactory,
            $code,
            $jsonSerializer,
            $jsonSerializer = $jsonSerializer ?: $this->serializer = $serializer
        );

        $this->commandManagerPool = $commandManagerPool;
        $this->vaultProvider = $vaultProvider;
        $this->verifyPaymentFlag = $verifyPaymentFlag;
        $this->tokenManagement = $tokenManagement;
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->jsonSerializer = $jsonSerializer;
    }

    public function canOrder()
    {
        return true;
    }

    public function order(InfoInterface $payment, $amount)
    {
        if (!$payment instanceof OrderPaymentInterface) {
            throw new \DomainException('Not implemented');
        }

        if (!$this->verifyPaymentFlag->isVerifyPayment($payment->getMethod())) {
            return parent::order($payment, $amount);
        }

        /** @var $payment OrderPaymentInterface */
        $this->attachTokenExtensionAttribute($payment);
        $this->attachCreditCardInfo($payment);

        $commandExecutor = $this->commandManagerPool->get(
            $this->vaultProvider->getCode()
        );

        $commandExecutor->executeByCode(
            VaultPaymentInterface::VAULT_ORDER,
            $payment,
            ['amount' => $amount]
        );

        $payment->setMethod($this->vaultProvider->getCode());

        return $this;
    }

    /**
     * Attaches token extension attribute.
     *
     * @param OrderPaymentInterface $orderPayment
     * @return void
     */
    private function attachTokenExtensionAttribute(OrderPaymentInterface $orderPayment)
    {
        $additionalInformation = $orderPayment->getAdditionalInformation();
        if (empty($additionalInformation[PaymentTokenInterface::PUBLIC_HASH])) {
            throw new \LogicException('Public hash should be defined');
        }

        $publicHash = $additionalInformation[PaymentTokenInterface::PUBLIC_HASH];
        $customerId = (int)($additionalInformation[PaymentTokenInterface::CUSTOMER_ID] ?? 0);

        $paymentToken = $this->tokenManagement->getByPublicHash($publicHash, $customerId);
        if ($paymentToken === null) {
            throw new \LogicException("No token found");
        }

        $extensionAttributes = $this->getPaymentExtensionAttributes($orderPayment);
        $extensionAttributes->setVaultPaymentToken($paymentToken);
    }

    /**
     * Returns Payment's extension attributes.
     *
     * @param OrderPaymentInterface $payment
     * @return OrderPaymentExtensionInterface
     */
    private function getPaymentExtensionAttributes(OrderPaymentInterface $payment)
    {
        $extensionAttributes = $payment->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->paymentExtensionFactory->create();
            $payment->setExtensionAttributes($extensionAttributes);
        }

        return $extensionAttributes;
    }

    /**
     * Attaches credit card info.
     *
     * @param OrderPaymentInterface $payment
     * @return void
     */
    private function attachCreditCardInfo(OrderPaymentInterface $payment): void
    {
        $paymentToken = $payment->getExtensionAttributes()->getVaultPaymentToken();
        if ($paymentToken === null) {
            return;
        }

        $tokenDetails = $paymentToken->getTokenDetails();
        if (empty($tokenDetails)) {
            return;
        }

        if (is_string($tokenDetails)) {
            $tokenDetails = $this->jsonSerializer->unserialize($tokenDetails);
        }

        if (is_array($tokenDetails)) {
            /** @phpstan-ignore-next-line */
            $payment->addData($tokenDetails);
        }
    }
}
