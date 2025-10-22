<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoPagoService
{
    private $accessToken;
    private $baseUrl;

    public function __construct()
    {
        $this->accessToken = config('mercadopago.access_token');
        $this->baseUrl = 'https://api.mercadopago.com';
    }

    /**
     * Cria uma preference (link de pagamento) no Mercado Pago
     */
    public function createPreference($user, $planType, $amount)
    {
        Log::info('MercadoPagoService@createPreference - INÍCIO', [
            'user_id' => $user->id,
            'plan_type' => $planType,
            'amount' => $amount,
            'has_access_token' => !empty($this->accessToken),
            'access_token_prefix' => substr($this->accessToken ?? '', 0, 20) . '...',
        ]);

        $planNames = [
            'monthly' => 'Plano Mensal',
            'quarterly' => 'Plano Trimestral',
            'semiannual' => 'Plano Semestral',
            'annual' => 'Plano Anual',
        ];

        $preference = [
            'items' => [
                [
                    'title' => $planNames[$planType] ?? 'Assinatura',
                    'description' => 'Acesso completo à plataforma',
                    'quantity' => 1,
                    'unit_price' => (float) $amount,
                    'currency_id' => 'BRL',
                ]
            ],
            'payer' => [
                'name' => 'Agenda Digital',
                'email' => $user->email,
            ],
            'back_urls' => [
                'success' => config('mercadopago.back_urls.success'),
                'failure' => config('mercadopago.back_urls.failure'),
                'pending' => config('mercadopago.back_urls.pending'),
            ],
            'auto_return' => 'approved',
            'notification_url' => config('mercadopago.notification_url'),
            'external_reference' => $user->id . '-' . time(),
            'payment_methods' => [
                'excluded_payment_types' => [],
                'installments' => 12,
            ],
            'metadata' => [
                'user_id' => $user->id,
                'plan_type' => $planType,
            ],
        ];

        Log::info('MercadoPagoService@createPreference - Preference payload', [
            'url' => $this->baseUrl . '/checkout/preferences',
            'back_urls' => $preference['back_urls'],
            'notification_url' => $preference['notification_url'],
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/checkout/preferences', $preference);

            Log::info('MercadoPagoService@createPreference - Response recebida', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body_preview' => substr($response->body(), 0, 500),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('MercadoPagoService@createPreference - SUCESSO', [
                    'preference_id' => $data['id'] ?? null,
                    'init_point' => $data['init_point'] ?? null,
                ]);
                return $data;
            }

            Log::error('Erro ao criar preference no Mercado Pago', [
                'response' => $response->json(),
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exceção ao criar preference no Mercado Pago', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Busca informações de um pagamento no Mercado Pago
     */
    public function getPayment($paymentId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
            ])->get($this->baseUrl . '/v1/payments/' . $paymentId);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Erro ao buscar pagamento no Mercado Pago', [
                'payment_id' => $paymentId,
                'response' => $response->json(),
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exceção ao buscar pagamento no Mercado Pago', [
                'payment_id' => $paymentId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Processa o webhook do Mercado Pago
     */
    public function processWebhook($data)
    {
        try {
            // O Mercado Pago envia o tipo e o ID do recurso
            if (!isset($data['type']) || !isset($data['data']['id'])) {
                Log::warning('Webhook do Mercado Pago sem dados necessários', ['data' => $data]);
                return false;
            }

            // Processa apenas notificações de pagamento
            if ($data['type'] !== 'payment') {
                Log::info('Webhook ignorado - tipo diferente de payment', ['type' => $data['type']]);
                return false;
            }

            $paymentId = $data['data']['id'];

            // Busca as informações completas do pagamento
            $paymentData = $this->getPayment($paymentId);

            if (!$paymentData) {
                Log::error('Não foi possível buscar dados do pagamento', ['payment_id' => $paymentId]);
                return false;
            }

            // Processa o pagamento
            $this->processPayment($paymentData);

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook do Mercado Pago', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Processa os dados do pagamento e atualiza a assinatura
     */
    private function processPayment($paymentData)
    {
        $mercadopagoPaymentId = $paymentData['id'];
        $status = $paymentData['status'];
        $metadata = $paymentData['metadata'] ?? [];
        $userId = $metadata['user_id'] ?? null;
        $planType = $metadata['plan_type'] ?? null;

        if (!$userId) {
            Log::error('Pagamento sem user_id nos metadados', ['payment_data' => $paymentData]);
            return;
        }

        // Busca ou cria a assinatura
        $subscription = Subscription::where('user_id', $userId)
            ->where('mercadopago_preference_id', $paymentData['preference_id'] ?? null)
            ->first();

        if (!$subscription) {
            // Cria nova assinatura se não existir
            $subscription = Subscription::create([
                'user_id' => $userId,
                'plan_type' => $planType ?? 'monthly',
                'amount' => $paymentData['transaction_amount'],
                'status' => 'pending',
                'mercadopago_preference_id' => $paymentData['preference_id'] ?? null,
                'mercadopago_payment_id' => $mercadopagoPaymentId,
            ]);
        }

        // Cria ou atualiza o registro de pagamento
        $payment = Payment::updateOrCreate(
            ['mercadopago_payment_id' => $mercadopagoPaymentId],
            [
                'subscription_id' => $subscription->id,
                'user_id' => $userId,
                'mercadopago_preference_id' => $paymentData['preference_id'] ?? null,
                'status' => $status,
                'status_detail' => $paymentData['status_detail'] ?? null,
                'transaction_amount' => $paymentData['transaction_amount'],
                'payment_method_id' => $paymentData['payment_method_id'] ?? null,
                'payment_type_id' => $paymentData['payment_type_id'] ?? null,
                'metadata' => json_encode($metadata),
                'approved_at' => $status === 'approved' ? now() : null,
            ]
        );

        // Se o pagamento foi aprovado, ativa a assinatura
        if ($status === 'approved') {
            $subscription->activate();
            Log::info('Assinatura ativada', [
                'subscription_id' => $subscription->id,
                'user_id' => $userId,
                'expires_at' => $subscription->expires_at,
            ]);
        }

        // Se o pagamento foi rejeitado ou cancelado, cancela a assinatura
        if (in_array($status, ['rejected', 'cancelled', 'refunded', 'charged_back'])) {
            $subscription->cancel();
            Log::info('Assinatura cancelada devido ao status do pagamento', [
                'subscription_id' => $subscription->id,
                'user_id' => $userId,
                'payment_status' => $status,
            ]);
        }
    }

    /**
     * Verifica se um usuário tem assinatura ativa
     */
    public function hasActiveSubscription($userId)
    {
        $subscription = Subscription::where('user_id', $userId)
            ->active()
            ->first();

        return $subscription !== null;
    }

    /**
     * Retorna a assinatura ativa do usuário
     */
    public function getActiveSubscription($userId)
    {
        return Subscription::where('user_id', $userId)
            ->active()
            ->first();
    }
}
