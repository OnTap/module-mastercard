<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Controller\Threedsecure;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;

class Response extends \Magento\Framework\App\Action\Action
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
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $paRes = $this->getRequest()->getParam('PaRes');

        $payment = $this->session->getQuote()->getPayment();
        $payment->setAdditionalInformation('PaRes', $paRes);
        $payment->save();

        $resultRaw = $this->rawFactory->create();
        return $resultRaw
            ->setContents("<script>window.parent.tnsThreeDSecureClose();</script>");
    }
}
