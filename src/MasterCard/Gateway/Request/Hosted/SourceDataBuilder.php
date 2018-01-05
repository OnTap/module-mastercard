<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Request\Hosted;

use Magento\Payment\Gateway\Request\BuilderInterface;
use OnTap\MasterCard\Gateway\Request\SourceDataBuilder as BaseBuilder;

class SourceDataBuilder implements BuilderInterface
{
    const TXN_SOURCE_FRONTEND = 'INTERNET';
    const TXN_SOURCE_ADMIN = 'MOTO';

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function build(array $buildSubject)
    {
        return [
            'transaction' => [
                'source' => BaseBuilder::TXN_SOURCE_FRONTEND,
            ]
        ];
    }
}
