#!/bin/bash

# Script de diagnóstico para problemas de deploy na Hostinger

echo "🔍 Diagnóstico de Deploy - Hostinger"
echo "====================================="

# Verificar se o Git Hook está configurado no servidor
echo ""
echo "📋 INSTRUÇÕES PARA VERIFICAR NO SERVIDOR:"
echo ""
echo "1. Conecte via SSH no servidor Hostinger:"
echo "   ssh usuario@servidor"
echo ""
echo "2. Verifique se o Git Hook existe:"
echo "   ls -la /home/usuario/.git/hooks/post-receive"
echo ""
echo "3. Verifique se o Git Hook é executável:"
echo "   chmod +x /home/usuario/.git/hooks/post-receive"
echo ""
echo "4. Verifique a configuração do Git:"
echo "   cd /home/usuario"
echo "   git remote -v"
echo "   git branch -a"
echo ""
echo "5. Verifique se os diretórios dos subdomínios existem:"
echo "   ls -la /home/usuario/api-hom.virtualt.com.br/"
echo "   ls -la /home/usuario/app-hom.virtualt.com.br/"
echo ""
echo "6. Verifique logs do deploy:"
echo "   tail -f /home/usuario/deploy.log"
echo ""
echo "7. Execute deploy manual:"
echo "   cd /home/usuario"
echo "   ./deploy.sh"
echo ""
echo "8. Verifique permissões dos diretórios:"
echo "   ls -la /home/usuario/api-hom.virtualt.com.br/"
echo "   ls -la /home/usuario/app-hom.virtualt.com.br/"
echo ""
echo "9. Verifique arquivos .htaccess:"
echo "   cat /home/usuario/api-hom.virtualt.com.br/.htaccess"
echo "   cat /home/usuario/app-hom.virtualt.com.br/.htaccess"
echo ""

# Verificar status atual dos subdomínios
echo "🌐 Status atual dos subdomínios:"
echo ""

echo "📡 API (api-hom.virtualt.com.br):"
API_STATUS=$(curl -s -o /dev/null -w "%{http_code}" https://api-hom.virtualt.com.br)
case $API_STATUS in
    200) echo "✅ API funcionando (HTTP 200)" ;;
    301|302) echo "⚠️ API redirecionando (HTTP $API_STATUS)" ;;
    403) echo "❌ API com erro de permissão (HTTP 403)" ;;
    404) echo "❌ API não encontrada (HTTP 404)" ;;
    500) echo "❌ Erro interno do servidor (HTTP 500)" ;;
    *) echo "❌ API não responde (HTTP $API_STATUS)" ;;
esac

echo ""
echo "🌐 Frontend (app-hom.virtualt.com.br):"
FRONTEND_STATUS=$(curl -s -o /dev/null -w "%{http_code}" https://app-hom.virtualt.com.br)
case $FRONTEND_STATUS in
    200) echo "✅ Frontend funcionando (HTTP 200)" ;;
    301|302) echo "⚠️ Frontend redirecionando (HTTP $FRONTEND_STATUS)" ;;
    403) echo "❌ Frontend com erro de permissão (HTTP 403)" ;;
    404) echo "❌ Frontend não encontrado (HTTP 404)" ;;
    500) echo "❌ Erro interno do servidor (HTTP 500)" ;;
    *) echo "❌ Frontend não responde (HTTP $FRONTEND_STATUS)" ;;
esac

echo ""
echo "🎯 PROBLEMAS COMUNS E SOLUÇÕES:"
echo ""
echo "❌ HTTP 403 (Forbidden):"
echo "   - Verificar permissões dos diretórios (755 para pastas, 644 para arquivos)"
echo "   - Verificar se o .htaccess está configurado corretamente"
echo "   - Verificar se os arquivos index.php/index.html existem"
echo ""
echo "❌ HTTP 404 (Not Found):"
echo "   - Verificar se o deploy foi executado"
echo "   - Verificar se os diretórios dos subdomínios existem"
echo "   - Verificar se o Git Hook está funcionando"
echo ""
echo "❌ HTTP 500 (Internal Server Error):"
echo "   - Verificar logs do Laravel (storage/logs/laravel.log)"
echo "   - Verificar configuração do .env"
echo "   - Verificar conectividade com banco de dados"
echo ""
echo "🔧 COMANDOS PARA CORRIGIR:"
echo ""
echo "1. Configurar Git Hook no servidor:"
echo "   ./scripts/setup-git-hooks-subdomains.sh"
echo ""
echo "2. Fazer deploy manual:"
echo "   cd /home/usuario"
echo "   git pull origin hostinger-hom"
echo ""
echo "3. Configurar permissões:"
echo "   chmod -R 755 /home/usuario/api-hom.virtualt.com.br/"
echo "   chmod -R 755 /home/usuario/app-hom.virtualt.com.br/"
echo "   chmod 644 /home/usuario/api-hom.virtualt.com.br/.env"
echo ""
echo "4. Configurar variáveis de ambiente:"
echo "   cd /home/usuario/api-hom.virtualt.com.br/"
echo "   cp .env.example .env"
echo "   nano .env"
echo ""
echo "5. Executar migrações:"
echo "   cd /home/usuario/api-hom.virtualt.com.br/"
echo "   php artisan migrate --force"
echo ""
echo "📞 Se os problemas persistirem, verifique:"
echo "   - Logs do servidor web (error.log)"
echo "   - Configuração dos subdomínios no painel da Hostinger"
echo "   - Configuração do banco de dados"
echo "   - Configuração do SSL/HTTPS"
