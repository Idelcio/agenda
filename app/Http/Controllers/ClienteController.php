<?php

namespace App\Http\Controllers;

use App\Jobs\SendMassMessageJob;
use App\Models\MassMessage;
use App\Models\MassMessageItem;
use App\Models\User;
use App\Services\WhatsAppService;
use Carbon\Carbon;
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
            ->with('clienteTags') // Carrega as tags dos clientes
            ->orderBy('name')
            ->paginate(15);

        // Carrega as tags da empresa para filtros e gerenciamento
        $tags = auth()->user()->tags()->orderBy('nome')->get();

        return view('clientes.index', compact('clientes', 'tags'));
    }


    /**
     * Formulário de criação.
     */
    public function create()
    {
        $tags = auth()->user()->tags()->orderBy('nome')->get();
        return view('clientes.create', compact('tags'));
    }

    /**
     * Armazena um novo cliente (somente nome e WhatsApp).
     */
    public function store(Request $request, WhatsAppService $whatsapp)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'whatsapp_number' => ['required', 'string', 'max:20'],
            'tag_ids' => ['nullable', 'string'],
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

        // Adiciona as tags selecionadas ao cliente
        if (!empty($validated['tag_ids'])) {
            $tagIds = array_filter(explode(',', $validated['tag_ids']));
            if (!empty($tagIds)) {
                // Verifica se todas as tags pertencem ao usuário logado
                $validTags = auth()->user()->tags()->whereIn('id', $tagIds)->pluck('id')->toArray();
                if (!empty($validTags)) {
                    $cliente->clienteTags()->attach($validTags);
                }
            }
        }

        // Mensagem de boas-vindas via WhatsApp - DESABILITADO
        // try {
        //     $empresa = auth()->user();
        //     $mensagem = "Olá *{$cliente->name}*! 👋\n\n"
        //         . "Você foi cadastrado(a) no sistema de agendamentos de *{$empresa->name}*.\n\n"
        //         . "A partir de agora você receberá lembretes e confirmações de seus atendamentos por aqui.\n\n"
        //         . "Seja bem-vindo(a)! 😊";
        //
        //     $whatsapp->sendText($cliente->whatsapp_number, $mensagem);
        //
        //     Log::info('✅ Mensagem de boas-vindas enviada para novo cliente', [
        //         'cliente_id' => $cliente->id,
        //         'whatsapp' => $cliente->whatsapp_number,
        //     ]);
        // } catch (\Exception $e) {
        //     Log::warning('⚠️ Não foi possível enviar mensagem de boas-vindas', [
        //         'cliente_id' => $cliente->id,
        //         'erro' => $e->getMessage(),
        //     ]);
        // }

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

        // Carrega as tags do usuário logado e as tags do cliente
        $tags = auth()->user()->tags()->orderBy('nome')->get();
        $cliente->load('clienteTags');

        return view('clientes.edit', compact('cliente', 'tags'));
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

    /**
     * Processa envio em massa (texto, imagem ou ambos) com possibilidade de agendamento.
     */
    public function sendMassMessage(Request $request)
    {
        $agendamentoTipo = $request->input('agendamento_tipo');

        $validated = $request->validate([
            'client_ids' => ['required', 'json'],
            'titulo' => ['required', 'string', 'max:150'],
            'mensagem' => ['required', 'string', 'max:1000'],
            'agendamento_tipo' => ['required', Rule::in(['imediato', 'agendado'])],
            'scheduled_for' => [
                Rule::requiredIf(fn() => $agendamentoTipo === 'agendado'),
                'nullable',
                'date',
            ],
        ], [
            'titulo.required' => 'Informe um titulo para identificar o envio.',
            'mensagem.required' => 'Informe o conteudo da mensagem.',
            'scheduled_for.required' => 'Informe a data e hora para agendamento.',
        ]);

        $clientIds = json_decode($validated['client_ids'], true);

        if (empty($clientIds) || ! is_array($clientIds)) {
            return redirect()->route('clientes.index')
                ->with('error', 'Nenhum cliente foi selecionado.');
        }

        $clientes = User::where('user_id', auth()->id())
            ->where('tipo', 'cliente')
            ->whereIn('id', $clientIds)
            ->whereNotNull('whatsapp_number')
            ->get();

        if ($clientes->isEmpty()) {
            return redirect()->route('clientes.index')
                ->with('error', 'Nenhum cliente com WhatsApp foi encontrado.');
        }

        $scheduledFor = now();

        if ($validated['agendamento_tipo'] === 'agendado') {
            try {
                $scheduledFor = Carbon::parse($validated['scheduled_for']);
            } catch (\Exception $e) {
                throw ValidationException::withMessages([
                    'scheduled_for' => 'Data e hora de agendamento invalidas.',
                ]);
            }

            if ($scheduledFor->lessThanOrEqualTo(now())) {
                throw ValidationException::withMessages([
                    'scheduled_for' => 'Escolha um horario futuro para agendar o envio.',
                ]);
            }
        }

        $destinatarios = $clientes->count();
        $scheduledLabel = $scheduledFor->greaterThan(now())
            ? 'O envio esta agendado para ' . $scheduledFor->format('d/m/Y H:i') . '.'
            : 'O envio sera iniciado em instantes.';

        $massMessage = MassMessage::create([
            'user_id' => auth()->id(),
            'titulo' => $validated['titulo'],
            'mensagem' => $validated['mensagem'],
            'total_destinatarios' => $destinatarios,
            'status' => 'pendente',
            'scheduled_for' => $scheduledFor,
        ]);

        foreach ($clientes as $cliente) {
            MassMessageItem::create([
                'mass_message_id' => $massMessage->id,
                'cliente_id' => $cliente->id,
                'telefone' => $cliente->whatsapp_number,
                'status' => 'pendente',
            ]);
        }

        $dispatchAt = $scheduledFor->greaterThan(now()) ? $scheduledFor : now();
        SendMassMessageJob::dispatch($massMessage->id)->delay($dispatchAt);

        Log::info('Envio em massa (texto) configurado', [
            'mass_message_id' => $massMessage->id,
            'user_id' => auth()->id(),
            'total_clientes' => $destinatarios,
            'scheduled_for' => $dispatchAt,
        ]);

        return redirect()->route('agenda.index')
            ->with('success', "Envio configurado para {$destinatarios} cliente(s). {$scheduledLabel}");
    }
}
