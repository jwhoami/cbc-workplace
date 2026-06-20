<?php

declare(strict_types=1);

namespace App\Http\Requests\Public;

use App\Enums\ContractType;
use App\Enums\WorkModality;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Validates inbound query parameters for the public job board listing
 * (FR-013: URL is the single source of truth for view state).
 *
 * The request is anonymous-accessible (FR-001), so all rules are about
 * shape, not authorization. Bad inputs render the public error state
 * via the exception handler (FR-030) instead of a Laravel JSON dump.
 */
class SearchOffersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Public surface (FR-030): on validation failure render the friendly
     * error-state Blade with HTTP 400, instead of Laravel's default 302
     * redirect-back-with-errors. This keeps anonymous visitors on a stable
     * URL and gives crawlers a real status code.
     */
    protected function failedValidation(Validator $validator): void
    {
        $response = response()->view('public.errors.server-error', [
            'status' => 400,
        ], 400);

        throw new HttpResponseException($response);
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:200'],
            'category' => ['nullable', 'array', 'max:32'],
            'category.*' => ['integer', 'exists:categories,id'],
            'work_mode' => ['nullable', 'array', 'max:8'],
            'work_mode.*' => ['integer', 'in:'.implode(',', array_column(WorkModality::cases(), 'value'))],
            'contract' => ['nullable', 'array', 'max:8'],
            'contract.*' => ['integer', 'in:'.implode(',', array_column(ContractType::cases(), 'value'))],
            'city' => ['nullable', 'array', 'max:32'],
            'city.*' => ['string', 'max:100'],
            'sort' => ['nullable', 'string', 'in:recent,deadline'],
            'page' => ['nullable', 'integer', 'min:1', 'max:9999'],
        ];
    }

    /**
     * Returns a normalized filter dictionary suitable for SearchPublicOffersAction.
     *
     * @return array{
     *     keyword: ?string,
     *     filters: array<string, array<int, int|string>>,
     *     sort: string,
     *     page: int,
     * }
     */
    public function normalized(): array
    {
        return [
            'keyword' => trim((string) $this->input('q', '')) ?: null,
            'filters' => [
                'category' => array_values(array_map('intval', $this->input('category', []))),
                'work_mode' => array_values(array_map('intval', $this->input('work_mode', []))),
                'contract' => array_values(array_map('intval', $this->input('contract', []))),
                'city' => array_values($this->input('city', [])),
            ],
            'sort' => in_array($this->input('sort'), ['recent', 'deadline'], true)
                ? (string) $this->input('sort')
                : 'recent',
            'page' => max(1, (int) $this->input('page', 1)),
        ];
    }
}
