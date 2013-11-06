<?php
/*
 * MindTouch OpenContainer - a dependency injection container for PHP
 * Copyright (C) 2006-2013 MindTouch, Inc.
 * www.mindtouch.com  oss@mindtouch.com
 *
 * For community documentation and downloads visit wiki.developer.mindtouch.com;
 * please review the licensing section.
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
namespace MindTouch\OpenContainer\test\tests;

use MindTouch\OpenContainer\test\NonInjectableClass;
use MindTouch\OpenContainer\test\TestContainer;
use PHPUnit_Framework_TestCase;

class registerSharedType_Test extends PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function Can_register_shared_type_by_injectable_class_with_no_dependencies_and_get_instance() {

        // arrange
        $Container = new TestContainer();
        $expectedInstanceClass = 'MindTouch\OpenContainer\test\InjectableTestClassWithNoDependencies';

        // act
        $Container->registerSharedType('InjectableTestClassWithNoDependencies', $expectedInstanceClass);
        $Instance = $Container->InjectableTestClassWithNoDependencies;

        // assert
        $this->assertInstanceOf($expectedInstanceClass, $Instance);
    }

    /**
     * @test
     */
    public function Can_register_shared_type_by_injectable_class_with_dependencies_and_get_instance() {

        // arrange
        $Container = new TestContainer();
        $Container->registerType('InjectableTestClassWithNoDependencies',
            'MindTouch\OpenContainer\test\InjectableTestClassWithNoDependencies');
        $Container->registerType('InjectableTestClassWithSimpleDependencies',
            'MindTouch\OpenContainer\test\InjectableTestClassWithSimpleDependencies');
        $Container->registerType('InjectableTestClassWithComplexDependenciesOne',
            'MindTouch\OpenContainer\test\InjectableTestClassWithComplexDependenciesOne');
        $Container->registerType('InjectableTestClassWithComplexDependenciesTwo',
            'MindTouch\OpenContainer\test\InjectableTestClassWithComplexDependenciesTwo');
        $expectedInstanceClass = 'MindTouch\OpenContainer\test\InjectableTestClassWithComplexDependenciesThree';

        // act
        $Container->registerSharedType('InjectableTestClassWithComplexDependenciesThree', $expectedInstanceClass);
        $Instance = $Container->InjectableTestClassWithComplexDependenciesThree;

        // assert
        $this->assertInstanceOf($expectedInstanceClass, $Instance);
    }

    /**
     * @test
     */
    public function Can_register_shared_type_with_closure_and_no_dependencies() {

        // arrange
        $Container = new TestContainer();

        // act
        $Container->registerSharedType('NonInjectableClass', function() { return NonInjectableClass::newNonInjectableClass(); });
        $Instance = $Container->NonInjectableClass;

        // assert
        $this->assertInstanceOf('MindTouch\OpenContainer\test\NonInjectableClass', $Instance);
    }

    /**
     * @test
     */
    public function Can_register_shared_type_with_closure_and_dependencies() {

        // arrange
        $Container = new TestContainer();
        $Container->registerType('InjectableTestClassWithNoDependencies',
            'MindTouch\OpenContainer\test\InjectableTestClassWithNoDependencies');
        $Container->registerType('InjectableTestClassWithSimpleDependencies',
            'MindTouch\OpenContainer\test\InjectableTestClassWithSimpleDependencies');

        // act
        $Container->registerSharedType('NonInjectableClass', function(TestContainer $Container) {
            return NonInjectableClass::newNonInjectableClassWithSimpleDependency(
                $Container->InjectableTestClassWithSimpleDependencies
            );
        });
        $Instance = $Container->NonInjectableClass;

        // assert
        $this->assertInstanceOf('MindTouch\OpenContainer\test\NonInjectableClass', $Instance);
    }

    /**
     * @test
     */
    public function Can_register_shared_type_with_instance_dependency() {

        // arrange
        $Container = new TestContainer();
        $RegisteredInstance = NonInjectableClass::newNonInjectableClass();
        $Container->registerInstance('NonInjectableClass', $RegisteredInstance);
        $expectedInstanceClass = 'MindTouch\OpenContainer\test\InjectableTestClassWithInstanceDependency';

        // act
        $Container->registerSharedType('InjectableTestClassWithInstanceDependency', $expectedInstanceClass);
        $Instance = $Container->InjectableTestClassWithInstanceDependency;

        // assert
        $this->assertInstanceOf($expectedInstanceClass, $Instance);
    }
}