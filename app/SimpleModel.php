<?php

namespace App;

class SimpleModel
{
    private $_errors = [];


    public static function getClass()
    {
        return get_called_class();
    }


    /**
     * Loads data into all public properties.
     * @param array $data
     * @return bool
     */
    public function load(array $data)
    {
        $i = 0;

        if (is_array($data)) {
            $ref = new \ReflectionClass(static::getClass());
            $props = $ref->getProperties(\ReflectionProperty::IS_PUBLIC);

            foreach ($props as $prop) {
                if (array_key_exists($prop->name, $data)) {
                    $this->{$prop->name} = $data[$prop->name];
                    $i++;
                }
            }
        }

        return $i > 0;
    }


    /**
     * Adds error with given message.
     * @param $message
     */
    public function addError($message)
    {
        $this->_errors []= $message;
    }


    /**
     * Clears all errors of the model.
     */
    public function clearErrors()
    {
        $this->_errors = [];
    }


    /**
     * Returns true if model has at least one error.
     * @return bool
     */
    public function hasErrors()
    {
        return count($this->_errors) > 0;
    }


    /**
     * Returns first error message.
     * @return string
     */
    public function getError()
    {
        return count($this->_errors) > 0 ? $this->_errors[0] : '';
    }
}
