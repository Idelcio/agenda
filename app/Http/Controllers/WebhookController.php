<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MercadoPagoService;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    private $mercadoPagoService;

    public function __construct(MercadoPagoService $mercadoPagoService)
    {
        $this->mercadoPagoService = $mercadoPagoService;
    }

    /**
     * Recebe notificações do Mercado Pago
     */
    public function mercadopago(Request $request)
    {
        // Log da requisição para debug
        Log::info('Webhook Mercado Pago recebido', [
            'body' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        try {
            // Processa o webhook
            $result = $this->mercadoPagoService->processWebhook($request->all());

            if ($result) {
                return response()->json(['success' => true], 200);
            }

            return response()->json(['success' => false], 400);
        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook do Mercado Pago', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Retorna 200 mesmo com erro para não reenviar o webhook
            return response()->json(['success' => false, 'error' => $e->getMessage()], 200);
        }
    }
}
