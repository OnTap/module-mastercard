<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\MasterCard\Observer\Hpf;

use OnTap\MasterCard\Observer\DataAssignAbstract;

class DataAssignObserver extends DataAssignAbstract
{
    const SESSION = 'session';

    /**
     * @var array
     */
    protected $additionalInformationList = [
        self::SESSION,
    ];
}
