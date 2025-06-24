-- Create databases for different environments
CREATE DATABASE IF NOT EXISTS `rei_do_oleo_dev` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS `rei_do_oleo_test` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS `rei_do_oleo_staging` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user if not exists (MySQL 8.0 syntax)
CREATE USER IF NOT EXISTS 'rei_do_oleo'@'%' IDENTIFIED BY 'secret123';

-- Grant permissions to user
GRANT ALL PRIVILEGES ON `rei_do_oleo_dev`.* TO 'rei_do_oleo'@'%';
GRANT ALL PRIVILEGES ON `rei_do_oleo_test`.* TO 'rei_do_oleo'@'%';
GRANT ALL PRIVILEGES ON `rei_do_oleo_staging`.* TO 'rei_do_oleo'@'%';

-- Additional permissions for development
GRANT CREATE, ALTER, DROP, INSERT, UPDATE, DELETE, SELECT, REFERENCES, RELOAD on *.* TO 'rei_do_oleo'@'%';

FLUSH PRIVILEGES; 