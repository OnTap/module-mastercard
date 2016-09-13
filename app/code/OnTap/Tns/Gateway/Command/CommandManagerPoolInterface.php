<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace OnTap\Tns\Gateway\Command;

use Magento\Framework\Exception\NotFoundException;

/**
 * Interface CommandManagerPoolInterface
 * @api
 */
interface CommandManagerPoolInterface
{
    /**
     * Returns Command executor for defined payment provider
     *
     * @param string $paymentProviderCode
     * @return CommandManagerInterface
     * @throws NotFoundException
     */
    public function get($paymentProviderCode);
}
