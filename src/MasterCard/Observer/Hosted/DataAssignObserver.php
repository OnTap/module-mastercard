<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Observer\Hosted;

use OnTap\MasterCard\Observer\DataAssignAbstract;

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
