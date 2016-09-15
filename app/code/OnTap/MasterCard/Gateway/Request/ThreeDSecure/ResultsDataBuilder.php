<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Request\ThreeDSecure;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use OnTap\MasterCard\Gateway\Response\ThreeDSecure\CheckHandler;

class ResultsDataBuilder implements BuilderInterface
{
    const ENROLLED = 'ENROLLED';
    const NOT_ENROLLED = 'NOT_ENROLLED';
    const ENROLLMENT_STATUS_UNDETERMINED = 'ENROLLMENT_STATUS_UNDETERMINED';

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * ResultsDataBuilder constructor.
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param PaymentDataObjectInterface $paymentDO
     * @return string
     */
    protected function getEnrollmentStatus(PaymentDataObjectInterface $paymentDO)
    {
        $tdsCheck = $paymentDO->getPayment()->getAdditionalInformation(CheckHandler::THREEDSECURE_CHECK);

        switch ($tdsCheck['status']) {
            case 'CARD_ENROLLED':
                $status = static::ENROLLED;
                break;

            case 'CARD_NOT_ENROLLED':
                $status = static::NOT_ENROLLED;
                break;

            default:
            case 'CARD_DOES_NOT_SUPPORT_3DS':
                $status = static::ENROLLMENT_STATUS_UNDETERMINED;
                break;
        }

        return $status;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        if ($this->config->getValue('three_d_secure') !== '1') {
            return [];
        }

        $paymentDO = SubjectReader::readPayment($buildSubject);
        //$tdsResult = $paymentDO->getPayment()->getAdditionalInformation(ResultHandler::THREEDSECURE_RESULT);

        return [
            '3DSecureId' => $paymentDO->getPayment()->getAdditionalInformation('3DSecureId'),
            /*'3DSecure' => [
                'acsEci' => $tdsResult['acsEci'],
                'authenticationToken' => $tdsResult['authenticationToken'],
                'xid' => $tdsResult['xid'],
                'enrollmentStatus' => $this->getEnrollmentStatus($paymentDO)
            ]*/
        ];
    }
}
