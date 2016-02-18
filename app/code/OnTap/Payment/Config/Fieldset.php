<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace OnTap\Payment\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Fieldset extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getChildrenElementsHtml(AbstractElement $element)
    {
        $elements = '';
        foreach ($element->getElements() as $field) {
            if ($field instanceof \Magento\Framework\Data\Form\Element\Fieldset) {
                $elements .= '<tr id="row_' . $field->getHtmlId() . '">'
                    . '<td colspan="4">' . $field->toHtml() . '</td></tr>';
            } else {
                $elements .= $field->toHtml();
            }
        }
        return $elements;
    }
}
