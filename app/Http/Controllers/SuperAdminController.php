<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Appointment;
use App\Models\ChatbotMessage;
use App\Services\PlanService;
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
        // Estatisticas gerais
        $totalEmpresas = User::where('tipo', 'empresa')->count();
        $empresasAtivas = User::where('tipo', 'empresa')
            ->where('acesso_ativo', true)
            ->count();
        $empresasVencidas = User::where('tipo', 'empresa')
            ->where('acesso_ativo', true)
            ->where('acesso_liberado_ate', '<', now())
            ->count();
        $mensagensQueryBase = Appointment::where('status_lembrete', 'enviado')
            ->whereNotNull('whatsapp_mensagem')
            ->whereNotNull('lembrete_enviado_em');

        $totalMensagensMes = (clone $mensagensQueryBase)
            ->where('lembrete_enviado_em', '>=', now()->startOfMonth())
            ->count();

        // Empresas recentes
        $empresasRecentes = User::where('tipo', 'empresa')
            ->latest()
            ->limit(10)
            ->get();

        // Dados para graficos
        $empresasPorPlano = User::where('tipo', 'empresa')
            ->select('plano', DB::raw('count(*) as total'))
            ->groupBy('plano')
            ->get();

        // Mensagens enviadas por mes (ultimos 6 meses)
        $mensagensUltimosSeisMeses = Appointment::select(
            DB::raw('DATE_FORMAT(lembrete_enviado_em, "%Y-%m") as mes'),
            DB::raw('count(*) as total')
        )
            ->where('status_lembrete', 'enviado')
            ->whereNotNull('whatsapp_mensagem')
            ->whereNotNull('lembrete_enviado_em')
            ->where('lembrete_enviado_em', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $mensagensPorEmpresa = Appointment::select(
                'users.id',
                'users.name',
                DB::raw('COUNT(appointments.id) as total')
            )
            ->join('users', 'appointments.user_id', '=', 'users.id')
            ->where('users.tipo', 'empresa')
            ->where('appointments.status_lembrete', 'enviado')
            ->whereNotNull('appointments.whatsapp_mensagem')
            ->whereNotNull('appointments.lembrete_enviado_em')
            ->where('appointments.lembrete_enviado_em', '>=', now()->startOfMonth())
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->get();

        return view('super-admin.dashboard', compact(
            'totalEmpresas',
            'empresasAtivas',
            'empresasVencidas',
            'totalMensagensMes',
            'empresasRecentes',
            'empresasPorPlano',
            'mensagensUltimosSeisMeses',
            'mensagensPorEmpresa'
        ));
    }

    /**
     * Lista todas as empresas com filtros
     */
    public function empresas(Request $request)
    {
        $query = User::where('tipo', 'empresa');

        // Filtros
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

        $empresaIds = $empresas->pluck('id');

        if ($empresaIds->isNotEmpty()) {
            $mensagensTotais = Appointment::select('user_id', DB::raw('COUNT(*) as total'))
                ->whereIn('user_id', $empresaIds)
                ->where('status_lembrete', 'enviado')
                ->whereNotNull('whatsapp_mensagem')
                ->whereNotNull('lembrete_enviado_em')
                ->groupBy('user_id')
                ->get()
                ->keyBy('user_id');

            $mensagensMes = Appointment::select('user_id', DB::raw('COUNT(*) as total'))
                ->whereIn('user_id', $empresaIds)
                ->where('status_lembrete', 'enviado')
                ->whereNotNull('whatsapp_mensagem')
                ->whereNotNull('lembrete_enviado_em')
                ->where('lembrete_enviado_em', '>=', now()->startOfMonth())
                ->groupBy('user_id')
                ->get()
                ->keyBy('user_id');

            $empresas->getCollection()->transform(function ($empresa) use ($mensagensTotais, $mensagensMes) {
                $empresa->total_mensagens = (int) ($mensagensTotais[$empresa->id]->total ?? 0);
                $empresa->mensagens_mes = (int) ($mensagensMes[$empresa->id]->total ?? 0);

                return $empresa;
            });
        }

        return view('super-admin.empresas.index', compact('empresas'));
    }

    /**
     * Detalhes de uma empresa especifica
     */
    public function empresaDetalhes($id)
    {
        $empresa = User::where('tipo', 'empresa')
            ->with(['appointments', 'clientes'])
            ->findOrFail($id);

        // Estatisticas da empresa
        $totalCompromissos = $empresa->appointments()->count();
        $compromissosConfirmados = $empresa->appointments()->where('status', 'confirmado')->count();
        $compromissosCancelados = $empresa->appointments()->where('status', 'cancelado')->count();
        $totalClientes = $empresa->clientes()->count();

        $mensagensBase = Appointment::where('user_id', $id)
            ->where('status_lembrete', 'enviado')
            ->whereNotNull('whatsapp_mensagem')
            ->whereNotNull('lembrete_enviado_em');

        $mensagensUltimos30Dias = (clone $mensagensBase)
            ->where('lembrete_enviado_em', '>=', now()->subDays(30))
            ->count();

        $mensagensMesAtual = (clone $mensagensBase)
            ->where('lembrete_enviado_em', '>=', now()->startOfMonth())
            ->count();

        $totalMensagens = (clone $mensagensBase)->count();

        $mensagensPorDia = (clone $mensagensBase)
            ->where('lembrete_enviado_em', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(lembrete_enviado_em) as dia'),
                DB::raw('count(*) as total')
            )
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
            'mensagensMesAtual',
            'totalMensagens',
            'mensagensPorDia'
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
            'acesso_ativo' => 'nullable|boolean',
            'acesso_liberado_ate' => 'nullable|date',
            'plano' => 'required|in:trial,monthly,quarterly,semiannual,annual',
            'limite_requisicoes_mes' => 'required|integer|min:0',
            'valor_pago' => 'nullable|numeric|min:0',
            'observacoes_admin' => 'nullable|string',
        ]);

        // Garante que acesso_ativo seja boolean
        $validated['acesso_ativo'] = $request->has('acesso_ativo') ? (bool) $request->acesso_ativo : false;

        $empresa->update($validated);

        return redirect()
            ->route('super-admin.empresas.detalhes', $id)
            ->with('success', 'Empresa atualizada com sucesso!');
    }

    /**
     * Liberar acesso temporario (trial)
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
     * Liberar acesso total (1 ano + credenciais WhatsApp)
     */
    public function liberarAcessoTotal($id)
    {
        $empresa = User::where('tipo', 'empresa')->findOrFail($id);

        $empresa->update([
            'acesso_liberado_ate' => now()->addYear(),
            'acesso_ativo' => true,
            'apibrasil_device_id' => $empresa->apibrasil_device_token ?: $empresa->apibrasil_device_id,
        ]);

        return redirect()
            ->route('super-admin.empresas.detalhes', $id)
            ->with('success', 'Acesso total liberado por 1 ano!');
    }

    /**
     * Deletar empresa
     */
    public function empresaDeletar($id)
    {
        $empresa = User::where('tipo', 'empresa')->findOrFail($id);

        // Deleta todos os relacionamentos
        $empresa->appointments()->delete();
        $empresa->clientes()->delete();
        ChatbotMessage::where('user_id', $id)->delete();

        $empresa->delete();

        return redirect()
            ->route('super-admin.empresas')
            ->with('success', 'Empresa deletada com sucesso!');
    }

    /**
     * Resetar contador de requisicoes
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
            ->with('success', 'Contador de requisicoes resetado!');
    }

    /**
     * Relatorios e Analytics
     */
    public function relatorios()
    {
        // Estatisticas de mensagens por empresa (top 10)
        $mensagensTotais = Appointment::select('user_id', DB::raw('COUNT(*) as total'))
            ->where('status_lembrete', 'enviado')
            ->whereNotNull('whatsapp_mensagem')
            ->whereNotNull('lembrete_enviado_em')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $mensagensMesAtual = Appointment::select('user_id', DB::raw('COUNT(*) as total'))
            ->where('status_lembrete', 'enviado')
            ->whereNotNull('whatsapp_mensagem')
            ->whereNotNull('lembrete_enviado_em')
            ->where('lembrete_enviado_em', '>=', now()->startOfMonth())
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $todasEmpresas = User::where('tipo', 'empresa')
            ->get()
            ->map(function ($empresa) use ($mensagensTotais, $mensagensMesAtual) {
                $empresa->total_mensagens = (int) ($mensagensTotais[$empresa->id]->total ?? 0);
                $empresa->mensagens_mes = (int) ($mensagensMesAtual[$empresa->id]->total ?? 0);
                return $empresa;
            })
            ->sortByDesc('total_mensagens')
            ->values();

        $topEmpresas = $todasEmpresas->take(10);

        // Receita total
        $receitaTotal = User::where('tipo', 'empresa')->sum('valor_pago');

        // Receita por mes (ultimos 12 meses)
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
            'todasEmpresas',
            'receitaTotal',
            'receitaPorMes'
        ));
    }

    /**
     * Lista todos os planos de assinatura
     */
    public function planos(PlanService $planService)
    {
        $plans = $planService->all();

        return view('super-admin.planos.index', compact('plans'));
    }

    /**
     * Editar um plano específico
     */
    public function planoEditar($slug, PlanService $planService)
    {
        $plans = $planService->all();

        if (!isset($plans[$slug])) {
            return redirect()
                ->route('super-admin.planos')
                ->with('error', 'Plano não encontrado!');
        }

        $plan = array_merge(['slug' => $slug], $plans[$slug]);

        return view('super-admin.planos.editar', compact('plan'));
    }

    /**
     * Atualizar um plano
     */
    public function planoAtualizar(Request $request, $slug, PlanService $planService)
    {
        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
            'discount_percent' => 'required|integer|min:0|max:100',
        ]);

        try {
            $planService->update($slug, $validated);

            return redirect()
                ->route('super-admin.planos')
                ->with('success', 'Plano atualizado com sucesso!');
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('super-admin.planos')
                ->with('error', $e->getMessage());
        }
    }
}
