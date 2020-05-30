<?php

namespace App\Http\Requests;

use App\Repository;

class StoreRepository extends FormRequest {


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return Repository::getRules();
    }


    /**
     * {@inheritDoc}
     */
    protected function prepareForValidation() {
        $this->merge(['user_id' => $this->user()->id]);
    }
}
