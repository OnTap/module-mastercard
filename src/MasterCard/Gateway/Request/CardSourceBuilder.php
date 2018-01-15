<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class CardSourceBuilder implements BuilderInterface
{
    const TYPE = 'CARD';

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        return [
            'sourceOfFunds' => [
                'type' => self::TYPE,
            ]
        ];
    }
}
