<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Model\Adminhtml\Source\ThreeDSecure;

class ValidatorBehaviour extends \OnTap\Tns\Model\Adminhtml\Source\ValidatorBehaviour
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => static::ACCEPT,
                'label' => __('Accept')
            ],
            [
                'value' => static::REJECT,
                'label' => __('Reject')
            ],
        ];
    }
}
