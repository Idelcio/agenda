<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pagamento Rejeitado - {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo2.png') }}">
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #ef4444, #dc2626, #b91c1c);
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --error: #ef4444;
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
            box-shadow: 0 25px 50px -12px rgba(239, 68, 68, 0.4);
            backdrop-filter: blur(20px);
        }

        .icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: shake 0.5s ease-out;
        }

        .icon::before {
            content: "✕";
            color: white;
            font-size: 3.5rem;
            font-weight: bold;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        h1 {
            font-size: 2.5rem;
            margin: 0 0 1rem 0;
            color: var(--text-primary);
            font-weight: 700;
        }

        .subtitle {
            font-size: 1.2rem;
            color: var(--error);
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
            background: rgba(239, 68, 68, 0.1);
            border-left: 4px solid var(--error);
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

        <h1>Pagamento Rejeitado</h1>
        <p class="subtitle">Não conseguimos processar seu pagamento</p>

        <p>
            Infelizmente seu pagamento foi rejeitado. Isso pode acontecer por diversos motivos,
            mas não se preocupe, você pode tentar novamente.
        </p>

        <div class="info-box">
            <p><strong>🔍 Possíveis motivos:</strong></p>
            <p>• Saldo insuficiente no cartão</p>
            <p>• Dados do cartão incorretos</p>
            <p>• Cartão vencido ou bloqueado</p>
            <p>• Limite do cartão excedido</p>
            <p>• Problema temporário com a operadora</p>
        </div>

        <p style="font-size: 0.95rem;">
            <strong>💡 Dica:</strong> Verifique os dados do seu cartão e tente novamente.
            Se o problema persistir, entre em contato com seu banco ou use outro cartão.
        </p>

        <div class="actions">
            <a href="{{ route('subscription.plans') }}" class="btn btn-primary">
                Tentar Novamente
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                Voltar ao Início
            </a>
        </div>
    </div>
</body>

</html>
