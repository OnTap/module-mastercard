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

namespace OnTap\MasterCard\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTime as CoreDateTime;
use OnTap\MasterCard\Model\Cert as CertModel;

class Cert extends AbstractDb
{
    /**
     * @var CoreDateTime
     */
    protected $coreDateTime;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @param Context $context
     * @param CoreDateTime $coreDate
     * @param DateTime $dateTime
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        CoreDateTime $coreDate,
        DateTime $dateTime,
        $connectionName = null
    ) {
        $this->coreDateTime = $coreDate;
        $this->dateTime = $dateTime;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mastercard_config_files', 'cert_id');
    }

    /**
     * Set date of last update
     *
     * @param AbstractModel $object
     *
     * @return AbstractDb
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $object->setUpdatedAt(
            $this->dateTime->formatDate(
                $this->coreDateTime->gmtDate()
            )
        );

        return parent::_beforeSave($object);
    }

    /**
     * Load model by website id
     *
     * @param CertModel $object
     * @param bool $strictLoad
     *
     * @return CertModel
     * @throws LocalizedException
     */
    public function loadByPathAndWebsite($object, $strictLoad = true)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(['main_table' => $this->getMainTable()]);

        if ($strictLoad) {
            $select
                ->where('main_table.path = ?', $object->getPath())
                ->where('main_table.website_id = ?', $object->getWebsiteId());
        } else {
            $select
                ->where('main_table.path = ?', $object->getPath())
                ->where('main_table.website_id IN(0, ?)', $object->getWebsiteId())
                ->order('main_table.website_id DESC')
                ->limit(1);
        }

        $data = $connection->fetchRow($select);
        if ($data) {
            $object->setData($data);
        }

        return $object;
    }
}
