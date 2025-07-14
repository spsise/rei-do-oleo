#!/bin/bash

echo "🔍 Testando conectividade com MySQL..."

# Lista de senhas comuns para testar
PASSWORDS=("" "root" "root123" "password" "admin" "mysql" "123456" "secret")

for password in "${PASSWORDS[@]}"; do
    echo "Tentando senha: '$password'"
    
    if [ -z "$password" ]; then
        # Testar sem senha
        if docker exec -i rei-do-oleo_devcontainer-devcontainer-1 mysql -h mysql -u root -e "SELECT 1;" 2>/dev/null; then
            echo "✅ Conectado sem senha!"
            exit 0
        fi
    else
        # Testar com senha
        if docker exec -i rei-do-oleo_devcontainer-devcontainer-1 mysql -h mysql -u root -p"$password" -e "SELECT 1;" 2>/dev/null; then
            echo "✅ Conectado com senha: $password"
            exit 0
        fi
    fi
done

echo "❌ Nenhuma senha funcionou. Vamos verificar os logs do MySQL..."
docker logs rei-do-oleo_devcontainer-mysql-1 2>&1 | grep -i "password\|generated" | tail -10 