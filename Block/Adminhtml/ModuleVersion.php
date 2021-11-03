<?php
/**
 * Copyright (c) 2016-2021 Mastercard
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

namespace OnTap\MasterCard\Block\Adminhtml;

use Magento\Config\Block\System\Config\Form\Field\Heading;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Context;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Exception;

class ModuleVersion extends Heading
{
    const CACHE_KEY = 'MPGS_MODULE_VERSION';

    /**
     * @var ReadFactory
     */
    protected $readFactory;

    /**
     * @var ComponentRegistrarInterface
     */
    protected $componentRegistrar;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Json
     */
    private $json;

    /**
     * ModuleVersion constructor.
     * @param Context $context
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param ReadFactory $readFactory
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        Context $context,
        ComponentRegistrarInterface $componentRegistrar,
        ReadFactory $readFactory,
        Json $json,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->context = $context;
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;
        $this->json = $json;
    }

    /**
     * @param string $moduleName
     * @return string
     */
    protected function getVersionInfo($moduleName)
    {
        $cache = $this->context->getCache();
        $version = $cache->load(self::CACHE_KEY);

        if (!$version) {
            $path = $this->componentRegistrar->getPath(
                ComponentRegistrar::MODULE,
                $moduleName
            );

            $dir = $this->readFactory->create($path);

            try {
                $jsonData = $dir->readFile('composer.json');
                $data = $this->json->unserialize($jsonData);
            } catch (Exception $e) {
                $this->_logger->error('Module read error', [
                    'exception' => $e
                ]);
                $data = [];
            }

            $version = isset($data['version']) ? $data['version'] : 'unknown';
            $cache->save($version, self::CACHE_KEY);
        }

        return (string) __('Module version: %1', $version);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        return sprintf(
            '<tr class="system-fieldset-sub-head" id="row_%s">'
            . '<td colspan="5"><div style="background-color:#fff;">%s</div></td>'
            . '</tr>',
            $element->getHtmlId(),
            $this->getVersionInfo('OnTap_MasterCard')
        );
    }
}
