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
namespace OnTap\MasterCard\Gateway\Http\Client;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

class Rest implements ClientInterface
{
    /**
     * HTTP protocol versions
     */
    const HTTP_1 = '1.1';
    const HTTP_0 = '1.0';

    /**
     * HTTP request methods
     */
    const GET     = 'GET';
    const POST    = 'POST';
    const PUT     = 'PUT';
    const HEAD    = 'HEAD';
    const DELETE  = 'DELETE';
    const TRACE   = 'TRACE';
    const OPTIONS = 'OPTIONS';
    const CONNECT = 'CONNECT';
    const MERGE   = 'MERGE';
    const PATCH   = 'PATCH';

    /**
     * @const int Request timeout
     */
    const REQUEST_TIMEOUT = 360;

    /**
     * @var ConverterInterface
     */
    private $converter;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var \Zend_Http_Client_Adapter_Interface
     */
    private $adapter;
    /**
     * @var Json
     */
    private $json;

    /**
     * Constructor
     *
     * @param Logger $logger
     * @param ConverterInterface $converter
     * @param ResponseFactory $responseFactory
     * @param \Zend_Http_Client_Adapter_Interface $adapter
     * @param Json $json
     */
    public function __construct(
        Logger $logger,
        ConverterInterface $converter,
        ResponseFactory $responseFactory,
        \Zend_Http_Client_Adapter_Interface $adapter,
        Json $json
    ) {
        $this->logger = $logger;
        $this->converter = $converter;
        $this->responseFactory = $responseFactory;
        $this->adapter = $adapter;
        $this->json = $json;
    }

    /**
     * @inheritdoc
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $log = [
            'request' => json_encode($transferObject->getBody(), JSON_UNESCAPED_SLASHES),
            'request_uri' => $transferObject->getUri()
        ];
        $response = [];

        try {
            $this->adapter->setOptions(
                [
                    CURLOPT_USERPWD => $transferObject->getAuthUsername() . ":" . $transferObject->getAuthPassword(),
                    CURLOPT_TIMEOUT => self::REQUEST_TIMEOUT
                ]
            );
            $headers = [];
            foreach ($transferObject->getHeaders() as $name => $value) {
                $headers[] = sprintf('%s: %s', $name, $value);
            }
            $this->adapter->write(
                $transferObject->getMethod(),
                \Zend_Uri_Http::fromString($transferObject->getUri()),
                self::HTTP_1,
                $headers,
                $this->json->serialize($transferObject->getBody())
            );

            $response = $this->converter->convert($this->read());
        } catch (\Exception $e) {
            throw new ClientException(__($e->getMessage()));
        } finally {
            $log['response'] = $response;
            $this->logger->debug($log);
        }

        return (array) $response;
    }

    /**
     * @inheritdoc
     */
    public function read()
    {
        return $this->responseFactory->create($this->adapter->read())->getBody();
    }
}
