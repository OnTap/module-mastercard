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

namespace OnTap\MasterCard\Model;

use Magento\AdminNotification\Model\InboxFactory;
use Magento\Backend\App\ConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\CacheInterface;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;
use Zend_Http_Client;
use Exception;

class Feed
{
    const XML_FEED_URL_PATH = 'mpgs/adminnotification/feed_url';
    const XML_FREQUENCY_PATH = 'mpgs/adminnotification/frequency';
    const CACHE_KEY = 'mpgs_admin_notifications_lastcheck';

    /**
     * @var ConfigInterface
     */
    protected $backendConfig;

    /**
     * @var InboxFactory
     */
    protected $inboxFactory;

    /**
     * @var CurlFactory
     */
    protected $curlFactory;

    /**
     * @var DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var CacheInterface
     */
    protected $cacheManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Feed constructor.
     * @param ConfigInterface $backendConfig
     * @param InboxFactory $inboxFactory
     * @param CurlFactory $curlFactory
     * @param DeploymentConfig $deploymentConfig
     * @param ProductMetadataInterface $productMetadata
     * @param UrlInterface $urlBuilder
     * @param CacheInterface $cacheManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConfigInterface $backendConfig,
        InboxFactory $inboxFactory,
        CurlFactory $curlFactory,
        DeploymentConfig $deploymentConfig,
        ProductMetadataInterface $productMetadata,
        UrlInterface $urlBuilder,
        CacheInterface $cacheManager,
        LoggerInterface $logger
    ) {
        $this->backendConfig = $backendConfig;
        $this->inboxFactory = $inboxFactory;
        $this->curlFactory = $curlFactory;
        $this->deploymentConfig = $deploymentConfig;
        $this->productMetadata = $productMetadata;
        $this->urlBuilder = $urlBuilder;
        $this->cacheManager = $cacheManager;
        $this->logger = $logger;
    }

    /**
     * @return $this
     */
    public function checkUpdate()
    {
        if ($this->getFrequency() + (int) $this->getLastUpdate() > time()) {
            return $this;
        }

        $feedData = [];

        $feedXml = $this->getFeedData();

        if ($feedXml && property_exists($feedXml, 'channel') && property_exists($feedXml->channel, 'item')) {
            $installDate = strtotime(
                $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE)
            );
            foreach ($feedXml->channel->item as $item) {
                $itemPublicationDate = strtotime((string)$item->pubDate);
                if ($installDate <= $itemPublicationDate) {
                    $feedData[] = [
                        'severity' => (int)$item->severity,
                        'date_added' => date('Y-m-d H:i:s', $itemPublicationDate),
                        'title' => $this->escapeString($item->title),
                        'description' => $this->escapeString($item->description),
                        'url' => $this->escapeString($item->link),
                    ];
                }
            }

            if ($feedData) {
                $this->inboxFactory->create()->parse(array_reverse($feedData));
            }
        }
        $this->setLastUpdate();

        return $this;
    }

    /**
     * Retrieve feed data as XML element
     *
     * @return SimpleXMLElement|null
     */
    public function getFeedData()
    {
        $curl = $this->curlFactory->create();
        $curl->setConfig(
            [
                'timeout'   => 2,
                'useragent' => $this->productMetadata->getName()
                    . '/' . $this->productMetadata->getVersion()
                    . ' (' . $this->productMetadata->getEdition() . ')',
                'referer'   => $this->urlBuilder->getUrl('*/*/*')
            ]
        );
        $feedUrl = $this->backendConfig->getValue(self::XML_FEED_URL_PATH);
        $curl->write(Zend_Http_Client::GET, $feedUrl, '1.0');
        $data = $curl->read();
        $data = preg_split('/^\r?$/m', $data, 2);
        $data = trim($data[1]);
        $curl->close();

        try {
            $xml = new SimpleXMLElement($data);
        } catch (Exception $e) {
            $this->logger->warning(sprintf('Unable to parse admin-notification feed from %s', $feedUrl), [
                'exception' => $e
            ]);
            return null;
        }

        return $xml;
    }

    /**
     * Retrieve Update Frequency
     *
     * @return int
     */
    public function getFrequency()
    {
        return $this->backendConfig->getValue(self::XML_FREQUENCY_PATH) * 3600;
    }

    /**
     * Retrieve Last update time
     *
     * @return string
     */
    public function getLastUpdate()
    {
        return $this->cacheManager->load(self::CACHE_KEY);
    }

    /**
     * Set last update time (now)
     *
     * @return $this
     */
    public function setLastUpdate()
    {
        $this->cacheManager->save((string) time(), self::CACHE_KEY);
        return $this;
    }

    /**
     * Converts incoming data to string format and escapes special characters.
     *
     * @param SimpleXMLElement $data
     * @return string
     */
    private function escapeString(SimpleXMLElement $data)
    {
        // @codingStandardsIgnoreLine
        return htmlspecialchars((string)$data);
    }
}
