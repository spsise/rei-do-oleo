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
}

export interface TechnicianSearchResult {
  client: TechnicianClient;
  vehicles: TechnicianVehicle[];
  recent_services: TechnicianService[];
  found_by: string;
}

export interface CreateTechnicianServiceData {
  client_id: number;
  vehicle_id: number;
  description: string;
  estimated_duration: number;
  priority: 'low' | 'medium' | 'high';
  notes?: string;
}
