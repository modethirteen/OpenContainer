<?php
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

use modethirteen\OpenContainer\OpenContainer;
use PHPUnit\Framework\TestCase;

/**
 * Class OpenContainerTest
 * @package modethirteen\OpenContainer\Tests
 */
class OpenContainerTest extends TestCase {

    /**
     * @test
     */
    public function Circular_dependency_handling() {

        // arrange
        $container = new DependencyContainer();

        // act
        $foo = $container->CircularDependencyOne;
        $bar = $container->CircularDependencyTwo;

        // assert
        static::assertInstanceOf(CircularDependencyOne::class, $foo);
        static::assertInstanceOf(CircularDependencyTwo::class, $bar);
        static::assertNull($bar->getDependency());
        static::assertInstanceOf(CircularDependencyTwo::class, $foo->getDependency());
    }

    /**
     * @test
     */
    public function Instance_registration_handling() {

        // arrange
        $container = new DependencyContainer();

        // act
        $result = $container->Instance;

        // assert
        static::assertInstanceOf(Instance::class, $result);
    }

    /**
     * @test
     */
    public function Type_registration_handling() {

        // arrange
        $container = new OpenContainer();
        $container->flushInstance('Instance');
        $container->registerType('Plugh', Instance::class);

        // act
        /** @noinspection PhpUndefinedFieldInspection */
        $result = $container->Plugh;

        // assert
        static::assertInstanceOf(Instance::class, $result);
    }

    /**
     * @test
     */
    public function Builder_registration_handling() {

        // arrange
        $container = new DependencyContainer();
        $container->flushInstance('Instance');
        $container->registerBuilder('Xyzzy', function(IDependencyContainer $container) {
            static::assertInstanceOf(DependencyContainer::class, $container);
            return new Instance();
        });

        // act
        /** @noinspection PhpUndefinedFieldInspection */
        $result = $container->Xyzzy;

        // assert
        static::assertInstanceOf(Instance::class, $result);
    }
}
