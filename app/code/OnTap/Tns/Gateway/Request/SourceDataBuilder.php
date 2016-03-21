<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Framework\App\State;

class SourceDataBuilder implements BuilderInterface
{
    const TXN_SOURCE_FRONTEND = 'INTERNET';
    const TXN_SOURCE_ADMIN = 'MOTO';

    /**
     * @var State
     */
    protected $state;

    /**
     * TransactionDataBuilder constructor.
     * @param State $state
     */
    public function __construct(State $state)
    {
        $this->state = $state;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function build(array $buildSubject)
    {
        //$paymentDO = SubjectReader::readPayment($buildSubject);

        $source = static::TXN_SOURCE_FRONTEND;

        /*$isAuth = is_object($paymentDO->getPayment()->getAuthorizationTransaction());
        if (!$isAuth && $this->state->getAreaCode() === \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $source = static::TXN_SOURCE_ADMIN;
        }*/

        return [
            'transaction' => [
                'source' => $source,
            ]
        ];
    }
}
