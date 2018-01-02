<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Controller\Session;

use Magento\Framework\App\Action\Context;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Checkout\Api\PaymentInformationManagementInterface;

abstract class UpdateWallet extends \Magento\Framework\App\Action\Action
{
    /**
     * @var CommandPoolInterface
     */
    protected $commandPool;

    /**
     * @var PaymentDataObjectFactory
     */
    protected $paymentDataObjectFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var PaymentInformationManagementInterface
     */
    protected $paymentInformationManagement;

    /**
     * UpdateWallet constructor.
     * @param Context $context
     * @param CommandPoolInterface $commandPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param PaymentInformationManagementInterface $paymentInformationManagement
     */
    public function __construct(
        Context $context,
        CommandPoolInterface $commandPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        PaymentInformationManagementInterface $paymentInformationManagement
    ) {
        parent::__construct($context);
        $this->commandPool = $commandPool;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->checkoutSession = $checkoutSession;
        $this->paymentInformationManagement = $paymentInformationManagement;
    }

    /**
     * @return string
     */
    abstract protected function getMethod();

    /**
     * @throws \Exception
     */
    abstract public function execute();
}
