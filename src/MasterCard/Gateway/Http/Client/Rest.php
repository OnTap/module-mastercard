<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Gateway\Http\Client;

use Magento\Payment\Model\Method\Logger;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Gateway\Http\ConverterInterface;

/**
 * Class Rest
 */
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
     * Request timeout
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
     * Constructor
     *
     * @param Logger $logger
     * @param ConverterInterface $converter
     * @param ResponseFactory $responseFactory
     * @param \Zend_Http_Client_Adapter_Interface $adapter
     */
    public function __construct(
        Logger $logger,
        ConverterInterface $converter,
        ResponseFactory $responseFactory,
        \Zend_Http_Client_Adapter_Interface $adapter
    ) {
        $this->logger = $logger;
        $this->converter = $converter;
        $this->responseFactory = $responseFactory;
        $this->adapter = $adapter;
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
                \Zend_Json_Encoder::encode($transferObject->getBody())
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
