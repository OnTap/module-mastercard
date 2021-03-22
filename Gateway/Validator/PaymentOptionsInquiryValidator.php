<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;

class PaymentOptionsInquiryValidator extends AbstractValidator
{
    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function validate(array $validationSubject)
    {
        $error = false;
        $message = "";

        if (!empty($validationSubject['response']['result'])) {
            $error = $validationSubject['response']['result'] === 'ERROR';
        }

        if (!empty($validationSubject['response']['error']['cause'])) {
            $message .= $validationSubject['response']['error']['cause'];
        }

        if (!empty($validationSubject['response']['error']['explanation'])) {
            $message .= ' (' . $validationSubject['response']['error']['explanation'] . ')';
        }

        if ($error) {
            if ($message === '') {
                $message = __('General Error');
            }
            // @codingStandardsIgnoreStart
            throw new \Exception($message);
            // @codingStandardsIgnoreEnd
        }

        return $this->createResult(true);
    }
}
