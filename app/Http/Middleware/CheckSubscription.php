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

        return $next($request);
    }
}
