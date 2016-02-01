<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Observer\Hosted;

use Magento\Framework\Event\Observer;
use OnTap\Tns\Observer\DataAssignAbstract;

class DataAssignObserver extends DataAssignAbstract
{
    const RESULT_INDICATOR = 'resultIndicator';
    const SESSION_VERSION = 'sessionVersion';

    /**
     * @var array
     */
    protected $additionalInformationList = [
        self::RESULT_INDICATOR,
        self::SESSION_VERSION,
    ];
}
