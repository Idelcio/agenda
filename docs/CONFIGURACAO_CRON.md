# Configuração do CRON para Envio Automático de Lembretes

## O Problema que Foi Corrigido

Foram identificados **2 problemas principais** no sistema:

### 1. Resposta "1" marcava compromisso errado como Cancelado ❌

**Causa:** O webhook estava buscando o compromisso usando `destinatario_user_id`, pegando o último compromisso onde o usuário era destinatário, não o compromisso específico que acabou de receber o lembrete.

**Solução:** Alterado para buscar por `whatsapp_numero` + `status_lembrete = 'enviado'` + ordenar por `lembrete_enviado_em` (mais recente primeiro).

```php
// ❌ ANTES (ERRADO)
$appointment = Appointment::query()
    ->where('destinatario_user_id', $user->id)
    ->whereIn('status', ['pendente', 'confirmado'])
    ->latest('inicio')
    ->first();

// ✅ AGORA (CORRETO)
$appointment = Appointment::query()
    ->where('whatsapp_numero', preg_replace('/\D+/', '', $whatsappNumber))
    ->whereIn('status', ['pendente', 'confirmado'])
    ->where('status_lembrete', 'enviado')
    ->latest('lembrete_enviado_em')
    ->first();
```

### 2. Envio automático não funcionava em produção ⏰

**Causa:** O JavaScript tentava enviar lembretes apenas enquanto a página estava aberta no navegador. Em produção, ninguém fica 24/7 com a página aberta.

**Solução:** Removido o envio via JavaScript. O envio deve ser feito pelo **CRON do Laravel** executando o comando `agenda:disparar-lembretes` a cada minuto.

---

## Como Configurar o CRON (Essencial!)

### Em Produção (Linux/cPanel)

1. **Acesse o cPanel** ou SSH do servidor
2. **Abra o gerenciador de CRON Jobs**
3. **Adicione a seguinte linha:**

```bash
* * * * * cd /caminho/para/o/projeto && php artisan schedule:run >> /dev/null 2>&1
```

**Importante:** Substitua `/caminho/para/o/projeto` pelo caminho real da sua aplicação.

### Verificando se o CRON está funcionando

Execute manualmente o comando de disparo:

```bash
php artisan agenda:disparar-lembretes
```

Você deve ver algo como:

```
✓ Lembrete enviado: Consulta Médica (ID: 123)
✓ Lembrete enviado: Corte de Cabelo (ID: 124)

===== RESUMO =====
Lembretes enviados: 2
```

### Como o Sistema Funciona Agora

```
1. Usuário cria compromisso com lembrete ativado
   └─> Agenda salva no banco: status_lembrete = 'pendente'

2. CRON do Laravel roda a cada minuto
   └─> Executa: php artisan agenda:disparar-lembretes
       └─> Busca compromissos onde:
           - notificar_whatsapp = true
           - status_lembrete = 'pendente'
           - lembrar_em <= agora
       └─> Envia mensagem via WhatsApp
       └─> Atualiza: status_lembrete = 'enviado'

3. Cliente responde "1" ou "2"
   └─> Webhook recebe a mensagem
       └─> Busca o compromisso pelo whatsapp_numero + status_lembrete = 'enviado'
       └─> Atualiza status: 'confirmado' ou 'cancelado'
```

---

## Outras Melhorias Aplicadas

### Mensagem de Cancelamento

**Antes:**
```
❌ Seu atendimento foi CANCELADO!
💬 Deseja remarcar? Responda:
✅ Sim - para remarcar
❌ Não - para encerrar
```

**Agora (mais simples):**
```
❌ Seu atendimento foi CANCELADO!
💬 Para remarcar, entre em contato conosco.
```

---

## Comandos Úteis

```bash
# Testar envio de lembretes manualmente
php artisan agenda:disparar-lembretes

# Ver logs do Laravel
tail -f storage/logs/laravel.log

# Listar todos os comandos agendados
php artisan schedule:list

# Testar o agendador (simula 1 minuto de execução)
php artisan schedule:work
```

---

## Checklist de Validação

- [ ] CRON configurado no servidor (execute `crontab -l` para verificar)
- [ ] Comando manual `php artisan agenda:disparar-lembretes` funciona
- [ ] Logs em `storage/logs/laravel.log` mostram envios bem-sucedidos
- [ ] Criar compromisso de teste com lembrete para 2 minutos no futuro
- [ ] Aguardar e verificar se o lembrete foi enviado automaticamente
- [ ] Responder "1" no WhatsApp e verificar se marca como Confirmado
- [ ] Responder "2" em outro compromisso e verificar se marca como Cancelado

---

## Em Caso de Problemas

### Lembretes não estão sendo enviados

1. Verifique se o CRON está ativo:
   ```bash
   crontab -l
   ```

2. Execute manualmente e veja o erro:
   ```bash
   php artisan agenda:disparar-lembretes
   ```

3. Verifique os logs:
   ```bash
   tail -100 storage/logs/laravel.log
   ```

### Cliente responde mas o status não muda

1. Verifique se o webhook está configurado na API Brasil
2. Veja os logs de webhook:
   ```bash
   grep "Webhook recebido" storage/logs/laravel.log | tail -20
   ```

3. Confirme que o número está salvo corretamente (sem espaços, com código do país)

---

## Suporte

Se precisar de ajuda, verifique:

- **Logs do Laravel:** `storage/logs/laravel.log`
- **Configuração do .env:** Certifique-se que `API_BRASIL_*` está preenchido
- **Webhook da API Brasil:** Deve apontar para `https://seusite.com/api/whatsapp/webhook`
