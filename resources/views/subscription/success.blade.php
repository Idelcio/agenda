<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pagamento Aprovado - {{ config('app.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #25D366, #128C7E, #075E54);
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --success: #22c55e;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .container {
            max-width: 600px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 32px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(37, 211, 102, 0.4);
            backdrop-filter: blur(20px);
        }

        .icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: scaleIn 0.5s ease-out;
        }

        .icon::before {
            content: "✓";
            color: white;
            font-size: 3.5rem;
            font-weight: bold;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        h1 {
            font-size: 2.5rem;
            margin: 0 0 1rem 0;
            color: var(--text-primary);
            font-weight: 700;
        }

        .subtitle {
            font-size: 1.2rem;
            color: var(--success);
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        p {
            color: var(--text-secondary);
            line-height: 1.6;
            margin: 1rem 0;
            font-size: 1.05rem;
        }

        .info-box {
            background: rgba(34, 197, 94, 0.1);
            border-left: 4px solid var(--success);
            padding: 1.5rem;
            border-radius: 8px;
            margin: 2rem 0;
            text-align: left;
        }

        .info-box p {
            margin: 0.5rem 0;
        }

        .whatsapp-box {
            background: linear-gradient(135deg, rgba(37, 211, 102, 0.15), rgba(18, 140, 126, 0.15));
            border: 2px solid #25D366;
            padding: 2rem;
            border-radius: 16px;
            margin: 2rem 0;
            text-align: center;
        }

        .whatsapp-box h2 {
            margin: 0 0 1rem 0;
            font-size: 1.5rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .whatsapp-box h2::before {
            content: "💬";
            font-size: 2rem;
        }

        .whatsapp-box p {
            margin: 0.5rem 0;
            font-size: 1rem;
        }

        .whatsapp-number {
            font-size: 1.8rem;
            font-weight: 800;
            color: #25D366;
            margin: 1rem 0;
        }

        .btn {
            display: inline-block;
            padding: 1rem 2.5rem;
            background: #25D366;
            color: white;
            text-decoration: none;
            border-radius: 999px;
            font-weight: 700;
            font-size: 1.1rem;
            margin-top: 2rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(37, 211, 102, 0.3);
        }

        .btn:hover {
            background: #1fb855;
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(37, 211, 102, 0.4);
        }

        @media (max-width: 768px) {
            .container {
                padding: 2rem 1.5rem;
            }

            h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="icon"></div>

        <h1>Pagamento Aprovado!</h1>
        <p class="subtitle">Sua assinatura foi ativada com sucesso</p>

        <p>
            Parabéns! Seu pagamento foi aprovado e sua assinatura está ativa.
        </p>

        <div class="whatsapp-box">
            <h2>Próximo Passo: Configuração do WhatsApp</h2>
            <p style="font-size: 1.1rem; margin-bottom: 1rem;">
                Para começar a usar a plataforma, precisamos configurar suas credenciais da API Brasil.
            </p>
            <p><strong>Entre em contato conosco pelo WhatsApp:</strong></p>
            <div class="whatsapp-number">📱 +55 51 98487-1703</div>
            <p style="color: var(--text-secondary); font-size: 0.95rem;">
                Nossa equipe técnica entrará em contato em até <strong>24 horas</strong> para concluir a configuração.
            </p>
        </div>

        <div class="info-box">
            <p><strong>ℹ️ Como funciona:</strong></p>
            <p>1️⃣ Envie uma mensagem no WhatsApp informando que concluiu o pagamento</p>
            <p>2️⃣ Nossa equipe solicitará suas credenciais da API Brasil</p>
            <p>3️⃣ Faremos toda a configuração para você</p>
            <p>4️⃣ Em até 24h você estará pronto para usar!</p>
        </div>

        <p style="font-size: 0.95rem; color: var(--text-secondary);">
            Após a configuração, você poderá criar agendamentos, cadastrar clientes e enviar lembretes automáticos via WhatsApp.
        </p>

        <a href="{{ route('dashboard') }}" class="btn">
            Ir para o Dashboard
        </a>
    </div>
</body>

</html>
