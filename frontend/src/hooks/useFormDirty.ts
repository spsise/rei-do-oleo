import { useCallback, useEffect, useMemo, useRef, useState } from 'react';

interface UseFormDirtyOptions<T> {
  initialData: T;
  onDirtyChange?: (isDirty: boolean, changedFields: (keyof T)[]) => void;
  excludeFields?: (keyof T)[];
  debounceMs?: number;
  deepCompare?: boolean;
}

interface UseFormDirtyReturn<T> {
  isDirty: boolean;
  changedFields: (keyof T)[];
  currentData: T;
  updateData: (newData: T) => void;
  updateField: (field: keyof T, value: unknown) => void;
  reset: () => void;
  getChangedData: () => Partial<T>;
}

/**
 * Hook otimizado para detectar mudanças em formulários
 * Permite controle granular de campos e performance otimizada
 */
export function useFormDirty<T extends Record<string, unknown>>({
  initialData,
  onDirtyChange,
  excludeFields = [],
  debounceMs = 0,
  deepCompare = false,
}: UseFormDirtyOptions<T>): UseFormDirtyReturn<T> {
  const [currentData, setCurrentData] = useState<T>(initialData);
  const [isDirty, setIsDirty] = useState(false);
  const [changedFields, setChangedFields] = useState<(keyof T)[]>([]);
  const debounceTimeoutRef = useRef<NodeJS.Timeout | null>(null);

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

  // Função para detectar mudanças
  const detectChanges = useCallback(
    (newData: T) => {
      const changes: (keyof T)[] = [];

      Object.keys(newData).forEach((key) => {
        const fieldKey = key as keyof T;

        // Pular campos excluídos
        if (excludeFields.includes(fieldKey)) {
          return;
        }

        const initialValue = initialData[fieldKey];
        const currentValue = newData[fieldKey];

        // Comparar valores
        if (!compareValues(initialValue, currentValue)) {
          changes.push(fieldKey);
        }
      });

      const hasChanges = changes.length > 0;

      setChangedFields(changes);
      setIsDirty(hasChanges);

      // Callback opcional
      onDirtyChange?.(hasChanges, changes);
    },
    [initialData, excludeFields, compareValues, onDirtyChange]
  );

  // Função para atualizar dados com debounce
  const updateDataWithDebounce = useCallback(
    (newData: T) => {
      setCurrentData(newData);

      if (debounceMs > 0) {
        if (debounceTimeoutRef.current) {
          clearTimeout(debounceTimeoutRef.current);
        }

        debounceTimeoutRef.current = setTimeout(() => {
          detectChanges(newData);
        }, debounceMs);
      } else {
        detectChanges(newData);
      }
    },
    [detectChanges, debounceMs]
  );

  // Função para atualizar um campo específico
  const updateField = useCallback(
    (field: keyof T, value: unknown) => {
      const newData = { ...currentData, [field]: value };
      updateDataWithDebounce(newData);
    },
    [currentData, updateDataWithDebounce]
  );

  // Função para atualizar todos os dados
  const updateData = useCallback(
    (newData: T) => {
      updateDataWithDebounce(newData);
    },
    [updateDataWithDebounce]
  );

  // Função para resetar o formulário
  const reset = useCallback(() => {
    setCurrentData(initialData);
    setIsDirty(false);
    setChangedFields([]);

    if (debounceTimeoutRef.current) {
      clearTimeout(debounceTimeoutRef.current);
    }

    onDirtyChange?.(false, []);
  }, [initialData, onDirtyChange]);

  // Função para obter apenas os dados que mudaram
  const getChangedData = useCallback((): Partial<T> => {
    const changedData: Partial<T> = {};

    changedFields.forEach((field) => {
      changedData[field] = currentData[field];
    });

    return changedData;
  }, [changedFields, currentData]);

  // Cleanup do debounce no unmount
  useEffect(() => {
    return () => {
      if (debounceTimeoutRef.current) {
        clearTimeout(debounceTimeoutRef.current);
      }
    };
  }, []);

  // Detectar mudanças iniciais quando initialData muda
  useEffect(() => {
    detectChanges(currentData);
  }, [initialData, detectChanges]);

  return {
    isDirty,
    changedFields,
    currentData,
    updateData,
    updateField,
    reset,
    getChangedData,
  };
}

/**
 * Hook específico para serviços com configurações otimizadas
 */
export function useServiceFormDirty(
  initialService: Record<string, unknown>,
  onDirtyChange?: (isDirty: boolean, changedFields: string[]) => void
) {
  const excludeFields = useMemo(
    () => [
      'id',
      'created_at',
      'updated_at',
      'deleted_at',
      'service_center',
      'client',
      'vehicle',
      'technician',
      'attendant',
      'status',
      'payment_method',
      'financial',
    ],
    []
  );

  return useFormDirty({
    initialData: initialService,
    onDirtyChange,
    excludeFields,
    debounceMs: 300, // Debounce para campos de texto
    deepCompare: true,
  });
}

/**
 * Hook para detectar mudanças em arrays (como itens de serviço)
 */
export function useArrayFormDirty<T>(
  initialArray: T[],
  onDirtyChange?: (isDirty: boolean) => void
) {
  const [currentArray, setCurrentArray] = useState<T[]>(initialArray);
  const [isDirty, setIsDirty] = useState(false);

  const detectChanges = useCallback(
    (newArray: T[]) => {
      const hasChanges =
        JSON.stringify(newArray) !== JSON.stringify(initialArray);
      setIsDirty(hasChanges);
      onDirtyChange?.(hasChanges);
    },
    [initialArray, onDirtyChange]
  );

  const updateArray = useCallback(
    (newArray: T[]) => {
      setCurrentArray(newArray);
      detectChanges(newArray);
    },
    [detectChanges]
  );

  const reset = useCallback(() => {
    setCurrentArray(initialArray);
    setIsDirty(false);
    onDirtyChange?.(false);
  }, [initialArray, onDirtyChange]);

  return {
    isDirty,
    currentArray,
    updateArray,
    reset,
  };
}
