<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SetIdelcioAsSuperAdmin extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Busca o usuário Idelcio pelo email
        $user = User::where('email', 'idelcioforest@gmail.com')->first();

        if (!$user) {
            $this->command->error('❌ Usuário idelcioforest@gmail.com não encontrado!');
            return;
        }

        // Atualiza para super admin
        $user->update([
            'is_super_admin' => true,
            'acesso_ativo' => true,
            'acesso_liberado_ate' => null, // Acesso ilimitado
            'plano' => 'anual',
            'limite_requisicoes_mes' => 999999,
        ]);

        $this->command->info('✅ Usuário Idelcio Forest tornou-se Super Admin com sucesso!');
        $this->command->info('📧 Email: idelcioforest@gmail.com');
        $this->command->info('👑 Status: Super Administrador');
        $this->command->info('🔓 Acesso: Ilimitado');
    }
}
