import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useCallback, useState } from 'react';
import { toast } from 'react-hot-toast';
import { attendantServiceAPI } from '../services/attendantService';
import {
  type CreateCompleteServiceData,
  type CreateQuickServiceData,
  type LoadingStates,
  type ServiceFormData,
  type ServiceTemplateFilters,
  type ServiceValidationData,
} from '../types/attendant';

// Error type for API responses
interface ApiError {
  response?: {
    data?: {
      message?: string;
    };
  };
  message?: string;
}

// Validation result type
interface ValidationResult {
  isValid?: boolean;
  errors?: string[];
  suggestions?: string[];
  [key: string]: unknown;
}

export const useAttendantServices = () => {
  const queryClient = useQueryClient();
  const [loadingStates, setLoadingStates] = useState<LoadingStates>({
    creating: false,
    validating: false,
    loadingTemplates: false,
    loadingSuggestions: false,
    loadingStats: false,
  });

  // Templates
  const {
    data: templates = [],
    isLoading: isLoadingTemplates,
    error: templatesError,
    refetch: refetchTemplates,
  } = useQuery({
    queryKey: ['attendant', 'templates'],
    queryFn: () => attendantServiceAPI.getTemplates(),
    staleTime: 5 * 60 * 1000, // 5 minutes
  });

  // Estatísticas
  const {
    data: stats,
    isLoading: isLoadingStats,
    error: statsError,
    refetch: refetchStats,
  } = useQuery({
    queryKey: ['attendant', 'stats'],
    queryFn: () => attendantServiceAPI.getQuickStats(),
    staleTime: 2 * 60 * 1000, // 2 minutes
  });

  // Mutations
  const createQuickServiceMutation = useMutation({
    mutationFn: attendantServiceAPI.createQuickService,
    onSuccess: () => {
      toast.success('Serviço criado com sucesso!');
      queryClient.invalidateQueries({ queryKey: ['attendant', 'services'] });
      queryClient.invalidateQueries({ queryKey: ['attendant', 'stats'] });
    },
    onError: (error: ApiError) => {
      toast.error(error.response?.data?.message || 'Erro ao criar serviço');
    },
  });

  const createCompleteServiceMutation = useMutation({
    mutationFn: attendantServiceAPI.createCompleteService,
    onSuccess: () => {
      toast.success('Serviço completo criado com sucesso!');
      queryClient.invalidateQueries({ queryKey: ['attendant', 'services'] });
      queryClient.invalidateQueries({ queryKey: ['attendant', 'stats'] });
    },
    onError: (error: ApiError) => {
      toast.error(
        error.response?.data?.message || 'Erro ao criar serviço completo'
      );
    },
  });

  // Funções de criação
  const createQuickService = useCallback(
    async (data: CreateQuickServiceData) => {
      setLoadingStates((prev) => ({ ...prev, creating: true }));
      try {
        const result = await createQuickServiceMutation.mutateAsync(data);
        return result;
      } finally {
        setLoadingStates((prev) => ({ ...prev, creating: false }));
      }
    },
    [createQuickServiceMutation]
  );

  const createCompleteService = useCallback(
    async (data: CreateCompleteServiceData) => {
      setLoadingStates((prev) => ({ ...prev, creating: true }));
      try {
        const result = await createCompleteServiceMutation.mutateAsync(data);
        return result;
      } finally {
        setLoadingStates((prev) => ({ ...prev, creating: false }));
      }
    },
    [createCompleteServiceMutation]
  );

  // Função para buscar templates com filtros
  const getTemplatesWithFilters = useCallback(
    async (filters: ServiceTemplateFilters) => {
      setLoadingStates((prev) => ({ ...prev, loadingTemplates: true }));
      try {
        const result = await attendantServiceAPI.getTemplates(filters);
        return result;
      } finally {
        setLoadingStates((prev) => ({ ...prev, loadingTemplates: false }));
      }
    },
    []
  );

  // Função para validação
  const validateService = useCallback(async (data: ServiceValidationData) => {
    setLoadingStates((prev) => ({ ...prev, validating: true }));
    try {
      const result = await attendantServiceAPI.validateService(data);
      return result;
    } finally {
      setLoadingStates((prev) => ({ ...prev, validating: false }));
    }
  }, []);

  // Função para buscar sugestões
  const getSuggestions = useCallback(
    async (clientId: number, vehicleId: number) => {
      setLoadingStates((prev) => ({ ...prev, loadingSuggestions: true }));
      try {
        const result = await attendantServiceAPI.getSuggestions(
          clientId,
          vehicleId
        );
        return result;
      } finally {
        setLoadingStates((prev) => ({ ...prev, loadingSuggestions: false }));
      }
    },
    []
  );

  return {
    // Data
    templates,
    stats,

    // Loading states
    loadingStates,
    isLoadingTemplates,
    isLoadingStats,

    // Errors
    templatesError,
    statsError,

    // Functions
    createQuickService,
    createCompleteService,
    getTemplatesWithFilters,
    validateService,
    getSuggestions,
    refetchTemplates,
    refetchStats,
  };
};

export const useServiceTemplates = (filters?: ServiceTemplateFilters) => {
  const {
    data: templates = [],
    isLoading,
    error,
    refetch,
  } = useQuery({
    queryKey: ['attendant', 'templates', filters],
    queryFn: () => attendantServiceAPI.getTemplates(filters),
    staleTime: 5 * 60 * 1000, // 5 minutes
  });

  return {
    templates,
    isLoading,
    error,
    refetch,
  };
};

export const useServiceValidation = () => {
  const [validationResult, setValidationResult] =
    useState<ValidationResult | null>(null);
  const [isValidating, setIsValidating] = useState(false);

  const validateService = useCallback(async (data: ServiceValidationData) => {
    setIsValidating(true);
    try {
      const result = await attendantServiceAPI.validateService(data);
      setValidationResult(result as unknown as ValidationResult);
      return result;
    } catch (error: unknown) {
      toast.error('Erro ao validar serviço');
      throw error;
    } finally {
      setIsValidating(false);
    }
  }, []);

  const clearValidation = useCallback(() => {
    setValidationResult(null);
  }, []);

  return {
    validationResult,
    isValidating,
    validateService,
    clearValidation,
  };
};

export const useServiceSuggestions = (
  clientId?: number,
  vehicleId?: number
) => {
  const {
    data: suggestions,
    isLoading,
    error,
    refetch,
  } = useQuery({
    queryKey: ['attendant', 'suggestions', clientId, vehicleId],
    queryFn: () => attendantServiceAPI.getSuggestions(clientId!, vehicleId!),
    enabled: !!clientId && !!vehicleId,
    staleTime: 10 * 60 * 1000, // 10 minutes
  });

  return {
    suggestions,
    isLoading,
    error,
    refetch,
  };
};

export const useServiceForm = (initialData?: Partial<ServiceFormData>) => {
  const [formData, setFormData] = useState<ServiceFormData>({
    client_id: 0,
    vehicle_id: 0,
    description: '',
    estimated_duration: 60,
    priority: 'medium',
    notes: '',
    observations: '',
    scheduled_at: '',
    service_items: [],
    ...initialData,
  });

  const updateFormData = useCallback((updates: Partial<ServiceFormData>) => {
    setFormData((prev) => ({ ...prev, ...updates }));
  }, []);

  const resetForm = useCallback(() => {
    setFormData({
      client_id: 0,
      vehicle_id: 0,
      description: '',
      estimated_duration: 60,
      priority: 'medium',
      notes: '',
      observations: '',
      scheduled_at: '',
      service_items: [],
    });
  }, []);

  const isFormValid = useCallback(() => {
    return (
      formData.client_id > 0 &&
      formData.vehicle_id > 0 &&
      formData.description.trim().length > 0 &&
      formData.estimated_duration > 0
    );
  }, [formData]);

  return {
    formData,
    updateFormData,
    resetForm,
    isFormValid,
  };
};

export const useServiceStats = () => {
  const {
    data: stats,
    isLoading,
    error,
    refetch,
  } = useQuery({
    queryKey: ['attendant', 'stats'],
    queryFn: () => attendantServiceAPI.getQuickStats(),
    staleTime: 2 * 60 * 1000, // 2 minutes
    refetchInterval: 5 * 60 * 1000, // Auto-refresh every 5 minutes
  });

  return {
    stats,
    isLoading,
    error,
    refetch,
  };
};

export const useServiceList = (params?: {
  page?: number;
  per_page?: number;
  search?: string;
  status?: string;
  priority?: string;
  client_id?: number;
  vehicle_id?: number;
  date_from?: string;
  date_to?: string;
}) => {
  const {
    data: services,
    isLoading,
    error,
    refetch,
  } = useQuery({
    queryKey: ['attendant', 'services', params],
    queryFn: () => attendantServiceAPI.getServices(params),
    staleTime: 2 * 60 * 1000, // 2 minutes
  });

  return {
    services,
    isLoading,
    error,
    refetch,
  };
};
