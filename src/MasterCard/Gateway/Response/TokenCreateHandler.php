<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Vault\Model\VaultPaymentInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Vault\Api\Data\PaymentTokenInterfaceFactory;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Gateway\ConfigInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TokenCreateHandler implements HandlerInterface
{
    /**
     * @var VaultPaymentInterface
     */
    protected $vaultPayment;

    /**
     * @var PaymentTokenInterfaceFactory
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
     * TokenCreateHandler constructor.
     * @param ConfigInterface $config
     * @param VaultPaymentInterface $vaultPayment
     * @param PaymentTokenInterfaceFactory $paymentTokenFactory
     * @param OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
     */
    public function __construct(
        ConfigInterface $config,
        VaultPaymentInterface $vaultPayment,
        PaymentTokenInterfaceFactory $paymentTokenFactory,
        OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
    ) {
        // @todo: fetch config generically from payment
        $this->config = $config;
        $this->vaultPayment = $vaultPayment;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentExtensionFactory = $paymentExtensionFactory;
    }

    /**
     * @param array $response
     * @return string
     */
    protected function getToken(array $response)
    {
        if (!isset($response['token'])) {
            throw new \InvalidArgumentException('Token not present in response');
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
        $json = \Zend_Json::encode($details);
        return $json ? $json : '{}';
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
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        $paymentInfo = $paymentDO->getPayment();

        $isActiveVaultModule = $this->config->isVaultEnabled();
        if ($isActiveVaultModule) {
            $paymentToken = $this->getPaymentToken($response);
            if ($paymentToken !== null) {
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
     */
    protected function getPaymentToken(array $response)
    {
        $token = $this->getToken($response);
        if (empty($token)) {
            return null;
        }

        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $this->paymentTokenFactory->create();
        $paymentToken->setGatewayToken($token);

        if (!isset($response['sourceOfFunds']['provided']['card'])) {
            throw new \InvalidArgumentException(__("Card details not provided by tokenization"));
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
     */
    private function getExpirationDate($exprMonth, $exprYear)
    {
        $expDate = new \DateTime(
            $exprYear
            . '-'
            . $exprMonth
            . '-'
            . '01'
            . ' '
            . '00:00:00',
            new \DateTimeZone('UTC')
        );
        $expDate->add(new \DateInterval('P1M'));
        return $expDate->format('Y-m-d 00:00:00');
    }

    /**
     * @param string $brand
     * @return string
     */
    public static function getCcTypeFromBrand($brand)
    {
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
