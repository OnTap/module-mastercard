<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Model\Ui;

use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory;
use Magento\Framework\UrlInterface;
use OnTap\MasterCard\Gateway\Config\ConfigFactory;
use OnTap\MasterCard\Gateway\Config\Config;

/**
 * Class TokenUiComponentProvider
 */
class TokenUiComponentProvider implements TokenUiComponentProviderInterface
{
    /**
     * @var TokenUiComponentInterfaceFactory
     */
    protected $componentFactory;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param ConfigFactory $configFactory
     * @param TokenUiComponentInterfaceFactory $componentFactory
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        ConfigFactory $configFactory,
        TokenUiComponentInterfaceFactory $componentFactory,
        UrlInterface $urlBuilder
    ) {
        $this->componentFactory = $componentFactory;
        $this->urlBuilder = $urlBuilder;
        $this->config = $configFactory->create();
    }

    /**
     * Get UI component for token
     * @param PaymentTokenInterface $paymentToken
     * @return TokenUiComponentInterface
     */
    public function getComponentForToken(PaymentTokenInterface $paymentToken)
    {
        $this->config->setMethodCode($paymentToken->getPaymentMethodCode());
        $jsonDetails = \Zend_Json_Decoder::decode($paymentToken->getTokenDetails() ?: '{}', true);

        // Check for merchant ID, if the token merchant ID does not match the payment extension merchant ID
        // then do not render the vault method in hand.
        // @todo: not the best way to decide if a token payment needs to be rendered, refactor it
        //
        if (!isset($jsonDetails['merchant_id']) || $this->config->getMerchantId() !== $jsonDetails['merchant_id']) {
            return $component = $this->componentFactory->create([
                'config' => [],
                'name' => null
            ]);
        }

        $component = $this->componentFactory->create(
            [
                'config' => [
                    'code' => $paymentToken->getPaymentMethodCode() . '_vault',
                    TokenUiComponentProviderInterface::COMPONENT_DETAILS => $jsonDetails,
                    TokenUiComponentProviderInterface::COMPONENT_PUBLIC_HASH => $paymentToken->getPublicHash()
                ],
                'name' => 'OnTap_MasterCard/js/view/payment/method-renderer/vault'
            ]
        );

        return $component;
    }
}
