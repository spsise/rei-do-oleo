# 🚀 Guia de Acesso - Aplicação Vite no WSL2

## ✅ Status Atual

A aplicação Vite está **funcionando corretamente** no devcontainer WSL2!

### 📍 URLs de Acesso

1. **Via VS Code Dev Container (Recomendado)**

   - Abra o VS Code
   - Conecte-se ao devcontainer
   - Clique no link que aparece na notificação ou use: `http://localhost:5173`

2. **Via Browser Direto**

   - **Windows**: `http://localhost:5173`
   - **WSL2 IP**: `http://172.25.0.7:5173`

3. **Via Port Forwarding do VS Code**
   - Abra a aba "PORTS" no VS Code
   - Clique no ícone de "globe" ao lado da porta 5173
   - Isso abrirá automaticamente no seu browser

## 🔧 Configurações Atuais

### Vite Config (`frontend/vite.config.ts`)

```typescript
export default defineConfig({
  plugins: [react()],
  server: {
    host: '0.0.0.0', // Aceita conexões de qualquer IP
    port: 5173, // Porta fixa
    strictPort: true, // Falha se a porta estiver ocupada
    watch: {
      usePolling: true, // Necessário para WSL2
      interval: 1000, // Intervalo de polling
    },
  },
});
```

### DevContainer Config

- ✅ Porta 5173 configurada para forward automático
- ✅ `onAutoForward: "openBrowser"` ativado
- ✅ Protocolo HTTP configurado

## 🚀 Como Iniciar o Servidor

### Opção 1: Script Automático (Recomendado)

```bash
/workspace/scripts/dev-server.sh
```

### Opção 2: Comando Manual

```bash
cd /workspace/frontend
npm run dev
```

### Opção 3: Com Parâmetros Específicos

```bash
cd /workspace/frontend
npm run dev -- --host 0.0.0.0 --port 5173
```

## 🔍 Troubleshooting

### Se não conseguir acessar:

1. **Verificar se o servidor está rodando:**

   ```bash
   ps aux | grep vite
   netstat -tlnp | grep 5173
   ```

2. **Testar conectividade interna:**

   ```bash
   curl -I http://localhost:5173
   curl -I http://0.0.0.0:5173
   ```

3. **Verificar port forwarding no VS Code:**

   - Aba "PORTS" deve mostrar porta 5173 como "Forwarded"
   - Status deve ser "Running"

4. **Reiniciar o servidor:**
   ```bash
   pkill -f "vite"
   cd /workspace/frontend && npm run dev
   ```

### Problemas Comuns:

1. **"Connection refused"**

   - Servidor não está rodando
   - Execute: `pkill -f "vite" && cd /workspace/frontend && npm run dev`

2. **"Port already in use"**

   - Execute: `pkill -f "vite"` e reinicie

3. **"Cannot access from Windows"**
   - Use o port forwarding do VS Code
   - Ou acesse via `http://localhost:5173` no Windows

## 📱 Acesso Mobile (Opcional)

Para testar em dispositivos móveis na mesma rede:

1. **Descobrir IP do WSL2:**

   ```bash
   hostname -I
   ```

2. **Acessar via IP:**
   ```
   http://172.25.0.7:5173
   ```

## 🔄 Hot Reload

O servidor está configurado com:

- ✅ Hot reload ativo
- ✅ File watching com polling (necessário para WSL2)
- ✅ Auto-refresh no browser

## 📊 Monitoramento

### Logs do Servidor

- Os logs aparecem no terminal onde o `npm run dev` foi executado
- Erros de compilação são mostrados em tempo real
- Hot reload status é exibido

### Performance

- Tempo de compilação: ~1-3 segundos
- Hot reload: ~500ms
- Bundle size: Otimizado para desenvolvimento

## 🎯 Próximos Passos

1. ✅ Acesse a aplicação via `http://localhost:5173`
2. ✅ Teste o hot reload (edite um arquivo .tsx)
3. ✅ Verifique se a autenticação está funcionando
4. ✅ Teste as rotas protegidas

## 📞 Suporte

Se ainda tiver problemas:

1. Verifique se o devcontainer está rodando corretamente
2. Reinicie o VS Code se necessário
3. Execute `docker-compose down && docker-compose up` no diretório `.devcontainer`

---

**🎉 A aplicação está pronta para desenvolvimento!**
