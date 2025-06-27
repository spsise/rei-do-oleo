
import { api } from './api';
import { Cliente, NovoCliente } from '../types';

export const clienteService = {
  buscarTodos: async (): Promise<Cliente[]> => {
    const response = await api.get('/clientes');
    return response.data;
  },

  buscarPorId: async (id: string): Promise<Cliente> => {
    const response = await api.get(`/clientes/${id}`);
    return response.data;
  },

  buscarPorPlaca: async (placa: string): Promise<Cliente> => {
    const response = await api.get(`/clientes?placa=${placa}`);
    return response.data;
  },

  criar: async (cliente: NovoCliente): Promise<Cliente> => {
    const response = await api.post('/clientes', cliente);
    return response.data;
  },

  atualizar: async (id: number, dados: Partial<Cliente>): Promise<Cliente> => {
    const response = await api.put(`/clientes/${id}`, dados);
    return response.data;
  },

  deletar: async (id: number): Promise<void> => {
    await api.delete(`/clientes/${id}`);
  },

  buscarHistorico: async (clienteId: string) => {
    const response = await api.get(`/clientes/${clienteId}/historico`);
    return response.data;
  }
};
