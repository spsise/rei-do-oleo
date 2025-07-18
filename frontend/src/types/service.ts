import type { Category } from './category';

export interface Service {
  id: number;
  service_number: string;
  description?: string;
  complaint?: string;
  diagnosis?: string;
  solution?: string;
  scheduled_date?: string;
  started_at?: string;
  finished_at?: string;
  duration?: number;
  duration_formatted?: string;
  status?: {
    id: number | null;
    name: string | null;
    label: string | null;
    color: string | null;
  };
  priority?: string;
  priority_label?: string;
  payment_method?: {
    id: number;
    name: string;
    label: string | null;
  };
  financial?: {
    labor_cost?: number;
    items_total: number;
    items_total_formatted: string;
    discount?: number;
    total_amount: string;
    total_amount_formatted: string;
  };
  vehicle?: {
    id: number;
    license_plate: string;
    brand: string;
    model: string;
    year: number;
    mileage_at_service?: number;
    fuel_level?: string;
    fuel_level_label?: string;
  };
  client?: {
    id: number;
    name: string;
    phone?: string;
    document?: string;
  };
  service_center?: {
    id: number;
    name: string;
    code: string;
  };
  technician?: {
    id: number;
    name: string;
    specialties?: string[];
  };
  attendant?: {
    id: number;
    name: string;
  };
  warranty_months?: number;
  observations?: string;
  internal_notes?: string;
  created_at: string;
  updated_at: string;
}

export interface ServiceItem {
  id: number;
  service_id: number;
  product_id: number;
  quantity: number;
  unit_price: number;
  discount?: number;
  total_price: number;
  notes?: string;
  created_at: string;
  updated_at: string;

  // Relacionamentos
  product?: Product;
}

export interface ServiceStatus {
  id: number;
  name: string;
  color?: string;
  active: boolean;
  created_at: string;
  updated_at: string;
}

export interface ServiceCenter {
  id: number;
  code: string;
  name: string;
  slug: string;
  cnpj?: string;
  city?: string;
  state?: string;
  phone?: string;
  email?: string;
  active: boolean;
  created_at: string;
  updated_at: string;
}

export interface Vehicle {
  id: number;
  client_id: number;
  brand: string;
  model: string;
  year: number;
  color: string;
  license_plate: string;
  chassis?: string;
  engine?: string;
  mileage?: number;
  active: boolean;
  created_at: string;
  updated_at: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  active: boolean;
  created_at: string;
  updated_at: string;
}

export interface Product {
  id: number;
  name: string;
  description?: string;
  category_id: number;
  price: number;
  stock_quantity: number;
  min_stock: number;
  active: boolean;
  created_at: string;
  updated_at: string;

  // Relacionamentos
  category?: Category;
}

export interface PaymentMethod {
  id: number;
  name: string;
  active: boolean;
  created_at: string;
  updated_at: string;
}

export interface Client {
  id: number;
  name: string;
  email: string;
  document: string;
  phone?: string;
  type: 'pessoa_fisica' | 'pessoa_juridica';
  active: boolean;
  created_at: string;
  updated_at: string;
}

// Filtros para listagem
export interface ServiceFilters {
  search?: string;
  service_center_id?: number;
  client_id?: number;
  vehicle_id?: number;
  status?: string;
  technician_id?: number;
  date_from?: string;
  date_to?: string;
  per_page?: number;
  page?: number;
}

// Dados para criação
export interface CreateServiceData {
  service_center_id: number;
  client_id: number;
  vehicle_id: number;
  service_number?: string;
  description: string;
  complaint?: string;
  diagnosis?: string;
  solution?: string;
  scheduled_date?: string;
  started_at?: string;
  finished_at?: string;
  technician_id?: number;
  attendant_id?: number;
  status_id: number;
  payment_method_id?: number;
  labor_cost?: number;
  discount?: number;
  total_amount?: number;
  mileage?: number;
  fuel_level?: 'empty' | '1/4' | '1/2' | '3/4' | 'full';
  observations?: string;
  internal_notes?: string;
  warranty_months?: number;
  items?: CreateServiceItemData[];
}

// Dados para atualização
export interface UpdateServiceData {
  service_center_id?: number;
  client_id?: number;
  vehicle_id?: number;
  service_number?: string;
  description?: string;
  complaint?: string;
  diagnosis?: string;
  solution?: string;
  scheduled_date?: string;
  started_at?: string;
  finished_at?: string;
  technician_id?: number;
  attendant_id?: number;
  status_id?: number;
  payment_method_id?: number;
  labor_cost?: number;
  discount?: number;
  total_amount?: number;
  mileage?: number;
  fuel_level?: 'empty' | '1/4' | '1/2' | '3/4' | 'full';
  observations?: string;
  internal_notes?: string;
  warranty_months?: number;
}

// Dados para criação de item
export interface CreateServiceItemData {
  product_id: number;
  quantity: number;
  unit_price: number;
  discount?: number;
  notes?: string;
}

// Dados para atualização de item
export interface UpdateServiceItemData {
  product_id?: number;
  quantity?: number;
  unit_price?: number;
  discount?: number;
  notes?: string;
}

// Dados para busca por número
export interface SearchServiceByNumberData {
  service_number: string;
}

export interface SearchServiceData {
  service_number?: string;
  client_name?: string;
  vehicle_license_plate?: string;
}

// Dados para atualização de status
export interface UpdateServiceStatusData {
  status_id: number;
  notes?: string;
}
