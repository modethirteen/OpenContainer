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
namespace modethirteen\OpenContainer;

use Closure;

class OpenContainer implements IContainer {

    /**
     * @var Closure[]
     */
    private $builders = array();

    /**
     * @var object[]
     */
    private $instances = array();

    /**
     * @var string[]
     */
    private $types = array();

    /**
     * @param string $id
     * @return object
     * @throws OpenContainerNotRegisteredInContainerException
     */
    public function __get(string $id) : object {
        if(isset($this->instances[$id])) {

            // type was registered as an instance or was a previously instantiated
            return $this->instances[$id];
        }

        // instantiate the type or builder
        $builder = null;
        $type = isset($this->types[$id]) ? $this->types[$id] : null;
        if($type === null) {
            if(!isset($this->builders[$id])) {
                throw new OpenContainerNotRegisteredInContainerException($id);
            }
            $builder = isset($this->builders[$id]) ? $this->builders[$id] : null;
        }

        // build instance
        $instance = $builder !== null ? $builder($this) : new $type($this);
        $this->instances[$id] = $instance;
        return $instance;
    }

    public function flushInstance(string $id) : void {
        if(isset($this->instances[$id])) {
            unset($this->instances[$id]);
        }
    }

    public function isRegistered(string $id) : bool {
        return in_array($id, array_merge(array_keys($this->builders), array_keys($this->instances), array_keys($this->types)));
    }

    public function isResolved(string $id): bool {
        return isset($this->instances[$id]);
    }

    public function registerBuilder(string $id, Closure $builder) : void {
        $this->unregister($id);
        $this->builders[$id] = $builder;
    }

    public function registerInstance(string $id, object $instance) : void {
        $this->unregister($id);
        $this->instances[$id] = $instance;
    }

    public function registerType(string $id, string $class) : void {
        $this->unregister($id);
        $this->types[$id] = $class;
    }

    /**
     * Remove all registrations and instances for an id
     *
     * @param string $id
     */
    protected function unregister(string $id) : void {
        unset($this->builders[$id]);
        unset($this->instances[$id]);
        unset($this->types[$id]);
    }
}
