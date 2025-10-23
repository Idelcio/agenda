<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GerarCompromissosRecorrentes extends Command
{
    protected $signature = 'agenda:gerar-recorrentes';
    protected $description = 'Gera compromissos recorrentes automaticamente';

    public function handle(): int
    {
        $this->info('🔁 Iniciando geração de compromissos recorrentes...');

        // Busca todos os compromissos recorrentes ativos
        $compromissosRecorrentes = Appointment::where('recorrente', true)
            ->whereNull('compromisso_pai_id') // Apenas os "pais", não os filhos
            ->get();

        if ($compromissosRecorrentes->isEmpty()) {
            $this->info('📭 Nenhum compromisso recorrente encontrado.');
            return Command::SUCCESS;
        }

        $totalGerados = 0;

        foreach ($compromissosRecorrentes as $compromisso) {
            $gerados = $this->gerarProximosCompromissos($compromisso);
            $totalGerados += $gerados;

            if ($gerados > 0) {
                $this->info("✅ {$compromisso->titulo}: {$gerados} compromisso(s) gerado(s)");
            }
        }

        $this->info("🎉 Total: {$totalGerados} compromisso(s) gerado(s) com sucesso!");

        return Command::SUCCESS;
    }

    private function gerarProximosCompromissos(Appointment $compromisso): int
    {
        if (!$compromisso->frequencia_recorrencia) {
            return 0;
        }

        $totalGerados = 0;
        $dataLimite = $compromisso->data_fim_recorrencia
            ? Carbon::parse($compromisso->data_fim_recorrencia)
            : now()->addMonths(3);

        // Busca o último compromisso filho gerado
        $ultimoFilho = $compromisso->compromissosFilhos()
            ->orderBy('inicio', 'desc')
            ->first();

        // Define a partir de quando começar a gerar
        $proximaData = $ultimoFilho
            ? $this->calcularProximaOcorrencia($ultimoFilho->inicio, $compromisso->frequencia_recorrencia)
            : $this->calcularProximaOcorrencia($compromisso->inicio, $compromisso->frequencia_recorrencia);

        // Gera compromissos até a data limite (máximo 3 meses à frente)
        while ($proximaData <= $dataLimite && $proximaData <= now()->addMonths(3)) {
            // Verifica se já existe um compromisso nesta data
            $existe = Appointment::where('compromisso_pai_id', $compromisso->id)
                ->whereDate('inicio', $proximaData->toDateString())
                ->whereTime('inicio', $proximaData->toTimeString())
                ->exists();

            if (!$existe) {
                $this->criarCompromissoFilho($compromisso, $proximaData);
                $totalGerados++;
            }

            $proximaData = $this->calcularProximaOcorrencia($proximaData, $compromisso->frequencia_recorrencia);
        }

        return $totalGerados;
    }

    private function calcularProximaOcorrencia(Carbon $dataBase, string $frequencia): Carbon
    {
        $proximaData = $dataBase->copy();

        switch ($frequencia) {
            case 'semanal':
                $proximaData->addWeek();
                break;
            case 'quinzenal':
                $proximaData->addWeeks(2);
                break;
            case 'mensal':
                $proximaData->addMonth();
                break;
            case 'anual':
                $proximaData->addYear();
                break;
        }

        return $proximaData;
    }

    private function criarCompromissoFilho(Appointment $pai, Carbon $novaData): void
    {
        $duracao = $pai->fim ? $pai->inicio->diffInMinutes($pai->fim) : 60;
        $novaDataFim = $novaData->copy()->addMinutes($duracao);

        // Calcula o lembrar_em baseado na antecedência
        $lembrarEm = null;
        if ($pai->notificar_whatsapp && $pai->antecedencia_minutos) {
            $lembrarEm = $novaData->copy()->subMinutes($pai->antecedencia_minutos);
        }

        Appointment::create([
            'user_id' => $pai->user_id,
            'destinatario_user_id' => $pai->destinatario_user_id,
            'titulo' => $pai->titulo,
            'descricao' => $pai->descricao,
            'inicio' => $novaData,
            'fim' => $novaDataFim,
            'dia_inteiro' => $pai->dia_inteiro,
            'status' => 'pendente',
            'notificar_whatsapp' => $pai->notificar_whatsapp,
            'whatsapp_numero' => $pai->whatsapp_numero,
            'whatsapp_mensagem' => $pai->whatsapp_mensagem,
            'antecedencia_minutos' => $pai->antecedencia_minutos,
            'lembrar_em' => $lembrarEm,
            'status_lembrete' => 'pendente',
            'compromisso_pai_id' => $pai->id,
            'recorrente' => false, // Os filhos não são recorrentes
            'observacoes' => $pai->observacoes,
        ]);
    }
}
