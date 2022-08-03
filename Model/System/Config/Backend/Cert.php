<?php
/**
 * Copyright (c) 2016-2022 Mastercard
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

namespace OnTap\MasterCard\Model\System\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value as ConfigValue;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use OnTap\MasterCard\Model\CertFactory;

/**
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class Cert extends ConfigValue
{
    /**
     * @var CertFactory
     */
    protected $certFactory;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var ReadInterface
     */
    protected $tmpDirectory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param CertFactory $certFactory
     * @param EncryptorInterface $encryptor
     * @param Filesystem $filesystem
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        CertFactory $certFactory,
        EncryptorInterface $encryptor,
        Filesystem $filesystem,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
        $this->certFactory = $certFactory;
        $this->encryptor = $encryptor;
        $this->tmpDirectory = $filesystem->getDirectoryRead(DirectoryList::SYS_TMP);
    }

    /**
     * @return $this
     *
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        /** @var string|array $value */
        $value = $this->getValue();

        if (!empty($value['value'])) {
            $this->setValue($value['value']);
        }

        if (is_array($value) && !empty($value['delete'])) {
            $this->setValue('');
            $this->certFactory
                ->create()
                ->loadByPathAndWebsite(
                    $this->getPath(),
                    $this->getScopeId()
                )
                ->delete();
        }

        if (empty($value['tmp_name']) && !empty($value['value'])) {
            $this->setValue($value['value']);

            return $this;
        } elseif (empty($value['tmp_name']) && !empty($value['value'])) {
            $this->setValue('');

            return $this;
        }

        $tmpPath = $this->tmpDirectory->getRelativePath($value['tmp_name']);

        if ($tmpPath && $this->tmpDirectory->isExist($tmpPath)) {
            if (!$this->tmpDirectory->stat($tmpPath)['size']) {
                throw new LocalizedException(
                    __('The PayPal certificate file is empty.')
                );
            }
            $this->setValue($value['name']);
            $content = $this->encryptor->encrypt(
                $this->tmpDirectory->readFile($tmpPath)
            );
            $this->certFactory
                ->create()
                ->loadByPathAndWebsite(
                    $this->getPath(),
                    $this->getScopeId()
                )
                ->setContent($content)
                ->save();
        }

        return $this;
    }

    /**
     * Process object after delete data
     *
     * @return $this
     *
     * @throws LocalizedException
     */
    public function afterDelete()
    {
        $this->certFactory
            ->create()
            ->loadByPathAndWebsite(
                $this->getPath(),
                $this->getScopeId()
            )
            ->delete();

        return $this;
    }
}
