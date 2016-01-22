<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use OnTap\Tns\Gateway\Request\Direct\CardDataBuilder;

class DataAssignObserver extends AbstractDataAssignObserver
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

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        // @todo:
        // remove this when magento releases readPaymentModelArgument()
        $paymentInfo = $this->readMethodArgument($observer)->getInfoInstance();

        // @todo: not released yet
        //$paymentInfo = $this->readPaymentModelArgument($observer);

        foreach ($this->additionalInformationList as $additionalInformationKey) {
            if ($data->getDataByKey($additionalInformationKey) !== null) {
                $paymentInfo->setAdditionalInformation(
                    $additionalInformationKey,
                    $data->getDataByKey($additionalInformationKey)
                );
            }
        }
    }
}
