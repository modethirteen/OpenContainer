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

use MindTouch\OpenContainer\test\NonInjectableClass;
use MindTouch\OpenContainer\test\TestContainer;
use PHPUnit_Framework_TestCase;

class getRegisteredTypes_Test extends PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function Can_get_registered_types() {

        // arrange
        $Container = new TestContainer();
        $Container->registerType('InjectableTestClassWithNoDependencies',
            'MindTouch\OpenContainer\test\InjectableTestClassWithNoDependencies');
        $Container->registerSharedType('InjectableTestClassWithSimpleDependencies',
            'MindTouch\OpenContainer\test\InjectableTestClassWithSimpleDependencies');
        $Container->registerInstance('NonInjectableClass', NonInjectableClass::newNonInjectableClass());

        // act
        $types = $Container->getRegisteredTypes();

        // assert
        $this->assertEquals(array(
            'InjectableTestClassWithNoDependencies',
            'InjectableTestClassWithSimpleDependencies',
            'NonInjectableClass'
        ), $types);
    }
}
