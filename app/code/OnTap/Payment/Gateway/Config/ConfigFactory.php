<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace OnTap\Payment\Gateway\Config;

use Magento\Framework\ObjectManagerInterface;
use OnTap\Payment\Gateway\ConfigFactoryInterface;
use Magento\Payment\Gateway\Config\Config;

class ConfigFactory implements ConfigFactoryInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $om;

    /**
     * ConfigFactory constructor.
     * @param ObjectManagerInterface $om
     */
    public function __construct(
        ObjectManagerInterface $om
    ) {
        $this->om = $om;
    }

    /**
     * @param string|null $paymentCode
     * @param string|null $pathPattern
     * @return mixed
     */
    public function create($paymentCode = null, $pathPattern = null)
    {
        $arguments = [
            'methodCode' => $paymentCode
        ];

        if ($pathPattern !== null) {
            $arguments['pathPattern'] = $pathPattern;
        }

        return $this->om->create(Config::class, $arguments);
    }
}
