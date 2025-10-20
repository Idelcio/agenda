<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MercadoPagoService;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    private $mercadoPagoService;

    public function __construct(MercadoPagoService $mercadoPagoService)
    {
        $this->mercadoPagoService = $mercadoPagoService;
    }

    /**
     * Lista os planos disponíveis
     */
    public function plans()
    {
        $plans = config('mercadopago.plans');

        return response()->json([
            'success' => true,
            'plans' => $plans,
        ]);
    }

    /**
     * Cria uma assinatura e retorna o link de pagamento
     */
    public function create(Request $request)
    {
        $request->validate([
            'plan_type' => 'required|in:monthly,quarterly,semiannual,annual',
        ]);

        $user = Auth::user();
        $planType = $request->plan_type;
        $plans = config('mercadopago.plans');

        // Verifica se o plano existe
        if (!isset($plans[$planType])) {
            return response()->json([
                'success' => false,
                'message' => 'Plano inválido',
            ], 400);
        }

        $amount = $plans[$planType]['price'];

        // Verifica se já existe uma assinatura ativa
        if ($this->mercadoPagoService->hasActiveSubscription($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Você já possui uma assinatura ativa',
            ], 400);
        }

        // Cria a preference no Mercado Pago
        $preference = $this->mercadoPagoService->createPreference($user, $planType, $amount);

        if (!$preference) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar link de pagamento',
            ], 500);
        }

        // Cria a assinatura no banco com status pending
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_type' => $planType,
            'amount' => $amount,
            'status' => 'pending',
            'mercadopago_preference_id' => $preference['id'],
        ]);

        return response()->json([
            'success' => true,
            'subscription_id' => $subscription->id,
            'checkout_url' => $preference['init_point'], // URL para redirecionar o usuário
            'sandbox_url' => $preference['sandbox_init_point'] ?? null, // URL de teste
        ]);
    }

    /**
     * Retorna a assinatura atual do usuário
     */
    public function current()
    {
        $user = Auth::user();
        $subscription = $this->mercadoPagoService->getActiveSubscription($user->id);

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Você não possui uma assinatura ativa',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'subscription' => [
                'id' => $subscription->id,
                'plan_type' => $subscription->plan_type,
                'amount' => $subscription->amount,
                'status' => $subscription->status,
                'starts_at' => $subscription->starts_at?->format('Y-m-d H:i:s'),
                'expires_at' => $subscription->expires_at?->format('Y-m-d H:i:s'),
                'is_active' => $subscription->isActive(),
            ],
        ]);
    }

    /**
     * Cancela a assinatura do usuário
     */
    public function cancel()
    {
        $user = Auth::user();
        $subscription = $this->mercadoPagoService->getActiveSubscription($user->id);

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Você não possui uma assinatura ativa',
            ], 404);
        }

        $subscription->cancel();

        return response()->json([
            'success' => true,
            'message' => 'Assinatura cancelada com sucesso',
        ]);
    }

    /**
     * Histórico de assinaturas do usuário
     */
    public function history()
    {
        $user = Auth::user();
        $subscriptions = Subscription::where('user_id', $user->id)
            ->with('payments')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'subscriptions' => $subscriptions->map(function ($subscription) {
                return [
                    'id' => $subscription->id,
                    'plan_type' => $subscription->plan_type,
                    'amount' => $subscription->amount,
                    'status' => $subscription->status,
                    'starts_at' => $subscription->starts_at?->format('Y-m-d H:i:s'),
                    'expires_at' => $subscription->expires_at?->format('Y-m-d H:i:s'),
                    'created_at' => $subscription->created_at->format('Y-m-d H:i:s'),
                    'payments' => $subscription->payments->map(function ($payment) {
                        return [
                            'id' => $payment->id,
                            'status' => $payment->status,
                            'amount' => $payment->transaction_amount,
                            'payment_method' => $payment->payment_method_id,
                            'approved_at' => $payment->approved_at?->format('Y-m-d H:i:s'),
                        ];
                    }),
                ];
            }),
        ]);
    }
}
