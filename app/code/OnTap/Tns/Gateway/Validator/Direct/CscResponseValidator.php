<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Validator\Direct;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use OnTap\Tns\Model\Adminhtml\Source\ValidatorBehaviour;

class CscResponseValidator extends AbstractValidator
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var array
     */
    protected $codeConfigMap = [
        'MATCH' => 'csc_rules_match',
        'NOT_PRESENT' => 'csc_rules_not_present',
        'NOT_PROCESSED' => 'csc_rules_not_processed',
        'NOT_SUPPORTED' => 'csc_rules_not_supported',
        'NO_MATCH' => 'csc_rules_no_match'
    ];

    /**
     * CscResponseValidator constructor.
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
        if ($this->config->getValue('csc_rules') !== '1') {
            return $this->createResult(true);
        }

        $response = SubjectReader::readResponse($validationSubject);

        if (!isset($response['response']['cardSecurityCode'])) {
            return $this->createResult(false, [__('CSC validator error.')]);
        }

        $csc = $response['response']['cardSecurityCode'];
        $configPath = $this->codeConfigMap[$csc['gatewayCode']];

        if ($this->config->getValue($configPath) === ValidatorBehaviour::REJECT) {
            return $this->createResult(false, [__('Transaction declined by CSC validation.')]);
        }

        return $this->createResult(true);
    }
}
