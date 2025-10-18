<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cria ou atualiza o Super Admin
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@sistema.com'],
            [
                'name' => 'Super Administrador',
                'password' => Hash::make('admin123'),
                'tipo' => 'empresa', // Mantém como empresa para compatibilidade
                'is_super_admin' => true,
                'acesso_ativo' => true,
                'acesso_liberado_ate' => null, // Acesso ilimitado
                'plano' => 'anual',
                'limite_requisicoes_mes' => 999999,
            ]
        );

        $this->command->info('✅ Super Admin criado com sucesso!');
        $this->command->info('📧 Email: admin@sistema.com');
        $this->command->info('🔑 Senha: admin123');
        $this->command->warn('⚠️  IMPORTANTE: Altere a senha após o primeiro login!');
    }
}
