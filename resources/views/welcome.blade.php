<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Agenda Digital') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <style>
        :root {
            color-scheme: light;
            --bg-gradient: linear-gradient(135deg, #25D366, #128C7E, #075E54);
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --card-bg: rgba(255, 255, 255, 0.95);
            --shadow: 0 25px 50px -12px rgba(37, 211, 102, 0.4);
            --accent: #25D366;
            --accent-hover: #1fb855;
            --accent-secondary: #128C7E;
            --highlight: rgba(37, 211, 102, 0.12);
            --whatsapp-green: #25D366;
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .landing {
            width: min(1200px, 100%);
            background: var(--card-bg);
            border-radius: 32px;
            padding: 4rem;
            display: grid;
            gap: 3.5rem;
            box-shadow: var(--shadow);
            backdrop-filter: blur(20px);
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            font-size: clamp(1.8rem, 4vw, 2.4rem);
            margin: 0;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        header h1::before {
            content: "📱";
            font-size: 2.5rem;
        }

        header span {
            font-size: 1rem;
            color: var(--text-secondary);
            display: block;
            margin-top: 0.5rem;
            font-weight: 500;
        }

        .hero {
            display: grid;
            gap: 2.5rem;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            align-items: center;
        }

        .hero-text h2 {
            font-size: clamp(2.2rem, 3.8vw, 3rem);
            margin: 0;
            line-height: 1.1;
        }

        .hero-text p {
            margin-top: 1rem;
            font-size: 1.05rem;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .cta-group {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .cta-group a {
            text-decoration: none;
            font-weight: 600;
            padding: 0.95rem 1.8rem;
            border-radius: 999px;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .cta-primary {
            background: var(--accent);
            color: #fff;
            box-shadow: 0 15px 35px rgba(29, 78, 216, 0.35);
        }

        .cta-primary:hover {
            background: var(--accent-hover);
            transform: translateY(-3px);
        }

        .cta-secondary {
            background: #fff;
            color: var(--accent);
            border: 1px solid rgba(29, 78, 216, 0.12);
        }

        .cta-secondary:hover {
            background: var(--highlight);
            transform: translateY(-3px);
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.75);
            border-radius: 22px;
            padding: 2.5rem;
            display: grid;
            gap: 1.5rem;
            backdrop-filter: blur(12px);
        }

        .feature-card h3 {
            margin: 0;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .feature-card h3 span {
            background: var(--highlight);
            color: var(--accent);
            font-size: 0.9rem;
            padding: 0.4rem 0.9rem;
            border-radius: 999px;
            font-weight: 600;
        }

        .feature-card ul {
            margin: 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: 0.9rem;
        }

        .feature-card li {
            display: flex;
            align-items: start;
            gap: 0.75rem;
            font-size: 0.98rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .feature-card li::before {
            content: "✓";
            color: var(--accent);
            font-weight: bold;
            margin-top: 0.1rem;
            font-size: 1.2rem;
        }

        .steps-section {
            background: linear-gradient(135deg, rgba(37, 211, 102, 0.08), rgba(18, 140, 126, 0.08));
            border-radius: 24px;
            padding: 3rem;
            display: grid;
            gap: 2rem;
        }

        .steps-section h2 {
            text-align: center;
            font-size: clamp(1.8rem, 3vw, 2.2rem);
            margin: 0 0 1rem 0;
            color: var(--text-primary);
        }

        .steps-section>p {
            text-align: center;
            color: var(--text-secondary);
            font-size: 1.05rem;
            margin: 0 0 2rem 0;
        }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .step-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 2px solid transparent;
        }

        .step-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(37, 211, 102, 0.2);
            border-color: var(--accent);
        }

        .step-number {
            width: 50px;
            height: 50px;
            background: var(--accent);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .step-card h3 {
            margin: 0;
            font-size: 1.3rem;
            color: var(--text-primary);
        }

        .step-card p {
            margin: 0;
            color: var(--text-secondary);
            line-height: 1.6;
            font-size: 0.98rem;
        }

        .whatsapp-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--whatsapp-green);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 999px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .whatsapp-badge::before {
            content: "💬";
        }

        footer {
            font-size: 0.85rem;
            color: var(--text-secondary);
            text-align: center;
        }

        footer a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
        }

        footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 720px) {
            body {
                padding: 1rem;
            }

            .landing {
                padding: 2rem;
                gap: 2.5rem;
            }

            header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            header h1 {
                font-size: 1.8rem;
            }

            header h1::before {
                font-size: 2rem;
            }

            .hero {
                grid-template-columns: 1fr;
            }

            .hero-text h2 {
                font-size: 1.6rem;
            }

            .cta-group {
                flex-direction: column;
            }

            .cta-group a {
                text-align: center;
                width: 100%;
            }

            .feature-card {
                padding: 2rem;
            }

            .steps-section {
                padding: 2rem;
            }

            .steps-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .step-card {
                padding: 1.5rem;
            }
        }
    </style>
    @env('local')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        @php
            $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
        @endphp

        <link rel="stylesheet" href="{{ asset('build/' . $manifest['resources/css/app.css']['file']) }}">
        <script type="module" src="{{ asset('build/' . $manifest['resources/js/app.js']['file']) }}"></script>
    @endenv
</head>

<body>
    <div class="landing">
        <header>
            <div>
                <h1>{{ config('app.name') }}</h1>
                <span>Agenda inteligente com WhatsApp integrado</span>
            </div>
            @auth
                <a href="{{ route('dashboard') }}" class="cta-secondary">Ir para a minha agenda</a>
            @endauth
        </header>

        <section class="hero">
            <div class="hero-text">
                <h2>Gerencie seus clientes e lembretes com envios automáticos via WhatsApp</h2>
                <p>
                    A solução completa para cadastrar clientes, criar lembretes e receber respostas automaticamente.
                    Tudo sincronizado em uma agenda organizada e fácil de consultar.
                </p>
                @guest
                    <div class="cta-group">
                        <a href="{{ route('register') }}" class="cta-primary">Começar agora</a>
                        <a href="{{ route('login') }}" class="cta-secondary">Já tenho conta</a>
                    </div>
                @else
                    <div class="cta-group">
                        <a href="{{ route('dashboard') }}" class="cta-primary">Acessar minha agenda</a>
                    </div>
                @endguest
            </div>
            <div class="feature-card">
                <h3>
                    <span class="whatsapp-badge">WhatsApp</span>
                    Recursos principais
                </h3>
                <ul>
                    <li>Cadastre seus clientes uma única vez com nome, telefone e informações relevantes</li>
                    <li>Crie lembretes personalizados que são enviados automaticamente pelo WhatsApp</li>
                    <li>Receba as respostas dos clientes direto na agenda, sem precisar checar o celular</li>
                    <li>Visualize tudo em uma tabela organizada: quem confirmou, quem cancelou, quem ainda não respondeu
                    </li>
                    <li>Sistema 100% automatizado: você só cadastra, cria o lembrete e acompanha os resultados</li>
                </ul>
            </div>
        </section>

        <section class="steps-section">
            <h2>Como funciona?</h2>
            <p>Simples, rápido e totalmente automatizado - do cadastro até o acompanhamento</p>

            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h3>Cadastre e conecte</h3>
                    <p>No primeiro acesso após o pagamento, nossa equipe técnica entrará em contato via WhatsApp para configurar suas credenciais e fazer a leitura do QR Code. Após isso, você já pode começar a cadastrar seus clientes!</p>
                </div>

                <div class="step-card">
                    <div class="step-number">2</div>
                    <h3>Crie o agendamento</h3>
                    <p>Selecione o cliente cadastrado, defina data, horário e escreva a mensagem personalizada. Tudo em poucos cliques na sua agenda.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">3</div>
                    <h3>Envio automático</h3>
                    <p>No horário marcado, o sistema envia automaticamente a mensagem via WhatsApp. Você não precisa fazer nada, é tudo automático.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">4</div>
                    <h3>Cliente responde</h3>
                    <p>Seu cliente recebe e responde pelo WhatsApp normalmente. Pode confirmar, reagendar ou cancelar de forma natural.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">5</div>
                    <h3>Chatbot processa</h3>
                    <p>O chatbot inteligente identifica e processa a resposta automaticamente, atualizando o status do agendamento na sua agenda em tempo real.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">6</div>
                    <h3>Acompanhe e gerencie</h3>
                    <p>Visualize tudo no dashboard: status, horários, clientes e respostas organizados. Edite, reagende ou envie novos lembretes quando precisar.</p>
                </div>
            </div>
        </section>

        <footer>
            <p>Desenvolvido por <strong>Forest Desenvolvimento</strong></p>
            <p style="margin-top: 0.5rem;">
                <a href="{{ route('terms') }}">Termos de Uso e Política de Privacidade</a>
            </p>
        </footer>
    </div>
</body>

</html>
