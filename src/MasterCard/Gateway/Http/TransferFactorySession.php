<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Gateway\Http;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Http\TransferBuilder;
use OnTap\MasterCard\Gateway\Http\Client\Rest;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

class TransferFactorySession extends TransferFactory
{
    /**
     * @var string
     */
    protected $httpMethod = Rest::POST;

    /**
     * @var bool
     */
    protected $createNewSession;

    /**
     * TransferFactorySession constructor.
     * @param ConfigInterface $config
     * @param TransferBuilder $transferBuilder
     * @param bool $createNewSession
     */
    public function __construct(
        ConfigInterface $config,
        TransferBuilder $transferBuilder,
        $createNewSession = false
    ) {
        parent::__construct($config, $transferBuilder);
        $this->createNewSession = $createNewSession;
    }

    /**
     * @param PaymentDataObjectInterface $payment
     * @return string
     */
    protected function getUri(PaymentDataObjectInterface $payment)
    {
        $suffix = '';
        if (!$this->createNewSession) {
            $session = $payment->getPayment()->getAdditionalInformation('session');
            if (!$session || !isset($session['id'])) {
                throw new \InvalidArgumentException(__("Session ID not present for order"));
            }
            $suffix = '/' . $session['id'];
        }

        return $this->getGatewayUri() . 'session' . $suffix;
    }
}
