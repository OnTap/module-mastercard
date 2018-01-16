<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Controller;

use Magento\Framework\App\Action\Context;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;

abstract class Wallet extends \Magento\Framework\App\Action\Action
{
    const GUEST_EMAIL = 'guestEmail';
    const QUOTE_ID = 'quoteId';

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
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param PaymentInformationManagementInterface $paymentInformationManagement
     * @param GuestPaymentInformationManagementInterface $guestPaymentInformationManagement
     */
    public function __construct(
        Context $context,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        PaymentInformationManagementInterface $paymentInformationManagement,
        GuestPaymentInformationManagementInterface $guestPaymentInformationManagement
    ) {
        parent::__construct($context);
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->checkoutSession = $checkoutSession;
        $this->paymentInformationManagement = $paymentInformationManagement;
        $this->guestPaymentInformationManagement = $guestPaymentInformationManagement;
        $this->customerSession = $customerSession;
    }

    /**
     * @throws \Exception
     */
    abstract public function execute();
}
