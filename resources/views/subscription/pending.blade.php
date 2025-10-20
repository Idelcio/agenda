<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pagamento Pendente - {{ config('app.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #f59e0b, #f97316, #ea580c);
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --warning: #f59e0b;
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
            box-shadow: 0 25px 50px -12px rgba(245, 158, 11, 0.4);
            backdrop-filter: blur(20px);
        }

        .icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #f59e0b, #f97316);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: pulse 2s ease-in-out infinite;
        }

        .icon::before {
            content: "⏳";
            font-size: 3rem;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.9;
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
            color: var(--warning);
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
            background: rgba(245, 158, 11, 0.1);
            border-left: 4px solid var(--warning);
            padding: 1.5rem;
            border-radius: 8px;
            margin: 2rem 0;
            text-align: left;
        }

        .info-box p {
            margin: 0.5rem 0;
        }

        .info-box strong {
            color: var(--text-primary);
        }

        .steps {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin: 2rem 0;
            text-align: left;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .steps h3 {
            margin: 0 0 1rem 0;
            color: var(--text-primary);
            font-size: 1.1rem;
        }

        .steps ol {
            margin: 0;
            padding-left: 1.5rem;
            color: var(--text-secondary);
        }

        .steps li {
            margin: 0.5rem 0;
            line-height: 1.5;
        }

        .actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            text-decoration: none;
            border-radius: 999px;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #25D366;
            color: white;
            box-shadow: 0 10px 25px rgba(37, 211, 102, 0.3);
        }

        .btn-primary:hover {
            background: #1fb855;
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(37, 211, 102, 0.4);
        }

        .btn-secondary {
            background: white;
            color: var(--text-secondary);
            border: 2px solid rgba(0, 0, 0, 0.1);
        }

        .btn-secondary:hover {
            border-color: #25D366;
            color: #25D366;
        }

        @media (max-width: 768px) {
            .container {
                padding: 2rem 1.5rem;
            }

            h1 {
                font-size: 2rem;
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
        <div class="icon"></div>

        <h1>Pagamento Pendente</h1>
        <p class="subtitle">Aguardando confirmação do pagamento</p>

        <p>
            Seu pagamento está sendo processado. Isso pode levar alguns minutos.
            Você receberá uma notificação assim que for confirmado.
        </p>

        <div class="info-box">
            <p><strong>⏱️ O que significa pagamento pendente?</strong></p>
            <p>• Pagamento em análise pela operadora do cartão</p>
            <p>• Processamento em andamento (pode levar até 48h)</p>
            <p>• Aguardando confirmação bancária</p>
        </div>

        <div class="steps">
            <h3>📋 Próximos passos:</h3>
            <ol>
                <li>Aguarde a confirmação do pagamento (você receberá um email)</li>
                <li>Verifique sua caixa de entrada regularmente</li>
                <li>Assim que confirmado, sua assinatura será ativada automaticamente</li>
                <li>Você poderá acessar todos os recursos da plataforma</li>
            </ol>
        </div>

        <p style="font-size: 0.95rem;">
            <strong>💡 Importante:</strong> Se você não receber confirmação em até 48 horas,
            entre em contato com nosso suporte.
        </p>

        <div class="actions">
            <a href="{{ route('subscription.current') }}" class="btn btn-primary">
                Verificar Status
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                Voltar ao Início
            </a>
        </div>
    </div>
</body>

</html>
