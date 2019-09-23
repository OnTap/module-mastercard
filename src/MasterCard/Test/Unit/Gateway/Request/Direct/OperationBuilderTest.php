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

namespace OnTap\MasterCard\Test\Unit\Gateway\Request\Direct;

use OnTap\MasterCard\Gateway\Request\OperationBuilder;

class OperationBuilderTest extends \PHPUnit_Framework_TestCase
{
    const OPERATION = 'OPERATION';

    /**
     * @var OperationBuilder
     */
    private $operationBuilder;

    /**
     * setUp
     */
    public function setUp()
    {
        $this->operationBuilder = new OperationBuilder(static::OPERATION);
    }

    /**
     * testBuildSuccess
     */
    public function testBuildSuccess()
    {
        $expected = [
            'apiOperation' => static::OPERATION
        ];

        $result = $this->operationBuilder->build([]);
        static::assertEquals($expected, $result);
    }
}
