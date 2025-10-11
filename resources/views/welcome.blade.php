<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Agenda Digital') }}</title>
        <style>
            :root {
                color-scheme: light;
                --bg-gradient: linear-gradient(135deg, #1d4ed8, #9333ea);
                --text-primary: #0f172a;
                --text-secondary: #64748b;
                --card-bg: rgba(255, 255, 255, 0.9);
                --shadow: 0 25px 50px -12px rgba(30, 64, 175, 0.35);
                --accent: #1d4ed8;
                --accent-hover: #1e40af;
                --highlight: rgba(79, 70, 229, 0.12);
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
                width: min(1100px, 100%);
                background: var(--card-bg);
                border-radius: 28px;
                padding: 3.5rem;
                display: grid;
                gap: 3rem;
                box-shadow: var(--shadow);
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
            }

            header span {
                font-size: 0.95rem;
                color: var(--text-secondary);
                display: block;
                margin-top: 0.5rem;
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
                content: "✔";
                color: var(--accent);
                font-weight: bold;
                margin-top: 0.1rem;
            }

            footer {
                font-size: 0.85rem;
                color: var(--text-secondary);
                text-align: center;
            }

            @media (max-width: 720px) {
                body {
                    padding: 1.5rem;
                }

                .landing {
                    padding: 2.5rem;
                }

                header {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 1rem;
                }

                .cta-group {
                    flex-direction: column;
                }

                .cta-group a {
                    text-align: center;
                    width: 100%;
                }
            }
        </style>
    </head>
    <body>
        <div class="landing">
            <header>
                <div>
                    <h1>{{ config('app.name') }}</h1>
                    <span>Organize seus compromissos, lembretes e conversas com um fluxo inteligente e seguro.</span>
                </div>
                @auth
                    <a href="{{ route('dashboard') }}" class="cta-secondary">Ir para a minha agenda</a>
                @endauth
            </header>

            <section class="hero">
                <div class="hero-text">
                    <h2>Uma central completa para agendas profissionais e pessoais.</h2>
                    <p>
                        Cadastre compromissos, configure lembretes automáticos via WhatsApp, converse com o chatbot e mantenha todo o time sincronizado em um único lugar.
                    </p>
                    @guest
                        <div class="cta-group">
                            <a href="{{ route('register') }}" class="cta-primary">Criar minha conta</a>
                            <a href="{{ route('login') }}" class="cta-secondary">Já tenho login</a>
                        </div>
                    @else
                        <div class="cta-group">
                            <a href="{{ route('dashboard') }}" class="cta-primary">Acessar agenda</a>
                        </div>
                    @endguest
                </div>
                <div class="feature-card">
                    <h3>
                        <span>Por que usar?</span>
                        Benefícios do {{ config('app.name') }}
                    </h3>
                    <ul>
                        <li>Agenda multiusuário com visualização intuitiva de compromissos pendentes e concluídos.</li>
                        <li>Lembretes automatizados por WhatsApp com mensagens personalizadas e confirmação de envio.</li>
                        <li>Chatbot inteligente para criar e listar compromissos diretamente pelo aplicativo de mensagens.</li>
                        <li>Autenticação segura com Breeze e Painel totalmente traduzido para português.</li>
                        <li>Registro completo das interações para auditoria e gestão da equipe.</li>
                    </ul>
                </div>
            </section>

            <footer>
                Desenvolvido com Laravel. Conecte sua agenda, seu time e seus clientes em minutos.
            </footer>
        </div>
    </body>
</html>
