<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
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
