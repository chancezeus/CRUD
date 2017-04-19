<?php

namespace Backpack\CRUD\PanelTraits;

use Illuminate\Support\Arr;

trait FakeFields
{
    /**
     * Refactor the request array to something that can be passed to the model's create or update function.
     * The resulting array will only include the fields that are stored in the database and their values,
     * plus the '_token' and 'redirect_after_save' variables.
     *
     * @param Request $request - everything that was sent from the form, usually \Request::all()
     * @param string $form - create/update - to determine what fields should be compacted
     * @param int|bool $id
     *
     * @return array
     */
    public function compactFakeFields($request, $form = 'create', $id = false)
    {
        $fake_field_columns_to_encode = [];

        // get the right fields according to the form type (create/update)
        $fields = $this->getFields($form, $id);

        $locale = $request['locale'] ?? \App::getLocale();

        // go through each defined field
        foreach ($fields as $k => $field) {
            // if it's a fake field and the field is included in the request
            if (isset($fields[$k]['fake']) && $fields[$k]['fake'] == true) {
                if (Arr::exists($request, $field['name'])) {
                    $store_in = array_get($field, 'store_in', 'extras');

                    // remove the fake field
                    $value = array_pull($request, $field['name']);

                    if (!empty($value)) {
                        if (property_exists($this->model, 'translatable') && in_array($k, $this->model->getTranslatableAttributes(), true)) {
                            $current = [];
                            if ($id !== false) {
                                $item = $this->model->findOrFail($id);
                                $current = array_get($item->{$store_in}, $field['name'], []);
                            }

                            $current[$locale] = $value;
                            $value = $current;
                        }

                        $request[$store_in][$field['name']] = $value;

                        if (! in_array($store_in, $fake_field_columns_to_encode, true)) {
                            array_push($fake_field_columns_to_encode, $store_in);
                        }
                    }
                }
            }
        }

        // json_encode all fake_value columns in the database, so they can be properly stored and interpreted
        if (count($fake_field_columns_to_encode)) {
            foreach ($fake_field_columns_to_encode as $key => $value) {
                if (!property_exists($this->model, 'translatable') || !in_array($value, $this->model->getTranslatableAttributes(), true)) {
                    $request[$value] = json_encode($request[$value]);
                }
            }
        }

        // if there are no fake fields defined, this will just return the original Request in full
        // since no modifications or additions have been made to $request
        return $request;
    }
}
