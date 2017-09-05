<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Block\Adminhtml\Api;

use Magento\Backend\Block\Context;

class Version extends \Magento\Config\Block\System\Config\Form\Field\Heading
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var string
     */
    protected $methodCode;

    /**
     * Version constructor.
     * @param Context $context
     * @param string $methodCode
     * @param array $data
     */
    public function __construct(
        Context $context,
        $methodCode = '',
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->context = $context;
        $this->methodCode = $methodCode;
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    protected function getVersionInfo()
    {
        return __('This module uses API version %1', $this->getVersionNumber());
    }

    /**
     * @return string|null
     */
    protected function getVersionNumber()
    {
        return $this->context->getScopeConfig()->getValue(
            sprintf('payment/%s/api_version', $this->methodCode)
        );
    }

    /**
     * Render element html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return sprintf(
            '<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5"><div style="background-color:#eee;padding:1em;border:1px solid #ddd;">%s</div></td></tr>',
            $element->getHtmlId(),
            $this->getVersionInfo()
        );
    }
}
