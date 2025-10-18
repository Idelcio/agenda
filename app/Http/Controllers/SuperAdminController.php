<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Appointment;
use App\Models\ChatbotMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SuperAdminController extends Controller
{
    /**
     * Dashboard principal do Super Admin
     */
    public function index()
    {
        // 🔹 Estatísticas gerais
        $totalEmpresas = User::where('tipo', 'empresa')->count();
        $empresasAtivas = User::where('tipo', 'empresa')
            ->where('acesso_ativo', true)
            ->count();
        $empresasVencidas = User::where('tipo', 'empresa')
            ->where('acesso_ativo', true)
            ->where('acesso_liberado_ate', '<', now())
            ->count();
        $totalRequisicoesMes = User::where('tipo', 'empresa')->sum('requisicoes_mes_atual');

        // 🔹 Empresas recentes
        $empresasRecentes = User::where('tipo', 'empresa')
            ->latest()
            ->limit(10)
            ->get();

        // 🔹 Dados para gráficos
        $empresasPorPlano = User::where('tipo', 'empresa')
            ->select('plano', DB::raw('count(*) as total'))
            ->groupBy('plano')
            ->get();

        // 🔹 Requisições por mês (últimos 6 meses)
        $requisicoesUltimosSeisMeses = ChatbotMessage::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as mes'),
            DB::raw('count(*) as total')
        )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        return view('super-admin.dashboard', compact(
            'totalEmpresas',
            'empresasAtivas',
            'empresasVencidas',
            'totalRequisicoesMes',
            'empresasRecentes',
            'empresasPorPlano',
            'requisicoesUltimosSeisMeses'
        ));
    }

    /**
     * Lista todas as empresas com filtros
     */
    public function empresas(Request $request)
    {
        $query = User::where('tipo', 'empresa');

        // 🔹 Filtros
        if ($request->filled('status')) {
            if ($request->status === 'ativas') {
                $query->where('acesso_ativo', true)
                    ->where(function($q) {
                        $q->whereNull('acesso_liberado_ate')
                            ->orWhere('acesso_liberado_ate', '>=', now());
                    });
            } elseif ($request->status === 'vencidas') {
                $query->where('acesso_ativo', true)
                    ->where('acesso_liberado_ate', '<', now());
            } elseif ($request->status === 'bloqueadas') {
                $query->where('acesso_ativo', false);
            }
        }

        if ($request->filled('plano')) {
            $query->where('plano', $request->plano);
        }

        if ($request->filled('busca')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->busca}%")
                    ->orWhere('email', 'like', "%{$request->busca}%")
                    ->orWhere('whatsapp_number', 'like', "%{$request->busca}%");
            });
        }

        $empresas = $query->withCount('appointments')
            ->withCount('clientes')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('super-admin.empresas.index', compact('empresas'));
    }

    /**
     * Detalhes de uma empresa específica
     */
    public function empresaDetalhes($id)
    {
        $empresa = User::where('tipo', 'empresa')
            ->with(['appointments', 'clientes'])
            ->findOrFail($id);

        // 🔹 Estatísticas da empresa
        $totalCompromissos = $empresa->appointments()->count();
        $compromissosConfirmados = $empresa->appointments()->where('status', 'confirmado')->count();
        $compromissosCancelados = $empresa->appointments()->where('status', 'cancelado')->count();
        $totalClientes = $empresa->clientes()->count();

        // 🔹 Mensagens dos últimos 30 dias
        $mensagensUltimos30Dias = ChatbotMessage::where('user_id', $id)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        // 🔹 Gráfico de requisições por dia (últimos 30 dias)
        $requisicoesUltimos30Dias = ChatbotMessage::where('user_id', $id)
            ->select(
                DB::raw('DATE(created_at) as dia'),
                DB::raw('count(*) as total')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        return view('super-admin.empresas.detalhes', compact(
            'empresa',
            'totalCompromissos',
            'compromissosConfirmados',
            'compromissosCancelados',
            'totalClientes',
            'mensagensUltimos30Dias',
            'requisicoesUltimos30Dias'
        ));
    }

    /**
     * Editar empresa
     */
    public function empresaEditar($id)
    {
        $empresa = User::where('tipo', 'empresa')->findOrFail($id);
        return view('super-admin.empresas.editar', compact('empresa'));
    }

    /**
     * Atualizar empresa
     */
    public function empresaAtualizar(Request $request, $id)
    {
        $empresa = User::where('tipo', 'empresa')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'whatsapp_number' => 'nullable|string|max:20',
            'acesso_ativo' => 'boolean',
            'acesso_liberado_ate' => 'nullable|date',
            'plano' => 'required|in:trial,mensal,trimestral,semestral,anual',
            'limite_requisicoes_mes' => 'required|integer|min:0',
            'valor_pago' => 'nullable|numeric|min:0',
            'observacoes_admin' => 'nullable|string',
        ]);

        $empresa->update($validated);

        return redirect()
            ->route('super-admin.empresas.detalhes', $id)
            ->with('success', 'Empresa atualizada com sucesso!');
    }

    /**
     * Liberar acesso temporário (trial)
     */
    public function liberarAcessoTrial(Request $request, $id)
    {
        $empresa = User::where('tipo', 'empresa')->findOrFail($id);

        $dias = $request->input('dias', 3);

        $empresa->update([
            'acesso_ativo' => true,
            'acesso_liberado_ate' => now()->addDays($dias),
            'plano' => 'trial',
            'observacoes_admin' => "Trial liberado por {$dias} dias em " . now()->format('d/m/Y H:i'),
        ]);

        return redirect()
            ->route('super-admin.empresas.detalhes', $id)
            ->with('success', "Acesso trial liberado por {$dias} dias!");
    }

    /**
     * Bloquear/Desbloquear acesso
     */
    public function toggleAcesso($id)
    {
        $empresa = User::where('tipo', 'empresa')->findOrFail($id);

        $empresa->update([
            'acesso_ativo' => !$empresa->acesso_ativo,
        ]);

        $status = $empresa->acesso_ativo ? 'liberado' : 'bloqueado';

        return redirect()
            ->route('super-admin.empresas.detalhes', $id)
            ->with('success', "Acesso {$status} com sucesso!");
    }

    /**
     * Deletar empresa
     */
    public function empresaDeletar($id)
    {
        $empresa = User::where('tipo', 'empresa')->findOrFail($id);

        // 🔹 Deleta todos os relacionamentos
        $empresa->appointments()->delete();
        $empresa->clientes()->delete();
        ChatbotMessage::where('user_id', $id)->delete();

        $empresa->delete();

        return redirect()
            ->route('super-admin.empresas')
            ->with('success', 'Empresa deletada com sucesso!');
    }

    /**
     * Resetar contador de requisições
     */
    public function resetarRequisicoes($id)
    {
        $empresa = User::where('tipo', 'empresa')->findOrFail($id);

        $empresa->update([
            'requisicoes_mes_atual' => 0,
            'ultimo_reset_requisicoes' => now(),
        ]);

        return redirect()
            ->route('super-admin.empresas.detalhes', $id)
            ->with('success', 'Contador de requisições resetado!');
    }

    /**
     * Relatórios e Analytics
     */
    public function relatorios()
    {
        // 🔹 Total de requisições por empresa (top 10)
        $topEmpresas = User::where('tipo', 'empresa')
            ->orderBy('total_requisicoes', 'desc')
            ->limit(10)
            ->get();

        // 🔹 Receita total
        $receitaTotal = User::where('tipo', 'empresa')->sum('valor_pago');

        // 🔹 Receita por mês (últimos 12 meses)
        $receitaPorMes = User::where('tipo', 'empresa')
            ->select(
                DB::raw('DATE_FORMAT(data_ultimo_pagamento, "%Y-%m") as mes'),
                DB::raw('SUM(valor_pago) as total')
            )
            ->whereNotNull('data_ultimo_pagamento')
            ->where('data_ultimo_pagamento', '>=', now()->subMonths(12))
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        return view('super-admin.relatorios', compact(
            'topEmpresas',
            'receitaTotal',
            'receitaPorMes'
        ));
    }
}
