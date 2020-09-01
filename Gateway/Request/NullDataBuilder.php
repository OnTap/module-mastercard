<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class NullDataBuilder implements BuilderInterface
{
    /**
     * @inheritDoc
     */
    public function build(array $buildSubject)
    {
        return [];
    }
}
