<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Controller\Session;

use Magento\Framework\App\Action\Context;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;

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
     * @var GuestPaymentInformationManagementInterface
     */
    protected $guestPaymentInformationManagement;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * UpdateWallet constructor.
     * @param Context $context
     * @param CommandPoolInterface $commandPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param PaymentInformationManagementInterface $paymentInformationManagement
     * @param GuestPaymentInformationManagementInterface $guestPaymentInformationManagement
     */
    public function __construct(
        Context $context,
        CommandPoolInterface $commandPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        PaymentInformationManagementInterface $paymentInformationManagement,
        GuestPaymentInformationManagementInterface $guestPaymentInformationManagement
    ) {
        parent::__construct($context);
        $this->commandPool = $commandPool;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->checkoutSession = $checkoutSession;
        $this->paymentInformationManagement = $paymentInformationManagement;
        $this->guestPaymentInformationManagement = $guestPaymentInformationManagement;
        $this->customerSession = $customerSession;
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
