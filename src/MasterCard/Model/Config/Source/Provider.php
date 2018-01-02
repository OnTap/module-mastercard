<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Model\Config\Source;

class Provider implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Payment\Model\MethodInterface[]
     */
    protected $providers;

    /**
     * Provider constructor.
     * @param \Magento\Payment\Model\MethodInterface[] $providers
     */
    public function __construct(
        $providers = []
    ) {
        $this->providers = $providers;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ($this->providers as $provider) {
//            $active = (bool) $provider->isActive();
//            if (!$active) {
//                continue;
//            }
            $options[] = [
                'label' => $provider->getTitle(),
                'value' => $provider->getCode()
            ];
        }

        return $options;
    }
}
