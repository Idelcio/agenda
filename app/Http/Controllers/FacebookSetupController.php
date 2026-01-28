<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class FacebookSetupController extends Controller
{
    /**
     * Exibe a página de configuração do WhatsApp Cloud API (Meta).
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Tabela de Preços Estimados (Valores em BRL para o Brasil)
        // Valores baseados na tabela oficial da Meta (sujeitos a variação cambial)
        $pricing = [
            'marketing' => [
                'title' => 'Marketing',
                'description' => 'Promoções, ofertas, novidades e convites.',
                'price' => 'R$ 0,35',
                'unit' => 'por conversa (24h)',
                'icon' => 'campaign', // material icon name
                'color' => 'text-purple-600',
                'bg' => 'bg-purple-50'
            ],
            'utility' => [
                'title' => 'Utilidade',
                'description' => 'Lembretes de agendamento, confirmações e atualizações de pedido.',
                'price' => 'R$ 0,20',
                'unit' => 'por conversa (24h)',
                'icon' => 'event',
                'color' => 'text-blue-600',
                'bg' => 'bg-blue-50'
            ],
            'authentication' => [
                'title' => 'Autenticação',
                'description' => 'Códigos de verificação e senhas descartáveis (OTP).',
                'price' => 'R$ 0,18',
                'unit' => 'por conversa (24h)',
                'icon' => 'lock',
                'color' => 'text-green-600',
                'bg' => 'bg-green-50'
            ],
            'service' => [
                'title' => 'Atendimento',
                'description' => 'Quando o cliente inicia a conversa para tirar dúvidas.',
                'price' => 'Grátis',
                'unit' => 'nas primeiras 1.000/mês',
                'note' => 'Após isso, aprox R$ 0,15',
                'icon' => 'support_agent',
                'color' => 'text-gray-600',
                'bg' => 'bg-gray-50'
            ]
        ];

        // Verifica se já está 'conectado' (tem token salvo)
        $isConnected = !empty($user->meta_access_token) && !empty($user->meta_phone_id);

        return view('setup-meta.index', [
            'pricing' => $pricing,
            'isConnected' => $isConnected,
            'user' => $user
        ]);
    }


    /**
     * Salva as credenciais manuais (Modo Desenvolvedor).
     */
    public function store(Request $request)
    {
        $request->validate([
            'meta_phone_id' => 'required|string',
            'meta_access_token' => 'required|string',
            'meta_business_id' => 'nullable|string',
        ]);

        $user = $request->user();

        $user->update([
            'whatsapp_driver' => 'meta',
            'meta_phone_id' => $request->meta_phone_id,
            'meta_access_token' => $request->meta_access_token,
            'meta_business_id' => $request->meta_business_id,
            'quota_limit' => 1000, // Começa com padrão de 1000
            'quota_usage' => 0
        ]);

        return redirect()->route('setup-meta.index')->with('success', 'Credenciais salvas com sucesso! Agora você pode testar o envio.');
    }
}
