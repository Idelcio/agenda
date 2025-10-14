<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    /**
     * Exibe todos os usuários (clientes e/ou empresas filhas) vinculados à empresa logada.
     */
    public function index()
    {
        $empresas = User::where('user_id', auth()->id())
            ->orderBy('name')
            ->paginate(15);

        return view('clientes.index', compact('empresas'));
    }


    /**
     * Formulário de criação.
     */
    public function create()
    {
        return view('clientes.create');
    }

    /**
     * Armazena um novo usuário (empresa filha ou cliente).
     */
    public function store(Request $request, WhatsAppService $whatsapp)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'whatsapp_number' => ['required', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        // Criação da empresa filha
        $usuario = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'whatsapp_number' => $validated['whatsapp_number'],
            'password' => isset($validated['password'])
                ? Hash::make($validated['password'])
                : Hash::make('empresa123'),
            'tipo' => 'empresa', // ⚙️ define que é uma empresa filha
            'user_id' => auth()->id(), // vincula à empresa logada
            'is_admin' => false,
        ]);

        // 🔹 Mensagem de boas-vindas via WhatsApp
        try {
            $empresa = auth()->user();
            $mensagem = "Olá *{$usuario->name}*! 👋\n\n"
                . "Você foi cadastrado(a) no sistema de agendamentos de *{$empresa->name}*.\n\n"
                . "A partir de agora você receberá lembretes e confirmações de seus atendimentos por aqui.\n\n"
                . "Seja bem-vindo(a)! 😊";

            $whatsapp->sendText($usuario->whatsapp_number, $mensagem);

            Log::info('✅ Mensagem de boas-vindas enviada para nova empresa', [
                'empresa_id' => $usuario->id,
                'whatsapp' => $usuario->whatsapp_number,
            ]);
        } catch (\Exception $e) {
            Log::warning('⚠️ Não foi possível enviar mensagem de boas-vindas', [
                'empresa_id' => $usuario->id,
                'erro' => $e->getMessage(),
            ]);
        }

        return redirect()->route('clientes.index')
            ->with('success', 'Empresa cadastrada com sucesso!');
    }

    /**
     * Exibe uma empresa específica.
     */
    public function show(User $usuario)
    {
        if ($usuario->user_id !== auth()->id()) {
            abort(403, 'Acesso negado.');
        }

        return view('clientes.show', compact('usuario'));
    }

    /**
     * Formulário de edição de empresa.
     */
    public function edit(User $usuario)
    {
        if ($usuario->user_id !== auth()->id()) {
            abort(403, 'Acesso negado.');
        }

        return view('clientes.edit', compact('usuario'));
    }

    /**
     * Atualiza os dados de uma empresa.
     */
    public function update(Request $request, User $usuario)
    {
        if ($usuario->user_id !== auth()->id()) {
            abort(403, 'Acesso negado.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($usuario->id)],
            'whatsapp_number' => ['required', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $usuario->fill([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'whatsapp_number' => $validated['whatsapp_number'],
        ]);

        if (!empty($validated['password'])) {
            $usuario->password = Hash::make($validated['password']);
        }

        $usuario->save();

        return redirect()->route('clientes.index')
            ->with('success', 'Empresa atualizada com sucesso!');
    }

    /**
     * Exclui uma empresa filha.
     */
    public function destroy(User $usuario)
    {
        if ($usuario->user_id !== auth()->id()) {
            abort(403, 'Acesso negado.');
        }

        $usuario->delete();

        return redirect()->route('clientes.index')
            ->with('success', 'Empresa excluída com sucesso!');
    }
}
