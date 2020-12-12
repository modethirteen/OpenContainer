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
    public static function container_Provider() : array {
        return [
            'container' => [new DependencyContainer()],
            'deferred container' => [(new DependencyContainer())->toDeferredContainer()],
        ];
    }

    /**
     * @return array
     */
    public static function container_psr_Provider() : array {
        return [
            'container' => [new DependencyContainer(), false],
            'deferred container' => [(new DependencyContainer())->toDeferredContainer(), false],
            'container with psr interface' => [new DependencyContainer(), true],
            'deferred container with psr interface' => [(new DependencyContainer())->toDeferredContainer(), true]
        ];
    }

    /**
     * @return array
     */
    public static function psr_Provider() : array {
        return [
            'with psr interface' => [true],
            'without psr interface' => [false]
        ];
    }

    /**
     * @dataProvider container_psr_Provider
     * @param IDependencyContainer $container
     * @param bool $psr
     * @test
     */
    public function Can_handle_instance_registration(IDependencyContainer $container, bool $psr) : void {

        // act
        $result = $psr ? $container->get('Instance') : $container->Instance;

        // assert
        static::assertInstanceOf(Instance::class, $result);
    }

    /**
     * @dataProvider container_psr_Provider
     * @param IDependencyContainer $container
     * @param bool $psr
     * @test
     */
    public function Can_handle_type_registration(IDependencyContainer $container, bool $psr) : void {

        // arrange
        $container->flushInstance('Instance');
        $container->registerType('Plugh', Instance::class);

        // act
        /** @noinspection PhpUndefinedFieldInspection */
        $result = $psr ? $container->get('Plugh') : $container->Plugh;

        // assert
        static::assertInstanceOf(Instance::class, $result);
    }

    /**
     * @dataProvider container_psr_Provider
     * @param IDependencyContainer $container
     * @param bool $psr
     * @test
     */
    public function Can_handle_builder_registration(IDependencyContainer $container, bool $psr) : void {

        // arrange
        $container->flushInstance('Instance');
        $container->registerBuilder('Xyzzy', function(IDependencyContainer $container) : Instance {
            static::assertInstanceOf(DependencyContainer::class, $container);
            return new Instance();
        });

        // act
        /** @noinspection PhpUndefinedFieldInspection */
        $result = $psr ? $container->get('Xyzzy') : $container->Xyzzy;

        // assert
        static::assertInstanceOf(Instance::class, $result);
    }

    /**
     * @dataProvider container_psr_Provider
     * @param IDependencyContainer $container
     * @param bool $psr
     * @test
     */
    public function Can_handle_circular_dependency_resolution(IDependencyContainer $container, bool $psr) : void {

        // arrange
        if($psr && !$container->isDeferredContainer()) {
            static::markTestSkipped('Using the PSR-11 container "get" method with a non-deferred container creates an endless nested function loop');
        }

        // act
        $foo = $psr ? $container->get('PsrCompatibleCircularDependencyOne') : $container->CircularDependencyOne;
        $bar = $psr ? $container->get('PsrCompatibleCircularDependencyTwo') : $container->CircularDependencyTwo;

        // assert
        if($container->isDeferredContainer()) {

            // deferred container can manage circular dependencies at construction
            if($psr) {
                static::assertInstanceOf(PsrCompatibleCircularDependencyOne::class, $foo);
                static::assertInstanceOf(PsrCompatibleCircularDependencyTwo::class, $bar);
                static::assertInstanceOf(PsrCompatibleCircularDependencyOne::class, $bar->getDependency());
                static::assertInstanceOf(PsrCompatibleCircularDependencyTwo::class, $foo->getDependency());
            } else {
                static::assertInstanceOf(CircularDependencyOne::class, $foo);
                static::assertInstanceOf(CircularDependencyTwo::class, $bar);
                static::assertInstanceOf(CircularDependencyOne::class, $bar->getDependency());
                static::assertInstanceOf(CircularDependencyTwo::class, $foo->getDependency());
            }
        } else {

            // without deferred container, circular dependencies are presented as null in injectable class constructors
            if($psr) {
                static::assertInstanceOf(PsrCompatibleCircularDependencyOne::class, $foo);
                static::assertInstanceOf(PsrCompatibleCircularDependencyTwo::class, $bar);
                static::assertNull($bar->getDependency());
                static::assertInstanceOf(PsrCompatibleCircularDependencyTwo::class, $foo->getDependency());
            } else {
                static::assertInstanceOf(CircularDependencyOne::class, $foo);
                static::assertInstanceOf(CircularDependencyTwo::class, $bar);
                static::assertNull($bar->getDependency());
                static::assertInstanceOf(CircularDependencyTwo::class, $foo->getDependency());
            }
        }
    }

    /**
     * @dataProvider container_psr_Provider
     * @param IDependencyContainer $container
     * @param bool $psr
     * @test
     */
    public function Can_handle_unregistered_dependency(IDependencyContainer $container, bool $psr) : void {

        // assert
        static::expectException(OpenContainerNotRegisteredInContainerException::class);

        // act
        /** @noinspection PhpUndefinedFieldInspection */
        $psr ? $container->get('Ogre') : $container->Ogre;
    }

    /**
     * @dataProvider psr_Provider
     * @param bool $psr
     * @test
     */
    public function Can_handle_deferred_dependency_construction_error(bool $psr) : void {

        // assert
        static::expectException(OpenContainerCannotBuildDeferredInstanceException::class);

        // arrange
        $container = (new DependencyContainer())->toDeferredContainer();
        $container->registerBuilder('Puppy', function() {
            return new Instance();
        });

        // act
        $psr ? $container->get('Puppy') : $container->Puppy;
    }

    /**
     * @dataProvider container_psr_Provider
     * @param IDependencyContainer $container
     * @param bool $psr
     * @test
     */
    public function Can_check_if_dependency_is_registered(IDependencyContainer $container, bool $psr) : void {

        // act
        $result1 = $psr ? $container->has('Instance') : $container->isRegistered('Instance');
        $result2 = $psr ? $container->has('Fred') : $container->isRegistered('Fred');

        // assert
        static::assertTrue($result1);
        static::assertFalse($result2);
    }

    /**
     * @dataProvider container_Provider
     * @param IDependencyContainer $container
     * @test
     */
    public function Can_check_if_dependency_is_resolved(IDependencyContainer $container) : void {

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
