<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Plugin;

class MiniCartPlugin
{
    /**
     * @var \Magento\Payment\Gateway\Config\Config[]
     */
    protected $configPool = [];

    /**
     * MiniCartPlugin constructor.
     * @param \Magento\Payment\Gateway\Config\Config[] $configPool
     */
    public function __construct(
        $configPool = []
    ) {
        $this->configPool = $configPool;
    }

    /**
     * @return bool
     */
    protected function shouldRedirectToCart()
    {
        foreach ($this->configPool as $config) {
            if ($config->getValue('active')) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param \Magento\Checkout\Block\Cart\Sidebar $subject
     * @param $result
     * @return string
     */
    public function afterGetCheckoutUrl(\Magento\Checkout\Block\Cart\Sidebar $subject, $result)
    {
        if (!$this->shouldRedirectToCart()) {
            return $result;
        }
        return $subject->getUrl('checkout/cart');
    }
}
