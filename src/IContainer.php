<?php declare(strict_types=1);
/**
 * OpenContainer - a dependency injection container for PHP
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace modethirteen\OpenContainer;

use Closure;

interface IContainer {

    /**
     * @param string $id
     */
    function flushInstance(string $id) : void;

    /**
     * @return bool
     */
    function isDeferredContainer() : bool;

    /**
     * @param string $id
     * @return bool
     */
    function isRegistered(string $id) : bool;

    /**
     * Register a callback that builds an instance
     *
     * @param string $id
     * @param Closure $builder
     */
    function registerBuilder(string $id, Closure $builder) : void;

    /**
     * Register an instance
     *
     * @param string $id
     * @param object $instance
     */
    function registerInstance(string $id, object $instance) : void;

    /**
     * Register a class type
     *
     * @param string $id
     * @param string $class
     */
    function registerType(string $id, string $class) : void;

    /**
     * @return static
     */
    function toDeferredContainer() : object;
}
