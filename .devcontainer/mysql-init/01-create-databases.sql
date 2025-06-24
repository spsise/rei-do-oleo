-- MySQL Initialization Script for Rei do Óleo Development

-- Create development database
CREATE DATABASE IF NOT EXISTS `rei_do_oleo_dev` 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

-- Create test database
CREATE DATABASE IF NOT EXISTS `rei_do_oleo_test` 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

-- Grant privileges to the application user
GRANT ALL PRIVILEGES ON `rei_do_oleo_dev`.* TO 'rei_do_oleo'@'%';
GRANT ALL PRIVILEGES ON `rei_do_oleo_test`.* TO 'rei_do_oleo'@'%';

-- Flush privileges
FLUSH PRIVILEGES;

-- Log the initialization
SELECT 'Database initialization completed successfully!' as message; 