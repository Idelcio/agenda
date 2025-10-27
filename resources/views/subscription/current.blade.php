<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Minha Assinatura - {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicons/logo2.png') }}">
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #25D366, #128C7E, #075E54);
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --card-bg: rgba(255, 255, 255, 0.95);
            --shadow: 0 25px 50px -12px rgba(37, 211, 102, 0.4);
            --accent: #25D366;
            --accent-hover: #1fb855;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg-gradient);
            color: var(--text-primary);
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: var(--card-bg);
            border-radius: 32px;
            padding: 3rem;
            box-shadow: var(--shadow);
            backdrop-filter: blur(20px);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 2rem;
            transition: gap 0.2s ease;
        }

        .back-link:hover {
            gap: 0.75rem;
        }

        .header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .header h1 {
            font-size: 2.5rem;
            margin: 0 0 0.5rem 0;
            font-weight: 700;
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            border-radius: 999px;
            font-weight: 700;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-active {
            background: linear-gradient(135deg, #25D366, #1fb855);
            color: white;
        }

        .subscription-card {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .subscription-details {
            display: grid;
            gap: 1.5rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-size: 0.95rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .detail-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .detail-value.highlight {
            color: var(--accent);
            font-size: 1.5rem;
        }

        .plan-name {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--accent);
        }

        .features-section {
            background: linear-gradient(135deg, rgba(37, 211, 102, 0.08), rgba(18, 140, 126, 0.08));
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .features-section h3 {
            margin: 0 0 1.5rem 0;
            font-size: 1.3rem;
            color: var(--text-primary);
        }

        .features-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 0.75rem;
        }

        .features-list li {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.95rem;
            color: var(--text-secondary);
        }

        .features-list li::before {
            content: "✓";
            color: var(--accent);
            font-weight: bold;
            font-size: 1.2rem;
        }

        .actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 999px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: var(--accent);
            color: white;
            box-shadow: 0 10px 25px rgba(37, 211, 102, 0.3);
        }

        .btn-primary:hover {
            background: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(37, 211, 102, 0.4);
        }

        .btn-secondary {
            background: white;
            color: var(--text-secondary);
            border: 2px solid rgba(0, 0, 0, 0.1);
        }

        .btn-secondary:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        .info-box {
            background: rgba(59, 130, 246, 0.1);
            border-left: 4px solid #3b82f6;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-top: 2rem;
        }

        .info-box p {
            margin: 0;
            color: var(--text-secondary);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .container {
                padding: 2rem 1.5rem;
            }

            .header h1 {
                font-size: 2rem;
            }

            .subscription-card {
                padding: 1.5rem;
            }

            .detail-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="{{ route('dashboard') }}" class="back-link">
            ← Voltar para o Dashboard
        </a>

        <div class="header">
            <h1>Minha Assinatura</h1>
            @if ($subscription->isActive())
                <span class="status-badge status-active">✓ Ativa</span>
            @endif
        </div>

        <div class="subscription-card">
            <div class="subscription-details">
                <div class="detail-row">
                    <span class="detail-label">Plano</span>
                    <span class="plan-name">
                        @switch($subscription->plan_type)
                            @case('monthly')
                                Mensal
                            @break

                            @case('quarterly')
                                Trimestral
                            @break

                            @case('semiannual')
                                Semestral
                            @break

                            @case('annual')
                                Anual
                            @break
                        @endswitch
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Valor Pago</span>
                    <span class="detail-value highlight">R$
                        {{ number_format($subscription->amount, 2, ',', '.') }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Data de Início</span>
                    <span class="detail-value">{{ $subscription->starts_at->format('d/m/Y') }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Válido até</span>
                    <span class="detail-value">{{ $subscription->expires_at->format('d/m/Y') }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Dias restantes</span>
                    <span class="detail-value">{{ now()->diffInDays($subscription->expires_at) }} dias</span>
                </div>
            </div>
        </div>

        <div class="features-section">
            <h3>✨ Você tem acesso a:</h3>
            <ul class="features-list">
                <li>Agendamentos ilimitados</li>
                <li>Clientes ilimitados</li>
                <li>Envios automáticos via WhatsApp</li>
                <li>Chatbot inteligente com IA</li>
                <li>Respostas em tempo real</li>
                <li>Relatórios e dashboard completo</li>
                <li>Suporte técnico prioritário</li>
                <li>Todas as atualizações gratuitas</li>
            </ul>
        </div>

        <div class="actions">
            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                Ir para o Dashboard
            </a>
            <a href="{{ route('subscription.history') }}" class="btn btn-secondary">
                Ver Histórico
            </a>
        </div>

        <div class="info-box">
            <p>
                <strong>💡 Dica:</strong> Sua assinatura renova automaticamente. Para cancelar ou alterar seu plano,
                entre em contato com nosso suporte.
            </p>
        </div>
    </div>
</body>

</html>
