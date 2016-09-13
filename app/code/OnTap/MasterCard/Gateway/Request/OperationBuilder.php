<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\MasterCard\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class OperationBuilder implements BuilderInterface
{
    /**
     * @var string
     */
    protected $operation;

    /**
     * OperationBuilder constructor.
     * @param string $operation
     */
    public function __construct($operation = '')
    {
        $this->operation = $operation;
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
        return [
            'apiOperation' => $this->operation,
        ];
    }
}
