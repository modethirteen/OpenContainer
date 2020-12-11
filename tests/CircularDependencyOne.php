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
namespace modethirteen\OpenContainer\Tests;

/**
 * Class CircularDependencyOne
 * @package modethirteen\OpenContainer\Tests
 */
class CircularDependencyOne {

    /**
     * @var CircularDependencyTwo|null
     */
    private $dependency;

    /**
     * @param IDependencyContainer $container
     */
    public function __construct(IDependencyContainer $container) {
        $this->dependency = $container->CircularDependencyTwo;
    }

    /**
     * @return CircularDependencyTwo|null
     */
    public function getDependency() : ?CircularDependencyTwo {
        return $this->dependency;
    }
}
