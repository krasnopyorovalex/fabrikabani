<?php

namespace Domain\Service\Requests;

use App\Http\Requests\Request;
use Illuminate\Validation\Rule;

/**
 * Class UpdateServiceRequest
 * @package Domain\Service\Requests
 */
class UpdateServiceRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'bail|required|max:512',
            'parent_id' => 'numeric|exists:services,id|nullable',
            'title' => 'required|max:512',
            'description' => 'required|string|max:512',
            'text' => 'string|nullable',
            'short_text' => 'string|nullable',
            'price' => 'string|nullable',
            'is_published' => 'digits_between:0,1',
            'pos' => 'integer|min:0|max:255',
            'gallery_id' => 'integer|exists:galleries,id|nullable',
            'image' => 'image',
            'imageAlt' => 'string|max:255',
            'imageTitle' => 'string|max:255',
            'alias' => [
                'required',
                'max:64',
                Rule::unique('services')->ignore($this->id)
            ]
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Поле «Название» обязательно для заполнения',
            'title.required'  => 'Поле «Title» обязательно для заполнения',
            'description.required'  => 'Поле «Description» обязательно для заполнения',
            'text.required'  => 'Поле «Текст» обязательно для заполнения',
            'alias.required'  => 'Поле «Alias» обязательно для заполнения',
            'alias.unique'  => 'Значение поля «Alias» уже присутствует в базе данных',
        ];
    }
}
