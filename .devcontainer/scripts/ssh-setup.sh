#!/bin/bash

echo "🔐 Configurando SSH para Git..."

SSH_DIR="/home/vscode/.ssh"

# Verificar se o diretório SSH existe
if [ -d "$SSH_DIR" ]; then
    echo "✅ Diretório SSH encontrado: $SSH_DIR"

    # Configurar permissões corretas
    chmod 700 "$SSH_DIR"

    # Detectar e configurar chaves SSH disponíveis
    for key_type in id_rsa id_ed25519 id_ecdsa; do
        if [ -f "$SSH_DIR/$key_type" ]; then
            echo "🔑 Configurando chave SSH: $key_type"
            chmod 600 "$SSH_DIR/$key_type"

            # Configurar GIT_SSH_COMMAND para esta chave
            export GIT_SSH_COMMAND="ssh -i $SSH_DIR/$key_type -o StrictHostKeyChecking=no"

            # Adicionar ao bashrc e zshrc para persistir
            echo "export GIT_SSH_COMMAND=\"ssh -i $SSH_DIR/$key_type -o StrictHostKeyChecking=no\"" >> /home/vscode/.bashrc
            echo "export GIT_SSH_COMMAND=\"ssh -i $SSH_DIR/$key_type -o StrictHostKeyChecking=no\"" >> /home/vscode/.zshrc

            echo "✅ Chave $key_type configurada"
            break
        fi
    done

    # Configurar permissões para chaves públicas
    for pub_key in "$SSH_DIR"/*.pub; do
        if [ -f "$pub_key" ]; then
            chmod 644 "$pub_key"
        fi
    done

    # Configurar known_hosts se existir
    if [ -f "$SSH_DIR/known_hosts" ]; then
        chmod 644 "$SSH_DIR/known_hosts"
    fi

    # Testar conexão SSH com GitHub
    echo "🧪 Testando conexão SSH com GitHub..."
    if ssh -T git@github.com 2>&1 | grep -q "successfully authenticated"; then
        echo "✅ Conexão SSH com GitHub funcionando!"
    else
        echo "⚠️  Conexão SSH pode não estar funcionando. Verifique suas chaves."
    fi

    # Configurar git para usar SSH por padrão
    git config --global url."git@github.com:".insteadOf "https://github.com/"

    echo "✅ SSH configurado com sucesso!"

else
    echo "⚠️  Diretório SSH não encontrado. Certifique-se de que suas chaves SSH estão em ~/.ssh no host."
    echo "💡 Para resolver:"
    echo "   1. Gere uma chave SSH no host: ssh-keygen -t ed25519 -C 'your_email@example.com'"
    echo "   2. Adicione a chave ao GitHub: cat ~/.ssh/id_ed25519.pub"
    echo "   3. Reconstrua o container: Dev Containers: Rebuild Container"
fi
