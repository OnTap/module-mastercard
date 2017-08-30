<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\ConfigInterface;

class ExtraDataBuilder implements BuilderInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var string
     */
    protected $field;

    /**
     * ExtraDataBuilder constructor.
     * @param ConfigInterface $config
     * @param string $field
     */
    public function __construct(ConfigInterface $config, $field = '')
    {
        $this->config = $config;
        $this->field = $field;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $value  = $this->config->getValue($this->field);
        return \Zend_Json::decode($value);
    }
}
