<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Model\Ui\Amex;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Store\Model\StoreManagerInterface;
use OnTap\MasterCard\Model\Method\Wallet\WalletInterface;
use Magento\Payment\Model\Method\InstanceFactory;

class ConfigProvider implements ConfigProviderInterface
{
    protected static $group = 'amexWallet';

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
        foreach ($list as $method) {
            $methodInstance = $this->instanceFactory->create($method);
            if ($methodInstance instanceof WalletInterface) {
                $provider = $methodInstance->getProvider();
                if (!$provider) {
                    break;
                }

                $methods['mpgs_amex_wallet_method'] = [
                    'is_enabled' => true,
                    'title' => $methodInstance->getTitle(),
                    'config' => [
                    ],
                    'component' => 'OnTap_MasterCard/js/view/payment/method-renderer/amex-wallet',
                ];
                break;
            }
        }

        return [
            'payment' => [
                self::$group => $methods
            ]
        ];
    }
}
