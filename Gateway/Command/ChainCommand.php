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

namespace OnTap\MasterCard\Gateway\Command;

use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;

class ChainCommand implements CommandInterface
{
    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var string[]
     */
    private $commandChain;

    /**
     * @param CommandPoolInterface $commandPool
     * @param string[]|null $commandChain
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        $commandChain = []
    ) {
        $this->commandPool = $commandPool;
        $this->commandChain = $commandChain;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(array $commandSubject)
    {
        foreach ($this->commandChain as $commandCode) {
            $this->commandPool
                ->get($commandCode)
                ->execute($commandSubject);
        }

        return null;
    }
}
