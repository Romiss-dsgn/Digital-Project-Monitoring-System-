<?php

namespace App\JsonApi\V2\Users;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class UserRequest extends ResourceRequest
{

    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {
         /** @var \App\Models\User|null $model */
         if ($model = $this->model()) {
            return [
                'username'      => ['sometimes', 'string', Rule::unique('users')->ignore($model->id)],
                'name'          => ['sometimes', 'string'],
                'email'         => ['sometimes', 'email', Rule::unique('users')->ignore($model->id)],
                'badge_number'  => ['nullable', 'string', 'max:255', Rule::unique('users')->ignore($model->id)],
                'contact_number'=> ['nullable', 'string', 'max:50'],
                'position'      => ['nullable', 'string', 'max:255'],
                'office_unit'   => ['nullable', 'string', 'max:255'],
                'password'      => ['sometimes', 'confirmed', 'string', 'min:8'],
            ];
        }

        return [
            'username'      => ['nullable', 'string', Rule::unique('users')],
            'name'          => ['required', 'string'],
            'email'         => ['required', 'email', Rule::unique('users')],
            'badge_number'  => ['nullable', 'string', 'max:255', Rule::unique('users')],
            'contact_number'=> ['nullable', 'string', 'max:50'],
            'position'      => ['nullable', 'string', 'max:255'],
            'office_unit'   => ['nullable', 'string', 'max:255'],
            'password'      => ['required', 'confirmed', 'string', 'min:8'],
        ];
    }

}
