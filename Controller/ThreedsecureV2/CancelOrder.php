<?php
/**
 * Copyright (c) 2016-2020 Mastercard
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

declare(strict_types=1);

namespace OnTap\MasterCard\Controller\ThreedsecureV2;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use OnTap\MasterCard\Model\Service\CancelOrderService;
use OnTap\MasterCard\Model\Service\ReorderService;

class CancelOrder extends Action implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var CancelOrderService
     */
    private $cancelOrderService;

    /**
     * @var ReorderService
     */
    private $reorderService;

    /**
     * Check constructor.
     * @param JsonFactory $jsonFactory
     * @param Context $context
     * @param CancelOrderService $cancelOrderService
     * @param ReorderService $reorderService
     */
    public function __construct(
        JsonFactory $jsonFactory,
        Context $context,
        CancelOrderService $cancelOrderService,
        ReorderService $reorderService
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->cancelOrderService = $cancelOrderService;
        $this->reorderService = $reorderService;
    }

    /**
     * Dispatch request
     *
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        $orderId = (int)$this->getRequest()->getParam('order_id');

        $jsonResult = $this->jsonFactory->create();
        try {
            $order = $this->cancelOrderService->execute($orderId);
            $this->reorderService->execute($order);
        } catch (NoSuchEntityException $noSuchEntityException) {
            return $jsonResult;
        } catch (Exception $e) {
            $jsonResult
                ->setHttpResponseCode(400)
                ->setData([
                    'message' => $e->getMessage()
                ]);
        }

        return $jsonResult;
    }
}
