# 🔄 Fluxograma do Sistema Telegram - Rei do Óleo

## 🎯 Visão Geral

Este documento apresenta um fluxograma visual que mostra como uma mensagem do Telegram é processada pelo sistema, desde o envio pelo usuário até a resposta ser retornada.

## 📊 Fluxograma Principal

### **Fluxo Completo de Processamento**

```mermaid
flowchart TD
    A[👤 Usuário envia mensagem 'menu'] --> B[📱 Telegram Servers]
    B --> C[🌐 Webhook HTTP POST /api/telegram/webhook]
    C --> D[🔒 TelegramWebhookController handle]
    D --> E{📋 Validação de Request}

    E -->|✅ Válido| F[🔍 Processar Mensagem TelegramBotService]
    E -->|❌ Inválido| G[🚫 Retornar Erro 422]

    F --> H{🎯 Tipo de Mensagem}

    H -->|📝 Texto| I[📝 Processar Comando de Texto]
    H -->|🎤 Voz| J[🎤 Processar Mensagem de Voz]
    H -->|🔘 Callback Query| K[🔘 Processar Callback Query]
    H -->|📎 Outros| L[📎 Processar Outros Tipos]

    I --> M[🔍 Parser de Comandos UnifiedCommandSystem]
    J --> N[🎵 Speech-to-Text Vosk/OpenAI Whisper]
    K --> O[🔘 Processar Ação do Botão]
    L --> P[📎 Processar Arquivo/Mídia]

    N --> Q[📝 Texto Convertido]
    Q --> M

    M --> R{🎯 Comando Reconhecido?}

    R -->|✅ Sim| S[🚀 Executar Handler TelegramMenuBuilder]
    R -->|❌ Não| T[💡 Gerar Fallback Sugestões + Ajuda]

    S --> U[📊 Gerar Dados Services/Repositories]
    T --> V[📱 Resposta de Fallback]

    U --> W[🎨 Formatar Resposta Markdown + Emojis]
    W --> X[⌨️ Adicionar Teclado Botões Inline]

    X --> Y[📤 Enviar via TelegramChannel sendMessageWithKeyboard]
    V --> Y

    Y --> Z[🌐 Telegram API]
    Z --> AA[📱 Usuário recebe resposta]

    AA --> BB{🔄 Nova Interação?}
    BB -->|✅ Sim| A
    BB -->|❌ Não| CC[🏁 Fim da Sessão]

    style A fill:#e1f5fe
    style AA fill:#c8e6c9
    style G fill:#ffcdd2
    style T fill:#fff3e0
    style CC fill:#f3e5f5
```

## 🔍 Fluxo Detalhado por Tipo de Mensagem

### **1. Mensagem de Texto**

```mermaid
flowchart TD
    A[📝 Mensagem de Texto 'menu'] --> B[🔍 UnifiedCommandSystem]
    B --> C[📚 Buscar no Cache]

    C --> D{Cache Hit?}
    D -->|✅ Sim| E[⚡ Retornar do Cache]
    D -->|❌ Não| F[🔍 Buscar no Banco]

    F --> G{Comando Encontrado?}
    G -->|✅ Sim| H[🎯 Resolver Handler]
    G -->|❌ Não| I[💡 Fallback + Sugestões]

    H --> J[🔧 Instanciar Handler TelegramMenuBuilder]
    J --> K[📊 Executar Método showMainMenu]
    K --> L[📱 Retornar Resposta]

    E --> L
    I --> L

    style A fill:#e3f2fd
    style L fill:#c8e6c9
    style I fill:#fff3e0
```

### **2. Mensagem de Voz**

```mermaid
flowchart TD
    A[🎤 Mensagem de Voz] --> B[📥 Download do Arquivo]
    B --> C[🔍 Detectar Formato OGG/Opus/WAV]

    C --> D{Formato Suportado?}
    D -->|✅ Sim| E[🎵 Processar Diretamente]
    D -->|❌ Não| F[🔄 Converter para WAV]

    F --> G[🔧 FFmpeg/PHP Conversion]
    G --> H{Conversão OK?}
    H -->|✅ Sim| I[🎵 Áudio Convertido]
    H -->|❌ Não| J[⚠️ Erro de Conversão]

    E --> K[🎤 Speech-to-Text]
    I --> K
    J --> L[📝 Mensagem de Erro]

    K --> M{Provider Configurado?}
    M -->|✅ Sim| N[🚀 OpenAI Whisper/Vosk]
    M -->|❌ Não| O[⚠️ Provider não configurado]

    N --> P[📝 Texto Reconhecido]
    P --> Q[🔍 Processar como Texto]

    O --> L
    L --> R[📱 Resposta de Erro]

    style A fill:#fce4ec
    style P fill:#c8e6c9
    style L fill:#ffcdd2
```

### **3. Callback Query (Botões)**

```mermaid
flowchart TD
    A[🔘 Usuário clica botão] --> B[📡 Callback Query]
    B --> C[🌐 Webhook com callback_query]
    C --> D[🔒 TelegramWebhookController handleCallbackQuery]

    D --> E[🔍 Extrair callback_data]
    E --> F[🎯 Mapear para Ação]

    F --> G{🎯 Ação Reconhecida?}
    G -->|✅ Sim| H[🚀 Executar Ação]
    G -->|❌ Não| I[⚠️ Ação não encontrada]

    H --> J{📊 Tipo de Ação}

    J -->|📋 Menu| K[📱 Mostrar Novo Menu]
    J -->|📊 Relatório| L[📊 Gerar Relatório]
    J -->|🔧 Sistema| M[🔧 Executar Comando]

    K --> N[⌨️ Criar Teclado]
    L --> O[📊 Buscar Dados]
    M --> P[⚙️ Executar Lógica]

    O --> Q[🎨 Formatar Relatório]
    P --> R[📱 Resposta do Sistema]

    N --> S[📤 Enviar Resposta]
    Q --> S
    R --> S

    I --> T[📱 Resposta de Erro]

    style A fill:#e8f5e8
    style S fill:#c8e6c9
    style T fill:#ffcdd2
```

## 🏗️ Arquitetura dos Componentes

### **Estrutura de Classes**

```mermaid
classDiagram
    class TelegramWebhookController {
        +handle(Request) Response
        +handleCallbackQuery(Request) Response
    }

    class TelegramBotService {
        +processMessage(array) array
        +processCallbackQuery(array) array
        +sendMainMenu(int) array
        +sendReportMenu(int) array
    }

    class UnifiedCommandSystem {
        +findCommand(string) Command
        +executeCommand(Command) array
        +getFallback(string) array
    }

    class TelegramChannel {
        +sendTextMessage(string, string) array
        +sendMessageWithKeyboard(string, string, array) array
        +answerCallbackQuery(string) array
    }

    class TelegramMenuBuilder {
        +showMainMenu() array
        +showServicesMenu() array
        +showReportsMenu() array
    }

    class SpeechToTextService {
        +convertVoiceToText(string) string
        +detectAudioFormat(string) string
        +prepareAudioForOpenAI(string) string
    }

    TelegramWebhookController --> TelegramBotService
    TelegramBotService --> UnifiedCommandSystem
    TelegramBotService --> TelegramChannel
    TelegramBotService --> TelegramMenuBuilder
    TelegramBotService --> SpeechToTextService
    UnifiedCommandSystem --> TelegramChannel
```

## 📊 Fluxo de Dados

### **Sequência de Processamento**

```mermaid
sequenceDiagram
    participant U as 👤 Usuário
    participant T as 📱 Telegram
    participant W as 🌐 Webhook
    participant C as 🔒 Controller
    participant S as 🔧 Service
    participant U as 🎯 UnifiedSystem
    participant H as 🚀 Handler
    participant TC as 📤 Channel
    participant TA as 📡 Telegram API

    U->>T: Envia "menu"
    T->>W: POST /api/telegram/webhook
    W->>C: handle()
    C->>S: processMessage()
    S->>U: findCommand("menu")
    U->>H: executeCommand()
    H->>S: showMainMenu()
    S->>TC: sendMessageWithKeyboard()
    TC->>TA: POST /sendMessage
    TA->>T: Envia resposta
    T->>U: Mostra menu com botões
```

## 🎯 Casos de Uso Específicos

### **1. Comando Simples: "menu"**

```mermaid
flowchart LR
    A[👤 "menu"] --> B[🔍 Parser]
    B --> C[📚 Cache/Banco]
    C --> D[🎯 main_menu]
    D --> E[🚀 TelegramMenuBuilder]
    E --> F[📱 showMainMenu]
    F --> G[⌨️ Teclado + Botões]
    G --> H[📤 TelegramChannel]
    H --> I[📱 Resposta ao Usuário]

    style A fill:#e1f5fe
    style I fill:#c8e6c9
```

### **2. Comando de Voz: "quero ver o menu"**

```mermaid
flowchart LR
    A[🎤 Voz] --> B[🎵 Speech-to-Text]
    B --> C[📝 "quero ver o menu"]
    C --> D[🔍 Parser Natural]
    D --> E[🎯 main_menu]
    E --> F[🚀 TelegramMenuBuilder]
    F --> G[📱 showMainMenu]
    G --> H[⌨️ Teclado + Botões]
    H --> I[📤 TelegramChannel]
    I --> J[📱 Resposta ao Usuário]

    style A fill:#fce4ec
    style J fill:#c8e6c9
```

### **3. Callback Query: Botão "📊 Relatórios"**

```mermaid
flowchart LR
    A[🔘 Clique] --> B[📡 Callback Query]
    B --> C[🔍 callback_data]
    C --> D[🎯 reports_menu]
    D --> E[🚀 TelegramBotService]
    E --> F[📱 sendReportMenu]
    F --> G[⌨️ Teclado Relatórios]
    G --> H[📤 TelegramChannel]
    H --> I[📱 Novo Menu]

    style A fill:#e8f5e8
    style I fill:#c8e6c9
```

## 🔧 Componentes Técnicos

### **Arquivos Principais**

| Componente         | Arquivo                         | Responsabilidade             |
| ------------------ | ------------------------------- | ---------------------------- |
| **Controller**     | `TelegramWebhookController.php` | Recebe webhooks e roteia     |
| **Service**        | `TelegramBotService.php`        | Lógica de negócio principal  |
| **Command System** | `UnifiedCommandSystem.php`      | Processamento de comandos    |
| **Channel**        | `TelegramChannel.php`           | Comunicação com Telegram API |
| **Menu Builder**   | `TelegramMenuBuilder.php`       | Construção de menus          |
| **Speech Service** | `SpeechToTextService.php`       | Conversão de voz para texto  |

### **Configurações Importantes**

```env
# Telegram Configuration
TELEGRAM_ENABLED=true
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_RECIPIENTS=123456789,987654321

# Speech-to-Text
SPEECH_PROVIDER=vosk
VOSK_MODEL_PATH=/path/to/model

# Cache
TELEGRAM_COMMANDS_CACHE_ENABLED=true
TELEGRAM_COMMANDS_CACHE_TTL=1800
```

## 🚀 Como Testar o Fluxo

### **1. Teste Manual**

```bash
# 1. Envie mensagem para o bot
# 2. Verifique logs
tail -f storage/logs/laravel.log | grep "Telegram"

# 3. Teste comandos específicos
php artisan telegram:menu-test --menu=main
```

### **2. Teste de Comandos**

```bash
# Testar sistema de comandos
php artisan telegram:unified-commands list
php artisan telegram:unified-commands stats

# Testar speech-to-text
php artisan telegram:setup-speech --test
```

### **3. Monitoramento**

```bash
# Ver logs em tempo real
tail -f storage/logs/telegram-webhook.log

# Ver métricas
php artisan telegram:debug --get-updates
```

## 🔍 Troubleshooting

### **Problemas Comuns**

| Problema                    | Causa                    | Solução                                        |
| --------------------------- | ------------------------ | ---------------------------------------------- |
| **Webhook não recebido**    | URL incorreta            | `php artisan telegram:bot-setup --set-webhook` |
| **Comando não reconhecido** | Cache desatualizado      | `php artisan cache:clear`                      |
| **Voz não funciona**        | Provider não configurado | Configurar Vosk/OpenAI                         |
| **Botões não respondem**    | Callback mal configurado | Verificar `callback_data`                      |

### **Logs Importantes**

```bash
# Logs de webhook
grep "webhook" storage/logs/laravel.log

# Logs de comandos
grep "command" storage/logs/laravel.log

# Logs de voz
grep "speech" storage/logs/laravel.log
```

## 📈 Métricas e Monitoramento

### **KPIs Importantes**

- **Tempo de resposta** - < 2 segundos
- **Taxa de sucesso** - > 95%
- **Cache hit rate** - > 80%
- **Uptime** - > 99.9%

### **Alertas Recomendados**

- Webhook não recebido por > 5 minutos
- Taxa de erro > 5%
- Tempo de resposta > 5 segundos
- Cache miss rate > 30%

---

## 🎯 Resumo Executivo

O fluxo de processamento de mensagens do Telegram segue uma arquitetura modular e escalável:

1. **📥 Recepção** - Webhook recebe mensagens do Telegram
2. **🔍 Validação** - Request é validado e autenticado
3. **🎯 Processamento** - Sistema unificado processa comandos
4. **🚀 Execução** - Handlers específicos executam ações
5. **📤 Resposta** - Resposta formatada é enviada via API
6. **📱 Entrega** - Usuário recebe resposta no Telegram

**O sistema é robusto, rápido e oferece uma experiência de usuário excepcional!** 🚀✨

---

**📖 Para informações técnicas detalhadas, consulte:**

- `TELEGRAM_BOT_REPORTS.md` - Funcionalidades do bot
- `TELEGRAM_INTERACTIVE_MENUS.md` - Sistema de menus
- `TELEGRAM_VOICE_COMMANDS_IMPLEMENTATION.md` - Comandos de voz
- `UNIFIED_COMMAND_SYSTEM_USAGE.md` - Sistema unificado de comandos
