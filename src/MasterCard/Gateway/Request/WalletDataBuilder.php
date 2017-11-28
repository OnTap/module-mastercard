<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Data\Quote\QuoteAdapter;
use Magento\Sales\Model\Order\Payment;

class WalletDataBuilder implements BuilderInterface
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

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        $provider = $payment->getAdditionalInformation('walletProvider');
        $session = $payment->getAdditionalInformation('session');

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $payment->getQuote();
        $quote->collectTotals();

        return [
            'order' => [
                'walletProvider' => $provider,
                'amount' => sprintf('%.2F', $quote->getGrandTotal()),
                'currency' => $order->getCurrencyCode(),
            ],
            'session' => [
                'version' => $session['version']
            ]
        ];
    }
}
