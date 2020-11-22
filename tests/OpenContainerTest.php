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

use PHPUnit\Framework\TestCase;

/**
 * Class OpenContainerTest
 * @package modethirteen\OpenContainer\Tests
 */
class OpenContainerTest extends TestCase {

    /**
     * @return array
     */
    public static function containerProvider() : array {
        return [
            'container' => [new DependencyContainer()],
            'deferred container' => [(new DependencyContainer())->toDeferredContainer()]
        ];
    }

    /**
     * @dataProvider containerProvider
     * @param IDependencyContainer $container
     * @test
     */
    public function Circular_dependency_handling(IDependencyContainer $container) : void {

        // act
        $foo = $container->CircularDependencyOne;
        $bar = $container->CircularDependencyTwo;

        // assert
        if($container->isDeferredContainer()) {

            // deferred container can manage circular dependencies at construction
            static::assertInstanceOf(CircularDependencyOne::class, $foo);
            static::assertInstanceOf(CircularDependencyTwo::class, $bar);
            static::assertInstanceOf(CircularDependencyOne::class, $bar->getDependency());
            static::assertInstanceOf(CircularDependencyTwo::class, $foo->getDependency());
        } else {

            // without deferred container, circular dependencies are presented as null in injectable class constructors
            static::assertInstanceOf(CircularDependencyOne::class, $foo);
            static::assertInstanceOf(CircularDependencyTwo::class, $bar);
            static::assertNull($bar->getDependency());
            static::assertInstanceOf(CircularDependencyTwo::class, $foo->getDependency());
        }
    }

    /**
     * @dataProvider containerProvider
     * @param IDependencyContainer $container
     * @test
     */
    public function Instance_registration_handling(IDependencyContainer $container) : void {

        // act
        $result = $container->Instance;

        // assert
        static::assertInstanceOf(Instance::class, $result);
    }

    /**
     * @dataProvider containerProvider
     * @param IDependencyContainer $container
     * @test
     */
    public function Type_registration_handling(IDependencyContainer $container) : void {

        // arrange
        $container->flushInstance('Instance');
        $container->registerType('Plugh', Instance::class);

        // act
        /** @noinspection PhpUndefinedFieldInspection */
        $result = $container->Plugh;

        // assert
        static::assertInstanceOf(Instance::class, $result);
    }

    /**
     * @dataProvider containerProvider
     * @param IDependencyContainer $container
     * @test
     */
    public function Builder_registration_handling(IDependencyContainer $container) : void {

        // arrange
        $container->flushInstance('Instance');
        $container->registerBuilder('Xyzzy', function(IDependencyContainer $container) : Instance {
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
