<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PublicEventKind;
use App\Enums\VisitorVariant;
use App\Models\PublicEvent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PublicEvent>
 */
class PublicEventFactory extends Factory
{
    protected $model = PublicEvent::class;

    public function definition(): array
    {
        $kind = $this->faker->randomElement(PublicEventKind::cases());

        return [
            'kind' => $kind,
            'correlation_id' => (string) Str::uuid(),
            'occurred_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'path' => $this->faker->randomElement([
                '/bolsa-de-trabajo',
                '/bolsa-de-trabajo/'.$this->faker->slug(3),
            ]),
            'query_string' => $this->faker->boolean(40) ? 'page='.$this->faker->numberBetween(1, 20) : null,
            'visitor_variant' => $this->faker->randomElement(VisitorVariant::cases())->value,
            'page_number' => $this->faker->boolean(60) ? $this->faker->numberBetween(1, 20) : null,
            'payload' => $this->buildPayloadFor($kind),
        ];
    }

    public function pageView(): static
    {
        return $this->state(fn () => [
            'kind' => PublicEventKind::PageView,
            'payload' => [],
        ]);
    }

    public function keywordQuery(string $folded = 'disenador', int $rawLength = 9): static
    {
        return $this->state(fn () => [
            'kind' => PublicEventKind::KeywordQuery,
            'payload' => [
                'folded_keyword' => $folded,
                'raw_length' => $rawLength,
                'active_filters' => [],
            ],
        ]);
    }

    public function filterChange(string $filterType = 'category'): static
    {
        return $this->state(fn () => [
            'kind' => PublicEventKind::FilterChange,
            'payload' => [
                'action' => 'add',
                'filter_type' => $filterType,
                'values' => [$this->faker->word()],
            ],
        ]);
    }

    public function detailOpen(string $slug = 'sample-offer', int $offerId = 1): static
    {
        return $this->state(fn () => [
            'kind' => PublicEventKind::DetailOpen,
            'payload' => [
                'slug' => $slug,
                'offer_id' => $offerId,
            ],
        ]);
    }

    public function errorShown(string $failureMode = 'timeout'): static
    {
        return $this->state(fn () => [
            'kind' => PublicEventKind::ErrorShown,
            'payload' => [
                'failure_mode' => $failureMode,
                'http_status' => 500,
            ],
        ]);
    }

    private function buildPayloadFor(PublicEventKind $kind): array
    {
        return match ($kind) {
            PublicEventKind::PageView => [],
            PublicEventKind::KeywordQuery => [
                'folded_keyword' => $this->faker->word(),
                'raw_length' => $this->faker->numberBetween(2, 30),
                'active_filters' => [],
            ],
            PublicEventKind::FilterChange => [
                'action' => $this->faker->randomElement(['add', 'remove', 'clear']),
                'filter_type' => $this->faker->randomElement(['city', 'category', 'work_mode', 'contract']),
                'values' => [$this->faker->word()],
            ],
            PublicEventKind::DetailOpen => [
                'slug' => $this->faker->slug(3),
                'offer_id' => $this->faker->numberBetween(1, 1000),
            ],
            PublicEventKind::ErrorShown => [
                'failure_mode' => $this->faker->randomElement(['timeout', '5xx', 'network', 'uncaught']),
                'http_status' => $this->faker->randomElement([500, 502, 503, 504, null]),
            ],
        };
    }
}
