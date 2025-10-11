# Agenda Digital

Aplicacao Laravel localizada em portugues, com autenticacao Breeze, agenda multiusuario, lembretes via WhatsApp e chatbot integrado.

## Principais recursos
- Cadastro e login com Laravel Breeze (Blade + Tailwind).
- Agenda pessoal por usuario com criacao, edicao, conclusao e exclusao de compromissos.
- Lembretes automaticos ou manuais por WhatsApp com mensagens personalizadas.
- Comando Artisan para disparo automatico (`php artisan agenda:disparar-lembretes`).
- Chatbot com comandos rapidos (`MENU`, `LISTAR`, `CRIAR ...`).
- Registro completo das interacoes do chatbot.

## Requisitos
- PHP 8.1+
- Composer
- Node.js 18+ e npm
- MySQL 8 (ou compatível)

## Instalacao
1. Clone o projeto e acesse a pasta.
2. Copie o arquivo de ambiente:
   ```bash
   cp .env.example .env
   ```
3. Ajuste as variaveis `DB_*` no `.env` para apontar para o seu MySQL.
4. Instale dependencias:
   ```bash
   composer install
   npm install
   ```
5. Gere a chave da aplicacao e execute as migracoes:
   ```bash
   php artisan key:generate
   php artisan migrate
   ```
6. Compile os assets:
   ```bash
   npm run dev   # desenvolvimento
   npm run build # producao
   ```
7. Suba o servidor:
   ```bash
   php artisan serve
   ```

## Integracao com WhatsApp (API Brasil)
1. Crie uma conta em [API Brasil](https://apibrasil.io/), habilite o servico de WhatsApp e obtenha:
   - URL base (ex.: `https://api.apibrasil.io/v2/whatsapp`)
   - Token de acesso (JWT)
   - Profile ID
   - Device Token (informado na tela do dispositivo conectado)
2. No `.env`, preencha:
   ```env
   API_BRASIL_URL=https://api.apibrasil.io/v2/whatsapp
   API_BRASIL_TOKEN=seu_token_aqui
   API_BRASIL_PROFILE_ID=seu_profile_id_aqui
   API_BRASIL_DEVICE_TOKEN=seu_device_token_aqui
   WHATSAPP_TEST_NUMBER=+5511999999999
   ```
3. Autorize o numero desejado na API Brasil (sandbox) e cadastre o mesmo numero em **Perfil -> Informacoes do perfil** dentro da aplicacao.
4. Opcional: exponha a rota `POST /webhooks/whatsapp` se quiser receber mensagens de entrada.

> **Dica:** use `php artisan agenda:whatsapp-teste` para enviar uma mensagem rapida ao numero configurado.

## Chatbot: comandos disponiveis
- `MENU` ou `AJUDA` — mostra os comandos.
- `LISTAR` — lista os proximos compromissos.
- `CRIAR Titulo; 25/12/2025; 14:00` — cria um compromisso rapido.

Todos os registros ficam na tabela `chatbot_messages`.

## Lembretes automaticos
- Execute manualmente: `php artisan agenda:disparar-lembretes`.
- Para producao, agende no cron:
  ```
  * * * * * php /caminho/para/artisan schedule:run >> /dev/null 2>&1
  ```
- O agendador roda a cada 5 minutos e dispara lembretes cujo campo `lembrar_em` ja venceu.

## Scripts uteis
- `php artisan serve` — servidor local.
- `npm run dev` — Vite em modo desenvolvimento.
- `npm run build` — build de producao.
- `php artisan test` — executa a suite de testes.

## Proximos passos sugeridos
- Configurar filas (Redis ou database) para envio assincrono das mensagens.
- Expandir o chatbot (concluir compromisso, cancelar, reatribuir etc.).
- Personalizar os layouts Blade conforme a identidade visual do projeto.
## Fluxo de confirmacoes via WhatsApp
- Lembretes enviam uma mensagem de texto resumindo o compromisso e, em seguida, um conjunto de botoes com as opcoes `Confirmar` e `Cancelar`.
- As respostas sao processadas pelo comando `php artisan agenda:sincronizar-respostas`, que atualiza o status do compromisso (confirmado/cancelado).
- Voce pode ajustar os textos e as opcoes em `App\Services\WhatsAppReminderService::buildConfirmationButtons()`.

## Envio manual na agenda
- O bloco "WhatsApp rápido" permite enviar mensagens individuais com ou sem anexos (imagens e PDF).
- Para enviar arquivos, o campo de anexo converte automaticamente em base64 e utiliza o endpoint `sendFile64`.
- Todas as mensagens enviadas e recebidas são registradas na tabela `whatsapp_messages`.
