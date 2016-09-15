<?php
/**
 * Copyright (c) 2016. On Tap Networks Limited.
 */

namespace OnTap\MasterCard\Gateway\Command;

use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use OnTap\MasterCard\Gateway\Response\ThreeDSecure\CheckHandler;
use Magento\Framework\App\State;
use Magento\Vault\Model\Ui\VaultConfigProvider;

/**
 * Class VerificationStrategyCommand
 * @package OnTap\MasterCard\Gateway\Command
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VerificationStrategyCommand implements CommandInterface
{
    const PROCESS_3DS_RESULT = '3ds_process';
    const CREATE_TOKEN = 'create_token';

    /**
     * @var Command\CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var string
     */
    private $successCommand;

    /**
     * @var State
     */
    private $state;

    /**
     * VerificationStrategyCommand constructor.
     * @param State $state
     * @param Command\CommandPoolInterface $commandPool
     * @param ConfigInterface $config
     * @param string $successCommand
     */
    public function __construct(
        State $state,
        Command\CommandPoolInterface $commandPool,
        ConfigInterface $config,
        $successCommand = ''
    ) {
        $this->state = $state;
        $this->commandPool = $commandPool;
        $this->config = $config;
        $this->successCommand = $successCommand;
    }

    /**
     * @param PaymentDataObjectInterface $paymentDO
     * @return bool
     */
    public function isThreeDSSupported(PaymentDataObjectInterface $paymentDO)
    {
        /** @var Payment $paymentInfo */
        $paymentInfo = $paymentDO->getPayment();

        // Don't use 3DS in admin
        if ($this->state->getAreaCode() === \Magento\Framework\App\Area::AREA_ADMINHTML) {
            return false;
        }

        // Don't use 3DS with pre-authorized transactions
        if ($paymentInfo->getAuthorizationTransaction()) {
            return false;
        }

        $isEnabled = $this->config->getValue('three_d_secure') === '1';
        if (!$isEnabled) {
            return false;
        }

        $data = $paymentInfo->getAdditionalInformation(CheckHandler::THREEDSECURE_CHECK);

        if (isset($data['status'])) {
            if ($data['status'] == "CARD_DOES_NOT_SUPPORT_3DS") {
                return false;
            }
            if ($data['status'] == "CARD_NOT_ENROLLED") {
                return false;
            }
            if ($data['status'] == "CARD_ENROLLED") {
                return true;
            }
        }

        return true;
    }

    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return null|Command\ResultInterface
     */
    public function execute(array $commandSubject)
    {
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = SubjectReader::readPayment($commandSubject);

        /** @var Payment $paymentInfo */
        $paymentInfo = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($paymentInfo);

        if ($this->isThreeDSSupported($paymentDO)) {
            $this->commandPool
                ->get(static::PROCESS_3DS_RESULT)
                ->execute($commandSubject);
        }

        // Vault enabled from configuration
        // 'Save for later use' checked on frontend
        if ($this->config->isVaultEnabled() &&
            $paymentInfo->getAdditionalInformation(VaultConfigProvider::IS_ACTIVE_CODE)) {
            $this->commandPool
                ->get(static::CREATE_TOKEN)
                ->execute($commandSubject);
        }

        $this->commandPool
            ->get($this->successCommand)
            ->execute($commandSubject);
    }
}
