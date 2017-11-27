<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Controller\Masterpass;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\PageFactory;

class Review extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * Review constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        /**
        On Success, Masterpass returns the following parameters:
        mpstatus: String that indicates whether the Masterpass flow resulted in success, failure, or cancel.
        checkout_resource_url: The API URL that will be used to retrieve checkout information in Step 7.
        oauth_verifier: The verifier token that is used If the successCallback parameter to retrieve the access token in Step 6.
        oauth_token: The request token that is used to retrieve the access token in Step 6. This token has the same value as the request token that is generated in Step 1.
         */

        $page = $this->pageFactory->create();
        return $page;
    }
}
