<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Observer\Hpf;

use OnTap\Tns\Observer\DataAssignAbstract;

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
