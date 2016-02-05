<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Controller\Threedsecure;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\View\LayoutFactory;
use Magento\Checkout\Model\Session;

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
            ->createBlock('\Magento\Framework\View\Element\Template');

        $payment = $this->session->getQuote()->getPayment();

        $block
            ->setTemplate('OnTap_Tns::threedsecure/form.phtml')
            ->setData($payment->getAdditionalInformation());

        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents(
            $block->toHtml()
        );
    }
}
