<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Gateway\Config\Wallet;

class CommandProvider implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $commandMap;

    /**
     * CommandProvider constructor.
     * @param array $commandMap
     */
    public function __construct($commandMap)
    {
        $this->commandMap = $commandMap;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return $this->commandMap;
    }

    /**
     * @param string $type
     * @return string
     */
    public function getByType($type)
    {
        if (!isset($this->commandMap[$type])) {
            throw new \RuntimeException(sprintf('%s could not be mapped into a command', $type));
        }
        return $this->commandMap[$type];
    }
}
