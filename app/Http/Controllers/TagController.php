<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TagController extends Controller
{
    /**
     * Listar todas as tags do usuário logado
     */
    public function index()
    {
        $tags = Auth::user()->tags()->orderBy('nome')->get();

        return response()->json([
            'tags' => $tags
        ]);
    }

    /**
     * Criar nova tag
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:50',
            'cor' => 'required|string|max:20',
        ]);

        // Verifica se já existe tag com esse nome para o usuário
        $existente = Auth::user()->tags()
            ->where('nome', $validated['nome'])
            ->first();

        if ($existente) {
            return response()->json([
                'message' => 'Já existe uma tag com este nome.'
            ], 422);
        }

        $tag = Auth::user()->tags()->create($validated);

        return response()->json([
            'message' => 'Tag criada com sucesso!',
            'tag' => $tag
        ], 201);
    }

    /**
     * Atualizar tag existente
     */
    public function update(Request $request, Tag $tag)
    {
        // Verifica se a tag pertence ao usuário logado
        if ($tag->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Você não tem permissão para editar esta tag.'
            ], 403);
        }

        $validated = $request->validate([
            'nome' => 'required|string|max:50',
            'cor' => 'required|string|max:20',
        ]);

        // Verifica se já existe outra tag com esse nome
        $existente = Auth::user()->tags()
            ->where('nome', $validated['nome'])
            ->where('id', '!=', $tag->id)
            ->first();

        if ($existente) {
            return response()->json([
                'message' => 'Já existe uma tag com este nome.'
            ], 422);
        }

        $tag->update($validated);

        return response()->json([
            'message' => 'Tag atualizada com sucesso!',
            'tag' => $tag
        ]);
    }

    /**
     * Excluir tag
     */
    public function destroy(Tag $tag)
    {
        // Verifica se a tag pertence ao usuário logado
        if ($tag->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Você não tem permissão para excluir esta tag.'
            ], 403);
        }

        $tag->delete();

        return response()->json([
            'message' => 'Tag excluída com sucesso!'
        ]);
    }

    /**
     * Adicionar tag a um cliente
     */
    public function attachToCliente(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:users,id',
            'tag_id' => 'required|exists:tags,id',
        ]);

        $tag = Tag::findOrFail($validated['tag_id']);

        // Verifica se a tag pertence ao usuário logado
        if ($tag->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Você não tem permissão para usar esta tag.'
            ], 403);
        }

        // Verifica se o cliente pertence ao usuário logado
        $cliente = Auth::user()->clientes()->findOrFail($validated['cliente_id']);

        // Adiciona a tag ao cliente (se já não tiver)
        if (!$cliente->clienteTags()->where('tag_id', $tag->id)->exists()) {
            $cliente->clienteTags()->attach($tag->id);
        }

        return response()->json([
            'message' => 'Tag adicionada ao cliente com sucesso!',
            'tag' => $tag
        ]);
    }

    /**
     * Remover tag de um cliente
     */
    public function detachFromCliente(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:users,id',
            'tag_id' => 'required|exists:tags,id',
        ]);

        $tag = Tag::findOrFail($validated['tag_id']);

        // Verifica se a tag pertence ao usuário logado
        if ($tag->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Você não tem permissão para usar esta tag.'
            ], 403);
        }

        // Verifica se o cliente pertence ao usuário logado
        $cliente = Auth::user()->clientes()->findOrFail($validated['cliente_id']);

        // Remove a tag do cliente
        $cliente->clienteTags()->detach($tag->id);

        return response()->json([
            'message' => 'Tag removida do cliente com sucesso!'
        ]);
    }

    /**
     * Adicionar tag a múltiplos clientes de uma vez
     */
    public function attachToMultipleClientes(Request $request)
    {
        $validated = $request->validate([
            'cliente_ids' => 'required|array',
            'cliente_ids.*' => 'exists:users,id',
            'tag_id' => 'required|exists:tags,id',
        ]);

        $tag = Tag::findOrFail($validated['tag_id']);

        // Verifica se a tag pertence ao usuário logado
        if ($tag->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Você não tem permissão para usar esta tag.'
            ], 403);
        }

        $count = 0;
        foreach ($validated['cliente_ids'] as $clienteId) {
            // Verifica se o cliente pertence ao usuário logado
            $cliente = Auth::user()->clientes()->find($clienteId);

            if ($cliente && !$cliente->clienteTags()->where('tag_id', $tag->id)->exists()) {
                $cliente->clienteTags()->attach($tag->id);
                $count++;
            }
        }

        return response()->json([
            'message' => "Tag adicionada a {$count} cliente(s) com sucesso!",
            'tag' => $tag,
            'count' => $count
        ]);
    }

    /**
     * Remover tag de múltiplos clientes de uma vez
     */
    public function detachFromMultipleClientes(Request $request)
    {
        $validated = $request->validate([
            'cliente_ids' => 'required|array',
            'cliente_ids.*' => 'exists:users,id',
            'tag_id' => 'required|exists:tags,id',
        ]);

        $tag = Tag::findOrFail($validated['tag_id']);

        // Verifica se a tag pertence ao usuário logado
        if ($tag->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Você não tem permissão para usar esta tag.'
            ], 403);
        }

        $count = 0;
        foreach ($validated['cliente_ids'] as $clienteId) {
            // Verifica se o cliente pertence ao usuário logado
            $cliente = Auth::user()->clientes()->find($clienteId);

            if ($cliente) {
                $cliente->clienteTags()->detach($tag->id);
                $count++;
            }
        }

        return response()->json([
            'message' => "Tag removida de {$count} cliente(s) com sucesso!",
            'count' => $count
        ]);
    }
}
