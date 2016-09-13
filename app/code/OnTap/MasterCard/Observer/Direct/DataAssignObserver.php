<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\MasterCard\Observer\Direct;

use OnTap\MasterCard\Gateway\Request\Direct\CardDataBuilder;
use OnTap\MasterCard\Observer\DataAssignAbstract;

class DataAssignObserver extends DataAssignAbstract
{
    /**
     * @var array
     */
    protected $additionalInformationList = [
        CardDataBuilder::CC_TYPE,
        CardDataBuilder::CC_EXP_YEAR,
        CardDataBuilder::CC_EXP_MONTH,
        CardDataBuilder::CC_NUMBER,
        CardDataBuilder::CC_CID
    ];
}
