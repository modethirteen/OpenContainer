<?php
/**
 * MindTouch OpenContainer - a dependency injection container for PHP
 * Copyright (C) 2006-2016 MindTouch, Inc.
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

use MindTouch\OpenContainer\NotRegisteredInOpenContainerException;
use MindTouch\OpenContainer\test\NonInjectableClass;
use MindTouch\OpenContainer\test\TestContainer;
use PHPUnit_Framework_TestCase;

class __get_Test extends PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function Getting_an_unregistered_type_throws_exception() {

        // arrange
        $Container = new TestContainer();
        $exceptionThrown = false;

        // act
        try {
            $Container->InjectableTestClassWithNoDependencies;
        } catch(NotRegisteredInOpenContainerException $e) {
            $exceptionThrown = true;
        }

        // assert
        $this->assertTrue($exceptionThrown);
    }

    /**
     * @test
     */
    public function Getting_a_stateless_type_always_returns_new_instance() {

        // arrange
        $Container = new TestContainer();

        // act
        $Container->registerType('InjectableTestClassWithNoDependencies',
            'MindTouch\OpenContainer\test\InjectableTestClassWithNoDependencies');
        $InstanceOne = $Container->InjectableTestClassWithNoDependencies;
        $InstanceTwo = $Container->InjectableTestClassWithNoDependencies;

        // assert
        $this->assertNotSame($InstanceOne, $InstanceTwo);
    }

    /**
     * @test
     */
    public function Getting_a_shared_state_type_always_returns_same_instance() {

        // arrange
        $Container = new TestContainer();

        // act
        $Container->registerSharedType('InjectableTestClassWithNoDependencies',
            'MindTouch\OpenContainer\test\InjectableTestClassWithNoDependencies');
        $InstanceOne = $Container->InjectableTestClassWithNoDependencies;
        $InstanceTwo = $Container->InjectableTestClassWithNoDependencies;

        // assert
        $this->assertSame($InstanceOne, $InstanceTwo);
    }

    /**
     * @test
     */
    public function Getting_an_instance_type_always_returns_same_instance() {

        // arrange
        $Container = new TestContainer();

        // act
        $Container->registerInstance('NonInjectableClass', NonInjectableClass::newNonInjectableClass());
        $InstanceOne = $Container->NonInjectableClass;
        $InstanceTwo = $Container->NonInjectableClass;

        // assert
        $this->assertSame($InstanceOne, $InstanceTwo);
    }
}
