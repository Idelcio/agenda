<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class SetSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:set-super-admin {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Define um usuário como Super Admin pelo email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("❌ Usuário com email '{$email}' não encontrado!");
            return 1;
        }

        // Atualiza para super admin
        $user->update([
            'is_super_admin' => true,
            'acesso_ativo' => true,
            'acesso_liberado_ate' => null, // Acesso ilimitado
            'plano' => 'anual',
            'limite_requisicoes_mes' => 999999,
        ]);

        $this->info('✅ Sucesso!');
        $this->info("👤 Usuário: {$user->name}");
        $this->info("📧 Email: {$user->email}");
        $this->info('👑 Status: Super Administrador');
        $this->info('🔓 Acesso: Ilimitado');

        return 0;
    }
}
