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

export interface VehicleFilters {
  search?: string;
  client_id?: number;
  brand?: string;
  model?: string;
  year?: number;
  active?: boolean;
  per_page?: number;
  page?: number;
}

export interface CreateVehicleData {
  client_id: number;
  brand: string;
  model: string;
  year: number;
  color: string;
  license_plate: string;
  chassis?: string;
  engine?: string;
  mileage?: number;
  active?: boolean;
}

export type UpdateVehicleData = Partial<CreateVehicleData>;

export interface VehicleListResponse {
  data: Vehicle[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}
