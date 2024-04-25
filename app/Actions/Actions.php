<?php

namespace App\Actions;

abstract class Actions
{
    private function getUniqueProperty(string $propertyName): string|int|float|bool|null
    {
        if (property_exists($this, $propertyName)) {
            return $this->$propertyName;
        } else {
            return false;
        }
    }

    private function getListOfProperties(array $propertyNames): array
    {
        $data = [];

        foreach ($propertyNames as $property) {
            if (property_exists($this, $property)) {
                $data[$property] = $this->$property;
            }
        }

        return $data;
    }

    private function getAllProperties(): array
    {
        $properties = get_object_vars($this);
        $data = [];

        foreach ($properties as $property => $value) {
            $data[$property] = $this->$property;
        }

        return $data;
    }


    /**
     * @param null|string properties
     * @return array|int|string|float|null|bool array if many properties are mentionned or the value of the unique property
     */
    public function get()
    {
        $args = func_get_args();
        $instanceData = [];

        if (!empty($args) && count($args) === 1) {
            $this->getUniqueProperty($args[0]);
        } else if (!empty($args) && count($args) > 1) {
            $instanceData = $this->getListOfProperties($args);
        } else {
            $instanceData = $this->getAllProperties();
        }

        return $instanceData;
    }
}