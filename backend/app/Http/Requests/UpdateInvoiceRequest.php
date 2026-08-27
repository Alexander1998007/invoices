<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $issueDate = $this->invoice->issue_date
            ? $this->invoice->issue_date->format('Y-m-d')
            : null;

        return [
            'net_amount' => 'required|numeric|gt:0',
            'vat_amount' => 'required|numeric|gte:0',
            'due_date'   => array_filter([
                'nullable',
                'date',
                $issueDate ? "after_or_equal:{$issueDate}" : null,
            ]),
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->invoice->status !== 'pending') {
                $validator->errors()->add('status', 'Only pending invoices can be updated.');
            }
        });
    }
}
