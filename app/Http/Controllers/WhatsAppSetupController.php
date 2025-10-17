<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WhatsAppSetupController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->middleware('auth');
        $this->whatsappService = $whatsappService;
    }

    /**
     * Exibe a página de setup do WhatsApp
     */
    public function index()
    {
        $user = Auth::user();

        // Se já completou o setup, redireciona para agenda
        if ($user->apibrasil_setup_completed) {
            return redirect()->route('agenda.index');
        }

        return view('whatsapp-setup.index', [
            'user' => $user,
            'qrcodeStatus' => $user->apibrasil_qrcode_status,
        ]);
    }

    /**
     * Salva as credenciais do device que a empresa criou manualmente
     */
    public function saveDeviceCredentials(Request $request)
    {
        \Log::info('Requisição recebida para salvar credenciais', [
            'device_name' => $request->device_name,
            'device_token' => $request->device_token,
            'user_id' => Auth::id(),
        ]);

        $request->validate([
            'device_name' => 'required|string|max:255',
            'device_token' => 'required|string|max:255',
        ]);

        try {
            $user = Auth::user();

            \Log::info('Tentando atualizar usuário', [
                'user_id' => $user->id,
                'user_email' => $user->email,
            ]);

            // Salva as credenciais do device no banco
            // Marcamos como 'connected' direto, pois assumimos que a empresa
            // já escaneou o QR Code no painel da API Brasil antes de salvar
            $user->update([
                'apibrasil_device_token' => $request->device_token,
                'apibrasil_device_name' => $request->device_name,
                'apibrasil_qrcode_status' => 'connected',
                'apibrasil_setup_completed' => true,
            ]);

            \Log::info('Credenciais salvas com sucesso', [
                'user_id' => $user->id,
                'device_name' => $user->apibrasil_device_name,
                'device_token' => substr($user->apibrasil_device_token, 0, 10) . '...',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Credenciais salvas com sucesso',
                'redirect' => route('agenda.index'),
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao salvar credenciais: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar credenciais: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retorna o QR Code do device
     */
    public function getQrCode(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user->apibrasil_device_token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dispositivo não configurado'
                ], 400);
            }

            // Busca o QR Code usando o device token específico
            $qrCode = $this->whatsappService->getDeviceQrCode($user->apibrasil_device_token);

            if (!$qrCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao obter QR Code'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'qrcode' => $qrCode,
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao obter QR Code: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter QR Code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verifica o status da conexão do WhatsApp
     */
    public function checkConnection(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user->apibrasil_device_token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dispositivo não configurado'
                ], 400);
            }

            // Verifica o status usando o device token específico
            $status = $this->whatsappService->checkDeviceStatus($user->apibrasil_device_token);

            $isConnected = isset($status['connected']) && $status['connected'] === true;

            // Atualiza o status no banco
            if ($isConnected) {
                $user->update([
                    'apibrasil_qrcode_status' => 'connected',
                ]);
            }

            return response()->json([
                'success' => true,
                'connected' => $isConnected,
                'status' => $status,
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao verificar conexão: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar conexão: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finaliza o setup do WhatsApp
     */
    public function completeSetup(Request $request)
    {
        try {
            $user = Auth::user();

            // Verifica se está conectado
            if ($user->apibrasil_qrcode_status !== 'connected') {
                return response()->json([
                    'success' => false,
                    'message' => 'WhatsApp não está conectado'
                ], 400);
            }

            // Marca o setup como completo
            $user->update([
                'apibrasil_setup_completed' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Setup concluído com sucesso',
                'redirect' => route('agenda.index'),
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao completar setup: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao completar setup: ' . $e->getMessage()
            ], 500);
        }
    }
}
