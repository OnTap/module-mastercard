<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Composer\ComposerJsonFinder;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Component\ComponentRegistrar;
use Composer\IO\BufferIO;

class VersionDataBuilder implements BuilderInterface
{
    const MODULE_NAME = 'OnTap_MasterCard';
    const VERSION_PATTERN = '%s_%s_%s__%s';

    /**
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * VersionDataBuilder constructor.
     * @param ComponentRegistrarInterface $componentRegistrar
     */
    public function __construct(ComponentRegistrarInterface $componentRegistrar)
    {
        $this->componentRegistrar = $componentRegistrar;
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

        $path = $this->componentRegistrar->getPath(
            ComponentRegistrar::MODULE,
            self::MODULE_NAME
        );
        $directoryList = new DirectoryList($path);
        $composerJsonFinder = new ComposerJsonFinder($directoryList);
        $moduleMetaData = \Composer\Factory::create(
            new BufferIO(),
            $composerJsonFinder->findComposerJson()
        );

        $versionString = sprintf(
            static::VERSION_PATTERN,
            $productMetadata->getName(),
            $productMetadata->getEdition(),
            $productMetadata->getVersion(),
            $moduleMetaData->getPackage()->getVersion()
        );

        return [
            'partnerSolutionId' => $versionString
        ];
    }
}
