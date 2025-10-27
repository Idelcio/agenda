<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWhatsAppMessageTemplateRequest;
use App\Models\WhatsAppMessageTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class QuickMessageTemplateController extends Controller
{
    public function store(StoreWhatsAppMessageTemplateRequest $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if ($user->quickMessageTemplates()->count() >= WhatsAppMessageTemplate::MAX_PER_USER) {
            return $this->limitReachedResponse($request);
        }

        $user->quickMessageTemplates()->create([
            'message' => trim((string) $request->input('mensagem')),
        ]);

        $templates = $this->templatesPayload($user->quickMessageTemplates());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Mensagem salva para uso futuro.',
                'templates' => $templates,
            ]);
        }

        return Redirect::back()->with('status', 'quick-template-saved');
    }

    public function update(StoreWhatsAppMessageTemplateRequest $request, WhatsAppMessageTemplate $template): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if ($template->user_id !== $user?->id) {
            abort(403);
        }

        $template->update([
            'message' => trim((string) $request->input('mensagem')),
        ]);

        $templates = $this->templatesPayload($user->quickMessageTemplates());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Mensagem atualizada.',
                'templates' => $templates,
            ]);
        }

        return Redirect::back()->with('status', 'quick-template-updated');
    }

    public function destroy(Request $request, WhatsAppMessageTemplate $template): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if ($template->user_id !== $user?->id) {
            abort(403);
        }

        $template->delete();

        $templates = $this->templatesPayload($user->quickMessageTemplates());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Mensagem removida.',
                'templates' => $templates,
            ]);
        }

        return Redirect::back()->with('status', 'quick-template-removed');
    }

    private function limitReachedResponse(Request $request): JsonResponse|RedirectResponse
    {
        $message = sprintf(
            'Você já possui %d mensagens salvas. Remova uma para adicionar outra.',
            WhatsAppMessageTemplate::MAX_PER_USER
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], 422);
        }

        return Redirect::back()->withErrors([
            'quick_message_template' => $message,
        ]);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Relations\HasMany|\Illuminate\Database\Eloquent\Builder $query
     * @return array<int, array{id:int,message:string,preview:string}>
     */
    private function templatesPayload($query): array
    {
        return $query
            ->latest()
            ->take(WhatsAppMessageTemplate::MAX_PER_USER)
            ->get()
            ->map(function (WhatsAppMessageTemplate $template) {
                return [
                    'id' => $template->id,
                    'message' => $template->message,
                    'preview' => Str::limit($template->message, 80),
                ];
            })
            ->values()
            ->all();
    }
}
