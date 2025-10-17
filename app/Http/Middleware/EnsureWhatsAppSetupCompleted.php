<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureWhatsAppSetupCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Se for um cliente (não é empresa), não precisa ter setup completo
        if ($user && $user->tipo === 'cliente') {
            return $next($request);
        }

        // Se for empresa e não completou o setup, redireciona
        if ($user && $user->tipo === 'empresa' && !$user->apibrasil_setup_completed) {
            // Permite acesso apenas às rotas de setup e logout
            if (!$request->routeIs('setup-whatsapp.*') && !$request->routeIs('logout')) {
                return redirect()->route('setup-whatsapp.index');
            }
        }

        return $next($request);
    }
}
