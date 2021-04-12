/*
 * Copyright (c) 2016-2021 Mastercard
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
/*global define*/
define(
    [
        'uiComponent'
    ],
    function (Component) {
        'use strict';
        return Component.extend({
            defaults: {
                adapter: false,
                exports: {
                    'adapter': '${ $.parent }:adapter'
                }
            },
            getCode: function () {
                return this.item.code;
            },
            getId: function () {
                return this.index;
            },
            initObservable: function () {
                this._super().observe('adapter')
                this.adapter(this);
                return this;
            },
            load: function () {
                throw "Not implemented";
            },
            configure: function () {
                throw "Not implemented";
            },
            pay: function () {
                throw "Not implemented";
            }
        });
    }
);
