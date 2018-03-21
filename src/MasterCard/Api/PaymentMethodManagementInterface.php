<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Api;

interface PaymentMethodManagementInterface
{
    /**
     * @param int $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $method
     * @return int
     */
    public function set($cartId, \Magento\Quote\Api\Data\PaymentInterface $method);
}
