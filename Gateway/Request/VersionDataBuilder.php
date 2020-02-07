<?php
/**
 * Copyright (c) 2016-2019 Mastercard
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OnTap\MasterCard\Gateway\Request;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Composer\ComposerJsonFinder;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Framework\Composer\ComposerFactory;

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
     * @throws \Exception
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

        $composerFactory = new ComposerFactory($directoryList, $composerJsonFinder);
        $moduleMetaData = $composerFactory->create();

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
