#!/bin/bash

# Script para corrigir problema de planos vazios em produção
# Execute este script no servidor de produção via SSH

echo "========================================="
echo "  Corrigindo Planos em Produção"
echo "========================================="
echo ""

# 1. Limpar todos os caches
echo "1. Limpando caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo "✓ Caches limpos"
echo ""

# 2. Recriar cache de configuração
echo "2. Recriando cache de configuração..."
php artisan config:cache
echo "✓ Cache de configuração criado"
echo ""

# 3. Verificar se os planos estão sendo carregados
echo "3. Verificando planos no config..."
php artisan tinker --execute="var_dump(config('mercadopago.plans'));"
echo ""

# 4. Verificar permissões
echo "4. Verificando permissões..."
ls -la config/mercadopago.php
ls -la storage/logs/
echo ""

# 5. Ver últimos erros do log
echo "5. Últimos 50 erros do log:"
tail -50 storage/logs/laravel.log | grep -i "error\|exception" || echo "Nenhum erro recente encontrado"
echo ""

echo "========================================="
echo "  Comandos executados com sucesso!"
echo "========================================="
echo ""
echo "Agora acesse a página de planos e verifique os logs:"
echo "tail -f storage/logs/laravel.log | grep 'SubscriptionWebController'"
