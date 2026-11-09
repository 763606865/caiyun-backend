<?php

namespace App\SApi\Requests;

/** 目录列表分页参数。 */
class PaginationRequest extends Request
{
    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['per_page' => '每页条数'];
    }
}
