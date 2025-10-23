# Agendoo

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
- MySQL 8 (ou compatÃ­vel)

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
- `MENU` ou `AJUDA` â€” mostra os comandos.
- `LISTAR` â€” lista os proximos compromissos.
- `CRIAR Titulo; 25/12/2025; 14:00` â€” cria um compromisso rapido.

Todos os registros ficam na tabela `chatbot_messages`.

## Lembretes por WhatsApp
- Quando o horÃ¡rio de `lembrar_em` chega, o compromisso passa para a seÃ§Ã£o â€œLembretes pendentesâ€ e exibe o botÃ£o **Enviar lembrete agora**.
- O mesmo botÃ£o aparece na tabela principal como **Reenviar lembrete** para quem criou o compromisso (ou administradores). Ao clicar, o lembrete Ã© disparado e o registro sai da fila.
- O comando `php artisan agenda:disparar-lembretes` continua disponÃ­vel caso queira disparar vÃ¡rios de uma sÃ³ vez.
- Ajuste os timeouts da API Brasil no `.env` se necessÃ¡rio:
  ```
  API_BRASIL_TIMEOUT=60
  API_BRASIL_CONNECT_TIMEOUT=15
  API_BRASIL_RETRY_TIMES=3
  API_BRASIL_RETRY_SLEEP=2000
  ```
  ApÃ³s mudar, execute `php artisan config:clear`.

## Scripts uteis
- `php artisan serve` â€” servidor local.
- `npm run dev` â€” Vite em modo desenvolvimento.
- `npm run build` â€” build de producao.
- `php artisan test` â€” executa a suite de testes.

## Geracao de PDF da Agenda Semanal
- Clique no botao **Gerar PDF Semanal** localizado ao lado do titulo "Calendario de Agendamentos" na pagina principal da agenda.
- O PDF gerado contem:
  - Todos os compromissos da semana atual (domingo a sabado)
  - Organizacao por dia da semana com totalizadores
  - Informacoes detalhadas: horario, titulo, descricao, cliente, telefone/WhatsApp e status
  - Layout profissional otimizado para impressao em A4
- Ideal para profissionais que trabalham em campo (instaladores, tecnicos, etc.) e precisam levar a agenda impressa com os telefones dos clientes.
- Rota: `GET /agenda/pdf-semanal`

## Proximos passos sugeridos
- Configurar filas (Redis ou database) para envio assincrono das mensagens.
- Expandir o chatbot (concluir compromisso, cancelar, reatribuir etc.).
- Personalizar os layouts Blade conforme a identidade visual do projeto.

## Acesso administrativo
- Apenas usuarios com `is_admin = true` podem editar, concluir, remover e reenviar lembretes de compromissos.
- Para promover um usuario existente, utilize o Tinker: `php artisan tinker -q` e execute `App\Models\User::find(1)->update(['is_admin' => true]);`.
- Caso crie usuarios via seed, lembre-se de informar o campo `is_admin`.
## Envio manual na agenda
- O bloco "WhatsApp rapido" permite enviar mensagens individuais com ou sem anexos (imagens e PDF).
- Apos o texto, a aplicacao dispara os botoes `Confirmar` e `Cancelar` para testes de resposta.
- Todas as mensagens enviadas e recebidas sao registradas na tabela `whatsapp_messages`.
