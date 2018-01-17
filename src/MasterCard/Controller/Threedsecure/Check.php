<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Controller\Threedsecure;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use OnTap\MasterCard\Gateway\Response\ThreeDSecure\CheckHandler;
use Magento\Payment\Gateway\Command\CommandPoolInterface;

class Check extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var PaymentDataObjectFactory
     */
    private $paymentDataObjectFactory;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * Check constructor.
     * @param CommandPoolInterface $commandPool
     * @param Session $checkoutSession
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param JsonFactory $jsonFactory
     * @param Context $context
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        Session $checkoutSession,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        JsonFactory $jsonFactory,
        Context $context
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->jsonFactory = $jsonFactory;
        $this->commandPool = $commandPool;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     */
    public function execute()
    {
        $quote = $this->checkoutSession->getQuote();
        $jsonResult = $this->jsonFactory->create();
        try {
            $paymentDataObject = $this->paymentDataObjectFactory->create($quote->getPayment());

            $this->commandPool
                ->get($this->getRequest()->getParam('method'))
                ->execute([
                    'payment' => $paymentDataObject,
                    'amount' => $quote->getGrandTotal(),
                ]);

            $checkData = $paymentDataObject
                ->getPayment()
                ->getAdditionalInformation(CheckHandler::THREEDSECURE_CHECK);

            $jsonResult->setData([
                'result' => $checkData['status']
            ]);
        } catch (\Exception $e) {
            $jsonResult
                ->setHttpResponseCode(400)
                ->setData([
                    'message' => $e->getMessage()
                ]);
        }

        return $jsonResult;
    }
}
