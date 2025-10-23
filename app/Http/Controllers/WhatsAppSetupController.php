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
            'device_id' => $request->device_id,
            'device_token' => $request->device_token,
            'user_id' => Auth::id(),
        ]);

        $request->validate([
            'device_name' => 'required|string|max:255',
            'device_id' => 'required|string|max:255',
            'device_token' => 'nullable|string|max:255',
        ]);

        try {
            $user = Auth::user();
            $deviceId = trim($request->device_id);
            $deviceToken = trim($request->device_token ?? '') ?: $deviceId;

            \Log::info('Tentando atualizar usuário', [
                'user_id' => $user->id,
                'user_email' => $user->email,
            ]);

            $this->updateUserDeviceCredentials($user, [
                'id' => $deviceId,
                'token' => $deviceToken,
                'name' => $request->device_name,
                'status' => 'connected',
            ], true);

            \Log::info('Credenciais salvas com sucesso', [
                'user_id' => $user->id,
                'device_name' => $user->apibrasil_device_name,
                'device_token' => $user->apibrasil_device_token,
                'device_id' => $user->apibrasil_device_id,
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
                'message' => 'Erro ao salvar credenciais: ' . $e->getMessage(),
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

            if (!$user->apibrasil_device_token || !$user->apibrasil_device_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dispositivo não configurado',
                ], 400);
            }

            $qrCode = $this->whatsappService->getDeviceQrCode($user->apibrasil_device_token);

            if (!$qrCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao obter QR Code',
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
                'message' => 'Erro ao obter QR Code: ' . $e->getMessage(),
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

            if (!$user->apibrasil_device_token || !$user->apibrasil_device_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dispositivo não configurado',
                ], 400);
            }

            $status = $this->whatsappService->checkDeviceStatus($user->apibrasil_device_token);

            $isConnected = isset($status['connected']) && $status['connected'] === true;
            $rawResponse = $status['full_response'] ?? $status;

            $this->updateUserDeviceCredentials($user, $rawResponse, $isConnected);

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
                'message' => 'Erro ao verificar conexão: ' . $e->getMessage(),
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

            if ($user->apibrasil_qrcode_status !== 'connected') {
                return response()->json([
                    'success' => false,
                    'message' => 'WhatsApp não está conectado',
                ], 400);
            }

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
                'message' => 'Erro ao completar setup: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Atualiza os campos relacionados ao dispositivo da API Brasil
     */
    private function updateUserDeviceCredentials($user, array $payload, bool $markSetupCompleted = false): void
    {
        $deviceId = data_get($payload, 'id')
            ?? data_get($payload, 'device_id')
            ?? data_get($payload, 'device.device_id')
            ?? data_get($payload, 'response.device_id');

        $deviceToken = data_get($payload, 'token')
            ?? data_get($payload, 'device_token')
            ?? data_get($payload, 'device.device_token')
            ?? data_get($payload, 'response.device_token');

        $deviceName = data_get($payload, 'name')
            ?? data_get($payload, 'device_name')
            ?? data_get($payload, 'device.device_name')
            ?? data_get($payload, 'response.device_name');

        $status = data_get($payload, 'status')
            ?? data_get($payload, 'device.status')
            ?? data_get($payload, 'state');

        $updates = array_filter([
            'apibrasil_device_id' => $deviceId ? trim((string) $deviceId) : null,
            'apibrasil_device_token' => $deviceToken ? trim((string) $deviceToken) : null,
            'apibrasil_device_name' => $deviceName ? trim((string) $deviceName) : null,
            'apibrasil_qrcode_status' => $status ? trim((string) $status) : null,
        ], fn($value) => !is_null($value) && $value !== '');

        if (!empty($updates['apibrasil_device_id']) && empty($updates['apibrasil_device_token'])) {
            $updates['apibrasil_device_token'] = $updates['apibrasil_device_id'];
        }

        if ($markSetupCompleted || (isset($updates['apibrasil_qrcode_status']) && Str::lower($updates['apibrasil_qrcode_status']) === 'connected')) {
            $updates['apibrasil_setup_completed'] = true;
            $updates['apibrasil_qrcode_status'] = $updates['apibrasil_qrcode_status'] ?? 'connected';
        }

        $dirty = [];

        foreach ($updates as $field => $value) {
            if ($user->{$field} !== $value) {
                $dirty[$field] = $value;
            }
        }

        if (!empty($dirty)) {
            $user->forceFill($dirty);
            $user->save();
        }
    }
}

