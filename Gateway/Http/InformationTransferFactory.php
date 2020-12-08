<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use OnTap\MasterCard\Gateway\Config\ConfigInterface;
use OnTap\MasterCard\Model\SelectedStore;

class InformationTransferFactory implements TransferFactoryInterface
{
    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var SelectedStore
     */
    protected $selectedStore;

    /**
     * InformationTransferFactory constructor.
     * @param TransferBuilder $transferBuilder
     * @param ConfigInterface $config
     * @param SelectedStore $selectedStore
     */
    public function __construct(
        TransferBuilder $transferBuilder,
        ConfigInterface $config,
        SelectedStore $selectedStore
    ) {
        $this->transferBuilder = $transferBuilder;
        $this->config = $config;
        $this->selectedStore = $selectedStore;
    }

    /**
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request)
    {
        $storeId = $this->selectedStore->getStoreId();
        $version = $this->config->getValue('api_version');
        $merchantId = $this->config->getMerchantId($storeId);

        return $this->transferBuilder
            ->setMethod('GET')
            ->setHeaders(['Content-Type' => 'application/json;charset=UTF-8'])
            ->setAuthUsername('merchant.' . $merchantId)
            ->setAuthPassword($this->config->getMerchantPassword($storeId))
            ->setUri(
                $this->config->getApiUrl($storeId)
                . 'version/' . $version
                . '/merchant/' . $merchantId
                . '/paymentOptionsInquiry'
            )
            ->build();
    }
}
