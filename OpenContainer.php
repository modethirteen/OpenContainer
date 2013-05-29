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
namespace MindTouch;

use Exception;

/**
 * Class OpenContainer
 * @package MindTouch
 */
class OpenContainer {
    private $types = array();
    private $sharedTypes = array();
    private $instances = array();

    /**
     * @param string $key
     * @throws NotRegisteredInOpenContainerException
     * @return mixed
     */
    public function __get($key) {
        if(!isset($this->types[$key])) {
            if(isset($this->instances[$key])) {
                return $this->instances[$key];
            }
            throw new NotRegisteredInOpenContainerException($key);
        }
        $type = $this->types[$key];
        $value = (is_callable($type)) ? $type($this) : new $type($this);
        if(isset($this->sharedTypes[$key])) {

            // move instantiated type to instances collection
            unset($this->types[$key]);
            unset($this->sharedTypes[$key]);
            $this->instances[$key] = &$value;
        }
        return $value;
    }

    /**
     * @param string $type
     * @param mixed $value - a class or callback to create this type
     */
    public function registerType($type, $value) {
        if(isset($this->instances[$type])) {
            unset($this->instances[$type]);
        }
        $this->types[$type] = $value;
    }

    /**
     * @param string $type
     * @param mixed $value - a class or callback to create this type
     */
    public function registerSharedType($type, $value) {
        if(isset($this->instances[$type])) {
            unset($this->instances[$type]);
        }
        $this->sharedTypes[$type] = '';
        $this->types[$type] = $value;
    }

    /**
     * @param string $type
     * @param object $instance - the instantiated type
     */
    public function registerInstance($type, &$instance) {
        if(isset($this->types[$type])) {
            unset($this->types[$type]);
            if(isset($this->sharedTypes[$type])) {
                unset($this->sharedTypes[$type]);
            }
        }
        $this->instances[$type] = $instance;
    }

    /**
     * @return array
     */
    public function getRegisteredTypes() {
        return array_merge(array_keys($this->types), array_keys($this->instances));
    }

    /**
     * @param string $type
     * @return bool
     */
    public function isRegistered($type) {
        return in_array($type, $this->getRegisteredTypes());
    }
}

class NotRegisteredInOpenContainerException extends Exception {

    /**
     * @param string $key
     */
    public function __construct($key) {
        parent::__construct('Could not find "' . $key . '" registered in the container');
    }
}