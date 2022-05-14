<?php

namespace Mrfrost\DynamicModal;

use stdClass;

class Universe
{
    /**
     * Create a modal with single field.
     * NOTE: If you want create multiple field modal,
     * use createMultiple.
     *
     * @param array|object $dataset
     * @param array $field
     * @param array $options
     * @param bool $multiple
     *
     * @return mixed
     * @throws \Exception
     */
    public function create(
        array|object $dataset,
        array $field = [],
        array $options = [],
        bool $single = true,
    ) {
        // Parsing Used Field
        if (is_array($dataset)) {
            $cleandataset = array_intersect_key($dataset, $field);
        } elseif (is_object($dataset)) {
            $cleandataset = array_intersect_key((array)$dataset, $field);
        } else {
            throw new \Exception("Dataset untuk dynamic modal harus berupa array atau objek");
        }

        // Catch Dataset Key If Available
        if (isset($options['form_key'])) {
            switch (gettype($dataset)) {
                case 'object':
                    $options['form_key'] = [
                        'key'   => $options['form_key'],
                        'value' => $dataset->{$options['form_key']}
                    ];
                    break;
                case 'array':
                    $options['form_key'] = [
                        'key'   => $options['form_key'],
                        'value' => $dataset[$options['form_key']]
                    ];
                    break;
                default:
                    $options['form_key'] = null;
                    break;
            }
        }

        // Field setter
        foreach ($field as $key => $value) {
            // Validasi Per field yang dimasukkan
            if (!isset(explode(',', $value)[0])) {
                throw new \Exception("Field dynamic modal harus mempunyai dua parameter, dengan parameter pertama type dan parameter kedua label menggunakan format comma separated value");
            } elseif (!isset(explode(',', $value)[1])) {
                throw new \Exception("Field dynamic modal harus mempunyai dua parameter, dengan parameter pertama type dan parameter kedua label menggunakan format comma separated value");
            } elseif (explode(',', $value)[0] == 'select') {
                if (!isset(explode(',', $value)[2])) {
                    throw new \Exception("Field dynamic modal dengan type select harus mempunyai parameter ketiga berisi value list optionnya");
                }
            }

            $field[$key] = [
                'type'  => explode(',', $value)[0],
                'label' => explode(',', $value)[1],
            ];

            if ($field[$key]['type'] == 'select') {
                $field[$key]['option'] = explode(',', $value)[2];
            }

            $this->fieldValidate($field[$key]);
        }

        if ($single) {
            $result = $this->createSingleField($cleandataset, $field, $options);
        } elseif (!$single) {
            $result = $this->createMultipleField($cleandataset, $field, $options);
        } else {
            throw new \Exception("Terjadi Kesalahan saat parsing data ke modal");
        }

        return $result;
    }

    protected function createSingleField(
        array $dataset,
        array $field = [],
        array $options = [],
    ) {
        $modal = new stdClass();
        foreach ($field as $key => $value) {
            // Set Default Options if not set
            $modal->{$key} = [
                'form_action'   => isset($options['form_action']) ? $options['form_action'] : '#!',
                'form_method'   => isset($options['form_method']) ? $options['form_method'] : 'POST',
                'form_submit'   => isset($options['form_submit']) ? $options['form_submit'] : 'Simpan',
                'title'         => isset($options['title']) ? (str_contains($options['title'], '{label}') ? str_replace('{label}', $field[$key]['label'], $options['title']) : $options['title']) : 'Judul Dynamic Modal',
                'form_key'      => isset($options['form_key']) ? $options['form_key'] : null,
            ];

            if (isset($options['form_key'])) {
                $modal->{$key}['form_input'] = [
                    [
                        'type'      => 'hidden',
                        'name'      => $options['form_key']['key'],
                        'value'     => $options['form_key']['value'],
                    ]
                ];
            }
            switch ($value['type']) {
                case 'select':
                    $modal->{$key}['form_input'][] =
                        [
                            'type'      => $value['type'],
                            'name'      => $key,
                            'value'     => $dataset[$key],
                            'label'     => $value['label'],
                            'option'    => $this->selectOptionsStringToArray($value['option']),
                        ];
                    break;
                default:
                    $modal->{$key}['form_input'][] =
                        [
                            'type'      => $value['type'],
                            'name'      => $key,
                            'value'     => $dataset[$key],
                            'label'     => $value['label'],
                        ];
                    break;
            }
        }

        $modal = $this->setSingleDynamicModal($modal);

        return $modal;
    }

    protected function createMultipleField(
        array $dataset,
        array $field = [],
        array $options = [],
    ) {
        $modal = null;
    }

    protected function setSingleDynamicModal($util)
    {
        foreach ($util as $field => $value) {
            $util->{$field} = htmlspecialchars(json_encode($value));
        }
        return $util;
    }

    protected function fieldValidate(array $field)
    {
        switch (strtolower($field['type'])) {
            case 'text':
                break;
            case 'textarea':
                break;
            case 'number':
                break;
            case 'select':
                if (!str_contains($field['option'], '|') && !str_contains($field['option'], '@')) {
                    throw new \Exception("Parameter ketiga dari field dynamic modal bertype select harus menggunakan multidimensional array berformat string dengan separator dimensi pertama dengan simbol pipe (|) dan separator dimensi kedua dengan simbol at (@)");
                }
                break;
            default:
                throw new \Exception("Parameter pertama Field dynamic modal harus berisi salah satu diantara input type (text, number, textarea, select)");
                break;
        }
    }

    public function selectOptionsStringToArray(string $string)
    {
        $array = [];
        if (str_contains($string, '|')) {
            foreach (explode('|', $string) as $value) {
                $array[] = str_contains($value, '@') ? [
                    'value' => explode('@', $value)[0],
                    'text'  => explode('@', $value)[1],
                ] : $value;
            }
        }
        return $array;
    }

    public function selectOptionsArrayToString(array $array)
    {
        $string = null;
        if ($this->IsArrayAllKeyInt($array)) {
            foreach ($array as $value) {
                $string .= $value . '@' . $value . '|';
            }
        } else {
            foreach ($array as $key => $value) {
                $string .= $key . '@' . $value . '|';
            }
        };
        return substr($string, 0, -1);
    }

    /*!
    \param[in] $InputArray          (array) Input array.
    \return                         (bool) \b true iff the input is an array whose keys are all integers.
    */
    public function IsArrayAllKeyInt($InputArray)
    {
        if (!is_array($InputArray)) {
            return false;
        }

        if (count($InputArray) <= 0) {
            return true;
        }

        return array_unique(array_map("is_int", array_keys($InputArray))) === array(true);
    }
}