<?php

namespace BTransilvania\Api\Model\Request;

use BTransilvania\Api\Model\IPayFormatter;

abstract class RequestModel implements RequestModelInterface
{
    public function __construct(?array $values = null)
    {
        $this->setProperties($values);
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        return null;
    }

    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            if (is_array($value)) {
                // Check if property is supposed to be an object and instantiate it
                $className = $this->getPropertyClassName($property);
                if (class_exists($className)) {
                    $this->$property = new $className($value);
                }
            } else {
                $this->$property = $value;
            }
        }
        return $this;
    }

    public function setProperties(?array $data): RequestModel
    {
        if ($data) {
            foreach ($data as $property => $value) {
                $this->__set($property, $value);
            }
        }
        return $this;
    }

    /**
     * Transform the property name into the expected class name format
     *
     * @param string $property
     * @return string|null
     */
    protected function getPropertyClassName(string $property): ?string
    {
        $className = 'BTransilvania\\Api\\Model\\Request\\'
            . str_replace(' ', '', ucwords(str_replace('_', ' ', $property))) . 'Model';
        return class_exists($className) ? $className : null;
    }

    public function buildRequest()
    {
        return $this->recursiveToArray(
            $this->filterData(get_object_vars($this))
        );
    }

    public function filterData(array $data):array
    {
        return $data;
    }

    private function recursiveToArray(array $array): array
    {
        foreach ($array as &$value) {
            if ($value instanceof RequestModelInterface) {
                $value = $value->buildRequest();
            } elseif (is_array($value)) {
                $value = $this->recursiveToArray($value);
            }
        }
        return $array;
    }

    public function fromArray(array $data)
    {
        $this->setProperties($data);
    }

    protected function getFormattedString($field)
    {
        if (is_scalar($field)) {
            return IPayFormatter::format((string)$field);
        }
        return '';
    }
}
