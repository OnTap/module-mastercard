<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OnTap\Tns\Gateway\Request;

use Magento\Framework\AppInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;

class VersionDataBuilder implements BuilderInterface
{
    const MODULE_NAME = 'OnTap_Tns';
    const VERSION_PATTERN = 'MAGENTO_%s_ONTAP_%s';

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * VersionDataBuilder constructor.
     * @param ModuleListInterface $moduleList
     */
    public function __construct(ModuleListInterface $moduleList)
    {
        $this->moduleList = $moduleList;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $moduleInfo = $this->moduleList->getOne(static::MODULE_NAME);
        $versionString = sprintf(static::VERSION_PATTERN, AppInterface::VERSION, $moduleInfo['setup_version']);
        return [
            'partnerSolutionId' => $versionString
        ];
    }
}
