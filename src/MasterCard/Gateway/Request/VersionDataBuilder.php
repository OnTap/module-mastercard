<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Request;

use Magento\Framework\Module\ModuleListInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Composer\ComposerJsonFinder;

class VersionDataBuilder implements BuilderInterface
{
    const MODULE_NAME = 'OnTap_MasterCard';
    const VERSION_PATTERN = '%s_%s_%s__OnTap_%s';

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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function build(array $buildSubject)
    {
        $directoryList = new DirectoryList(BP);
        $composerJsonFinder = new ComposerJsonFinder($directoryList);
        $productMetadata = new \Magento\Framework\App\ProductMetadata($composerJsonFinder);

        $moduleInfo = $this->moduleList->getOne(static::MODULE_NAME);
        $versionString = sprintf(
            static::VERSION_PATTERN,
            $productMetadata->getName(),
            $productMetadata->getEdition(),
            $productMetadata->getVersion(),
            $moduleInfo['setup_version']
        );
        return [
            'partnerSolutionId' => $versionString
        ];
    }
}
