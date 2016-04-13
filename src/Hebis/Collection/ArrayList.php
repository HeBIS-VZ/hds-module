<?php

/*
 *  Copyright (C) 2016
 *
 *  Sebastian Böttger
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Hebis\Collection;

/**
 * ArrayList
 *
 * @author Sebastian Böttger
 */
class ArrayList implements Collection
{

    protected $array;


    public function __construct(array $data = [])
    {
        $this->array = $data;
    }

    public function clear()
    {
        $this->array = [];
        return $this;
    }

    public function get($key)
    {
        return isset($this->array[$key]) ? $this->array[$key] : null;
    }

    public function set($key, $value)
    {
        $this->array[$key] = $value;
        return $this;
    }

    public function setArray(array $array)
    {
        $this->array = $array;
        return $this;
    }

    public function add($key, $value)
    {

        if (!array_key_exists($key, $this->array)) {
            $this->array[$key] = $value;
        } elseif (is_array($this->array[$key])) {
            $this->array[$key][] = $value;
        } else {
            $this->array[$key] = [$this->array[$key], $value];
        }

        return $this;
    }


    public function remove($key)
    {
        unset($this->array[$key]);

        return $this;
    }

    /**
     *
     * @param mixed $key
     * @return bool
     */
    public function hasKey($key)
    {
        return array_key_exists($key, $this->array);
    }

    /**
     *
     * @param string $value
     *
     * @return mixed
     */
    public function hasValue($value)
    {
        return array_search($value, $this->array, true);
    }

    /**
     *
     * @param array $data
     *
     * @return ArrayList
     */
    public function replace(array $data)
    {
        $this->array = $data;

        return $this;
    }

    public function getIterator()
    {

        return new \ArrayIterator($this->array);
    }

    public function offsetGet($offset)
    {

        return isset($this->array[$offset]) ? $this->array[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {

        $this->array[$offset] = $value;
    }

    public function offsetExists($offset)
    {

        return isset($this->array[$offset]);
    }

    public function offsetUnset($offset)
    {

        unset($this->array[$offset]);
    }

    public function toArray()
    {

        return $this->array;
    }

    public function count()
    {

        return count($this->array);
    }

}