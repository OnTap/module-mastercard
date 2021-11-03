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

use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use InvalidArgumentException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\VaultPaymentInterface;
use OnTap\MasterCard\Gateway\Config\ConfigInterface;

class TokenCreateHandler implements HandlerInterface
{
    /**
     * @var VaultPaymentInterface
     */
    protected $vaultPayment;

    /**
     * @var PaymentTokenFactoryInterface
     */
    protected $paymentTokenFactory;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var OrderPaymentExtensionInterfaceFactory
     */
    protected $paymentExtensionFactory;

    /**
     * @var Json
     */
    private $json;

    /**
     * TokenCreateHandler constructor.
     * @param ConfigInterface $config
     * @param VaultPaymentInterface $vaultPayment
     * @param PaymentTokenFactoryInterface $paymentTokenFactory
     * @param OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
     * @param Json $json
     */
    public function __construct(
        ConfigInterface $config,
        VaultPaymentInterface $vaultPayment,
        PaymentTokenFactoryInterface $paymentTokenFactory,
        OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
        Json $json
    ) {
        $this->config = $config;
        $this->vaultPayment = $vaultPayment;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->json = $json;
    }

    /**
     * @return ConfigInterface
     */
    protected function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $response
     * @return string
     */
    protected function getToken(array $response)
    {
        if (!isset($response['token'])) {
            throw new InvalidArgumentException('Token not present in response');
        }
        return $response['token'];
    }

    /**
     * Convert payment token details to JSON
     * @param array $details
     * @return string
     */
    private function convertDetailsToJSON($details)
    {
        $json = $this->json->serialize($details);
        return $json ?: '{}';
    }

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     * @throws Exception
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        $paymentInfo = $paymentDO->getPayment();

        $isActiveVaultModule = $this->config->isVaultEnabled();
        if ($isActiveVaultModule) {
            $paymentToken = $this->getPaymentToken($response);
            if ($paymentToken->getGatewayToken() !== '') {
                $extensionAttributes = $this->getExtensionAttributes($paymentInfo);
                $extensionAttributes->setVaultPaymentToken($paymentToken);
            }
        }
    }

    /**
     * Get payment extension attributes
     * @param InfoInterface $payment
     * @return OrderPaymentExtensionInterface
     */
    private function getExtensionAttributes(InfoInterface $payment)
    {
        $extensionAttributes = $payment->getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->paymentExtensionFactory->create();
            $payment->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    /**
     * @param array $response
     * @return PaymentTokenInterface
     * @throws Exception
     */
    protected function getPaymentToken(array $response)
    {
        $token = $this->getToken($response);
        $paymentToken = $this->paymentTokenFactory->create();
        $paymentToken->setType(PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD);

        if (empty($token)) {
            $paymentToken->setGatewayToken('');
            return $paymentToken;
        }

        $paymentToken->setGatewayToken($token);

        if (!isset($response['sourceOfFunds']['provided']['card'])) {
            throw new InvalidArgumentException(__("Card details not provided by tokenization"));
        }

        $m = [];
        preg_match('/^(\d{2})(\d{2})$/', $response['sourceOfFunds']['provided']['card']['expiry'], $m);

        $paymentToken->setTokenDetails($this->convertDetailsToJSON([
            'repository_id' => $response['repositoryId'],
            'merchant_id' => $this->config->getMerchantId(),
            'verification_strategy' => $response['verificationStrategy'],
            'cc_number' => $response['sourceOfFunds']['provided']['card']['number'],
            'cc_expr_month' => $m[1],
            'cc_expr_year' => $m[2],
            'type' => $this->getCcTypeFromBrand($response['sourceOfFunds']['provided']['card']['brand'])
        ]));

        $paymentToken->setExpiresAt($this->getExpirationDate($m[1], $m[2]));

        return $paymentToken;
    }

    /**
     * @param string $exprMonth
     * @param string $exprYear
     * @return string
     * @throws Exception
     */
    private function getExpirationDate($exprMonth, $exprYear)
    {
        $expDate = new DateTime(
            $exprYear
            . '-'
            . $exprMonth
            . '-'
            . '01'
            . ' '
            . '00:00:00',
            new DateTimeZone('UTC')
        );
        $expDate->add(new DateInterval('P1M'));
        return $expDate->format('Y-m-d 00:00:00');
    }

    // @codingStandardsIgnoreStart
    /**
     * @param string $brand
     * @return string
     */
    public static function getCcTypeFromBrand($brand)
    {
        // @codingStandardsIgnoreStop
        $brands = [
            'MASTERCARD' => 'MC',
            'VISA' => 'VI',
            'AMEX' => 'AE',
            'DINERS_CLUB' => 'DN',
            'DISCOVER' => 'DI',
            'JCB' => 'JCB',
            'MAESTRO' => 'SM',
        ];
        return isset($brands[$brand]) ? $brands[$brand] : $brand;
    }
}
