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

class AvsResponseValidator extends AbstractValidator
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * AvsResponseValidator constructor.
     * @param ResultInterfaceFactory $resultFactory
     * @param ConfigInterface $config
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        ConfigInterface $config
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

        if (!isset($response['response']['cardholderVerification']['avs'])) {
            return $this->createResult(false, [__('AVS validation failed.')]);
        }

        $avs = $response['response']['cardholderVerification']['avs'];

        $codeToPath = [
            'ZIP_MATCH' => 'avs_rules_zip_match',
            'NO_MATCH' => 'avs_rules_no_match',
        ];
        $configPath = $codeToPath[$avs['gatewayCode']];

        if ($this->config->getValue($configPath) === ValidatorBehaviour::REJECT) {
            return $this->createResult(false, [__('Transaction declined by AVS')]);
        }

        return $this->createResult(true);
    }
}
