<?php

namespace App\Http\Requests;

use App\Repository;
use Arr;

class UpdateRepository extends FormRequest {


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return $this->input('password')
            ? Repository::getRules()
            : Arr::except(Repository::getRules(), ['password']);
    }


    /**
     * {@inheritDoc}
     */
    protected function prepareForValidation() {
        $this->merge(['user_id' => $this->user()->id]);

        if (!$this->input('password')) {
            $this->replace($this->except(['password']));
        }
    }
}
