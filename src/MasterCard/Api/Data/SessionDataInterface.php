<?php
/**
 * Copyright (c) 2018. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Api\Data;


interface SessionDataInterface
{
    const SESSION_ID = 'session_id';
    const SESSION_VERSION = 'session_version';

    /**
     * @param string $id
     * @return $this
     */
    public function setSessionId($id);

    /**
     * @return string
     */
    public function getSessionId();

    /**
     * @param string $version
     * @return $this
     */
    public function setSessionVersion($version);

    /**
     * @return string
     */
    public function getSessionVersion();
}
