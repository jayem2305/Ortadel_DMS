<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FolderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|integer|exists:folders,id',
            'access_users' => 'nullable|array',
            'access_users.*' => 'integer|exists:users,id',
            'access_groups' => 'nullable|array',
            'access_groups.*' => 'integer|exists:groups,id',
            'access_roles' => 'nullable|array',
            'access_roles.*' => 'integer|exists:roles,id',
        ];
    }

    protected function prepareForValidation()
    {
        $parent = $this->input('parent_id');
        if ($parent === "" || $parent === null) {
            $this->merge(['parent_id' => null]);
        }
    }
}
