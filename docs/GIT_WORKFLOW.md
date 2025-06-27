# 🔄 Git Workflow & Versionamento - Sistema Rei do Óleo

## 📋 Índice

- [Padrão de Commits](#-padrão-de-commits)
- [Estrutura de Branches](#-estrutura-de-branches)
- [Versionamento Semântico](#-versionamento-semântico)
- [Comandos Git Úteis](#-comandos-git-úteis)
- [Workflow de Desenvolvimento](#-workflow-de-desenvolvimento)
- [Code Review](#-code-review)
- [Automação e Hooks](#-automação-e-hooks)

## 🏷️ Padrão de Commits

### 📍 Estrutura Base

```
[ÁREA] [TIPO] [ESCOPO]: [DESCRIÇÃO]

[CORPO OPCIONAL]

[RODAPÉ OPCIONAL]
```

### 🎯 Áreas do Projeto

#### 🐘 **Backend (Laravel)**

```bash
🐘 Backend ✨ feat: Adiciona autenticação via Sanctum
🐘 Backend 🐛 fix: Corrige validação de email no registro
🐘 Backend ♻️ refactor: Reorganiza estrutura de controllers
🐘 Backend 🔧 chore: Atualiza dependências do Composer
🐘 Backend 🧪 test: Adiciona testes para AuthController
🐘 Backend 📊 perf: Otimiza queries do usuário
🐘 Backend 🔒 security: Implementa rate limiting na API
```

#### ⚛️ **Frontend (React)**

```bash
⚛️ Frontend ✨ feat: Implementa tela de login
⚛️ Frontend 🐛 fix: Corrige responsividade do menu
⚛️ Frontend 🎨 style: Atualiza tema e cores do sistema
⚛️ Frontend ♻️ refactor: Converte componentes para TypeScript
⚛️ Frontend 🔧 chore: Atualiza dependências do npm
⚛️ Frontend 📱 mobile: Ajusta layout para dispositivos móveis
⚛️ Frontend 🧪 test: Adiciona testes para componente Button
```

#### 🐳 **DevOps/Infraestrutura**

```bash
🐳 DevOps 🚀 deploy: Configura pipeline CI/CD no GitHub Actions
🐳 DevOps 🔧 chore: Atualiza configuração do Docker
🐳 DevOps 📊 monitoring: Adiciona Prometheus e Grafana
🐳 DevOps 🔒 security: Configura SSL e certificados
🐳 DevOps 🏗️ infra: Provisiona recursos na AWS com Terraform
🐳 DevOps 🔄 backup: Implementa rotina automática de backup
```

#### 📊 **Database**

```bash
📊 Database ✨ feat: Cria tabela de produtos
📊 Database 🔄 migration: Adiciona índices de performance
📊 Database 🌱 seed: Atualiza dados iniciais do sistema
📊 Database 🐛 fix: Corrige constraint de foreign key
📊 Database ♻️ refactor: Normaliza estrutura de endereços
```

#### 📝 **Documentação**

```bash
📝 Docs ✨ feat: Adiciona documentação da API
📝 Docs 🔄 update: Atualiza README com instruções
📝 Docs 🐛 fix: Corrige links quebrados na documentação
📝 Docs 📊 diagram: Adiciona diagrama de arquitetura
```

### 🎨 Tipos de Commit (com ícones)

| Ícone | Tipo       | Descrição               | Exemplo                                          |
| ----- | ---------- | ----------------------- | ------------------------------------------------ |
| ✨    | `feat`     | Nova funcionalidade     | `✨ feat: Adiciona sistema de busca`             |
| 🐛    | `fix`      | Correção de bug         | `🐛 fix: Corrige erro de validação`              |
| ♻️    | `refactor` | Refatoração de código   | `♻️ refactor: Simplifica lógica de autenticação` |
| 🎨    | `style`    | Formatação e estilo     | `🎨 style: Ajusta indentação do código`          |
| ⚡    | `perf`     | Melhoria de performance | `⚡ perf: Otimiza consultas do banco`            |
| 🔧    | `chore`    | Tarefas de manutenção   | `🔧 chore: Atualiza dependências`                |
| 🧪    | `test`     | Testes                  | `🧪 test: Adiciona testes unitários`             |
| 📝    | `docs`     | Documentação            | `📝 docs: Atualiza guia de instalação`           |
| 🔒    | `security` | Segurança               | `🔒 security: Corrige vulnerabilidade XSS`       |
| 🚀    | `deploy`   | Deploy/Release          | `🚀 deploy: Publica versão 1.2.0`                |
| 🔥    | `remove`   | Remoção de código       | `🔥 remove: Remove código depreciado`            |
| 📱    | `mobile`   | Específico mobile       | `📱 mobile: Ajusta para iOS`                     |
| 🌐    | `i18n`     | Internacionalização     | `🌐 i18n: Adiciona tradução PT-BR`               |
| 🏗️    | `infra`    | Infraestrutura          | `🏗️ infra: Configura load balancer`              |

### 📋 Exemplos Práticos

#### Commit Simples

```bash
🐘 Backend ✨ feat: Adiciona campo active no retorno do login
```

#### Commit Completo com Corpo e Rodapé

```bash
🐘 Backend ✨ feat: Adiciona campo 'active' no retorno do login

- Inclui status ativo/inativo do usuário na resposta da API
- Atualiza documentação OpenAPI do endpoint
- Mantém compatibilidade com versões anteriores

Closes #145
Breaking-change: false
```

#### Commit de Hotfix

```bash
🐘 Backend 🐛 hotfix: Corrige erro crítico na autenticação

Emergency fix para resolver problema de tokens expirados

Fixes #urgent-152
Priority: High
```

## 🌿 Estrutura de Branches

### 📊 Branches Principais

```
main
├── develop
├── release/v1.2.0
├── hotfix/fix-critical-auth
└── feature/user-management
    ├── feature/user-crud
    └── feature/user-permissions
```

### 🏷️ Convenção de Nomenclatura

| Tipo        | Padrão                           | Exemplo                         | Descrição               |
| ----------- | -------------------------------- | ------------------------------- | ----------------------- |
| **Feature** | `feature/nome-da-funcionalidade` | `feature/user-authentication`   | Nova funcionalidade     |
| **Bugfix**  | `bugfix/nome-do-bug`             | `bugfix/login-validation-error` | Correção de bug         |
| **Hotfix**  | `hotfix/nome-do-hotfix`          | `hotfix/security-vulnerability` | Correção urgente        |
| **Release** | `release/vX.Y.Z`                 | `release/v1.2.0`                | Preparação para release |
| **Chore**   | `chore/nome-da-tarefa`           | `chore/update-dependencies`     | Manutenção              |

### 🔄 Fluxo de Branches

1. **main**: Código em produção
2. **develop**: Desenvolvimento contínuo
3. **feature/\***: Novas funcionalidades
4. **release/\***: Preparação para produção
5. **hotfix/\***: Correções urgentes

## 📦 Versionamento Semântico

### 🏷️ Formato: `MAJOR.MINOR.PATCH`

- **MAJOR** (1.0.0): Mudanças incompatíveis na API
- **MINOR** (0.1.0): Funcionalidades compatíveis
- **PATCH** (0.0.1): Correções de bugs compatíveis

### 📈 Exemplos de Versionamento

```bash
# Versão inicial
v1.0.0 - Sistema MVP completo

# Adição de funcionalidade
v1.1.0 - Adiciona sistema de relatórios

# Correção de bug
v1.1.1 - Corrige erro na autenticação

# Breaking change
v2.0.0 - Nova arquitetura de API
```

### 🏷️ Tags de Release

```bash
# Criar tag de release
git tag -a v1.2.0 -m "🚀 Release v1.2.0 - Adiciona gestão de usuários"

# Enviar tags para repositório
git push origin --tags

# Listar tags
git tag -l

# Ver detalhes de uma tag
git show v1.2.0
```

## 🛠️ Comandos Git Úteis

### 🔍 Pesquisa e Filtragem

```bash
# Ver apenas commits do backend
git log --oneline --grep="🐘 Backend"

# Ver apenas features
git log --oneline --grep="✨ feat"

# Ver commits por tipo
git log --oneline --grep="🐛 fix"

# Ver commits de uma área específica
git log --oneline --grep="⚛️ Frontend"

# Ver commits entre datas
git log --since="2024-01-01" --until="2024-01-31" --oneline

# Ver commits de um autor
git log --author="João Silva" --oneline

# Ver mudanças em arquivo específico
git log --oneline -- backend/app/Models/User.php
```

### 📊 Estatísticas e Histórico

```bash
# Estatísticas de commits por autor
git shortlog -sn

# Ver mudanças em período específico
git log --stat --since="1 week ago"

# Gráfico de branches
git log --graph --oneline --all

# Ver últimos 10 commits
git log --oneline -10

# Ver commits que afetaram um arquivo
git log --follow --patch -- arquivo.php
```

### 🔧 Manipulação de Commits

```bash
# Alterar último commit
git commit --amend -m "🐘 Backend ✨ feat: Nova mensagem do commit"

# Refazer últimos 3 commits (interativo)
git rebase -i HEAD~3

# Squash de commits em feature branch
git rebase -i develop

# Cherry-pick de commit específico
git cherry-pick a1b2c3d

# Reverter commit mantendo histórico
git revert a1b2c3d
```

### 🌿 Gestão de Branches

```bash
# Criar e mudar para nova branch
git checkout -b feature/nova-funcionalidade

# Listar todas as branches
git branch -a

# Deletar branch local
git branch -d feature/funcionalidade-concluida

# Deletar branch remota
git push origin --delete feature/funcionalidade-concluida

# Sincronizar com remoto
git fetch --prune

# Mudar para branch anterior
git checkout -
```

### 🔄 Sincronização e Merge

```bash
# Atualizar branch com rebase
git pull --rebase origin develop

# Merge com squash
git merge --squash feature/nova-funcionalidade

# Merge sem fast-forward
git merge --no-ff feature/nova-funcionalidade

# Stash mudanças temporariamente
git stash push -m "WIP: implementando nova feature"

# Aplicar stash
git stash pop
```

### 🧹 Limpeza e Manutenção

```bash
# Limpar branches já merged
git branch --merged | grep -v "\*\|main\|develop" | xargs -n 1 git branch -d

# Limpar arquivos não rastreados
git clean -fd

# Verificar status detalhado
git status --porcelain

# Ver diferenças staged
git diff --cached

# Resetar arquivo específico
git checkout HEAD -- arquivo.php
```

## 🔄 Workflow de Desenvolvimento

### 🚀 Fluxo Padrão para Novas Features

```bash
# 1. Atualizar develop
git checkout develop
git pull origin develop

# 2. Criar branch da feature
git checkout -b feature/gestao-usuarios

# 3. Desenvolver e commitar
git add .
git commit -m "🐘 Backend ✨ feat: Adiciona CRUD de usuários"

# 4. Manter branch atualizada
git fetch origin
git rebase origin/develop

# 5. Push da branch
git push -u origin feature/gestao-usuarios

# 6. Abrir Pull Request via GitHub
# 7. Após aprovação, merge com squash
```

### 🐛 Fluxo para Correções

```bash
# 1. Branch a partir de develop (ou main se urgente)
git checkout develop
git checkout -b bugfix/correcao-login

# 2. Implementar correção
git add .
git commit -m "🐘 Backend 🐛 fix: Corrige validação de email no login"

# 3. Testar correção
php artisan test

# 4. Push e PR
git push -u origin bugfix/correcao-login
```

### 🚨 Fluxo para Hotfixes

```bash
# 1. Branch a partir de main
git checkout main
git checkout -b hotfix/seguranca-critica

# 2. Aplicar correção
git add .
git commit -m "🐘 Backend 🔒 hotfix: Corrige vulnerabilidade de segurança"

# 3. Merge em main e develop
git checkout main
git merge --no-ff hotfix/seguranca-critica
git tag -a v1.2.1 -m "🚨 Hotfix v1.2.1 - Correção de segurança"

git checkout develop
git merge --no-ff hotfix/seguranca-critica

# 4. Deploy imediato
git push origin main develop --tags
```

## 👥 Code Review

### ✅ Checklist para Pull Requests

- [ ] Título e descrição claros
- [ ] Commits seguem padrão estabelecido
- [ ] Código testado e funcionando
- [ ] Documentação atualizada
- [ ] Sem conflitos com branch de destino
- [ ] CI/CD passando
- [ ] Aprovação de pelo menos 1 reviewer

### 📝 Template de PR

```markdown
## 📋 Descrição

Breve descrição das mudanças implementadas.

## 🎯 Tipo de Mudança

- [ ] 🐛 Bug fix
- [ ] ✨ Nova feature
- [ ] ♻️ Refatoração
- [ ] 📝 Documentação
- [ ] 🔒 Segurança

## 🧪 Como Testar

1. Passo 1
2. Passo 2
3. Resultado esperado

## 📸 Screenshots (se aplicável)

## ✅ Checklist

- [ ] Testes passando
- [ ] Documentação atualizada
- [ ] Code review solicitado

## 🔗 Issues Relacionadas

Closes #123
```

## 🔨 Automação e Hooks

### 🎣 Git Hooks Úteis

#### Pre-commit Hook

```bash
#!/bin/sh
# .git/hooks/pre-commit

# Executar testes antes do commit
echo "🧪 Executando testes..."
php artisan test

if [ $? -ne 0 ]; then
    echo "❌ Testes falharam. Commit cancelado."
    exit 1
fi

echo "✅ Testes passaram!"
```

#### Commit-msg Hook

```bash
#!/bin/sh
# .git/hooks/commit-msg

# Validar formato do commit
commit_regex='^(🐘|⚛️|🐳|📊|📝|🔧) (Backend|Frontend|DevOps|Database|Docs|Config) (✨|🐛|♻️|🎨|⚡|🔧|🧪|📝|🔒|🚀|🔥|📱|🌐|🏗️) (feat|fix|refactor|style|perf|chore|test|docs|security|deploy|remove|mobile|i18n|infra): .+'

if ! grep -qE "$commit_regex" "$1"; then
    echo "❌ Formato de commit inválido!"
    echo "Use: [ÁREA] [TIPO] [ESCOPO]: [DESCRIÇÃO]"
    echo "Exemplo: 🐘 Backend ✨ feat: Adiciona nova funcionalidade"
    exit 1
fi
```

### 🤖 Comandos Automatizados

```bash
# Script para setup de repositório
#!/bin/bash
# scripts/git-setup.sh

echo "🔧 Configurando Git..."

# Configurar hooks
cp scripts/hooks/* .git/hooks/
chmod +x .git/hooks/*

# Configurar aliases úteis
git config alias.co checkout
git config alias.br branch
git config alias.ci commit
git config alias.st status
git config alias.unstage 'reset HEAD --'
git config alias.last 'log -1 HEAD'
git config alias.visual '!gitk'

# Configurar assinatura de commits
git config commit.gpgsign true

echo "✅ Git configurado com sucesso!"
```

### 📋 Aliases Úteis

```bash
# Adicionar ao ~/.gitconfig ou executar comandos

[alias]
    # Status e logs
    s = status --short
    l = log --oneline --graph --decorate --all
    ll = log --graph --pretty=format:'%Cred%h%Creset -%C(yellow)%d%Creset %s %Cgreen(%cr) %C(bold blue)<%an>%Creset'

    # Branches
    b = branch
    co = checkout
    cob = checkout -b

    # Commits
    c = commit
    ca = commit -a
    cm = commit -m
    cam = commit -am

    # Push/Pull
    p = push
    pl = pull
    pf = push --force-with-lease

    # Reset e cleanup
    unstage = reset HEAD --
    undo = reset --soft HEAD~1
    cleanup = "!git branch --merged | grep -v '\\*\\|main\\|develop' | xargs -n 1 git branch -d"

    # Utilitários
    whoami = "!git config user.name && git config user.email"
    aliases = config --get-regexp alias
```

## 📚 Recursos e Ferramentas

### 🛠️ Ferramentas Recomendadas

- **GitKraken**: Interface gráfica avançada
- **SourceTree**: Interface gráfica gratuita
- **GitLens**: Extensão VS Code
- **Conventional Commits**: Extensão para padrões
- **Husky**: Git hooks para Node.js
- **Commitizen**: CLI para commits padronizados

### 📖 Documentação e Referências

- [Git Documentation](https://git-scm.com/docs)
- [GitHub Flow](https://guides.github.com/introduction/flow/)
- [Conventional Commits](https://www.conventionalcommits.org/)
- [Semantic Versioning](https://semver.org/)
- [Git Best Practices](https://sethrobertson.github.io/GitBestPractices/)

---

> 📝 **Nota**: Este documento deve ser seguido por toda a equipe para manter consistência no desenvolvimento e facilitar a manutenção do projeto.
