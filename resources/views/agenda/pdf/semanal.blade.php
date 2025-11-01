<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Agenda - {{ $periodo }}</title>
    <style>
        @page {
            margin: 10mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 8pt;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 8px;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
        }

        .header h1 {
            font-size: 14pt;
            margin: 0 0 2px 0;
        }

        .header .periodo {
            font-size: 9pt;
            color: #555;
        }

        .dias-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .dia {
            break-inside: avoid;
            margin-bottom: 6px;
        }

        .dia-titulo {
            background-color: #333;
            color: white;
            padding: 3px 6px;
            font-weight: bold;
            font-size: 8pt;
            margin-bottom: 3px;
        }

        .compromisso {
            border-left: 2px solid #999;
            padding: 2px 5px;
            margin-bottom: 4px;
            background-color: #f9f9f9;
            line-height: 1.3;
        }

        .compromisso.status-concluido {
            border-left-color: #10b981;
            background-color: #ecfdf5;
        }

        .compromisso.status-cancelado {
            border-left-color: #ef4444;
            background-color: #fef2f2;
        }

        .compromisso.status-pendente {
            border-left-color: #f59e0b;
            background-color: #fffbeb;
        }

        .hora {
            font-weight: bold;
            font-size: 9pt;
            color: #000;
        }

        .titulo {
            font-size: 8pt;
            font-weight: bold;
            margin: 1px 0;
            color: #000;
        }

        .cliente {
            font-size: 8pt;
            margin: 1px 0;
        }

        .telefone {
            font-family: 'Courier New', monospace;
            font-size: 8pt;
            font-weight: bold;
            color: #000;
        }

        .status {
            display: inline-block;
            font-size: 7pt;
            padding: 1px 4px;
            border-radius: 2px;
            font-weight: bold;
            margin-left: 4px;
        }

        .status.concluido {
            background-color: #10b981;
            color: white;
        }

        .status.cancelado {
            background-color: #ef4444;
            color: white;
        }

        .status.pendente {
            background-color: #f59e0b;
            color: white;
        }

        .descricao {
            font-size: 7pt;
            color: #555;
            margin-top: 2px;
            font-style: italic;
            line-height: 1.2;
        }

        .vazio {
            padding: 4px;
            text-align: center;
            color: #999;
            font-style: italic;
            font-size: 7pt;
        }

        .footer {
            margin-top: 8px;
            text-align: center;
            font-size: 7pt;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>AGENDA {{ strtoupper($tituloPeriodo ?? 'SEMANAL') }}</h1>
        <div class="periodo">{{ $periodo }}</div>
    </div>

    @php
        $diasSemana = [
            'Sunday' => 'DOM',
            'Monday' => 'SEG',
            'Tuesday' => 'TER',
            'Wednesday' => 'QUA',
            'Thursday' => 'QUI',
            'Friday' => 'SEX',
            'Saturday' => 'SÁB',
        ];
    @endphp

    <div class="dias-grid">
        @for ($dia = $inicioSemana->copy(); $dia <= $fimSemana; $dia->addDay())
            @php
                $diaFormatado = $dia->format('Y-m-d');
                $compromissosDia = $compromissosPorDia->get($diaFormatado, collect());
                $diaSemana = $diasSemana[$dia->englishDayOfWeek] ?? $dia->englishDayOfWeek;
            @endphp

            <div class="dia">
                <div class="dia-titulo">
                    {{ $diaSemana }} {{ $dia->format('d/m') }}
                </div>

                @if ($compromissosDia->isEmpty())
                    <div class="vazio">-</div>
                @else
                    @foreach ($compromissosDia as $compromisso)
                        @php
                            $statusClass = '';
                            $statusLabel = '';
                            switch ($compromisso->status) {
                                case 'concluido':
                                    $statusClass = 'concluido';
                                    $statusLabel = 'CONCLUÍDO';
                                    break;
                                case 'cancelado':
                                    $statusClass = 'cancelado';
                                    $statusLabel = 'CANCELADO';
                                    break;
                                case 'pendente':
                                default:
                                    $statusClass = 'pendente';
                                    $statusLabel = 'PENDENTE';
                                    break;
                            }
                        @endphp
                        <div class="compromisso status-{{ $statusClass }}">
                            <div class="hora">
                                {{ $compromisso->inicio->timezone(config('app.timezone'))->format('H:i') }}
                                <span class="status {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>
                            @if ($compromisso->titulo)
                                <div class="titulo">{{ $compromisso->titulo }}</div>
                            @endif
                            <div class="cliente">
                                {{ $compromisso->contact_name ?? $compromisso->destinatario->name ?? 'Sem nome' }}
                            </div>
                            <div class="telefone">
                                @if ($compromisso->contact_phone)
                                    {{ $compromisso->contact_phone }}
                                @elseif ($compromisso->whatsapp_numero)
                                    {{ $compromisso->whatsapp_numero }}
                                @elseif ($compromisso->destinatario?->whatsapp_number)
                                    {{ $compromisso->destinatario->whatsapp_number }}
                                @else
                                    -
                                @endif
                            </div>
                            @if ($compromisso->descricao)
                                <div class="descricao">{{ $compromisso->descricao }}</div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        @endfor
    </div>

    <div class="footer">
        {{ now()->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
    </div>
</body>
</html>
