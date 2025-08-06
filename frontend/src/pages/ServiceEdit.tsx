import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { ServiceForm } from '../components/Service/ServiceForm';
import {
  ChangesIndicator,
  UnsavedChangesAlert,
} from '../components/ui/ChangesIndicator';
import { SmartButtonGroup } from '../components/ui/SmartButton';
import { useServiceFormDirty } from '../hooks/useFormDirty';
import { useUnsavedChanges } from '../hooks/useUnsavedChanges';
import type { Service } from '../types/service';

// Mock data para demonstração
const mockService: Service = {
  id: 1,
  service_number: 'SER-2024-001',
  service_center: { id: 1, name: 'Centro Principal', code: 'CP' },
  client: { id: 1, name: 'João Silva', phone: '11999999999' },
  vehicle: {
    id: 1,
    brand: 'Toyota',
    model: 'Corolla',
    year: 2020,
    license_plate: 'ABC-1234',
    mileage_at_service: 50000,
    fuel_level: '1/2',
  },
  description: 'Troca de óleo e filtros',
  complaint: 'Veículo fazendo barulho no motor',
  diagnosis: 'Óleo vencido e filtros sujos',
  solution: 'Troca completa de óleo e filtros',
  scheduled_date: '2024-01-15',
  started_at: '2024-01-15T08:00:00',
  finished_at: '2024-01-15T10:00:00',
  technician: { id: 1, name: 'Carlos Silva' },
  attendant: { id: 1, name: 'Maria Santos' },
  status: {
    id: 1,
    name: 'Em Andamento',
    label: 'Em Andamento',
    color: 'yellow',
  },
  payment_method: {
    id: 1,
    name: 'Cartão de Crédito',
    label: 'Cartão de Crédito',
  },
  financial: {
    labor_cost: 150.0,
    discount: 0,
    total_amount: '245.90',
    items_total: 91.8,
    items_total_formatted: 'R$ 91,80',
    total_amount_formatted: 'R$ 245,90',
  },

  estimated_duration: 120,

  observations: 'Veículo em bom estado geral',
  internal_notes: 'Cliente solicitou troca de óleo premium',
  warranty_months: 12,
  items: [
    {
      id: 1,
      service_id: 1,
      product_id: 1,
      product: {
        id: 1,
        name: 'Óleo de Motor 5W30',
        sku: 'OLEO001',
        brand: 'Mobil',
        category: 'Óleos',
        unit: 'L',
        current_stock: 50,
      },
      quantity: 2,
      unit_price: 45.9,
      total_price: 91.8,
      discount: 0,
      notes: 'Óleo sintético premium',
      created_at: '2024-01-15T08:00:00',
      updated_at: '2024-01-15T10:00:00',
    },
  ],
  created_at: '2024-01-15T08:00:00',
  updated_at: '2024-01-15T10:00:00',
};

export const ServiceEditPage: React.FC = () => {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [service, setService] = useState<Service>(mockService);

  // Hook para detectar mudanças
  const { isDirty, changedFields, currentData, reset } = useServiceFormDirty(
    service as unknown as Record<string, unknown>,
    (isDirty, changedFields) => {
      console.log('Mudanças detectadas:', isDirty, changedFields);
    }
  );

  // Hook para gerenciar navegação com mudanças não salvas
  useUnsavedChanges({
    isDirty,
    onSave: handleSave,
    onDiscard: reset,
    message:
      'Você tem alterações não salvas no serviço. Deseja salvar antes de sair?',
  });

  // Função para salvar
  async function handleSave() {
    setLoading(true);
    try {
      // Simular chamada à API
      await new Promise((resolve) => setTimeout(resolve, 1000));

      // Atualizar o serviço com os dados atuais
      setService((prev) => ({
        ...prev,
        ...currentData,
        updated_at: new Date().toISOString(),
      }));

      // Reset do estado de mudanças
      reset();

      console.log('Serviço salvo com sucesso!');
    } catch (error) {
      console.error('Erro ao salvar serviço:', error);
    } finally {
      setLoading(false);
    }
  }

  // Função para cancelar
  const handleCancel = () => {
    navigate('/services');
  };

  // Função para descartar mudanças
  const handleDiscard = () => {
    reset();
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="mb-8">
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-3xl font-bold text-gray-900">
                Editar Serviço
              </h1>
              <p className="mt-2 text-sm text-gray-600">
                Serviço #{service.service_number} - {service.client?.name}
              </p>
            </div>

            <div className="flex items-center gap-4">
              {/* Indicador de Mudanças */}
              <ChangesIndicator
                isDirty={isDirty}
                changedFields={changedFields}
                changedFieldsCount={changedFields.length}
                variant="compact"
              />

              {/* Botões de Ação */}
              <SmartButtonGroup
                isDirty={isDirty}
                isSubmitting={loading}
                onSave={handleSave}
                onCancel={handleCancel}
                onReset={handleDiscard}
                saveText="Salvar Serviço"
                cancelText="Cancelar"
                resetText="Descartar"
                showReset={isDirty}
              />
            </div>
          </div>
        </div>

        {/* Alerta de Mudanças Não Salvas */}
        <UnsavedChangesAlert
          isDirty={isDirty}
          onSave={handleSave}
          onDiscard={handleDiscard}
          className="mb-6"
        />

        {/* Formulário */}
        <div className="bg-white shadow rounded-lg">
          <div className="px-6 py-4 border-b border-gray-200">
            <h2 className="text-lg font-medium text-gray-900">
              Informações do Serviço
            </h2>
            <p className="mt-1 text-sm text-gray-500">
              Edite as informações do serviço conforme necessário
            </p>
          </div>

          <div className="p-6">
            <ServiceForm
              service={service}
              onSubmit={handleSave}
              onCancel={handleCancel}
              loading={loading}
            />
          </div>
        </div>

        {/* Informações de Debug (apenas para demonstração) */}
        {process.env.NODE_ENV === 'development' && (
          <div className="mt-8 bg-gray-100 rounded-lg p-4">
            <h3 className="text-sm font-medium text-gray-700 mb-2">
              Debug Info (Desenvolvimento)
            </h3>
            <div className="text-xs text-gray-600 space-y-1">
              <div>Estado de Mudanças: {isDirty ? 'Sim' : 'Não'}</div>
              <div>
                Campos Alterados: {changedFields.join(', ') || 'Nenhum'}
              </div>
              <div>Total de Campos Alterados: {changedFields.length}</div>
              <div>Última Atualização: {service.updated_at}</div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};
