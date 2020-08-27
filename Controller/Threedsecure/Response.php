<?php
/**
 * Copyright (c) 2016-2019 Mastercard
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OnTap\MasterCard\Controller\Threedsecure;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Response extends Action implements CsrfAwareActionInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var RawFactory
     */
    private $rawFactory;

    /**
     * Response constructor.
     * @param Context $context
     * @param Session $session
     * @param RawFactory $rawFactory
     */
    public function __construct(
        Context $context,
        Session $session,
        RawFactory $rawFactory
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->rawFactory = $rawFactory;
    }

    /**
     * Dispatch request
     *
     * @return ResultInterface|ResponseInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $paRes = $this->getRequest()->getParam('PaRes');

        $quote = $this->session->getQuote();
        $payment = $quote->getPayment();
        $payment->setAdditionalInformation('PaRes', $paRes);
        $quote->save();

        $resultRaw = $this->rawFactory->create();
        return $resultRaw
            ->setContents("<script>window.parent.tnsThreeDSecureClose();</script>");
    }

    /**
     * @inheritdoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
