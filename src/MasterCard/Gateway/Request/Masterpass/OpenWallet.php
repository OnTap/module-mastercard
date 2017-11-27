<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Request\Masterpass;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Framework\UrlInterface;

class OpenWallet implements BuilderInterface
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * OpenWallet constructor.
     * @param UrlInterface $url
     */
    public function __construct(UrlInterface $url)
    {
        $this->urlBuilder = $url;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        return [
            'wallet' => [
                'masterpass' => [
                    'originUrl' => $this->urlBuilder->getUrl('mpgs/masterpass/review', ['_secure' => true])
                ]
            ]
        ];
    }
}
