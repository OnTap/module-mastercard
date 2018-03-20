<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Quote\Api\CartRepositoryInterface;

class AddressDataHandler implements HandlerInterface
{
    /**
     * @var \Magento\Quote\Api\Data\AddressInterfaceFactory
     */
    protected $addressFactory;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * AddressDataHandler constructor.
     * @param CartRepositoryInterface $cartRepository
     * @param \Magento\Quote\Api\Data\AddressInterfaceFactory $addressFactory
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        \Magento\Quote\Api\Data\AddressInterfaceFactory $addressFactory
    ) {
        $this->addressFactory = $addressFactory;
        $this->cartRepository = $cartRepository;
    }

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $payment = SubjectReader::readPayment($handlingSubject);

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $payment->getPayment()->getQuote();

        $customer = $response['customer'];
        $billing = $response['billing']['address'];

        /** @var \Magento\Quote\Model\Quote\Address $addressDO */
        $addressDO = $this->addressFactory->create();
        $addressDO->setData([
            'firstname' => $customer['firstName'],
            'lastname' => $customer['lastName'],
            'city' => $billing['city'],
            'country' => $billing['country'],
            'postcode' => $billing['postcodeZip'],
            'street' => $billing['street'],
            'telephone' => $customer['mobilePhone'],
            'email' => $customer['email']
        ]);

        $quote->removeAddress($quote->getBillingAddress()->getId());
        $quote->setBillingAddress($addressDO);
        $quote->setDataChanges(true);

        $this->cartRepository->save($quote);
    }
}
