<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Model\Ui\Wallet;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Store\Model\StoreManagerInterface;
use OnTap\MasterCard\Model\Method\WalletInterface;
use Magento\Payment\Model\Method\InstanceFactory;

class ConfigProvider implements ConfigProviderInterface
{
    protected static $group = 'wallets';

    /**
     * @var PaymentMethodListInterface
     */
    protected $paymentMethodList;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var InstanceFactory
     */
    protected $instanceFactory;

    /**
     * ConfigProvider constructor.
     * @param StoreManagerInterface $storeManager
     * @param PaymentMethodListInterface $paymentMethodList
     * @param InstanceFactory $instanceFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        PaymentMethodListInterface $paymentMethodList,
        InstanceFactory $instanceFactory
    ) {
        $this->storeManager = $storeManager;
        $this->paymentMethodList = $paymentMethodList;
        $this->instanceFactory = $instanceFactory;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $list = $this->paymentMethodList->getActiveList($storeId);

        $methods = [];
        $configs = [];
        foreach ($list as $i => $method) {
            $methodInstance = $this->instanceFactory->create($method);
            if ($methodInstance instanceof WalletInterface) {
                $methods[$method->getCode() . '_' . $i] = [
                    'title' => $methodInstance->getTitle(),
                    'component' => $methodInstance->getMethodConfig()->getValue('js_component'),
                ];
                $configs[$method->getCode()] = $methodInstance->getJsConfig();
                break;
            }
        }

        return [
            'payment' => [
                self::$group => $methods
            ],
            self::$group => $configs
        ];
    }
}
