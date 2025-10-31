#!/bin/bash

echo "🚀 Iniciando deploy para produção..."

# 1. Fazer pull do código mais recente
echo "📥 Atualizando código..."
git pull origin main

# 2. Instalar/atualizar dependências (se necessário)
echo "📦 Atualizando dependências..."
composer install --no-dev --optimize-autoloader

# 3. Executar migrations
echo "🗄️  Executando migrations..."
php artisan migrate --force

# 4. Limpar todos os caches
echo "🧹 Limpando caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# 5. Otimizar para produção
echo "⚡ Otimizando para produção..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Recarregar PHP-FPM (se disponível)
echo "🔄 Recarregando PHP..."
if command -v php-fpm &> /dev/null; then
    sudo systemctl reload php-fpm
fi

echo "✅ Deploy concluído!"
echo ""
echo "🔍 Verificando rotas de mensagens prontas..."
php artisan route:list | grep quick-messages

echo ""
echo "📊 Status final:"
php artisan --version
