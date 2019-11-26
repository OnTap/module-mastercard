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

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\View\LayoutFactory;
use Magento\Checkout\Model\Session;
use OnTap\MasterCard\Gateway\Response\ThreeDSecure\CheckHandler;

class Form extends Action
{
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var Session
     */
    protected $session;

    /**
     * Acs constructor.
     * @param Context $context
     * @param RawFactory $pageFactory
     * @param LayoutFactory $layoutFactory
     * @param Session $session
     */
    public function __construct(
        Context $context,
        RawFactory $pageFactory,
        LayoutFactory $layoutFactory,
        Session $session
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $pageFactory;
        $this->layoutFactory = $layoutFactory;
        $this->session = $session;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        /* @var \Magento\Framework\View\Element\Template $block */
        $block = $this->layoutFactory
            ->create()
            ->createBlock('\OnTap\MasterCard\Block\Threedsecure\Form');

        $payment = $this->session->getQuote()->getPayment();

        $block
            ->setTemplate('OnTap_MasterCard::threedsecure/form.phtml')
            ->setData($payment->getAdditionalInformation(CheckHandler::THREEDSECURE_CHECK));

        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents(
            $block->toHtml()
        );
    }
}
