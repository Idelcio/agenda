<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Escolha seu Plano - {{ config('app.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #25D366, #128C7E, #075E54);
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --card-bg: rgba(255, 255, 255, 0.95);
            --shadow: 0 25px 50px -12px rgba(37, 211, 102, 0.4);
            --accent: #25D366;
            --accent-hover: #1fb855;
            --accent-secondary: #128C7E;
            --highlight: rgba(37, 211, 102, 0.12);
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
            max-width: 1300px;
            margin: 0 auto;
            background: var(--card-bg);
            border-radius: 32px;
            padding: 3rem;
            box-shadow: var(--shadow);
            backdrop-filter: blur(20px);
        }

        .header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .header h1 {
            font-size: clamp(2rem, 5vw, 3rem);
            margin: 0 0 1rem 0;
            font-weight: 700;
            color: var(--text-primary);
        }

        .header p {
            font-size: 1.1rem;
            color: var(--text-secondary);
            margin: 0;
        }

        .alert {
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: center;
            font-weight: 600;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(37, 211, 102, 0.15), rgba(18, 140, 126, 0.15));
            border: 2px solid var(--accent);
            color: var(--text-primary);
        }

        .alert-welcome {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(37, 99, 235, 0.15));
            border: 2px solid #3b82f6;
            color: var(--text-primary);
        }

        .alert-welcome h2 {
            margin: 0 0 0.5rem 0;
            font-size: 1.5rem;
            color: #1e40af;
        }

        .alert-welcome p {
            margin: 0;
            font-size: 1rem;
            font-weight: 500;
        }

        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        @media (min-width: 1200px) {
            .plans-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        .plan-card {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 3px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .plan-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(37, 211, 102, 0.25);
        }

        .plan-card.popular {
            border-color: var(--accent);
            transform: translateY(-6px);
            box-shadow: 0 20px 60px rgba(37, 211, 102, 0.3);
        }

        .plan-card.popular:hover {
            transform: translateY(-10px);
        }

        .popular-badge {
            position: absolute;
            top: -3px;
            right: -3px;
            background: var(--accent);
            color: white;
            padding: 0.5rem 1.5rem;
            font-size: 0.85rem;
            font-weight: 700;
            border-radius: 0 20px 0 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .plan-header {
            margin-bottom: 1.5rem;
        }

        .plan-name {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0 0 0.5rem 0;
        }

        .plan-description {
            font-size: 0.95rem;
            color: var(--text-secondary);
            margin: 0;
        }

        .plan-pricing {
            margin: 1.75rem 0;
            text-align: center;
        }

        .price-total {
            display: flex;
            align-items: baseline;
            justify-content: center;
            gap: 0.3rem;
            margin-bottom: 0.5rem;
        }

        .price-currency {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .price-value {
            font-size: 3rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
        }

        .price-period {
            font-size: 1rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .price-monthly {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-top: 0.5rem;
        }

        .price-monthly strong {
            color: var(--accent);
            font-weight: 700;
        }

        .discount-badge {
            display: inline-block;
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #333;
            padding: 0.4rem 1rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 700;
            margin-top: 0.5rem;
        }

        .plan-features {
            list-style: none;
            padding: 0;
            margin: 0 0 2rem 0;
            flex-grow: 1;
        }

        .plan-features li {
            padding: 0.65rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: start;
            gap: 0.75rem;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .plan-features li:last-child {
            border-bottom: none;
        }

        .plan-features li::before {
            content: "✓";
            color: var(--accent);
            font-weight: bold;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .plan-button {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 999px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .plan-button.primary {
            background: var(--accent);
            color: white;
            box-shadow: 0 10px 25px rgba(37, 211, 102, 0.3);
        }

        .plan-button.primary:hover {
            background: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(37, 211, 102, 0.4);
        }

        .plan-button.secondary {
            background: white;
            color: var(--accent);
            border: 2px solid var(--accent);
        }

        .plan-button.secondary:hover {
            background: var(--highlight);
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

        .guarantee-section {
            background: linear-gradient(135deg, rgba(37, 211, 102, 0.08), rgba(18, 140, 126, 0.08));
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            margin-top: 3rem;
        }

        .guarantee-section h3 {
            margin: 0 0 1rem 0;
            font-size: 1.4rem;
            color: var(--text-primary);
        }

        .guarantee-section p {
            margin: 0;
            color: var(--text-secondary);
            font-size: 1rem;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .container {
                padding: 2rem 1.5rem;
            }

            .plans-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .plan-card.popular {
                transform: none;
            }

            .plan-card.popular:hover {
                transform: translateY(-6px);
            }

            .price-value {
                font-size: 2.6rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        @if($hasActiveSubscription)
            <a href="{{ route('dashboard') }}" class="back-link">
                ← Voltar para o Dashboard
            </a>
        @else
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="back-link" style="background: none; border: none; cursor: pointer; font: inherit;">
                    ← Sair
                </button>
            </form>
        @endif

        <div class="header">
            <h1>Escolha seu Plano</h1>
            <p>Acesso completo e ilimitado à plataforma. Cancele quando quiser.</p>
        </div>

        @if(session('welcome'))
        <div class="alert alert-welcome">
            <h2>🎉 {{ session('welcome') }}</h2>
            <p>Complete seu cadastro escolhendo um dos planos abaixo e tenha acesso imediato a todos os recursos!</p>
        </div>
        @endif

        @if($hasActiveSubscription)
        <div class="alert alert-success">
            ✓ Você já possui uma assinatura ativa! <a href="{{ route('subscription.current') }}" style="color: var(--accent); text-decoration: underline;">Ver detalhes</a>
        </div>
        @endif

        <div class="plans-grid">
            @foreach($plans as $key => $plan)
            <div class="plan-card {{ $key === 'semiannual' ? 'popular' : '' }}">
                @if($key === 'semiannual')
                <div class="popular-badge">Mais Popular</div>
                @endif

                <div class="plan-header">
                    <h2 class="plan-name">{{ $plan['name'] }}</h2>
                    <p class="plan-description">{{ $plan['description'] }}</p>
                </div>

                <div class="plan-pricing">
                    <div class="price-total">
                        <span class="price-currency">R$</span>
                        <span class="price-value">{{ number_format($plan['price'], 2, ',', '.') }}</span>
                    </div>
                    <div class="price-period">
                        @if($plan['duration_months'] == 1)
                            por mês
                        @else
                            a cada {{ $plan['duration_months'] }} meses
                        @endif
                    </div>

                    @if($plan['duration_months'] > 1)
                    <div class="price-monthly">
                        <strong>R$ {{ number_format($plan['price'] / $plan['duration_months'], 2, ',', '.') }}</strong> por mês
                    </div>
                    @endif

                    @if($plan['discount_percent'] > 0)
                    <div class="discount-badge">
                        🔥 Economize {{ $plan['discount_percent'] }}%
                    </div>
                    @endif
                </div>

                <ul class="plan-features">
                    <li>Agendamentos ilimitados</li>
                    <li>Clientes ilimitados</li>
                    <li>Envios automáticos via WhatsApp</li>
                    <li>Chatbot inteligente</li>
                    <li>Respostas em tempo real</li>
                    <li>Relatórios e dashboard completo</li>
                    <li>Suporte técnico prioritário</li>
                    <li>Atualizações gratuitas</li>
                </ul>

                @if($hasActiveSubscription)
                <button class="plan-button secondary" disabled>
                    Você já tem uma assinatura ativa
                </button>
                @else
                <form action="{{ route('subscription.checkout') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan_type" value="{{ $key }}">
                    <button type="submit" class="plan-button {{ $key === 'semiannual' ? 'primary' : 'secondary' }}">
                        Escolher {{ $plan['name'] }}
                    </button>
                </form>
                @endif
            </div>
            @endforeach
        </div>

        <div class="guarantee-section">
            <h3>📱 Como funciona após o pagamento?</h3>
            <p style="margin-bottom: 1rem;">
                Após confirmar seu pagamento, nossa equipe técnica entrará em contato via WhatsApp em até <strong>24 horas</strong>
                para configurar suas credenciais da API Brasil e você começar a usar a plataforma.
            </p>
            <p style="font-size: 0.95rem; color: var(--text-secondary);">
                <strong>Contato:</strong> +55 51 98487-1703 (WhatsApp)
            </p>
        </div>

        <div class="guarantee-section">
            <h3>💳 Pagamento 100% Seguro</h3>
            <p>
                Todos os pagamentos são processados pelo Mercado Pago, líder em segurança de pagamentos online na América Latina.
                Seus dados estão protegidos com a mais alta tecnologia de criptografia.
            </p>
        </div>
    </div>
</body>

</html>
