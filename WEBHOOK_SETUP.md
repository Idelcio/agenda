# 🔔 Guia de Configuração do Webhook Meta WhatsApp

## ✅ O que já foi feito:

1. ✅ Controller do Webhook criado (`MetaWebhookController.php`)
2. ✅ Rotas configuradas (`/webhooks/meta`)
3. ✅ CSRF desabilitado para o webhook
4. ✅ Token de verificação definido no `.env`

---

## 📋 Próximos Passos:

### **Passo 1: Expor o Localhost para a Internet**

A Meta precisa acessar seu webhook, mas `localhost:8000` não é acessível pela internet.

**Opção A: Usar ngrok (Recomendado para testes)**

1. Baixe o ngrok: https://ngrok.com/download
2. Extraia e execute:
   ```bash
   ngrok http 8000
   ```
3. Você verá uma URL pública como: `https://abc123.ngrok.io`
4. Use essa URL + `/webhooks/meta` no painel da Meta

**Opção B: Usar Localtunnel**
```bash
npx localtunnel --port 8000
```

**Opção C: Usar Serveo**
```bash
ssh -R 80:localhost:8000 serveo.net
```

---

### **Passo 2: Configurar o Webhook no Painel da Meta**

1. Acesse: https://developers.facebook.com/apps
2. Selecione seu app "Agendoo Chat"
3. No menu lateral, clique em **"WhatsApp" → "Configuração"**
4. Procure a seção **"Webhooks"** ou **"Configure webhooks"**
5. Clique em **"Editar"** ou **"Configure"**

**Preencha:**
- **URL de callback**: `https://SUA-URL-NGROK.ngrok.io/webhooks/meta`
- **Verificar token**: `agendoo_webhook_secret_2026`

6. Clique em **"Verificar e salvar"**

---

### **Passo 3: Inscrever nos Eventos**

Depois de verificar o webhook, você precisa se inscrever nos eventos:

1. Na mesma tela de Webhooks
2. Procure por **"Campos do webhook"** ou **"Webhook fields"**
3. **Marque as caixas:**
   - ✅ `messages` (mensagens recebidas)
   - ✅ `message_status` (status de entrega)
   - ✅ `message_echoes` (opcional)
   - ✅ `message_reactions` (opcional)

4. Clique em **"Salvar"**

---

## 🧪 Testando o Webhook:

### **Teste 1: Verificação**
Quando você clicar em "Verificar e salvar" na Meta, ela fará uma requisição GET para:
```
https://sua-url.ngrok.io/webhooks/meta?hub.mode=subscribe&hub.verify_token=agendoo_webhook_secret_2026&hub.challenge=123456
```

Se tudo estiver certo, você verá "Webhook verificado!" nos logs do Laravel.

### **Teste 2: Enviar Mensagem**
1. Vá para `http://localhost:8000/setup-meta`
2. Envie uma mensagem de teste
3. Verifique os logs do Laravel:
   ```bash
   tail -f storage/logs/laravel.log
   ```
4. Você deve ver:
   - "Status de mensagem atualizado" (sent, delivered, read)

### **Teste 3: Receber Mensagem**
1. Envie uma mensagem do seu WhatsApp para o número de teste da Meta
2. Verifique os logs
3. Você deve ver: "Mensagem recebida"

---

## 📊 Monitorando os Logs:

Execute em um terminal separado:
```bash
cd c:\Users\Forest\Projetos\Laravel\agenda\agenda
php artisan tail
```

Ou veja o arquivo de log:
```bash
tail -f storage/logs/laravel.log
```

---

## ⚠️ Importante:

- O ngrok gratuito muda a URL toda vez que você reinicia
- Para produção, use um domínio real com HTTPS
- O webhook DEVE usar HTTPS (ngrok já fornece isso)

---

## 🎯 URL do Webhook:

Depois de rodar o ngrok, sua URL será:
```
https://SEU-SUBDOMINIO.ngrok.io/webhooks/meta
```

**Token de Verificação:**
```
agendoo_webhook_secret_2026
```

---

## 🚀 Próximo Passo:

1. Baixe e rode o ngrok
2. Copie a URL HTTPS que ele gerar
3. Configure no painel da Meta
4. Teste enviando uma mensagem!
