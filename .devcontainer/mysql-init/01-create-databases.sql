-- Create databases for different environments
CREATE DATABASE IF NOT EXISTS `reidooleo_dev` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS `reidooleo_test` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS `reidooleo_staging` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Grant permissions to user
GRANT ALL PRIVILEGES ON `reidooleo_dev`.* TO 'reidooleo'@'%';
GRANT ALL PRIVILEGES ON `reidooleo_test`.* TO 'reidooleo'@'%';
GRANT ALL PRIVILEGES ON `reidooleo_staging`.* TO 'reidooleo'@'%';

FLUSH PRIVILEGES; 