<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Request\Direct;

use Magento\Payment\Gateway\Request\BuilderInterface;

class VerifyDataBuilder implements BuilderInterface
{
    const OPERATION = 'VERIFY';

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
            'apiOperation' => self::OPERATION,
        ];
    }
}
