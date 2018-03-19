<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Block\Cart;

use Magento\Framework\View\Element\Template;
use Magento\Payment\Model\MethodInterface;
use OnTap\MasterCard\Model\Method\WalletInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

class AmexButton extends Template
{
    /**
     * @var WalletInterface|MethodInterface
     */
    protected $method;

    /**
     * @var \Magento\Quote\Api\Data\ShippingMethodInterface|null
     */
    protected $shipping;

    /**
     * @var CheckoutSession
     */
    protected $session;

    /**
     * AmexButton constructor.
     * @param CheckoutSession $session
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        CheckoutSession $session,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->session = $session;
    }

    /**
     * @param WalletInterface|MethodInterface $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @return CheckoutSession
     */
    protected function getSession()
    {
        return $this->session;
    }
}
