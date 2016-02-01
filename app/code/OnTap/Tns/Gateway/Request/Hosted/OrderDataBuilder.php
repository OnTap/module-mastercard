<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Request\Hosted;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\Data\Quote\QuoteAdapter;

class OrderDataBuilder implements BuilderInterface
{
    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        /* @var QuoteAdapter $order */
        $order = $paymentDO->getOrder();

        return [
            'order' => [
                'currency' => $order->getCurrencyCode(),
                'id' => $order->getOrderIncrementId()
            ]
        ];
    }
}
