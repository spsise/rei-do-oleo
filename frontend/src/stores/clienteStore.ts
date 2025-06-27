
import { create } from "zustand";
import { clienteService } from "../services/clienteService";

export interface Cliente {
  id: number;
  nome: string;
  telefone: string;
  marca: string;
  modelo: string;
  placa: string;
  quilometragem: number;
}

interface ClienteStore {
  clientes: Cliente[];
  loading: boolean;
  buscarTodos: () => Promise<void>;
  buscarPorPlaca: (placa: string) => Promise<Cliente | null>;
  buscarPorId: (id: string) => Promise<Cliente | null>;
  criarCliente: (cliente: Omit<Cliente, 'id'>) => Promise<void>;
  atualizarCliente: (id: number, cliente: Partial<Cliente>) => Promise<void>;
}

export const useClienteStore = create<ClienteStore>((set, get) => ({
  clientes: [],
  loading: false,

  buscarTodos: async () => {
    set({ loading: true });
    try {
      const response = await clienteService.buscarTodos();
      set({ clientes: response, loading: false });
    } catch (error) {
      set({ loading: false });
      throw error;
    }
  },

  buscarPorPlaca: async (placa: string) => {
    set({ loading: true });
    try {
      const cliente = await clienteService.buscarPorPlaca(placa);
      set({ loading: false });
      return cliente;
    } catch (error) {
      set({ loading: false });
      throw error;
    }
  },

  buscarPorId: async (id: string) => {
    set({ loading: true });
    try {
      // Primeiro tenta buscar nos clientes já carregados
      const clienteExistente = get().clientes.find(c => c.id === parseInt(id));
      if (clienteExistente) {
        set({ loading: false });
        return clienteExistente;
      }
      
      // Se não encontrar, busca na API
      const cliente = await clienteService.buscarPorId(id);
      set({ loading: false });
      return cliente;
    } catch (error) {
      set({ loading: false });
      throw error;
    }
  },

  criarCliente: async (cliente: Omit<Cliente, 'id'>) => {
    set({ loading: true });
    try {
      const novoCliente = await clienteService.criar(cliente);
      set(state => ({ 
        clientes: [...state.clientes, novoCliente],
        loading: false 
      }));
    } catch (error) {
      set({ loading: false });
      throw error;
    }
  },

  atualizarCliente: async (id: number, dadosAtualizados: Partial<Cliente>) => {
    set({ loading: true });
    try {
      const clienteAtualizado = await clienteService.atualizar(id, dadosAtualizados);
      set(state => ({
        clientes: state.clientes.map(c => 
          c.id === id ? { ...c, ...clienteAtualizado } : c
        ),
        loading: false
      }));
    } catch (error) {
      set({ loading: false });
      throw error;
    }
  }
}));
