-- ============================================
-- Script para tornar Idelcio Super Admin
-- ============================================

-- Atualiza o usuário idelcioforest@gmail.com
UPDATE users
SET
    is_super_admin = 1,
    acesso_ativo = 1,
    acesso_liberado_ate = NULL,
    plano = 'anual',
    limite_requisicoes_mes = 999999
WHERE email = 'idelcioforest@gmail.com';

-- Verifica se foi atualizado
SELECT
    id,
    name,
    email,
    is_super_admin,
    acesso_ativo,
    plano,
    limite_requisicoes_mes
FROM users
WHERE email = 'idelcioforest@gmail.com';
