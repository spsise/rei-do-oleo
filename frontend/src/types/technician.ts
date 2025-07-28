// Tipos específicos para o módulo do técnico
// Estes tipos são mais simples que os tipos completos do sistema

export interface TechnicianClient {
  id: number;
  name: string;
  email: string;
  phone: string;
  document: string;
}

export interface TechnicianVehicle {
  id: number;
  brand: string;
  model: string;
  year: number;
  license_plate: string;
  color: string;
  mileage: number;
}

export interface TechnicianService {
  id: number;
  service_number: string;
  description: string;
  status: string;
  total_amount: number;
  created_at: string;
  scheduled_at?: string;
  mileage_at_service?: number;
  estimated_duration?: number;
  notes?: string;
  observations?: string;
  items?: TechnicianServiceItem[];
}

export interface TechnicianSearchResult {
  client: TechnicianClient;
  vehicles: TechnicianVehicle[];
  recent_services: TechnicianService[];
  found_by: string;
}

// Tipos para produtos
export interface TechnicianProduct {
  id: number;
  name: string;
  description?: string;
  sku: string;
  price: number;
  stock_quantity: number;
  category?: {
    id: number;
    name: string;
  };
}

// Tipos para itens de serviço
export interface TechnicianServiceItem {
  id?: string | number;
  product_id: number;
  quantity: number;
  unit_price: number;
  total_price: number;
  notes?: string;
  product?: TechnicianProduct;
}

export interface CreateTechnicianServiceData {
  client_id: number;
  vehicle_id: number;
  service_center_id?: number;
  technician_id?: number;
  attendant_id?: number;
  service_number?: string;
  description: string;
  estimated_duration: number;
  scheduled_at?: string;
  started_at?: string;
  completed_at?: string;
  service_status_id?: number;
  payment_method_id?: number;
  mileage_at_service?: number;
  total_amount?: number;
  discount_amount?: number;
  final_amount?: number;
  observations?: string;
  notes?: string;
  active?: boolean;
  items?: TechnicianServiceItem[];
}
