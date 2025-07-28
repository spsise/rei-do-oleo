import { useCallback, useMemo } from 'react';

interface UseFormChangesOptions<T> {
  initialData: T;
  currentData: T;
  excludeFields?: (keyof T)[];
  deepCompare?: boolean;
}

interface UseFormChangesReturn<T> {
  hasChanges: boolean;
  changedFields: (keyof T)[];
  resetChanges: () => void;
  getChangedData: () => Partial<T>;
}

/**
 * Hook para detectar mudanças em formulários
 * Só permite atualização quando há mudanças reais nos dados
 */
export function useFormChanges<T extends Record<string, unknown>>({
  initialData,
  currentData,
  excludeFields = [],
  deepCompare = false,
}: UseFormChangesOptions<T>): UseFormChangesReturn<T> {
  // Função para comparar valores
  const compareValues = useCallback(
    (value1: unknown, value2: unknown): boolean => {
      if (
        deepCompare &&
        typeof value1 === 'object' &&
        typeof value2 === 'object'
      ) {
        return JSON.stringify(value1) === JSON.stringify(value2);
      }
      return value1 === value2;
    },
    [deepCompare]
  );

  // Detectar mudanças usando useMemo para evitar recálculos desnecessários
  const { changedFields, hasChanges } = useMemo(() => {
    const changes: (keyof T)[] = [];

    Object.keys(currentData).forEach((key) => {
      const fieldKey = key as keyof T;

      // Pular campos excluídos
      if (excludeFields.includes(fieldKey)) {
        return;
      }

      const initialValue = initialData[fieldKey];
      const currentValue = currentData[fieldKey];

      // Comparar valores
      if (!compareValues(initialValue, currentValue)) {
        changes.push(fieldKey);
      }
    });

    return {
      changedFields: changes,
      hasChanges: changes.length > 0,
    };
  }, [initialData, currentData, excludeFields, compareValues]);

  // Função para resetar mudanças (não faz nada, apenas para compatibilidade)
  const resetChanges = useCallback(() => {
    // Não faz nada, pois os valores são calculados automaticamente
    console.warn(
      'resetChanges called but not implemented - changes are calculated automatically'
    );
  }, []);

  // Função para obter apenas os dados que mudaram
  const getChangedData = useCallback((): Partial<T> => {
    const changedData: Partial<T> = {};

    changedFields.forEach((field) => {
      changedData[field] = currentData[field];
    });

    return changedData;
  }, [changedFields, currentData]);

  return {
    hasChanges,
    changedFields,
    resetChanges,
    getChangedData,
  };
}

/**
 * Hook específico para serviços
 */
export function useServiceFormChanges(
  originalService: Record<string, unknown>,
  currentFormData: Record<string, unknown>
) {
  // Memoizar o array de campos excluídos para evitar recriação
  const excludeFields = useMemo(
    () => ['id', 'created_at', 'updated_at', 'deleted_at'],
    []
  );

  return useFormChanges({
    initialData: originalService,
    currentData: currentFormData,
    excludeFields,
    deepCompare: true,
  });
}

/**
 * Hook específico para itens de serviço
 */
export function useServiceItemsChanges(
  originalItems: Record<string, unknown>[],
  currentItems: Record<string, unknown>[]
) {
  // Usar useMemo para evitar recálculos desnecessários
  const hasChanges = useMemo(() => {
    const originalItemsStr = JSON.stringify(
      originalItems.map((item: Record<string, unknown>) => ({
        product_id: (item as Record<string, unknown>).product_id,
        quantity: (item as Record<string, unknown>).quantity,
        unit_price: (item as Record<string, unknown>).unit_price,
        notes: (item as Record<string, unknown>).notes || '',
      }))
    );

    const currentItemsStr = JSON.stringify(
      currentItems.map((item: Record<string, unknown>) => ({
        product_id: (item as Record<string, unknown>).product_id,
        quantity: (item as Record<string, unknown>).quantity,
        unit_price: (item as Record<string, unknown>).unit_price,
        notes: (item as Record<string, unknown>).notes || '',
      }))
    );

    return originalItemsStr !== currentItemsStr;
  }, [originalItems, currentItems]);

  return {
    hasChanges,
    resetChanges: () => {
      // Não faz nada, apenas para compatibilidade
      console.warn(
        'resetChanges called but not implemented - changes are calculated automatically'
      );
    },
  };
}
