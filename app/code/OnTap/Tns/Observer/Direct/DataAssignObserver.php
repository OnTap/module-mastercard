<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Observer\Direct;

use Magento\Framework\Event\Observer;
use OnTap\Tns\Gateway\Request\Direct\CardDataBuilder;
use OnTap\Tns\Observer\DataAssignAbstract;

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
