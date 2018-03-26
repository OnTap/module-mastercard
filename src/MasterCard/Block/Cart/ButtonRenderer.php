<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Block\Cart;

use Magento\Framework\View\Element\Template;
use Magento\Payment\Model\MethodList;
use Magento\Checkout\Model\Session as CheckoutSession;

class ButtonRenderer extends Template
{
    /**
     * @var MethodList
     */
    protected $methodList;

    /**
     * @var CheckoutSession
     */
    protected $session;

    /**
     * @var AmexButton
     */
    protected $buttonBlock;

    /**
     * AmexButton constructor.
     * @param CheckoutSession $session
     * @param MethodList $methodList
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        CheckoutSession $session,
        MethodList $methodList,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->methodList = $methodList;
        $this->session = $session;
    }

    /**
     * @inheritdoc
     */
    public function _prepareLayout()
    {
        $children = $this->getChildNames();
        $quote = $this->session->getQuote();
        $methods = $this->methodList->getAvailableMethods($quote);

        foreach ($methods as $method) {
            if (stripos($method->getCode(), '_amex') !== false && in_array($method->getCode(), $children)) {
                $this->buttonBlock = $this->getChildBlock($method->getCode());
                $this->buttonBlock->setMethod($method);
                break;
            }
        }

        return parent::_prepareLayout();
    }

    /**
     * @return bool
     */
    public function hasShortcuts()
    {
        return $this->buttonBlock !== null;
    }

    /**
     * @return string
     */
    public function renderButtonHtml()
    {
        return $this->buttonBlock->toHtml();
    }
}
