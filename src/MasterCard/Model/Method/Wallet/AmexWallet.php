<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Model\Method\Wallet;

use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\MethodInterface;
use OnTap\MasterCard\Model\Config\Source\Provider as SourceProvider;

class AmexWallet implements WalletInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var ValueHandlerPoolInterface
     */
    protected $valueHandlerPool;

    /**
     * @var MethodInterface
     */
    protected $provider;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var int
     */
    protected $storeId;

    /**
     * @var SourceProvider
     */
    protected $sourceProvider;

    /**
     * AmexWallet constructor.
     * @param SourceProvider $sourceProvider
     * @param ConfigInterface $config
     * @param ValueHandlerPoolInterface $valueHandlerPool
     * @param $code
     */
    public function __construct(
        SourceProvider $sourceProvider,
        ConfigInterface $config,
        ValueHandlerPoolInterface $valueHandlerPool,
        $code
    ) {
        $this->config = $config;
        $this->valueHandlerPool = $valueHandlerPool;
        $this->code = $code;
        $this->sourceProvider = $sourceProvider;
    }

    /**
     * @return string
     */
    public function getProviderCode()
    {
        return $this->getProvider()->getCode();
    }

    /**
     * Unifies configured value handling logic
     *
     * @param string $field
     * @param null $storeId
     * @return mixed
     */
    protected function getConfiguredValue($field, $storeId = null)
    {
        $handler = $this->valueHandlerPool->get($field);
        $subject = ['field' => $field];

        return $handler->handle($subject, $storeId ?: $this->getStore());
    }

    /**
     * @return MethodInterface
     */
    public function getProvider()
    {
        $provider = $this->config->getValue('provider');
        return $this->sourceProvider->getProvider($provider);
    }

    /**
     * Retrieve payment method code
     *
     * @return string
     *
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Retrieve block type for method form generation
     *
     * @return string
     *
     * @deprecated 100.0.2
     */
    public function getFormBlockType()
    {
        // TODO: Implement getFormBlockType() method.
    }

    /**
     * Retrieve payment method title
     *
     * @return string
     *
     */
    public function getTitle()
    {
        return $this->config->getValue('title');
    }

    /**
     * Store id setter
     * @param int $storeId
     * @return void
     */
    public function setStore($storeId)
    {
        $this->storeId = (int)$storeId;
    }

    /**
     * Store id getter
     * @return int
     */
    public function getStore()
    {
        return $this->storeId;
    }

    /**
     * Check order availability
     *
     * @return bool
     *
     */
    public function canOrder()
    {
        return false;
    }

    /**
     * Check authorize availability
     *
     * @return bool
     *
     */
    public function canAuthorize()
    {
        return true;
    }

    /**
     * Check capture availability
     *
     * @return bool
     *
     */
    public function canCapture()
    {
        return true;
    }

    /**
     * Check partial capture availability
     *
     * @return bool
     *
     */
    public function canCapturePartial()
    {
        return true;
    }

    /**
     * Check whether capture can be performed once and no further capture possible
     *
     * @return bool
     *
     */
    public function canCaptureOnce()
    {
        return true;
    }

    /**
     * Check refund availability
     *
     * @return bool
     *
     */
    public function canRefund()
    {
        return true;
    }

    /**
     * Check partial refund availability for invoice
     *
     * @return bool
     *
     */
    public function canRefundPartialPerInvoice()
    {
        return true;
    }

    /**
     * Check void availability
     * @return bool
     *
     */
    public function canVoid()
    {
        return true;
    }

    /**
     * Using internal pages for input payment data
     * Can be used in admin
     *
     * @return bool
     */
    public function canUseInternal()
    {
        return false;
    }

    /**
     * Can be used in regular checkout
     *
     * @return bool
     */
    public function canUseCheckout()
    {
        return true;
    }

    /**
     * Can be edit order (renew order)
     *
     * @return bool
     *
     */
    public function canEdit()
    {
        return false;
    }

    /**
     * Check fetch transaction info availability
     *
     * @return bool
     *
     */
    public function canFetchTransactionInfo()
    {
        return true;
    }

    /**
     * Fetch transaction info
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param string $transactionId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     */
    public function fetchTransactionInfo(\Magento\Payment\Model\InfoInterface $payment, $transactionId)
    {
        // TODO: Implement fetchTransactionInfo() method.
    }

    /**
     * Retrieve payment system relation flag
     *
     * @return bool
     *
     */
    public function isGateway()
    {
        return true;
    }

    /**
     * Retrieve payment method online/offline flag
     *
     * @return bool
     *
     */
    public function isOffline()
    {
        return false;
    }

    /**
     * Flag if we need to run payment initialize while order place
     *
     * @return bool
     *
     */
    public function isInitializeNeeded()
    {
        // TODO: Implement isInitializeNeeded() method.
    }

    /**
     * To check billing country is allowed for the payment method
     *
     * @param string $country
     * @return bool
     */
    public function canUseForCountry($country)
    {
        return true;
    }

    /**
     * Check method for processing with base currency
     *
     * @param string $currencyCode
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function canUseForCurrency($currencyCode)
    {
        return true;
    }

    /**
     * Retrieve block type for display method information
     *
     * @return string
     *
     * @deprecated 100.0.2
     */
    public function getInfoBlockType()
    {
        // TODO: Implement getInfoBlockType() method.
    }

    /**
     * Retrieve payment information model object
     *
     * @return \Magento\Payment\Model\InfoInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @deprecated 100.0.2
     */
    public function getInfoInstance()
    {
        // TODO: Implement getInfoInstance() method.
    }

    /**
     * Retrieve payment information model object
     *
     * @param \Magento\Payment\Model\InfoInterface $info
     * @return void
     *
     * @deprecated 100.0.2
     */
    public function setInfoInstance(\Magento\Payment\Model\InfoInterface $info)
    {
        // TODO: Implement setInfoInstance() method.
    }

    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     */
    public function validate()
    {
        return $this;
    }

    /**
     * Order payment method
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     *
     */
    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        // TODO: Implement order() method.
    }

    /**
     * Authorize payment method
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     *
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        // TODO: Implement authorize() method.
    }

    /**
     * Capture payment method
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     *
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        // TODO: Implement capture() method.
    }

    /**
     * Refund specified amount for payment
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     *
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        // TODO: Implement refund() method.
    }

    /**
     * Cancel payment method
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return $this
     *
     */
    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        // TODO: Implement cancel() method.
    }

    /**
     * Void payment method
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return $this
     *
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        // TODO: Implement void() method.
    }

    /**
     * Whether this method can accept or deny payment
     * @return bool
     *
     */
    public function canReviewPayment()
    {
        // TODO: Implement canReviewPayment() method.
    }

    /**
     * Attempt to accept a payment that us under review
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return false
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     */
    public function acceptPayment(\Magento\Payment\Model\InfoInterface $payment)
    {
        // TODO: Implement acceptPayment() method.
    }

    /**
     * Attempt to deny a payment that us under review
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return false
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     */
    public function denyPayment(\Magento\Payment\Model\InfoInterface $payment)
    {
        // TODO: Implement denyPayment() method.
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|string|null|\Magento\Store\Model\Store $storeId
     *
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        // TODO: Implement getConfigData() method.
    }

    /**
     * Assign data to info model instance
     *
     * @param \Magento\Framework\DataObject $data
     * @return $this
     *
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        // TODO: Implement assignData() method.
    }

    /**
     * Check whether payment method can be used
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     *
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return true;
    }

    /**
     * Is active
     *
     * @param int|null $storeId
     * @return bool
     *
     */
    public function isActive($storeId = null)
    {
        return (bool) $this->config->getValue('active', $storeId);
    }

    /**
     * Method that will be executed instead of authorize or capture
     * if flag isInitializeNeeded set to true
     *
     * @param string $paymentAction
     * @param object $stateObject
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     */
    public function initialize($paymentAction, $stateObject)
    {
        return $this;
    }

    /**
     * Get config payment action url
     * Used to universalize payment actions when processing payment place
     *
     * @return string
     *
     */
    public function getConfigPaymentAction()
    {
        // TODO: Implement getConfigPaymentAction() method.
    }
}