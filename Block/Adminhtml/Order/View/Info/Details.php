<?php
/**
 * Copyright (c) 2016-2019 Mastercard
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
     * @param string|array $data
     * @param string|null $field
     * @return string|null
     */
    public function safeValue($data, $field = null)
    {
        if ($field === null) {
            return !empty($data) ? $this->escapeHtml($data) : '-';
        }
        if (is_array($data)) {
            return isset($data[$field]) ? $this->escapeHtml($data[$field]) : '-';
        }
        return '-';
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if (!in_array($this->getPayment()->getMethod(), [
            \OnTap\MasterCard\Model\Ui\Hpf\ConfigProvider::METHOD_CODE,
            \OnTap\MasterCard\Model\Ui\Hosted\ConfigProvider::METHOD_CODE,
        ])) {
            return '';
        }
        return parent::toHtml();
    }
}
