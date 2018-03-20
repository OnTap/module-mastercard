<?php
/**
 * Copyright (c) 2017. On Tap Networks Limited.
 */
namespace OnTap\MasterCard\Model\Method;

use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Gateway\Command\CommandManagerInterface;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Gateway\Config\ConfigFactory;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Validator\ValidatorPoolInterface;

class Wallet extends \Magento\Payment\Model\Method\Adapter implements WalletInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Payment\Gateway\Config\Config
     */
    protected $methodConfig;

    /**
     * @var MethodInterface
     */
    protected $provider;

    /**
     * @var ConfigFactory
     */
    protected $configFactory;

    /**
     * @var ConfigInterface
     */
    protected $providerConfig;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var CommandPoolInterface|null
     */
    protected $_commandPool;

    /**
     * Wallet constructor.
     * @param UrlInterface $url
     * @param ConfigFactory $configFactory
     * @param MethodInterface $provider
     * @param ConfigInterface $config
     * @param ConfigInterface $providerConfig
     * @param ManagerInterface $eventManager
     * @param ValueHandlerPoolInterface $valueHandlerPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param string $code
     * @param string $formBlockType
     * @param string $infoBlockType
     * @param CommandPoolInterface|null $commandPool
     * @param ValidatorPoolInterface|null $validatorPool
     * @param CommandManagerInterface|null $commandExecutor
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        UrlInterface $url,
        ConfigFactory $configFactory,
        MethodInterface $provider,
        ConfigInterface $config,
        ConfigInterface $providerConfig,
        ManagerInterface $eventManager,
        ValueHandlerPoolInterface $valueHandlerPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        $code,
        $formBlockType,
        $infoBlockType,
        CommandPoolInterface $commandPool = null,
        ValidatorPoolInterface $validatorPool = null,
        CommandManagerInterface $commandExecutor = null,
        LoggerInterface $logger = null
    ) {
        parent::__construct(
            $eventManager,
            $valueHandlerPool,
            $paymentDataObjectFactory,
            $code,
            $formBlockType,
            $infoBlockType,
            $commandPool,
            $validatorPool,
            $commandExecutor,
            $logger
        );

        $this->config = $config;
        $this->provider = $provider;
        $this->configFactory = $configFactory;
        $this->url = $url;
        $this->providerConfig = $providerConfig;
        $this->_commandPool = $commandPool;
    }

    /**
     * @return CommandPoolInterface|null
     */
    public function getCommandPool()
    {
        return $this->_commandPool;
    }

    /**
     * @return UrlInterface
     */
    public function getUrlBuilder()
    {
        return $this->url;
    }

    /**
     * @inheritdoc
     */
    public function getProviderConfig()
    {
        return $this->providerConfig;
    }

    /**
     * @return \Magento\Payment\Gateway\Config\Config
     */
    public function getMethodConfig()
    {
        if (!$this->methodConfig) {
            $this->methodConfig = $this->configFactory->create($this->getCode());
        }
        return $this->methodConfig;
    }

    /**
     * @return string
     */
    public function getProviderCode()
    {
        return $this->getProvider()->getCode();
    }

    /**
     * @return MethodInterface
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Retrieve payment method title
     *
     * @return string
     *
     */
    public function getTitle()
    {
        return $this->config->getValue('title');
    }

    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     */
    public function validate()
    {
        $this->getProvider()->validate();
        return $this;
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|string|null|\Magento\Store\Model\Store $storeId
     *
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        return $this->config->getValue($field, $storeId);
    }

    /**
     * Is active
     *
     * @param int|null $storeId
     * @return bool
     *
     */
    public function isActive($storeId = null)
    {
        return (bool) $this->config->getValue('active', $storeId)
            && $this->getProviderCode() == $this->config->getValue('provider', $storeId);
    }

    /**
     * @return array
     */
    public function getJsConfig()
    {
        throw new \DomainException("Not implemented");
    }
}
