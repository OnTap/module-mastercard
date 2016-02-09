<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Validator\ThreeDSecure;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use OnTap\Tns\Model\Adminhtml\Source\ValidatorBehaviour;

class ProcessValidator extends AbstractValidator
{
    /**
     * @var array
     */
    protected $responseCodeConfig = [
        'CARD_NOT_ENROLLED' => 'tds_no_support',
        'CARD_ENROLLED' => 'tds_auth_not_available', // @todo: verify if CARD_ENROLLED in this stage is possible
        'AUTHENTICATION_NOT_AVAILABLE' => 'tds_auth_not_available',
        'AUTHENTICATION_SUCCESSFUL' => 'tds_auth_successful',
        'AUTHENTICATION_FAILED' => 'tds_auth_failed',
        'AUTHENTICATION_ATTEMPTED' => 'tds_auth_attempted',
        'CARD_DOES_NOT_SUPPORT_3DS' => 'tds_no_support',
    ];

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * ProcessValidator constructor.
     * @param ResultInterfaceFactory $resultFactory
     * @param ConfigInterface $config
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        ConfigInterface $config
    ) {
        parent::__construct($resultFactory);
        $this->config = $config;
    }

    /**
     * Performs domain-related validation for business object
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);

        if ($this->validateGatewayCode($response, ValidatorBehaviour::REJECT)) {
            return $this->createResult(false, [__('Transaction declined by 3D-Secure validation.')]);
        }

        return $this->createResult(true);
    }

    /**
     * @param array $response
     * @param string $code
     * @return bool
     */
    public function validateGatewayCode(array $response, $code)
    {
        if (!isset($response['3DSecure']['summaryStatus'])) {
            return false;
        }

        $tds = $response['3DSecure']['summaryStatus'];
        $configPath = $this->responseCodeConfig[$tds];

        return $this->config->getValue($configPath) === $code;
    }
}
