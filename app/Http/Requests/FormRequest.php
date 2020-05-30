<?php
namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest as BaseFormRequest;

abstract class FormRequest extends BaseFormRequest {


    protected function failedValidation(Validator $validator) {
        $this->session()->flash('error', 'Validation failed. Some of the fields are not valid.');
        parent::failedValidation($validator);
    }
}
