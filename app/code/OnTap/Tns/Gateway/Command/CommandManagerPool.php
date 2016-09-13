<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace OnTap\Tns\Gateway\Command;

use Magento\Framework\Exception\NotFoundException;
use OnTap\Tns\ObjectManager\TMapFactory;

/**
 * Class CommandManagerPool
 * @api
 */
class CommandManagerPool implements CommandManagerPoolInterface
{
    /**
     * @var CommandManagerInterface[] | \Magento\Framework\ObjectManager\TMap
     */
    private $executors;

    /**
     * @param TMapFactory $tmapFactory
     * @param array $executors
     */
    public function __construct(
        TMapFactory $tmapFactory,
        array $executors = []
    ) {
        $this->executors = $tmapFactory->createSharedObjectsMap(
            [
                'array' => $executors,
                'type' => CommandManagerInterface::class
            ]
        );
    }

    /**
     * Returns Command executor for defined payment provider
     *
     * @param string $paymentProviderCode
     * @return CommandManagerInterface
     * @throws NotFoundException
     */
    public function get($paymentProviderCode)
    {
        if (!isset($this->executors[$paymentProviderCode])) {
            throw new NotFoundException(
                __('Command Executor for %1 is not defined.', $paymentProviderCode)
            );
        }

        return $this->executors[$paymentProviderCode];
    }
}
