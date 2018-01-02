<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Model\Config\Source;


class Env implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $environments;

    /**
     * Env constructor.
     * @param array $environments
     */
    public function __construct(
        $environments = []
    ) {
        $this->environments = $environments;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->environments as $env => $title) {
            $options[] = [
                'label' => $title,
                'value' => $env
            ];
        }
        return $options;
    }
}
