<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Request\ThreeDSecure;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Framework\UrlInterface;
use OnTap\MasterCard\Gateway\Request\Direct\CardDataBuilder;

class CheckDataBuilder extends CardDataBuilder implements BuilderInterface
{
    const PAGE_GENERATION_MODE = 'CUSTOMIZED';
    const PAGE_ENCODING = 'UTF_8';
    const RESPONSE_URL = 'tns/threedsecure/response';

    /**
     * @var UrlInterface
     */
    protected $urlHelper;

    /**
     * ThreeDSecureDataBuilder constructor.
     * @param UrlInterface $urlHelper
     */
    public function __construct(UrlInterface $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $order = $paymentDO->getOrder();
        $payment = $paymentDO->getPayment();

        $data = [
            '3DSecure' => [
                'authenticationRedirect' => [
                    'pageGenerationMode' => static::PAGE_GENERATION_MODE,
                    'responseUrl' => $this->urlHelper->getUrl(static::RESPONSE_URL),
                ]
            ],
            'order' => [
                'amount' => sprintf('%.2F', SubjectReader::readAmount($buildSubject)),
                'currency' => $order->getCurrencyCode(),
            ],
        ];

        $code = $payment->getMethodInstance()->getCode();

        if ($code === \OnTap\MasterCard\Model\Ui\Direct\ConfigProvider::METHOD_CODE) {
            $data = array_merge($data, [
                'sourceOfFunds' => [
                    'provided' => [
                        'card' => [
                            'expiry' => [
                                'month' => $this->formatMonth(
                                    $payment->getAdditionalInformation(CardDataBuilder::CC_EXP_MONTH)
                                ),
                                'year' => $this->formatYear(
                                    $payment->getAdditionalInformation(CardDataBuilder::CC_EXP_YEAR)
                                ),
                            ],
                            'number' => $payment->getAdditionalInformation(CardDataBuilder::CC_NUMBER),
                        ],
                    ],
                ],
            ]);
        }

        if ($code === \OnTap\MasterCard\Model\Ui\Hpf\ConfigProvider::METHOD_CODE) {
            $data = array_merge($data, [
                'session' => [
                    'id' => $payment->getAdditionalInformation('session')
                ]
            ]);
        }

        return $data;
    }
}
