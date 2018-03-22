<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Controller\Checkout;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;

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
     * @var PaymentDataObjectFactory
     */
    protected $paymentDataObjectFactory;

    /**
     * Review constructor.
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param PageFactory $pageFactory
     * @param CheckoutSession $session
     * @param Context $context
     */
    public function __construct(
        PaymentDataObjectFactory $paymentDataObjectFactory,
        PageFactory $pageFactory,
        CheckoutSession $session,
        Context $context
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $pageFactory;
        $this->session = $session;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
//        try {
            $quote = $this->session->getQuote();

            /** @var \OnTap\MasterCard\Model\Method\Wallet $payment */
            $payment = $quote->getPayment()->getMethodInstance();

            $command = $payment->getCommandPool()->get('get_session');
            $paymentDO = $this->paymentDataObjectFactory->create($quote->getPayment());
            $command->execute([
                'payment' => $paymentDO
            ]);

//        } catch (\Exception $e) {
//            $this->messageManager->addErrorMessage($e->getMessage());
//            return $this->_redirect('checkout/cart');
//        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        /** @var \OnTap\MasterCard\Block\Checkout\Review $reviewBlock */
        $reviewBlock = $resultPage->getLayout()->getBlock('mpgs.checkout.review');
        $reviewBlock->setQuote($quote);

        return $resultPage;
    }
}
