# 🎯 Sistema de Detecção de Mudanças em Formulários

## 📋 Visão Geral

Este sistema permite detectar automaticamente mudanças em formulários e só habilitar o botão de salvar quando há alterações reais. Isso melhora significativamente a experiência do usuário e previne operações desnecessárias.

## 🚀 Funcionalidades Principais

### ✅ **Detecção Automática de Mudanças**

- Comparação inteligente entre dados originais e atuais
- Suporte a comparação profunda (deep compare)
- Exclusão de campos específicos da comparação
- Debounce para campos de texto

### ✅ **Botões Inteligentes**

- Botão só fica habilitado quando há mudanças
- Estados visuais claros (habilitado/desabilitado/loading)
- Indicadores visuais de mudanças
- Tooltips informativos

### ✅ **Navegação Segura**

- Avisos antes de sair da página com mudanças não salvas
- Confirmação para descartar alterações
- Integração com React Router

### ✅ **Indicadores Visuais**

- Badges mostrando campos alterados
- Contadores de mudanças
- Alertas de mudanças não salvas
- Estados visuais diferenciados

## 🛠️ Componentes Principais

### 1. **Hook `useFormDirty`**

Hook principal para detectar mudanças em formulários.

```typescript
import { useFormDirty } from '../hooks/useFormDirty';

const {
  isDirty,
  changedFields,
  currentData,
  updateData,
  updateField,
  reset,
  getChangedData,
} = useFormDirty({
  initialData: originalData,
  onDirtyChange: (isDirty, changedFields) => {
    console.log('Mudanças detectadas:', isDirty, changedFields);
  },
  excludeFields: ['id', 'created_at', 'updated_at'],
  debounceMs: 300,
  deepCompare: true,
});
```

**Parâmetros:**

- `initialData`: Dados originais para comparação
- `onDirtyChange`: Callback quando mudanças são detectadas
- `excludeFields`: Campos a serem ignorados na comparação
- `debounceMs`: Tempo de debounce para campos de texto
- `deepCompare`: Se deve fazer comparação profunda de objetos

**Retorna:**

- `isDirty`: Se há mudanças detectadas
- `changedFields`: Array com nomes dos campos alterados
- `currentData`: Dados atuais do formulário
- `updateData`: Função para atualizar todos os dados
- `updateField`: Função para atualizar um campo específico
- `reset`: Função para resetar o formulário
- `getChangedData`: Função para obter apenas dados alterados

### 2. **Componente `SmartButton`**

Botão que só fica habilitado quando há mudanças.

```typescript
import { SmartButton } from '../components/ui/SmartButton';

<SmartButton
  isDirty={isDirty}
  isSubmitting={loading}
  onClick={handleSave}
  variant="primary"
  size="md"
  showChangesIndicator={true}
  changedFieldsCount={changedFields.length}
>
  Salvar Serviço
</SmartButton>
```

**Props:**

- `isDirty`: Se há mudanças detectadas
- `isSubmitting`: Se está salvando
- `variant`: Variante do botão (primary, secondary, danger, success)
- `size`: Tamanho do botão (sm, md, lg)
- `showChangesIndicator`: Se deve mostrar indicador de mudanças
- `changedFieldsCount`: Número de campos alterados

### 3. **Componente `SmartButtonGroup`**

Grupo de botões para formulários com ações comuns.

```typescript
import { SmartButtonGroup } from '../components/ui/SmartButton';

<SmartButtonGroup
  isDirty={isDirty}
  isSubmitting={loading}
  onSave={handleSave}
  onCancel={handleCancel}
  onReset={handleReset}
  saveText="Salvar Serviço"
  cancelText="Cancelar"
  resetText="Descartar"
  showReset={isDirty}
/>
```

### 4. **Componente `ChangesIndicator`**

Indicador visual de mudanças no formulário.

```typescript
import { ChangesIndicator } from '../components/ui/ChangesIndicator';

<ChangesIndicator
  isDirty={isDirty}
  changedFields={changedFields}
  changedFieldsCount={changedFields.length}
  variant="detailed"
  showDetails={true}
/>
```

**Variantes:**

- `compact`: Indicador compacto
- `detailed`: Indicador detalhado com lista de campos
- `minimal`: Indicador mínimo apenas com ícone

### 5. **Hook `useUnsavedChanges`**

Hook para gerenciar navegação com mudanças não salvas.

```typescript
import { useUnsavedChanges } from '../hooks/useUnsavedChanges';

const { navigateWithConfirmation, discardChanges } = useUnsavedChanges({
  isDirty,
  onSave: handleSave,
  onDiscard: reset,
  message: 'Você tem alterações não salvas. Deseja salvar antes de sair?',
});
```

## 📝 Exemplo de Implementação Completa

### 1. **Página de Edição**

```typescript
import React, { useState } from 'react';
import { useServiceFormDirty } from '../hooks/useFormDirty';
import { useUnsavedChanges } from '../hooks/useUnsavedChanges';
import { SmartButtonGroup } from '../components/ui/SmartButton';
import { ChangesIndicator } from '../components/ui/ChangesIndicator';

export const ServiceEditPage: React.FC = () => {
  const [service, setService] = useState(originalService);
  const [loading, setLoading] = useState(false);

  // Hook para detectar mudanças
  const {
    isDirty,
    changedFields,
    currentData,
    updateData,
    reset,
  } = useServiceFormDirty(
    service as Record<string, any>,
    (isDirty, changedFields) => {
      console.log('Mudanças detectadas:', isDirty, changedFields);
    }
  );

  // Hook para navegação segura
  const { navigateWithConfirmation } = useUnsavedChanges({
    isDirty,
    onSave: handleSave,
    onDiscard: reset,
  });

  const handleSave = async () => {
    setLoading(true);
    try {
      await apiService.update(service.id, currentData);
      reset();
    } catch (error) {
      console.error('Erro ao salvar:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div>
      {/* Header com indicador de mudanças */}
      <div className="flex justify-between items-center">
        <h1>Editar Serviço</h1>

        <div className="flex items-center gap-4">
          <ChangesIndicator
            isDirty={isDirty}
            changedFields={changedFields}
            variant="compact"
          />

          <SmartButtonGroup
            isDirty={isDirty}
            isSubmitting={loading}
            onSave={handleSave}
            onCancel={() => navigateWithConfirmation('/services')}
            onReset={reset}
            saveText="Salvar Serviço"
          />
        </div>
      </div>

      {/* Formulário */}
      <ServiceForm
        service={service}
        onSubmit={handleSave}
        loading={loading}
      />
    </div>
  );
};
```

### 2. **Formulário com Detecção de Mudanças**

```typescript
export const ServiceForm: React.FC<ServiceFormProps> = ({
  service,
  onSubmit,
  loading,
}) => {
  const [formData, setFormData] = useState(service || {});

  // Hook para detectar mudanças
  const {
    isDirty,
    changedFields,
    updateField,
    reset,
  } = useServiceFormDirty(
    service || {},
    (isDirty, changedFields) => {
      console.log('Mudanças no formulário:', isDirty, changedFields);
    }
  );

  const handleInputChange = (field: string, value: any) => {
    // Atualizar dados do hook
    updateField(field, value);

    // Atualizar estado local (para compatibilidade)
    setFormData(prev => ({ ...prev, [field]: value }));
  };

  return (
    <form>
      {/* Campos do formulário */}
      <input
        value={formData.name}
        onChange={(e) => handleInputChange('name', e.target.value)}
      />

      {/* Botões com detecção de mudanças */}
      <SmartButtonGroup
        isDirty={isDirty}
        isSubmitting={loading}
        onSave={() => onSubmit(formData)}
        onReset={reset}
      />
    </form>
  );
};
```

## 🎨 Personalização

### **Estilos Customizados**

```typescript
// Botão customizado
<SmartButton
  isDirty={isDirty}
  className="custom-button-class"
  variant="primary"
  size="lg"
>
  Salvar
</SmartButton>

// Indicador customizado
<ChangesIndicator
  isDirty={isDirty}
  variant="detailed"
  className="custom-indicator-class"
/>
```

### **Configurações Avançadas**

```typescript
// Hook com configurações avançadas
const formDirty = useFormDirty({
  initialData: originalData,
  excludeFields: ['id', 'created_at', 'updated_at', 'metadata'],
  debounceMs: 500, // Debounce de 500ms
  deepCompare: true, // Comparação profunda
  onDirtyChange: (isDirty, changedFields) => {
    // Callback personalizado
    if (isDirty) {
      analytics.track('form_changes_detected', { fields: changedFields });
    }
  },
});
```

## 🔧 Hooks Específicos

### **`useServiceFormDirty`**

Hook específico para formulários de serviço com configurações otimizadas.

```typescript
const { isDirty, changedFields, currentData, updateData, reset } =
  useServiceFormDirty(service, (isDirty, changedFields) => {
    console.log('Mudanças no serviço:', isDirty, changedFields);
  });
```

### **`useArrayFormDirty`**

Hook para detectar mudanças em arrays (como itens de serviço).

```typescript
const { isDirty, currentArray, updateArray, reset } = useArrayFormDirty(
  originalItems,
  (isDirty) => {
    console.log('Mudanças nos itens:', isDirty);
  }
);
```

## 🚨 Boas Práticas

### **1. Performance**

- Use `excludeFields` para ignorar campos desnecessários
- Configure `debounceMs` para campos de texto
- Use `deepCompare: false` quando possível

### **2. UX**

- Sempre mostre feedback visual claro
- Use tooltips para explicar estados
- Forneça opção de descartar mudanças

### **3. Manutenção**

- Mantenha hooks reutilizáveis
- Use TypeScript para type safety
- Documente configurações específicas

### **4. Testes**

- Teste diferentes cenários de mudanças
- Verifique comportamento com navegação
- Teste performance com formulários grandes

## 📊 Métricas e Analytics

```typescript
// Exemplo de tracking de mudanças
const formDirty = useFormDirty({
  initialData,
  onDirtyChange: (isDirty, changedFields) => {
    if (isDirty) {
      analytics.track('form_changes_detected', {
        form_type: 'service_edit',
        changed_fields: changedFields,
        total_changes: changedFields.length,
      });
    }
  },
});
```

## 🔍 Troubleshooting

### **Problema: Mudanças não são detectadas**

- Verifique se `initialData` está correto
- Confirme se `excludeFields` não está excluindo campos necessários
- Use `deepCompare: true` para objetos aninhados

### **Problema: Performance lenta**

- Reduza `debounceMs` ou defina como 0
- Use `excludeFields` para ignorar campos desnecessários
- Considere usar `deepCompare: false`

### **Problema: Navegação não funciona**

- Verifique se `useUnsavedChanges` está configurado corretamente
- Confirme se `isDirty` está sendo passado corretamente
- Teste se `onSave` e `onDiscard` estão funcionando

## 🎯 Conclusão

Este sistema fornece uma solução completa e reutilizável para detecção de mudanças em formulários, melhorando significativamente a experiência do usuário e prevenindo operações desnecessárias. A implementação é modular, performática e fácil de manter.
