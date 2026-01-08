<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;

class ClientesTagsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = 1;

        // Verifica se o usuário existe
        $owner = User::find($userId);
        if (!$owner) {
            $this->command->error("Usuário com ID {$userId} não encontrado!");
            return;
        }

        // Cria 10 tags
        $tagNames = [
            'VIP',
            'Premium',
            'Novo Cliente',
            'Fidelizado',
            'Promoção',
            'Urgente',
            'Recorrente',
            'Potencial',
            'Inativo',
            'Especial'
        ];

        $colors = ['blue', 'green', 'red', 'yellow', 'purple', 'pink', 'orange', 'indigo', 'teal', 'cyan'];

        $tags = [];
        foreach ($tagNames as $index => $tagName) {
            $tag = Tag::create([
                'user_id' => $userId,
                'nome' => $tagName,
                'cor' => $colors[$index],
            ]);
            $tags[] = $tag;
            $this->command->info("Tag criada: {$tagName}");
        }

        // Nomes brasileiros comuns
        $primeiroNomes = [
            'João',
            'Maria',
            'José',
            'Ana',
            'Pedro',
            'Carla',
            'Paulo',
            'Juliana',
            'Carlos',
            'Fernanda',
            'Lucas',
            'Beatriz',
            'Rafael',
            'Camila',
            'Felipe',
            'Amanda',
            'Bruno',
            'Larissa',
            'Rodrigo',
            'Gabriela',
            'Marcelo',
            'Patricia',
            'Thiago',
            'Renata',
            'Diego',
            'Vanessa',
            'Gustavo',
            'Mariana',
            'Leonardo',
            'Tatiana',
            'Vinicius',
            'Daniela',
            'Matheus',
            'Aline',
            'André',
            'Priscila',
            'Ricardo',
            'Cristina',
            'Fabio',
            'Simone',
            'Leandro',
            'Michele',
            'Eduardo',
            'Roberta',
            'Henrique',
            'Adriana',
            'Guilherme',
            'Luciana',
            'Alexandre',
            'Claudia'
        ];

        $sobrenomes = [
            'Silva',
            'Santos',
            'Oliveira',
            'Souza',
            'Rodrigues',
            'Ferreira',
            'Alves',
            'Pereira',
            'Lima',
            'Gomes',
            'Costa',
            'Ribeiro',
            'Martins',
            'Carvalho',
            'Rocha',
            'Almeida',
            'Nascimento',
            'Araújo',
            'Melo',
            'Barbosa',
            'Cardoso',
            'Correia',
            'Dias',
            'Fernandes',
            'Freitas',
            'Mendes',
            'Moreira',
            'Nunes',
            'Ramos',
            'Reis',
            'Teixeira',
            'Vieira',
            'Castro',
            'Monteiro',
            'Pinto',
            'Campos',
            'Moura',
            'Cavalcanti',
            'Azevedo',
            'Barros',
            'Cunha',
            'Duarte',
            'Farias',
            'Gonçalves',
            'Lopes',
            'Machado',
            'Medeiros',
            'Miranda',
            'Nogueira',
            'Pires'
        ];

        // DDDs brasileiros comuns
        $ddds = ['11', '21', '31', '41', '51', '61', '71', '81', '85', '91'];

        // Cria 50 clientes
        for ($i = 1; $i <= 50; $i++) {
            $nome = $primeiroNomes[array_rand($primeiroNomes)] . ' ' . $sobrenomes[array_rand($sobrenomes)];
            $ddd = $ddds[array_rand($ddds)];
            $numero = '9' . rand(1000, 9999) . rand(1000, 9999);
            $whatsapp = '55' . $ddd . $numero;

            $cliente = User::create([
                'name' => $nome,
                'email' => strtolower(str_replace(' ', '.', $nome)) . $i . '@example.com',
                'whatsapp_number' => $whatsapp,
                'password' => bcrypt('password'),
                'tipo' => 'cliente',
                'user_id' => $userId,
            ]);

            // Atribui entre 1 e 3 tags aleatórias para cada cliente
            $numTags = rand(1, 3);
            $clienteTags = collect($tags)->random($numTags);

            foreach ($clienteTags as $tag) {
                DB::table('cliente_tag')->insert([
                    'cliente_id' => $cliente->id,
                    'tag_id' => $tag->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $tagNames = $clienteTags->pluck('nome')->join(', ');
            $this->command->info("Cliente {$i}/50 criado: {$nome} - Tags: [{$tagNames}]");
        }

        $this->command->info("\n✅ Seeder concluído!");
        $this->command->info("📊 10 tags criadas");
        $this->command->info("👥 50 clientes criados para o usuário ID {$userId}");
    }
}
