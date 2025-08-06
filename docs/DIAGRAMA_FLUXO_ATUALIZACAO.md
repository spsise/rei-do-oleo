# 🔄 Diagrama do Fluxo de Atualização de Serviço

## 📊 Fluxo Completo

```mermaid
sequenceDiagram
    participant U as Usuário
    participant F as Frontend (React)
    participant B as Backend (Laravel)
    participant DB as Database (MySQL)
    participant C as Cache (Redis)

    Note over U,DB: 1. INICIALIZAÇÃO
    U->>F: Abre modal de edição
    F->>F: Carrega dados do serviço
    F->>F: Inicializa estado local
    F->>F: Calcula totais iniciais

    Note over U,DB: 2. MANIPULAÇÃO DE DADOS
    U->>F: Altera quantidade/preço
    F->>F: Atualiza estado local
    F->>F: Recalcula totais
    F->>F: Atualiza interface

    Note over U,DB: 3. SUBMISSÃO - ETAPA 1
    U->>F: Clica "Atualizar"
    F->>F: Valida dados
    F->>F: Separa dados do serviço dos itens

    F->>B: PUT /api/services/{id}
    Note right of F: Dados do serviço
    B->>B: Valida entrada
    B->>DB: Atualiza serviço
    DB->>B: Confirma atualização
    B->>F: Retorna serviço atualizado

    Note over U,DB: 4. SUBMISSÃO - ETAPA 2
    F->>F: Aguarda 200ms
    F->>B: PUT /api/service-items/{id}/bulk-update
    Note right of F: Dados dos itens
    B->>B: Valida itens
    B->>DB: Remove itens antigos
    B->>DB: Insere novos itens
    B->>DB: Recalcula totais
    DB->>B: Confirma operação
    B->>F: Retorna itens atualizados

    Note over U,DB: 5. ATUALIZAÇÃO DE CACHE
    F->>F: Invalida cache local
    F->>C: Limpa cache do serviço
    F->>F: Atualiza interface
    F->>U: Exibe sucesso
```

## 🏗️ Arquitetura de Componentes

```mermaid
graph TB
    subgraph "Frontend (React)"
        A[TechnicianPage] --> B[EditServiceModal]
        B --> C[ServiceDetailsTab]
        B --> D[ServiceProductsTab]
        B --> E[ModalFooter]

        C --> F[handleServiceDataChange]
        D --> G[handleAddProduct]
        D --> H[handleUpdateProductQuantity]
        D --> I[handleUpdateProductPrice]
        D --> J[handleRemoveProduct]

        E --> K[handleSubmit]
        K --> L[handleEditServiceSubmit]
    end

    subgraph "Hooks & Services"
        L --> M[useUpdateService]
        L --> N[useUpdateServiceItems]
        M --> O[serviceService.updateService]
        N --> P[serviceItemService.updateServiceItems]
    end

    subgraph "Backend (Laravel)"
        O --> Q[ServiceController]
        P --> R[ServiceItemController]

        Q --> S[UpdateServiceRequest]
        Q --> T[ServiceService]
        R --> U[BulkUpdateServiceItemsRequest]
        R --> V[ServiceItemService]

        T --> W[ServiceRepository]
        V --> X[ServiceItemRepository]

        W --> Y[Service Model]
        X --> Z[ServiceItem Model]
    end

    subgraph "Database"
        Y --> AA[services table]
        Z --> BB[service_items table]
        Z --> CC[products table]
    end
```

## 📊 Fluxo de Dados

```mermaid
flowchart LR
    subgraph "Estado Inicial"
        A1[Serviço Original]
        A2[Itens Originais]
        A3[Totais Originais]
    end

    subgraph "Manipulação Frontend"
        B1[EditServiceModal]
        B2[Estado Local]
        B3[Cálculos em Tempo Real]
    end

    subgraph "Dados Enviados"
        C1[Dados do Serviço]
        C2[Dados dos Itens]
        C3[Totais Calculados]
    end

    subgraph "Processamento Backend"
        D1[Validação]
        D2[Atualização Serviço]
        D3[Atualização Itens]
        D4[Recálculo Totais]
    end

    subgraph "Resultado Final"
        E1[Serviço Atualizado]
        E2[Itens Atualizados]
        E3[Totais Corrigidos]
    end

    A1 --> B1
    A2 --> B1
    A3 --> B1

    B1 --> B2
    B2 --> B3

    B3 --> C1
    B3 --> C2
    B3 --> C3

    C1 --> D1
    C2 --> D1
    C3 --> D1

    D1 --> D2
    D1 --> D3
    D2 --> D4
    D3 --> D4

    D4 --> E1
    D4 --> E2
    D4 --> E3
```

## 🔄 Estados e Transições

```mermaid
stateDiagram-v2
    [*] --> Carregando
    Carregando --> Editando
    Editando --> Salvando
    Salvando --> Sucesso
    Salvando --> Erro
    Erro --> Editando
    Sucesso --> [*]

    state Editando {
        [*] --> DadosCarregados
        DadosCarregados --> AlterandoProdutos
        AlterandoProdutos --> CalculandoTotais
        CalculandoTotais --> DadosCarregados
        DadosCarregados --> ValidandoDados
        ValidandoDados --> DadosCarregados
    }

    state Salvando {
        [*] --> AtualizandoServico
        AtualizandoServico --> Aguardando
        Aguardando --> AtualizandoItens
        AtualizandoItens --> InvalidandoCache
        InvalidandoCache --> [*]
    }
```

## 📋 Campos e Validações

```mermaid
graph TD
    subgraph "Campos do Serviço"
        A1[vehicle_id] --> A2[integer]
        A3[description] --> A4[string]
        A5[estimated_duration] --> A6[integer]
        A7[scheduled_at] --> A8[datetime]
        A9[mileage_at_service] --> A10[integer]
        A11[observations] --> A12[text]
        A13[internal_notes] --> A14[text]
        A15[discount] --> A16[decimal]
        A17[total_amount] --> A18[decimal]
        A19[final_amount] --> A20[decimal]
    end

    subgraph "Campos dos Itens"
        B1[product_id] --> B2[integer]
        B3[quantity] --> B4[integer]
        B5[unit_price] --> B6[decimal]
        B7[discount] --> B8[decimal]
        B9[notes] --> B10[text]
    end

    subgraph "Validações"
        C1[Campos Obrigatórios]
        C2[Tipos de Dados]
        C3[Regras de Negócio]
        C4[Integridade Referencial]
    end

    A1 --> C1
    A3 --> C1
    B1 --> C1
    B3 --> C1
    B5 --> C1

    A2 --> C2
    A4 --> C2
    B2 --> C2
    B4 --> C2

    A6 --> C3
    A10 --> C3
    B4 --> C3
    B6 --> C3

    A1 --> C4
    B1 --> C4
```

## ⚠️ Tratamento de Erros

```mermaid
flowchart TD
    A[Erro Detectado] --> B{Tipo de Erro}

    B -->|Validação| C[Erro de Validação]
    B -->|Serviço não encontrado| D[Erro 404]
    B -->|Produto não encontrado| E[Erro de Produto]
    B -->|Erro de banco| F[Erro 500]
    B -->|Timeout| G[Erro de Timeout]

    C --> H[Exibir erros de validação]
    D --> I[Serviço não encontrado]
    E --> J[Produto não disponível]
    F --> K[Erro interno do servidor]
    G --> L[Tentar novamente]

    H --> M[Permitir correção]
    I --> N[Recarregar página]
    J --> O[Atualizar lista de produtos]
    K --> P[Log do erro]
    L --> Q[Retry automático]

    M --> R[Continuar edição]
    N --> S[Voltar à lista]
    O --> T[Recarregar produtos]
    P --> U[Notificar admin]
    Q --> V[Reenviar requisição]
```

## 🔄 Cache e Sincronização

```mermaid
graph LR
    subgraph "Cache Frontend"
        A1[React Query Cache]
        A2[Estado Local]
        A3[Component State]
    end

    subgraph "Cache Backend"
        B1[Redis Cache]
        B2[Database Cache]
        B3[Query Cache]
    end

    subgraph "Invalidação"
        C1[Invalidar Serviço]
        C2[Invalidar Itens]
        C3[Invalidar Busca]
        C4[Limpar Cache]
    end

    A1 --> C1
    A2 --> C2
    A3 --> C3

    C1 --> B1
    C2 --> B2
    C3 --> B3
    C4 --> B1
    C4 --> B2
    C4 --> B3
```

---

## 📝 Legenda

### **Símbolos**

- 🔄 **Fluxo de dados**: Movimento de informações entre camadas
- ⚛️ **Componente React**: Interface do usuário
- 🐘 **Serviço Laravel**: Lógica de negócio
- 🗄️ **Database**: Armazenamento persistente
- 💾 **Cache**: Armazenamento temporário
- ⚠️ **Erro**: Tratamento de exceções
- ✅ **Sucesso**: Operação concluída

### **Cores**

- 🔵 **Azul**: Frontend/Interface
- 🟢 **Verde**: Backend/Serviços
- 🟡 **Amarelo**: Database/Cache
- 🔴 **Vermelho**: Erros/Problemas
- 🟢 **Verde**: Sucesso/Conclusão

---

**📖 Este diagrama complementa a documentação técnica do fluxo de atualização de serviços.**
