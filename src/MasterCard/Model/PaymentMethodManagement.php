<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Model;

use Magento\Framework\Exception\State\InvalidTransitionException;
use OnTap\MasterCard\Api\PaymentMethodManagementInterface;

class PaymentMethodManagement implements PaymentMethodManagementInterface
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Payment\Model\Checks\ZeroTotal
     */
    protected $zeroTotalValidator;

    /**
     * Constructor
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Payment\Model\Checks\ZeroTotal $zeroTotalValidator
     * @param \Magento\Payment\Model\MethodList $methodList
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Payment\Model\Checks\ZeroTotal $zeroTotalValidator,
        \Magento\Payment\Model\MethodList $methodList
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->zeroTotalValidator = $zeroTotalValidator;
    }

    /**
     * {@inheritdoc}
     */
    public function set($cartId, \Magento\Quote\Api\Data\PaymentInterface $method)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->get($cartId);

        $method->setChecks([
            \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_CHECKOUT,
            \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY,
            \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY,
            \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
        ]);
        $payment = $quote->getPayment();

        $data = $method->getData();
        $payment->importData($data);

        $quote->getBillingAddress()->setPaymentMethod($payment->getMethod());

//        if ($quote->isVirtual()) {
//            $quote->getBillingAddress()->setPaymentMethod($payment->getMethod());
//        } else {
//            // check if shipping address is set
//            if ($quote->getShippingAddress()->getCountryId() === null) {
//                throw new InvalidTransitionException(__('Shipping address is not set'));
//            }
//            $quote->getShippingAddress()->setPaymentMethod($payment->getMethod());
//        }
//        if (!$quote->isVirtual() && $quote->getShippingAddress()) {
//            $quote->getShippingAddress()->setCollectShippingRates(true);
//        }

        if (!$this->zeroTotalValidator->isApplicable($payment->getMethodInstance(), $quote)) {
            throw new InvalidTransitionException(__('The requested Payment Method is not available.'));
        }

        $quote->setTotalsCollectedFlag(false)->collectTotals()->save();
        return $quote->getPayment()->getId();
    }
}
