<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ClienteController extends Controller
{
    /**
     * Exibe todos os clientes vinculados à empresa logada.
     */
    public function index()
    {
        $clientes = User::where('user_id', auth()->id())
            ->where('tipo', 'cliente')
            ->orderBy('name')
            ->paginate(15);

        return view('clientes.index', compact('clientes'));
    }


    /**
     * Formulário de criação.
     */
    public function create()
    {
        return view('clientes.create');
    }

    /**
     * Armazena um novo cliente (somente nome e WhatsApp).
     */
    public function store(Request $request, WhatsAppService $whatsapp)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'whatsapp_number' => ['required', 'string', 'max:20'],
        ]);

        $whatsappNumber = $this->normalizeWhatsappNumber($validated['whatsapp_number']);

        // Criação do cliente (sem email nem senha)
        $cliente = User::create([
            'name' => $validated['name'],
            'whatsapp_number' => $whatsappNumber,
            'tipo' => 'cliente', // Define como cliente
            'user_id' => auth()->id(), // Vincula à empresa logada
            'is_admin' => false,
            'email' => null,
            'password' => null,
        ]);

        // Mensagem de boas-vindas via WhatsApp
        try {
            $empresa = auth()->user();
            $mensagem = "Olá *{$cliente->name}*! 👋\n\n"
                . "Você foi cadastrado(a) no sistema de agendamentos de *{$empresa->name}*.\n\n"
                . "A partir de agora você receberá lembretes e confirmações de seus atendamentos por aqui.\n\n"
                . "Seja bem-vindo(a)! 😊";

            $whatsapp->sendText($cliente->whatsapp_number, $mensagem);

            Log::info('✅ Mensagem de boas-vindas enviada para novo cliente', [
                'cliente_id' => $cliente->id,
                'whatsapp' => $cliente->whatsapp_number,
            ]);
        } catch (\Exception $e) {
            Log::warning('⚠️ Não foi possível enviar mensagem de boas-vindas', [
                'cliente_id' => $cliente->id,
                'erro' => $e->getMessage(),
            ]);
        }

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente cadastrado com sucesso!');
    }

    /**
     * Exibe um cliente específico.
     */
    public function show(User $cliente)
    {
        if ($cliente->user_id !== auth()->id()) {
            abort(403, 'Acesso negado.');
        }

        return view('clientes.show', compact('cliente'));
    }

    /**
     * Formulário de edição de cliente.
     */
    public function edit(User $cliente)
    {
        if ($cliente->user_id !== auth()->id()) {
            abort(403, 'Acesso negado.');
        }

        return view('clientes.edit', compact('cliente'));
    }

    /**
     * Atualiza os dados de um cliente (somente nome e WhatsApp).
     */
    public function update(Request $request, User $cliente)
    {
        if ($cliente->user_id !== auth()->id()) {
            abort(403, 'Acesso negado.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'whatsapp_number' => ['required', 'string', 'max:20'],
        ]);

        $whatsappNumber = $this->normalizeWhatsappNumber($validated['whatsapp_number']);

        $cliente->fill([
            'name' => $validated['name'],
            'whatsapp_number' => $whatsappNumber,
        ]);

        $cliente->save();

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente atualizado com sucesso!');
    }

    private function normalizeWhatsappNumber(?string $value): string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        if ($digits === '') {
            throw ValidationException::withMessages([
                'whatsapp_number' => 'Informe um número de WhatsApp válido.',
            ]);
        }

        if (! str_starts_with($digits, '55')) {
            $digits = '55' . $digits;
        }

        $length = strlen($digits);

        if ($length < 12 || $length > 13) {
            throw ValidationException::withMessages([
                'whatsapp_number' => 'O número deve conter o DDD e o telefone (12 ou 13 dígitos após o 55).',
            ]);
        }

        return $digits;
    }

    /**
     * Exclui um cliente.
     */
    public function destroy(User $cliente)
    {
        if ($cliente->user_id !== auth()->id()) {
            abort(403, 'Acesso negado.');
        }

        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente excluído com sucesso!');
    }
}
