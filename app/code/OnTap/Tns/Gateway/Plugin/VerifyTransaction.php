<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Plugin;

use OnTap\Tns\Gateway\Command\GatewayCommand;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Command\CommandPool;

class VerifyTransaction
{
    const VERIFY_TXN = 'verify';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var CommandPool
     */
    private $commandPool;

    /**
     * VerifyTransaction constructor.
     * @param ConfigInterface $config
     * @param CommandPool $commandPool
     */
    public function __construct(
        ConfigInterface $config,
        CommandPool $commandPool
    ) {
        $this->config = $config;
        $this->commandPool = $commandPool;
    }

    /**
     * @param GatewayCommand $subject
     * @param array $commandSubject
     * @return array
     * @throws \Magento\Framework\Exception\NotFoundException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(GatewayCommand $subject, array $commandSubject)
    {
        if (
            $this->config->getValue('avs') === '1' ||
            $this->config->getValue('csc_rules') === '1') {

            $this->commandPool
                ->get(self::VERIFY_TXN)
                ->execute($commandSubject);
        }

        return [$commandSubject, ];
    }
}
