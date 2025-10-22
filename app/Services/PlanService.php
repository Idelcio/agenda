<?php

namespace App\Services;

use Illuminate\Support\Arr;

class PlanService
{
    private string $storagePath;

    public function __construct()
    {
        $this->storagePath = storage_path('app/plans.json');
    }

    public function all(): array
    {
        $plans = config('mercadopago.plans', []);

        // GARANTIR que $plans seja sempre um array
        if (!is_array($plans)) {
            \Log::error('PlanService: config(mercadopago.plans) não retornou array', [
                'type' => gettype($plans),
                'value' => $plans,
            ]);
            $plans = [];
        }

        if (is_file($this->storagePath)) {
            $overrides = json_decode(file_get_contents($this->storagePath), true) ?: [];

            foreach ($overrides as $slug => $data) {
                if (isset($plans[$slug]) && is_array($data)) {
                    $plans[$slug] = array_merge($plans[$slug], Arr::only($data, [
                        'price',
                        'discount_percent',
                        'name',
                        'description',
                        'duration_months',
                    ]));
                }
            }
        }

        return $plans;
    }

    public function update(string $slug, array $attributes): void
    {
        $plans = $this->all();

        if (! isset($plans[$slug])) {
            throw new \InvalidArgumentException('Plano nÃ£o encontrado.');
        }

        $updates = Arr::only($attributes, ['price', 'discount_percent']);

        if (array_key_exists('price', $updates)) {
            $updates['price'] = round((float) $updates['price'], 2);
        }

        if (array_key_exists('discount_percent', $updates)) {
            $updates['discount_percent'] = max(0, (int) $updates['discount_percent']);
        }

        $overrides = [];

        if (is_file($this->storagePath)) {
            $overrides = json_decode(file_get_contents($this->storagePath), true) ?: [];
        }

        $overrides[$slug] = array_merge($overrides[$slug] ?? [], $updates);

        file_put_contents(
            $this->storagePath,
            json_encode($overrides, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}