<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Block\Adminhtml\Order\View\Info;

class Details extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Sales\Model\Order\Payment
     */
    public function getPayment()
    {
        $order = $this->registry->registry('current_order');
        return $order->getPayment();
    }

    /**
     * @return array|null
     */
    public function getRiskData()
    {
        return $this->getPayment()->getAdditionalInformation('risk');
    }

    /**
     * @param string $data
     * @param string $field
     * @return string|null
     */
    public function safeValue($data, $field = null)
    {
        if ($field === null) {
            return !empty($data) ? $this->escapeHtml($data) : '-';
        }
        return isset($data[$field]) ? $this->escapeHtml($data[$field]) : '-';
    }
}
