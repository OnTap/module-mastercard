<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Block\Customer;

use OnTap\MasterCard\Model\Ui\ConfigProvider;
use Magento\Framework\View\Element\Template;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\AbstractCardRenderer;

/**
 * Class CardRenderer
 * @package OnTap\MasterCard\Block\Customer
 * @method array getAvailableProviders()
 */
class CardRenderer extends AbstractCardRenderer
{
    /**
     * Can render specified token
     *
     * @param PaymentTokenInterface $token
     * @return boolean
     */
    public function canRender(PaymentTokenInterface $token)
    {
        return in_array($token->getPaymentMethodCode(), array_values($this->getAvailableProviders()));
    }

    /**
     * @return string
     */
    public function getNumberLast4Digits()
    {
        return substr($this->getTokenDetails()['cc_number'], -4);
    }

    /**
     * @return string
     */
    public function getExpDate()
    {
        return $this->getTokenDetails()['cc_expr_month'] . '/' . $this->getTokenDetails()['cc_expr_year'];
    }

    /**
     * @return string
     */
    public function getIconUrl()
    {
        return $this->getIconForType($this->getTokenDetails()['type'])['url'];
    }

    /**
     * @return int
     */
    public function getIconHeight()
    {
        return $this->getIconForType($this->getTokenDetails()['type'])['height'];
    }

    /**
     * @return int
     */
    public function getIconWidth()
    {
        return $this->getIconForType($this->getTokenDetails()['type'])['width'];
    }
}
