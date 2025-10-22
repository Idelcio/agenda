<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MercadoPagoService;
use App\Services\PlanService;
use Illuminate\Support\Facades\Auth;

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
        $configPlans = config('mercadopago.plans');
        $plans = $this->planService->all();
        $user = Auth::user();

        // DEBUG: Log detalhado
        \Log::info('SubscriptionWebController@plans - DEBUG', [
            'config_plans' => $configPlans,
            'config_plans_count' => is_array($configPlans) ? count($configPlans) : 0,
            'service_plans' => $plans,
            'service_plans_count' => is_array($plans) ? count($plans) : 0,
            'user_id' => $user->id,
            'storage_path' => storage_path('app/plans.json'),
            'storage_exists' => file_exists(storage_path('app/plans.json')),
        ]);

        // FALLBACK: Se $plans estiver vazio, usar direto do config
        if (empty($plans)) {
            \Log::warning('SubscriptionWebController@plans - FALLBACK: usando config direto');
            $plans = $configPlans;
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
        $request->validate([
            'plan_type' => 'required|in:monthly,quarterly,semiannual,annual',
        ]);

        $user = Auth::user();
        $planType = $request->plan_type;
        $plans = $this->planService->all();

        // Verifica se o plano existe
        if (!isset($plans[$planType])) {
            return back()->with('error', 'Plano não encontrado.');
        }

        // Verifica se já existe uma assinatura ativa
        if ($this->mercadoPagoService->hasActiveSubscription($user->id)) {
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

        // Cria a preference no Mercado Pago
        $preference = $this->mercadoPagoService->createPreference($user, $planType, $amount);

        if (!$preference) {
            return back()->with('error', 'Erro ao criar link de pagamento. Tente novamente.');
        }

        // Cria a assinatura no banco com status pending
        $subscription = \App\Models\Subscription::create([
            'user_id' => $user->id,
            'plan_type' => $planType,
            'amount' => $amount,
            'status' => 'pending',
            'mercadopago_preference_id' => $preference['id'],
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
