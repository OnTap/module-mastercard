<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferFactoryInterface as CoreTransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\ConfigInterface;
use OnTap\Tns\Gateway\Http\Client\Rest;

/**
 * Class TransferFactory3DS
 * @todo: Refactor this class together with TransferFactory to something more generic
 */
class TransferFactory3DS implements CoreTransferFactoryInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var TransferBuilder
     */
    protected $transferBuilder;

    /**
     * TransferFactory constructor.
     * @param ConfigInterface $config
     * @param TransferBuilder $transferBuilder
     */
    public function __construct(
        ConfigInterface $config,
        TransferBuilder $transferBuilder
    ) {
        $this->config = $config;
        $this->transferBuilder = $transferBuilder;
    }

    /**
     * @return string
     */
    protected function getMerchantUsername()
    {
        return 'merchant.' . $this->config->getValue('api_test_username');
    }

    /**
     * @return string
     */
    protected function apiVersionUri()
    {
        return 'version/' . $this->config->getValue('api_version') . '/';
    }

    /**
     * @return string
     */
    protected function merchantUri()
    {
        return 'merchant/' . $this->config->getValue('api_test_username') . '/';
    }

    /**
     * @return string
     */
    protected function getGatewayUri()
    {
        return $this->config->getValue('api_test_url') . $this->apiVersionUri() . $this->merchantUri();
    }

    /**
     * @return string
     */
    protected function getUri()
    {
        $threeDSecureId = uniqid(sprintf('3DS-'));
        return $this->getGatewayUri() . '3DSecureId/' . $threeDSecureId;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request)
    {
        return $this->transferBuilder
            ->setMethod(Rest::PUT)
            ->setHeaders(['Content-Type' => 'application/json;charset=UTF-8'])
            ->setBody($request)
            ->setAuthUsername($this->getMerchantUsername())
            ->setAuthPassword($this->config->getValue('api_test_password'))
            ->setUri($this->getUri())
            ->build();
    }
}
