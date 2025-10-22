<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MercadoPagoService;
use App\Services\PlanService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubscriptionWebController extends Controller
{
    private $mercadoPagoService;
    private $planService;

    public function __construct(MercadoPagoService $mercadoPagoService, PlanService $planService)
    {
        $this->mercadoPagoService = $mercadoPagoService;
        $this->planService = $planService;
    }

    /**
     * Exibe a página de escolha de planos
     */
    public function plans()
    {
        // DEBUG: Testar diferentes métodos de obter planos
        $configPlans = config('mercadopago.plans', []);
        $plans = $this->planService->all();
        $user = Auth::user();

        // DEBUG: Log detalhado
        Log::info('SubscriptionWebController@plans - DEBUG', [
            'config_plans' => $configPlans,
            'config_plans_count' => is_array($configPlans) ? count($configPlans) : 0,
            'config_plans_type' => gettype($configPlans),
            'service_plans' => $plans,
            'service_plans_count' => is_array($plans) ? count($plans) : 0,
            'service_plans_type' => gettype($plans),
            'user_id' => $user->id,
            'storage_path' => storage_path('app/plans.json'),
            'storage_exists' => file_exists(storage_path('app/plans.json')),
        ]);

        // GARANTIR que $plans seja sempre um array
        if (!is_array($plans) || empty($plans)) {
            Log::warning('SubscriptionWebController@plans - FALLBACK: usando config direto', [
                'plans_was' => gettype($plans),
                'config_is' => gettype($configPlans),
            ]);
            $plans = is_array($configPlans) ? $configPlans : [];
        }

        // PROTEÇÃO EXTRA: Se ainda estiver vazio, usar valores hardcoded
        if (empty($plans)) {
            Log::error('SubscriptionWebController@plans - ERRO CRÍTICO: config vazio, usando hardcoded');
            $plans = [
                'monthly' => [
                    'name' => 'Plano Mensal',
                    'description' => 'Acesso completo por 1 mês',
                    'price' => 59.90,
                    'duration_months' => 1,
                    'discount_percent' => 0,
                ],
                'quarterly' => [
                    'name' => 'Plano Trimestral',
                    'description' => 'Acesso completo por 3 meses',
                    'price' => 159.90,
                    'duration_months' => 3,
                    'discount_percent' => 11,
                ],
                'semiannual' => [
                    'name' => 'Plano Semestral',
                    'description' => 'Acesso completo por 6 meses',
                    'price' => 299.90,
                    'duration_months' => 6,
                    'discount_percent' => 17,
                ],
                'annual' => [
                    'name' => 'Plano Anual',
                    'description' => 'Acesso completo por 1 ano',
                    'price' => 549.90,
                    'duration_months' => 12,
                    'discount_percent' => 24,
                ],
            ];
        }

        // Verifica se já tem assinatura ativa
        $hasActiveSubscription = $this->mercadoPagoService->hasActiveSubscription($user->id);

        return view('subscription.plans', compact('plans', 'hasActiveSubscription'));
    }

    /**
     * Processa a escolha do plano e redireciona para pagamento
     */
    public function checkout(Request $request)
    {
        Log::info('SubscriptionWebController@checkout - INÍCIO', [
            'user_id' => Auth::id(),
            'plan_type' => $request->plan_type,
        ]);

        $request->validate([
            'plan_type' => 'required|in:monthly,quarterly,semiannual,annual',
        ]);

        $user = Auth::user();
        $planType = $request->plan_type;
        $plans = $this->planService->all();

        Log::info('SubscriptionWebController@checkout - Planos carregados', [
            'plans_count' => count($plans),
            'plan_exists' => isset($plans[$planType]),
        ]);

        // Verifica se o plano existe
        if (!isset($plans[$planType])) {
            Log::error('SubscriptionWebController@checkout - Plano não encontrado', [
                'plan_type' => $planType,
                'available_plans' => array_keys($plans),
            ]);
            return back()->with('error', 'Plano não encontrado.');
        }

        // Verifica se já existe uma assinatura ativa
        if ($this->mercadoPagoService->hasActiveSubscription($user->id)) {
            Log::warning('SubscriptionWebController@checkout - Usuário já tem assinatura', [
                'user_id' => $user->id,
            ]);
            return redirect()->route('subscription.current')
                ->with('error', 'Você já possui uma assinatura ativa.');
        }

        // Calcula o valor final com desconto aplicado
        $plan = $plans[$planType];
        $amount = $plan['price'];

        if (isset($plan['discount_percent']) && $plan['discount_percent'] > 0) {
            $amount = $amount * (1 - $plan['discount_percent'] / 100);
        }

        // Arredonda para 2 casas decimais
        $amount = round($amount, 2);

        Log::info('SubscriptionWebController@checkout - Criando preference', [
            'user_id' => $user->id,
            'plan_type' => $planType,
            'amount' => $amount,
            'access_token_exists' => !empty(config('mercadopago.access_token')),
            'access_token_length' => strlen(config('mercadopago.access_token', '')),
        ]);

        // Cria a preference no Mercado Pago
        $preference = $this->mercadoPagoService->createPreference($user, $planType, $amount);

        Log::info('SubscriptionWebController@checkout - Resultado da preference', [
            'preference_created' => $preference !== null,
            'preference_id' => $preference['id'] ?? null,
            'init_point' => $preference['init_point'] ?? null,
        ]);

        if (!$preference) {
            Log::error('SubscriptionWebController@checkout - Falha ao criar preference');
            return back()->with('error', 'Erro ao criar link de pagamento. Verifique os logs ou entre em contato com o suporte.');
        }

        // Cria a assinatura no banco com status pending
        $subscription = \App\Models\Subscription::create([
            'user_id' => $user->id,
            'plan_type' => $planType,
            'amount' => $amount,
            'status' => 'pending',
            'mercadopago_preference_id' => $preference['id'],
        ]);

        Log::info('SubscriptionWebController@checkout - Assinatura criada, redirecionando', [
            'subscription_id' => $subscription->id,
            'redirect_url' => $preference['init_point'],
        ]);

        // DEBUG: Mostra todos os dados antes de redirecionar
        dd([
            'subscription_id' => $subscription->id,
            'subscription' => $subscription,
            'preference_full' => $preference,
            'redirect_url' => $preference['init_point'],
            'user' => $user,
            'plan' => $plan,
            'amount' => $amount,
        ]);

        // Redireciona para o Mercado Pago
        return redirect($preference['init_point']);
    }

    /**
     * Exibe a assinatura atual do usuário
     */
    public function current()
    {
        $user = Auth::user();
        $subscription = $this->mercadoPagoService->getActiveSubscription($user->id);

        if (!$subscription) {
            return redirect()->route('subscription.plans')
                ->with('info', 'Você não possui uma assinatura ativa.');
        }

        return view('subscription.current', compact('subscription'));
    }

    /**
     * Exibe o histórico de assinaturas
     */
    public function history()
    {
        $user = Auth::user();
        $subscriptions = \App\Models\Subscription::where('user_id', $user->id)
            ->with('payments')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('subscription.history', compact('subscriptions'));
    }

    /**
     * Página de sucesso após pagamento
     */
    public function success()
    {
        return view('subscription.success');
    }

    /**
     * Página de erro após pagamento
     */
    public function failure()
    {
        return view('subscription.failure');
    }

    /**
     * Página de pendente após pagamento
     */
    public function pending()
    {
        return view('subscription.pending');
    }
}
