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

namespace OnTap\MasterCard\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use OnTap\MasterCard\Model\ResourceModel\Cert as CertResource;

/**
 * @method Cert setContent(string $cert)
 * @method string getContent()
 */
class Cert extends AbstractModel
{
    /**
     * Certificate base path
     */
    const BASEPATH_CERT = 'cert/mastercard/';

    /**
     * @var WriteInterface
     */
    protected $varDirectory;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Filesystem $filesystem
     * @param EncryptorInterface $encryptor
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     *
     * @throws FileSystemException
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Filesystem $filesystem,
        EncryptorInterface $encryptor,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->encryptor = $encryptor;

        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(CertResource::class);
    }

    /**
     * @param string $path
     * @param string|int $websiteId
     * @param boolean $strictLoad
     *
     * @return Cert
     * @throws LocalizedException
     */
    public function loadByPathAndWebsite($path, $websiteId, $strictLoad = true)
    {
        $this->setPath($path);
        $this->setWebsiteId($websiteId);

        /** @var CertResource $resource */
        $resource = $this->_getResource();
        $resource->loadByPathAndWebsite($this, $strictLoad);

        return $this;
    }

    /**
     * Get path to PayPal certificate file, if file does not exist try to create it
     *
     * @return string
     *
     * @throws LocalizedException
     */
    public function getCertPath()
    {
        if (!$this->getContent()) {
            throw new LocalizedException(
                __('The Mastercard certificate does not exist.')
            );
        }

        $certFileName = sprintf(
            'cert_%s_%s_%s.pem',
            str_replace("/", "_", $this->getPath()),
            $this->getWebsiteId(),
            strtotime($this->getUpdatedAt())
        );
        $certFile = self::BASEPATH_CERT . $certFileName;

        if (!$this->varDirectory->isExist($certFile)) {
            $this->createCertFile($certFile);
        }

        return $this->varDirectory->getAbsolutePath($certFile);
    }

    /**
     * Create physical certificate file based on DB data
     *
     * @param string $file
     *
     * @return void
     * @throws FileSystemException
     */
    protected function createCertFile($file)
    {
        if ($this->varDirectory->isDirectory(self::BASEPATH_CERT)) {
            $this->removeOutdatedCertFile();
        }
        $this->varDirectory->writeFile(
            $file,
            $this->encryptor->decrypt(
                $this->getContent()
            )
        );
    }

    /**
     * Check and remove outdated certificate file by website
     *
     * @return void
     *
     * @throws FileSystemException
     */
    protected function removeOutdatedCertFile()
    {
        $pattern = sprintf(
            'cert_%s_%s*',
            $this->getPath(),
            $this->getWebsiteId()
        );
        $entries = $this->varDirectory->search($pattern, self::BASEPATH_CERT);
        foreach ($entries as $entry) {
            $this->varDirectory->delete($entry);
        }
    }

    /**
     * Delete assigned certificate file after delete object
     *
     * @return Cert
     *
     * @throws FileSystemException
     */
    public function afterDelete()
    {
        $this->removeOutdatedCertFile();

        return $this;
    }
}
