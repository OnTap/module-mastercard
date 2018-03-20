<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Model;

use Magento\Framework\DataObject;
use OnTap\MasterCard\Api\Data\SessionDataInterface;

class Session extends DataObject implements SessionDataInterface
{
    /**
     * @inheritdoc
     */
    public function setSessionId($id)
    {
        $this->setData(SessionDataInterface::SESSION_ID, $id);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSessionId()
    {
        return $this->getData(SessionDataInterface::SESSION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setSessionVersion($version)
    {
        $this->setData(SessionDataInterface::SESSION_VERSION, $version);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSessionVersion()
    {
        return $this->getData(SessionDataInterface::SESSION_VERSION);
    }
}
