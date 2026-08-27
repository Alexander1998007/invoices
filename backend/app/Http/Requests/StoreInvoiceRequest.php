<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
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
        return [
            'number' => 'required|string|unique:invoices,number',
            'supplier_name' => 'required|string|max:255',
            'supplier_tax_id' => 'required|string|max:50',
            'net_amount' => 'required|numeric|gt:0',
            'vat_amount' => 'required|numeric|gte:0',
            'gross_amount' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $expected = (float)$this->net_amount + (float)$this->vat_amount;
                    if (abs((float)$value - $expected) > 0.01) {
                        $fail('Gross amount must equal net_amount + vat_amount.');
                    }
                },
            ],
            'currency' => 'nullable|string|max:3',
            'status' => 'nullable|in:pending,approved,rejected',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:issue_date',
        ];
    }
}
