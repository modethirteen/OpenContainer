<?php
/**
 * MindTouch OpenContainer - a dependency injection container for PHP
 * Copyright (C) 2006-2016 MindTouch, Inc.
 * www.mindtouch.com  oss@mindtouch.com
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
namespace MindTouch\OpenContainer;

use Exception;

/**
 * Class OpenContainer
 * @package MindTouch\OpenContainer
 */
class OpenContainer {

    protected $types = array();
    protected $sharedTypes = array();
    protected $instances = array();

    /**
     * @param string $key
     * @throws NotRegisteredInOpenContainerException
     * @return mixed
     */
    public function __get($key) {
        if(isset($this->instances[$key])) {

            // type was registered as an instance or was a previously instantiated shared type
            return $this->instances[$key];
        }
        if(!isset($this->types[$key]) && !isset($this->sharedTypes[$key])) {
            throw new NotRegisteredInOpenContainerException($key);
        }

        // instantiate this type
        $storeInstance = false;
        if(isset($this->sharedTypes[$key])) {
            $storeInstance = true;
            $type = $this->sharedTypes[$key];
        } else {
            $type = $this->types[$key];
        }
        $value = (is_callable($type)) ? $type($this) : new $type($this);
        if($storeInstance) {

            // return instantiated shared types for future requests
            $this->instances[$key] = &$value;
        }
        return $value;
    }

    /**
     * @param string $type
     * @param mixed $value - a class or callback to create this type
     */
    public function registerType($type, $value) {
        $this->flush($type);
        $this->unregisterSharedType($type);
        $this->types[$type] = $value;
    }

    /**
     * @param string $type
     * @param mixed $value - a class or callback to create this type
     */
    public function registerSharedType($type, $value) {
        $this->flush($type);
        $this->unregisterType($type);
        $this->sharedTypes[$type] = $value;
    }

    /**
     * @param string $type
     * @param object $instance - the instantiated type
     */
    public function registerInstance($type, $instance) {
        $this->unregisterType($type);
        $this->unregisterSharedType($type);
        $this->instances[$type] = $instance;
    }

    /**
     * @return array
     */
    public function getRegisteredTypes() {
        return array_merge(array_keys($this->types), array_keys($this->sharedTypes), array_keys($this->instances));
    }

    /**
     * @param string $type
     * @return bool
     */
    public function isRegistered($type) {
        return in_array($type, $this->getRegisteredTypes());
    }

    /**
     * delete stored instance of this type
     *
     * @param string $type
     */
    public function flush($type) {
        if(isset($this->instances[$type])) {
            unset($this->instances[$type]);
        }
    }

    /**
     * @param string $type
     */
    private function unregisterType($type) {
        if(isset($this->types[$type])) {
            unset($this->types[$type]);
        }
    }

    /**
     * @param string $type
     */
    private function unregisterSharedType($type) {
        if(isset($this->sharedTypes[$type])) {
            unset($this->sharedTypes[$type]);
        }
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
