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
declare(strict_types=1);
namespace OnTap\MasterCard\Setup\Patch\Data;

use Exception;
use Magento\Config\Model\ResourceModel\Config\Data as ConfigResource;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
* Patch is mechanism, that allows to do atomic upgrade data changes
*/
class UninstallDirectMethod implements
    DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ConfigResource
     */
    private $configResource;

    /**
     * UninstallDirectMethod constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CollectionFactory $collectionFactory
     * @param ConfigResource $configResource
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CollectionFactory $collectionFactory,
        ConfigResource $configResource
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->collectionFactory = $collectionFactory;
        $this->configResource = $configResource;
    }

    /**
     * Do Upgrade
     *
     * @return void
     * @throws Exception
     */
    public function apply()
    {
        $this->removeConfigByPath('payment/tns_direct');
        $this->removeConfigByPath('payment/tns_direct_vault');
    }

    /**
     * @param string $path
     * @throws Exception
     */
    private function removeConfigByPath($path)
    {
        $configCollection = $this->collectionFactory->create();
        $configCollection->addPathFilter($path);
        foreach ($configCollection->getItems() as $configItem) {
            $this->configResource->delete($configItem);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }
}
