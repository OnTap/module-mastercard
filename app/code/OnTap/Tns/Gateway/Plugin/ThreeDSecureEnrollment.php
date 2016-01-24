<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Plugin;

use OnTap\Tns\Gateway\Command\GatewayCommand;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Command\CommandPool;

class ThreeDSecureEnrollment
{
    const CHECK_THREE_D_SECURE = '3ds_enrollment';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var CommandPool
     */
    private $commandPool;

    /**
     * Check3DSecureEnrollment constructor.
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
        $isThreeDSecure = $this->config->getValue('three_d_secure') === '1';

        if ($isThreeDSecure) {
            $this->commandPool
                ->get(self::CHECK_THREE_D_SECURE)
                ->execute($commandSubject);
        }

        return [$commandSubject, ];
    }
}
