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
     * @param string $requestType
     */
    public function __construct(
        ConfigInterface $config,
        TransferBuilder $transferBuilder,
        $createNewSession = false,
        $requestType = Rest::POST
    ) {
        parent::__construct($config, $transferBuilder);
        $this->createNewSession = $createNewSession;
        $this->httpMethod = $requestType;
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
            $sessionId = null;
            if (is_string($session)) {
                if (!$session) {
                    throw new \InvalidArgumentException(__("Could not find session ID from the payment."));
                }
                $sessionId = $session;
            }
            if (is_array($session)) {
                if (!isset($session['id'])) {
                    throw new \InvalidArgumentException(__("Could not find session ID from the payment."));
                }
                $sessionId = $session['id'];
            }
            $suffix = '/' . $sessionId;
        }

        return $this->getGatewayUri() . 'session' . $suffix;
    }
}
