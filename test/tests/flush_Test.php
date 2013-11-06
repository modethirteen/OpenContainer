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

use MindTouch\OpenContainer\NotRegisteredInOpenContainerException;
use MindTouch\OpenContainer\test\NonInjectableClass;
use MindTouch\OpenContainer\test\TestContainer;
use PHPUnit_Framework_TestCase;

class flush_Test extends PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function Registered_shared_type_is_recreated_after_flush() {

        // arrange
        $Container = new TestContainer();
        $type = 'InjectableTestClassWithNoDependencies';
        $expectedInstanceClass = 'MindTouch\OpenContainer\test\InjectableTestClassWithNoDependencies';

        // act
        $Container->registerSharedType($type, $expectedInstanceClass);
        $FirstInstance = $Container->InjectableTestClassWithNoDependencies;
        $Container->flush($type);
        $SecondInstance = $Container->InjectableTestClassWithNoDependencies;

        // assert
        $this->assertInstanceOf($expectedInstanceClass, $FirstInstance);
        $this->assertInstanceOf($expectedInstanceClass, $SecondInstance);
        $this->assertNotSame($FirstInstance, $SecondInstance);
    }

    /**
     * @test
     */
    public function Registered_instance_is_not_recreated_after_flush() {

        // arrange
        $Container = new TestContainer();
        $type = 'NonInjectableClass';
        $expectedInstanceClass = 'MindTouch\OpenContainer\test\NonInjectableClass';

        // act
        $Container->registerInstance($type, NonInjectableClass::newNonInjectableClass());
        $FirstInstance = $Container->NonInjectableClass;
        $Container->flush($type);
        $exceptionThrown = false;
        try {
            $Container->NonInjectableClass;
        } catch(NotRegisteredInOpenContainerException $e) {
            $exceptionThrown = true;
        }

        // assert
        $this->assertInstanceOf($expectedInstanceClass, $FirstInstance);
        $this->assertTrue($exceptionThrown);
    }
}
