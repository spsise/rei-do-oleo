-- 🗄️ Inicialização MySQL - Sistema Rei do Óleo
-- Script executado automaticamente na criação do container MySQL

-- Criar bancos de dados para diferentes ambientes
CREATE DATABASE IF NOT EXISTS `rei_do_oleo_dev` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS `rei_do_oleo_test` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS `rei_do_oleo_staging` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Criar usuário específico para a aplicação
CREATE USER IF NOT EXISTS 'rei_do_oleo'@'%' IDENTIFIED BY 'secret123';

-- Conceder permissões ao usuário nos bancos
GRANT ALL PRIVILEGES ON `rei_do_oleo_dev`.* TO 'rei_do_oleo'@'%';
GRANT ALL PRIVILEGES ON `rei_do_oleo_test`.* TO 'rei_do_oleo'@'%';
GRANT ALL PRIVILEGES ON `rei_do_oleo_staging`.* TO 'rei_do_oleo'@'%';

-- Aplicar permissões
FLUSH PRIVILEGES;

-- Configurações de performance básicas
SET GLOBAL innodb_buffer_pool_size = 268435456; -- 256MB
SET GLOBAL max_connections = 200;
SET GLOBAL query_cache_size = 67108864; -- 64MB 