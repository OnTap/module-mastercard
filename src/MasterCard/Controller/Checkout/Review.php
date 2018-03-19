<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Controller\Checkout;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Checkout\Model\Session as CheckoutSession;

class Review extends \Magento\Framework\App\Action\Action
{
    /**
     * @var CheckoutSession
     */
    protected $session;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Review constructor.
     * @param PageFactory $pageFactory
     * @param CheckoutSession $session
     * @param Context $context
     */
    public function __construct(
        PageFactory $pageFactory,
        CheckoutSession $session,
        Context $context
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $pageFactory;
        $this->session = $session;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
}
