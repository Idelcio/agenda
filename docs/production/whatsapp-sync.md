# Automação de Lembretes e Sincronização WhatsApp

Este guia descreve como manter, em produção, os jobs que:

- disparam lembretes agendados (`agenda:disparar-lembretes`);
- sincronizam respostas recebidas (`agenda:sincronizar-respostas`).

Assumimos que o projeto reside em `/var/www/agenda` e que o PHP CLI está disponível em `/usr/bin/php`. Ajuste conforme sua infraestrutura.

---

## 1. Scheduler do Laravel (crontab)

> Executa `php artisan schedule:run` a cada minuto; o próprio scheduler dispara `agenda:disparar-lembretes` e demais tarefas agendadas.

1. Edite a crontab do usuário que roda a aplicação:

   ```bash
   crontab -e
   ```

2. Adicione a linha:

   ```
   * * * * * cd /var/www/agenda && /usr/bin/php artisan schedule:run >> /var/log/agenda-schedule.log 2>&1
   ```

3. Salve e saia. O log ficará em `/var/log/agenda-schedule.log` (altere conforme desejar).

---

## 2. Daemon de sincronização contínua

> `agenda:sincronizar-respostas` é um loop infinito; use Supervisor (ou systemd/PM2) para mantê-lo ativo.

### 2.1. Configuração Supervisor

1. Crie o arquivo `/etc/supervisor/conf.d/agenda-sync-whatsapp.conf` com o conteúdo:

   ```
   [program:agenda_sync_whatsapp]
   command=/usr/bin/php artisan agenda:sincronizar-respostas
   directory=/var/www/agenda
   autostart=true
   autorestart=true
   startsecs=5
   stderr_logfile=/var/log/agenda-sync-error.log
   stdout_logfile=/var/log/agenda-sync.log
   user=www-data  ; ajuste para o usuário correto
   stopsignal=INT
   ```

2. Recarregue o Supervisor:

   ```bash
   sudo supervisorctl reread
   sudo supervisorctl update
   ```

3. Verifique o status:

   ```bash
   sudo supervisorctl status agenda_sync_whatsapp
   ```

### 2.2. Verificação e logs

- Log de saída: `/var/log/agenda-sync.log`
- Log de erros: `/var/log/agenda-sync-error.log`
- Para reiniciar manualmente:

  ```bash
  sudo supervisorctl restart agenda_sync_whatsapp
  ```

---

## 3. Checklist rápido

- [ ] `.env` corretamente configurado (DB, API Brasil, etc.).
- [ ] `php artisan config:cache` e `php artisan route:cache` executados após deploy.
- [ ] Permissões corretas para `storage/` e `bootstrap/cache/`.
- [ ] Supervisor ativo (`sudo systemctl status supervisor`).
- [ ] Cron ativo (`systemctl status cron` ou `systemctl status crond`, conforme distro).

---

## 4. Troubleshooting

| Sintoma | Possível causa | Sugestão |
|--------|----------------|----------|
| Lembretes não disparam | Crontab ausente ou com caminho incorreto | Reconfira a entrada do `schedule:run` |
| Respostas não sincronizam | Supervisor parado / DeviceToken inválido | `sudo supervisorctl status agenda_sync_whatsapp` e logs em `/var/log/agenda-sync*.log` |
| `agenda:sincronizar-respostas` trava | Exception na API Brasil | veja `storage/logs/laravel.log` e os logs do supervisor |

---

## 5. Alternativas

Caso prefira `systemd` em vez de Supervisor:

1. Unidade `/etc/systemd/system/agenda-sync.service`:

   ```ini
   [Unit]
   Description=Sync WhatsApp replies
   After=network.target

   [Service]
   WorkingDirectory=/var/www/agenda
   ExecStart=/usr/bin/php artisan agenda:sincronizar-respostas
   Restart=always
   RestartSec=5
   User=www-data
   StandardOutput=append:/var/log/agenda-sync.log
   StandardError=append:/var/log/agenda-sync-error.log

   [Install]
   WantedBy=multi-user.target
   ```

2. Habilite e inicie:

   ```bash
   sudo systemctl daemon-reload
   sudo systemctl enable agenda-sync.service
   sudo systemctl start agenda-sync.service
   sudo systemctl status agenda-sync.service
   ```

---

> **Importante:** sempre rode os comandos como o mesmo usuário/grupo que possui o diretório do projeto, garantindo permissões corretas nos logs e caches.

