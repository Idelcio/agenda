<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\MercadoPagoService;
use Illuminate\Support\Facades\Auth;

class CheckSubscription
{
    private $mercadoPagoService;

    public function __construct(MercadoPagoService $mercadoPagoService)
    {
        $this->mercadoPagoService = $mercadoPagoService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Se não estiver autenticado, deixa o middleware de autenticação tratar
        if (!$user) {
            return $next($request);
        }

        // Super admin tem acesso vitalício (sempre liberado)
        if (isset($user->is_super_admin) && $user->is_super_admin) {
            return $next($request);
        }

        // Verifica se tem acesso liberado até uma data futura
        if ($user->acesso_liberado_ate && $user->acesso_liberado_ate->isFuture()) {
            // Mesmo com acesso liberado, precisa ter credenciais do WhatsApp configuradas
            $hasWhatsAppCredentials = !empty($user->apibrasil_device_token) && !empty($user->apibrasil_device_id);

            if (!$hasWhatsAppCredentials) {
                return redirect()->route('setup-whatsapp.index')
                    ->with('info', 'Por favor, configure suas credenciais do WhatsApp para continuar.');
            }

            return $next($request);
        }

        // REMOVIDO: A verificação de acesso_ativo não deve liberar acesso sozinha
        // O acesso_ativo é apenas uma flag administrativa, não substitui a assinatura

        // Verifica se o usuário tem assinatura ativa
        if (!$this->mercadoPagoService->hasActiveSubscription($user->id)) {
            // Se for requisição API, retorna JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você precisa ter uma assinatura ativa para acessar este recurso',
                    'requires_subscription' => true,
                ], 403);
            }

            // Se for web, redireciona para página de escolha de plano
            return redirect()->route('subscription.plans')
                ->with('error', 'Você precisa ter uma assinatura ativa para acessar este recurso.');
        }

        // Se tem assinatura ativa, verifica se tem credenciais do WhatsApp
        $hasWhatsAppCredentials = !empty($user->apibrasil_device_token) && !empty($user->apibrasil_device_id);

        if (!$hasWhatsAppCredentials) {
            return redirect()->route('setup-whatsapp.index')
                ->with('info', 'Por favor, configure suas credenciais do WhatsApp para continuar.');
        }

        return $next($request);
    }
}
