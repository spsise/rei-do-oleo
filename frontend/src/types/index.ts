export type UserRole = "manager" | "admin" | "attendant" | "technician";

export interface User {
  id: number;
  name: string;
  email: string;
  highest_role?: UserRole;
  ativo?: boolean;
}

export interface Cliente {
  id: string;
  nome: string;
  telefone: string;
  placa: string;
  marca: string;
  modelo: string;
  quilometragem: number;
  criadoEm: string;
  atualizadoEm: string;
}

export interface TipoServico {
  id: string;
  nome: string;
  preco: number;
  tempoEstimado: number; // em minutos
  categoria: "lubrificacao" | "filtros" | "fluidos";
  ativo: boolean;
}

export interface Atendimento {
  id: string;
  clienteId: string;
  cliente: Cliente;
  servicos: TipoServico[];
  valorTotal: number;
  status: "agendado" | "em_andamento" | "concluido" | "cancelado";
  observacoes?: string;
  quilometragemAtual: number;
  criadoEm: string;
  iniciadoEm?: string;
  concluidoEm?: string;
}

export interface DashboardMetricas {
  servicosDoDia: number;
  clientesAtendidos: number;
  faturamento: number;
  tempoMedio: number;
}

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface NovoCliente {
  nome: string;
  telefone: string;
  placa: string;
  marca: string;
  modelo: string;
  quilometragem: number;
}

export interface NovoAtendimento {
  clienteId: string;
  servicosIds: string[];
  observacoes?: string;
  quilometragemAtual: number;
}

export interface ApiResponse<T = unknown> {
  status: "success" | "error";
  message: string;
  data?: T;
  errors?: Record<string, string[]>;
}

export interface LoginResponseData {
  user: User;
  token: string;
  token_type: string;
}

export type LoginResponse = ApiResponse<LoginResponseData>;

export interface RefreshResponseData {
  token: string;
  token_type: string;
}

export type RefreshResponse = ApiResponse<RefreshResponseData>;
