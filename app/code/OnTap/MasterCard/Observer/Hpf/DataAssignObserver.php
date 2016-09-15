<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
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
