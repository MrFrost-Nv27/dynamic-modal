<?php

namespace Mrfrost\DynamicModal;

class Universe
{
    protected function setSingleDynamicModal($util)
    {
        foreach ($util as $field => $value) {
            $util->{$field} = htmlspecialchars(json_encode($value));
        }
        return $util;
    }

    /**
     * Create a modal with single field.
     * NOTE: If you want create multiple field modal,
     * use createMultiple.
     *
     * @param array|object $dataset
     * @param array $field
     * @param array $option
     *
     * @return mixed
     * @throws \Exception
     */
    public function create(array|object $dataset, array $field = [], array $option = [])
    {
        var_dump($dataset, $field, $option);
        $cleandataset = array_intersect_key((array)$dataset, $field);

        var_dump($cleandataset);

        $result = '';
        return $result;
    }
}