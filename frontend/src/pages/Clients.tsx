import React, { useState } from 'react';
import { ClientTable } from '../components/Common/ClientTable';
import { Pagination } from '../components/Common/Pagination';
import { ClientFiltersComponent } from '../components/Forms/ClientFilters';
import { ClientForm } from '../components/Forms/ClientForm';
import { ClientSearchForm } from '../components/Forms/ClientSearchForm';
import {
  useClients,
  useCreateClient,
  useDeleteClient,
  useSearchClientByDocument,
  useSearchClientByPhone,
  useUpdateClient,
} from '../hooks/useClients';
import type {
  Client,
  ClientFilters,
  CreateClientData,
  UpdateClientData,
} from '../types/client';

interface ModalState {
  isOpen: boolean;
  type: 'create' | 'edit' | 'search';
  client?: Client;
}

export const ClientsPage: React.FC = () => {
  const [modal, setModal] = useState<ModalState>({
    isOpen: false,
    type: 'create',
  });
  const [filters, setFilters] = useState<ClientFilters>({ per_page: 15 });
  const [searchResult, setSearchResult] = useState<Client | null>(null);

  // Hooks do React Query
  const { data: clientsData, isLoading } = useClients(filters);
  const createClientMutation = useCreateClient();
  const updateClientMutation = useUpdateClient();
  const deleteClientMutation = useDeleteClient();
  const searchByDocumentMutation = useSearchClientByDocument();
  const searchByPhoneMutation = useSearchClientByPhone();

  // Abrir modal
  const openModal = (type: ModalState['type'], client?: Client) => {
    setModal({ isOpen: true, type, client });
    setSearchResult(null);
  };

  // Fechar modal
  const closeModal = () => {
    setModal({ isOpen: false, type: 'create' });
  };

  // Criar cliente
  const handleCreateClient = async (data: CreateClientData) => {
    await createClientMutation.mutateAsync(data);
    closeModal();
  };

  // Atualizar cliente
  const handleUpdateClient = async (data: UpdateClientData) => {
    if (!modal.client) return;
    await updateClientMutation.mutateAsync({ id: modal.client.id, data });
    closeModal();
  };

  // Excluir cliente
  const handleDeleteClient = async (client: Client) => {
    await deleteClientMutation.mutateAsync(client.id);
  };

  // Buscar por documento
  const handleSearchByDocument = async (document: string) => {
    try {
      const result = await searchByDocumentMutation.mutateAsync({ document });
      setSearchResult(result);
      setModal({ isOpen: false, type: 'create' });
    } catch {
      setSearchResult(null);
    }
  };

  // Buscar por telefone
  const handleSearchByPhone = async (phone: string) => {
    try {
      const result = await searchByPhoneMutation.mutateAsync({ phone });
      setSearchResult(result);
      setModal({ isOpen: false, type: 'create' });
    } catch {
      setSearchResult(null);
    }
  };

  // Aplicar filtros
  const handleFiltersChange = (newFilters: ClientFilters) => {
    setFilters(newFilters);
  };

  // Limpar filtros
  const handleClearFilters = () => {
    setFilters({ per_page: 15 });
  };

  // Mudar página
  const handlePageChange = (page: number) => {
    setFilters((prev) => ({ ...prev, page }));
  };

  const clients = clientsData?.data || [];
  const pagination = {
    current_page: clientsData?.current_page || 1,
    last_page: clientsData?.last_page || 1,
    per_page: clientsData?.per_page || 15,
    total: clientsData?.total || 0,
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-10xl mx-auto px-0 sm:px-0 lg:px-0 py-0">
        {/* Header */}
        <div className="mb-8">
          <div className="flex justify-between items-center">
            <div>
              <h1 className="text-3xl font-bold text-gray-900">
                Gestão de Clientes
              </h1>
              <p className="mt-2 text-gray-600">
                Gerencie seus clientes, visualize informações e mantenha os
                dados atualizados.
              </p>
            </div>
            <div className="flex space-x-3">
              <button
                onClick={() => openModal('search')}
                className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Buscar Cliente
              </button>
              <button
                onClick={() => openModal('create')}
                className="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Novo Cliente
              </button>
            </div>
          </div>
        </div>

        {/* Filtros */}
        <ClientFiltersComponent
          filters={filters}
          onFiltersChange={handleFiltersChange}
          onClearFilters={handleClearFilters}
        />

        {/* Resultado da busca */}
        {searchResult && (
          <div className="mb-6 bg-white p-6 rounded-lg shadow-sm border">
            <h3 className="text-lg font-medium text-gray-900 mb-4">
              Resultado da Busca
            </h3>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <div>
                <span className="text-sm font-medium text-gray-500">Nome:</span>
                <p className="text-sm text-gray-900">{searchResult.name}</p>
              </div>
              <div>
                <span className="text-sm font-medium text-gray-500">
                  Email:
                </span>
                <p className="text-sm text-gray-900">{searchResult.email}</p>
              </div>
              <div>
                <span className="text-sm font-medium text-gray-500">
                  Documento:
                </span>
                <p className="text-sm text-gray-900">{searchResult.document}</p>
              </div>
              <div>
                <span className="text-sm font-medium text-gray-500">
                  Telefone:
                </span>
                <p className="text-sm text-gray-900">
                  {searchResult.phone || 'Não informado'}
                </p>
              </div>
            </div>
            <div className="mt-4 flex space-x-3">
              <button
                onClick={() => openModal('edit', searchResult)}
                className="px-3 py-1 text-sm text-blue-600 hover:text-blue-700"
              >
                Editar
              </button>
              <button
                onClick={() => setSearchResult(null)}
                className="px-3 py-1 text-sm text-gray-600 hover:text-gray-700"
              >
                Fechar
              </button>
            </div>
          </div>
        )}
      </div>

      {/* Tabela de Clientes - FORA do container de página */}
      <div className="w-full overflow-x-auto">
        <div className="max-w-7xl mx-auto">
          <ClientTable
            clients={clients}
            onEdit={(client) => openModal('edit', client)}
            onDelete={handleDeleteClient}
            loading={isLoading}
          />
        </div>
      </div>

      {/* Paginação */}
      {pagination.last_page > 1 && (
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <Pagination
            currentPage={pagination.current_page}
            lastPage={pagination.last_page}
            total={pagination.total}
            perPage={pagination.per_page}
            onPageChange={handlePageChange}
          />
        </div>
      )}

      {/* Modal */}
      {modal.isOpen && (
        <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
          <div className="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div className="mt-3">
              <div className="flex justify-between items-center mb-4">
                <h3 className="text-lg font-medium text-gray-900">
                  {modal.type === 'create' && 'Novo Cliente'}
                  {modal.type === 'edit' && 'Editar Cliente'}
                  {modal.type === 'search' && 'Buscar Cliente'}
                </h3>
                <button
                  onClick={closeModal}
                  className="text-gray-400 hover:text-gray-600"
                >
                  <svg
                    className="h-6 w-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M6 18L18 6M6 6l12 12"
                    />
                  </svg>
                </button>
              </div>

              {modal.type === 'search' ? (
                <ClientSearchForm
                  onSearchByDocument={handleSearchByDocument}
                  onSearchByPhone={handleSearchByPhone}
                  loading={
                    searchByDocumentMutation.isPending ||
                    searchByPhoneMutation.isPending
                  }
                />
              ) : (
                <ClientForm
                  client={modal.client}
                  onSubmit={(data) => {
                    if (modal.type === 'create') {
                      handleCreateClient(data as CreateClientData);
                    } else {
                      handleUpdateClient(data as UpdateClientData);
                    }
                  }}
                  onCancel={closeModal}
                  loading={
                    createClientMutation.isPending ||
                    updateClientMutation.isPending
                  }
                />
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
