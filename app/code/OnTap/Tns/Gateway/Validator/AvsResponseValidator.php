<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use OnTap\Tns\Model\Adminhtml\Source\ValidatorBehaviour;

class AvsResponseValidator extends AbstractValidator
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var array
     */
    protected $responseCodeConfig = [
        'ADDRESS_ZIP_MATCH' => 'avs_rules_address_zip_match',
        'ZIP_MATCH' => 'avs_rules_zip_match',
        'ADDRESS_MATCH' => 'avs_rules_address_match',
        'NAME_MATCH' => 'avs_rules_name_match',
        'NAME_ZIP_MATCH' => 'avs_rules_name_zip_match',
        'NAME_ADDRESS_MATCH' => 'avs_rules_name_address_match',
        'NO_MATCH' => 'avs_rules_no_match',
        'SERVICE_NOT_SUPPORTED' => 'avs_rules_service_not_supported',
        'SERVICE_NOT_AVAILABLE_RETRY' => 'avs_rules_service_not_supported',
        'NOT_REQUESTED' => 'avs_rules_not_requested',
        'NOT_AVAILABLE' => 'avs_rules_not_available',
        'NOT_VERIFIED' => 'avs_rules_not_verified',
    ];

    /**
     * AvsResponseValidator constructor.
     * @param ConfigInterface $config
     * @param ResultInterfaceFactory $resultFactory
     */
    public function __construct(
        ConfigInterface $config,
        ResultInterfaceFactory $resultFactory
    ) {
        $this->config = $config;
        parent::__construct($resultFactory);
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

        if ($this->config->getValue('avs') !== '1' || isset($response['error'])) {
            return $this->createResult(true);
        }

        if (!isset($response['response']['cardholderVerification']['avs'])) {
            return $this->createResult(false, [__('AVS validator error.')]);
        }

        if ($this->validateGatewayCode($response, ValidatorBehaviour::REJECT)) {
            return $this->createResult(false, [__('Transaction declined by AVS validation.')]);
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
        $avs = $response['response']['cardholderVerification']['avs'];
        $configPath = $this->responseCodeConfig[$avs['gatewayCode']];

        return $this->config->getValue($configPath) === $code;
    }
}
