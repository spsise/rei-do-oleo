export interface Client {
  id: number;
  name: string;
  email?: string;
  document: string;
  document_formatted?: string;
  phone?: string;
  phone_formatted?: string;
  phone02?: string;
  phone02_formatted?: string;
  type: 'pessoa_fisica' | 'pessoa_juridica';
  type_label?: string;
  address?: string;
  city?: string;
  state?: string;
  zip_code?: string;
  zip_code_formatted?: string;
  full_address?: string;
  notes?: string;
  active: boolean;
  active_label?: string;
  vehicles_count?: number;
  services_count?: number;
  last_service_date?: string;
  created_at: string;
  updated_at: string;
}

export interface ClientFilters {
  search?: string;
  type?: 'pessoa_fisica' | 'pessoa_juridica';
  active?: boolean;
  per_page?: number;
}

export interface CreateClientData {
  name: string;
  email?: string;
  document: string;
  phone?: string;
  type: 'pessoa_fisica' | 'pessoa_juridica';
  address?: string;
  city?: string;
  state?: string;
  zip_code?: string;
  notes?: string;
  active?: boolean;
}

export type UpdateClientData = Partial<CreateClientData>;

export interface SearchByDocumentData {
  document: string;
}

export interface SearchByPhoneData {
  phone: string;
}

export interface ClientListResponse {
  data: Client[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}
