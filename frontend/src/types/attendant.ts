// Tipos para o sistema de atendentes

export interface ServiceTemplate {
  id: number;
  name: string;
  description: string;
  category: 'maintenance' | 'repair' | 'inspection' | 'general';
  estimated_duration: number;
  priority: 'low' | 'medium' | 'high';
  notes?: string;
  service_items?: ServiceItem[];
  active: boolean;
  sort_order: number;
  usage_count: number;
  created_at: string;
  updated_at: string;
}

export interface ServiceItem {
  product_name: string;
  quantity: number;
  unit_price: number;
  notes?: string;
}

export interface CreateQuickServiceData {
  client_id: number;
  vehicle_id: number;
  description: string;
  estimated_duration?: number;
  priority?: 'low' | 'medium' | 'high';
  notes?: string;
  template_id?: number;
}

export interface CreateCompleteServiceData extends CreateQuickServiceData {
  scheduled_at?: string;
  observations?: string;
  service_items?: ServiceItem[];
}

export interface ServiceValidationData {
  client_id: number;
  vehicle_id: number;
  description: string;
  estimated_duration?: number;
  priority?: 'low' | 'medium' | 'high';
  scheduled_at?: string;
  template_id?: number;
}

export interface ServiceValidationResult {
  is_valid: boolean;
  warnings: string[];
  suggestions: string[];
}

export interface ServiceSuggestion {
  recent_services: Array<{
    id: number;
    description: string;
    created_at: string;
    estimated_duration: number;
  }>;
  recommended_services: ServiceTemplate[];
  maintenance_due: Array<{
    type: string;
    due_date: string;
    description: string;
  }>;
}

export interface QuickStats {
  services_created_today: number;
  pending_services: number;
  completed_today: number;
  average_creation_time: number;
}

export interface AttendantService {
  id: number;
  service_number: string;
  description: string;
  estimated_duration: number;
  priority: 'low' | 'medium' | 'high';
  status: string;
  client: {
    id: number;
    name: string;
    email: string;
    phone: string;
  };
  vehicle: {
    id: number;
    brand: string;
    model: string;
    year: number;
    license_plate: string;
  };
  service_center: {
    id: number;
    name: string;
  };
  attendant: {
    id: number;
    name: string;
  };
  scheduled_at?: string;
  notes?: string;
  observations?: string;
  created_at: string;
  updated_at: string;
}

// Tipos para formulários
export interface ServiceFormData {
  client_id: number;
  vehicle_id: number;
  description: string;
  estimated_duration: number;
  priority: 'low' | 'medium' | 'high';
  notes: string;
  observations: string;
  scheduled_at: string;
  template_id?: number;
  service_items: ServiceItem[];
}

// Tipos para filtros
export interface ServiceTemplateFilters {
  category?: string;
  priority?: string;
  search?: string;
}

// Tipos para respostas da API
export interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

// Tipos para estados de loading
export interface LoadingStates {
  creating: boolean;
  validating: boolean;
  loadingTemplates: boolean;
  loadingSuggestions: boolean;
  loadingStats: boolean;
}

// Tipos para notificações
export interface ServiceNotification {
  type: 'success' | 'error' | 'warning' | 'info';
  title: string;
  message: string;
  duration?: number;
}

// Tipos para histórico de ações
export interface ServiceAction {
  id: number;
  action: 'created' | 'updated' | 'deleted' | 'validated';
  service_id: number;
  service_number: string;
  description: string;
  timestamp: string;
  attendant_name: string;
}

// Tipos para configurações
export interface AttendantConfig {
  auto_save: boolean;
  show_suggestions: boolean;
  default_priority: 'low' | 'medium' | 'high';
  default_duration: number;
  enable_templates: boolean;
  enable_validation: boolean;
}

// Tipos para relatórios
export interface ServiceReport {
  period: string;
  total_services: number;
  services_by_priority: {
    low: number;
    medium: number;
    high: number;
  };
  services_by_category: {
    maintenance: number;
    repair: number;
    inspection: number;
    general: number;
  };
  average_duration: number;
  most_used_templates: Array<{
    template_id: number;
    template_name: string;
    usage_count: number;
  }>;
}
