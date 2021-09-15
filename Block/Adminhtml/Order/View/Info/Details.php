<?php
/**
 * Copyright (c) 2016-2021 Mastercard
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OnTap\MasterCard\Block\Adminhtml\Order\View\Info;

use Magento\Framework\View\Element\Template;

class Details extends Template
{
    /**
     * Implement in subclasses
     * @var array
     */
    protected $applicableMethods = [];

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order = null;

    /**
     * AchDetails constructor.
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(Template\Context $context, array $data = [])
    {
        $this->escaper = $context->getEscaper();
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if ($this->order === null) {
            /** @var \Magento\Sales\Block\Adminhtml\Order\View\Tab\Info $parent */
            $parent = $this->getLayout()->getBlock('order_tab_info');
            $this->order = $parent->getOrder();
        }
        return $this->order;
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderPaymentInterface
     */
    public function getPayment()
    {
        return $this->getOrder()->getPayment();
    }

    /**
     * @param array|string $data
     * @param string|null $field
     * @return array|string
     */
    public function safeValue($data, $field = null)
    {
        if ($field === null) {
            return !empty($data) ? $this->escaper->escapeHtml($data) : '-';
        }
        if (is_array($data)) {
            return isset($data[$field]) ? $this->escaper->escapeHtml($data[$field]) : '-';
        }
        return '-';
    }

    /**
     * @return array|string|null
     */
    public function getRiskData()
    {
        $info = $this->getPayment()->getAdditionalInformation();
        return $info['risk'] ?? null;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if (!in_array($this->getPayment()->getMethod(), $this->applicableMethods)) {
            return '';
        }
        return parent::toHtml();
    }
}
