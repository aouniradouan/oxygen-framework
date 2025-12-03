<?php

namespace Oxygen\Http\Requests;

use Oxygen\Core\Request;
use Oxygen\Core\Validator;
use Oxygen\Core\Flash;

/**
 * Form Request Base Class
 * 
 * Provides validation and authorization for form requests.
 * 
 * @package    Oxygen\Http\Requests
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    2.0.0
 */
abstract class FormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request
     * 
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules
     * 
     * @return array
     */
    abstract public function rules();

    /**
     * Get custom validation messages
     * 
     * @return array
     */
    public function messages()
    {
        return [];
    }

    /**
     * Get custom attribute names
     * 
     * @return array
     */
    public function attributes()
    {
        return [];
    }

    /**
     * Validate the request
     * 
     * @return array
     */
    public function validate()
    {
        if (!$this->authorize()) {
            $this->unauthorized();
        }

        $validator = new Validator($this->all(), $this->rules(), $this->messages(), $this->attributes());

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        return $validator->validated();
    }

    /**
     * Handle a failed validation attempt
     * 
     * @param Validator $validator
     * @return void
     */
    protected function failedValidation($validator)
    {
        foreach ($validator->errors() as $field => $messages) {
            foreach ($messages as $message) {
                Flash::error($message);
            }
        }

        // Redirect back with errors
        if (isset($_SERVER['HTTP_REFERER'])) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else {
            header('Location: /');
        }
        exit;
    }

    /**
     * Handle unauthorized request
     * 
     * @return void
     */
    protected function unauthorized()
    {
        Flash::error('You are not authorized to perform this action.');
        header('Location: /');
        exit;
    }
}

