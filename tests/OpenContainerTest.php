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

use modethirteen\OpenContainer\OpenContainerCannotBuildDeferredInstanceException;
use modethirteen\OpenContainer\OpenContainerNotRegisteredInContainerException;
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
    public function Can_handle_instance_registration(IDependencyContainer $container) : void {

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
    public function Can_handle_type_registration(IDependencyContainer $container) : void {

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
    public function Can_handle_builder_registration(IDependencyContainer $container) : void {

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

    /**
     * @dataProvider containerProvider
     * @param IDependencyContainer $container
     * @test
     */
    public function Can_handle_circular_dependency_resolution(IDependencyContainer $container) : void {

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
    public function Can_handle_unregistered_dependency(IDependencyContainer $container) {

        // assert
        static::expectException(OpenContainerNotRegisteredInContainerException::class);

        // act
        /** @noinspection PhpUndefinedFieldInspection */
        $container->Ogre;
    }

    /**
     * @test
     */
    public function Can_handle_deferred_dependency_construction_error() {

        // assert
        static::expectException(OpenContainerCannotBuildDeferredInstanceException::class);

        // arrange
        $container = (new DependencyContainer())->toDeferredContainer();
        $container->registerBuilder('Puppy', function() {
            return new Instance();
        });

        // act
        $container->Puppy;
    }

    /**
     * @dataProvider containerProvider
     * @param IDependencyContainer $container
     * @test
     */
    public function Can_check_if_dependency_is_registered(IDependencyContainer $container) {

        // act
        $result1 = $container->isRegistered('Instance');
        $result2 = $container->isRegistered('Fred');

        // assert
        static::assertTrue($result1);
        static::assertFalse($result2);
    }

    /**
     * @dataProvider containerProvider
     * @param IDependencyContainer $container
     * @test
     */
    public function Can_check_if_dependency_is_resolved(IDependencyContainer $container) {

        // arrange
        $container->registerType('Plugh', Instance::class);

        // act
        $result1 = $container->isResolved('Plugh');

        /** @noinspection PhpUndefinedFieldInspection */
        $plugh = $container->Plugh;
        if($container->isDeferredContainer()) {
            $result2 = $container->isResolved('Plugh');
            $plugh->doSomething();
            $result3 = $container->isResolved('Plugh');

            // assert
            static::assertFalse($result1);
            static::assertFalse($result2);
            static::assertTrue($result3);
        } else {
            $result2 = $container->isResolved('Plugh');

            // assert
            static::assertFalse($result1);
            static::assertTrue($result2);
        }
    }
}
