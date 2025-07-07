import React, { useEffect, useState } from 'react';
import type {
  CreateServiceData,
  Service,
  UpdateServiceData,
} from '../../types/service';

interface ServiceFormProps {
  service?: Service;
  onSubmit: (data: CreateServiceData | UpdateServiceData) => void;
  onCancel: () => void;
  loading?: boolean;
}

export const ServiceForm: React.FC<ServiceFormProps> = ({
  service,
  onSubmit,
  onCancel,
  loading = false,
}) => {
  const [formData, setFormData] = useState<CreateServiceData>({
    service_center_id: 1,
    client_id: 0,
    vehicle_id: 0,
    description: '',
    complaint: '',
    diagnosis: '',
    solution: '',
    scheduled_date: '',
    started_at: '',
    finished_at: '',
    technician_id: undefined,
    attendant_id: undefined,
    status_id: 1,
    payment_method_id: undefined,
    labor_cost: 0,
    discount: 0,
    total_amount: 0,
    mileage: 0,
    fuel_level: '1/2',
    observations: '',
    internal_notes: '',
    warranty_months: 0,
    priority: 'normal',
  });

  const [errors, setErrors] = useState<Record<string, string>>({});

  useEffect(() => {
    if (service) {
      setFormData({
        service_center_id: service.service_center_id,
        client_id: service.client_id,
        vehicle_id: service.vehicle_id,
        description: service.description,
        complaint: service.complaint || '',
        diagnosis: service.diagnosis || '',
        solution: service.solution || '',
        scheduled_date: service.scheduled_date || '',
        started_at: service.started_at || '',
        finished_at: service.finished_at || '',
        technician_id: service.technician_id,
        attendant_id: service.attendant_id,
        status_id: service.status_id,
        payment_method_id: service.payment_method_id,
        labor_cost: service.labor_cost || 0,
        discount: service.discount || 0,
        total_amount: service.total_amount || 0,
        mileage: service.mileage || 0,
        fuel_level: service.fuel_level || '1/2',
        observations: service.observations || '',
        internal_notes: service.internal_notes || '',
        warranty_months: service.warranty_months || 0,
        priority: service.priority || 'normal',
      });
    }
  }, [service]);

  const handleInputChange = (field: keyof CreateServiceData, value: any) => {
    setFormData((prev) => ({ ...prev, [field]: value }));

    // Clear error when user starts typing
    if (errors[field]) {
      setErrors((prev) => ({ ...prev, [field]: '' }));
    }
  };

  const validateForm = (): boolean => {
    const newErrors: Record<string, string> = {};

    if (!formData.service_center_id) {
      newErrors.service_center_id = 'Centro de serviço é obrigatório';
    }

    if (!formData.client_id) {
      newErrors.client_id = 'Cliente é obrigatório';
    }

    if (!formData.vehicle_id) {
      newErrors.vehicle_id = 'Veículo é obrigatório';
    }

    if (!formData.description.trim()) {
      newErrors.description = 'Descrição é obrigatória';
    }

    if (!formData.status_id) {
      newErrors.status_id = 'Status é obrigatório';
    }

    if (formData.labor_cost && formData.labor_cost < 0) {
      newErrors.labor_cost = 'Custo da mão de obra não pode ser negativo';
    }

    if (formData.discount && formData.discount < 0) {
      newErrors.discount = 'Desconto não pode ser negativo';
    }

    if (formData.mileage && formData.mileage < 0) {
      newErrors.mileage = 'Quilometragem não pode ser negativa';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    if (validateForm()) {
      onSubmit(formData);
    }
  };

  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL',
    }).format(value);
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      {/* Informações Básicas */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label
            htmlFor="service_center_id"
            className="block text-sm font-medium text-gray-700 mb-1"
          >
            Centro de Serviço *
          </label>
          <select
            id="service_center_id"
            value={formData.service_center_id}
            onChange={(e) =>
              handleInputChange('service_center_id', Number(e.target.value))
            }
            className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
              errors.service_center_id ? 'border-red-500' : 'border-gray-300'
            }`}
          >
            <option value="">Selecione um centro</option>
            {/* TODO: Carregar centros dinamicamente */}
            <option value={1}>Centro Principal</option>
            <option value={2}>Centro Zona Sul</option>
          </select>
          {errors.service_center_id && (
            <p className="mt-1 text-sm text-red-600">
              {errors.service_center_id}
            </p>
          )}
        </div>

        <div>
          <label
            htmlFor="client_id"
            className="block text-sm font-medium text-gray-700 mb-1"
          >
            Cliente *
          </label>
          <select
            id="client_id"
            value={formData.client_id}
            onChange={(e) =>
              handleInputChange('client_id', Number(e.target.value))
            }
            className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
              errors.client_id ? 'border-red-500' : 'border-gray-300'
            }`}
          >
            <option value="">Selecione um cliente</option>
            {/* TODO: Carregar clientes dinamicamente */}
            <option value={1}>João Silva</option>
            <option value={2}>Maria Santos</option>
          </select>
          {errors.client_id && (
            <p className="mt-1 text-sm text-red-600">{errors.client_id}</p>
          )}
        </div>

        <div>
          <label
            htmlFor="vehicle_id"
            className="block text-sm font-medium text-gray-700 mb-1"
          >
            Veículo *
          </label>
          <select
            id="vehicle_id"
            value={formData.vehicle_id}
            onChange={(e) =>
              handleInputChange('vehicle_id', Number(e.target.value))
            }
            className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
              errors.vehicle_id ? 'border-red-500' : 'border-gray-300'
            }`}
          >
            <option value="">Selecione um veículo</option>
            {/* TODO: Carregar veículos dinamicamente */}
            <option value={1}>Toyota Corolla</option>
            <option value={2}>Honda Civic</option>
          </select>
          {errors.vehicle_id && (
            <p className="mt-1 text-sm text-red-600">{errors.vehicle_id}</p>
          )}
        </div>

        <div>
          <label
            htmlFor="status_id"
            className="block text-sm font-medium text-gray-700 mb-1"
          >
            Status *
          </label>
          <select
            id="status_id"
            value={formData.status_id}
            onChange={(e) =>
              handleInputChange('status_id', Number(e.target.value))
            }
            className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
              errors.status_id ? 'border-red-500' : 'border-gray-300'
            }`}
          >
            <option value="">Selecione um status</option>
            {/* TODO: Carregar status dinamicamente */}
            <option value={1}>Pendente</option>
            <option value={2}>Em Andamento</option>
            <option value={3}>Concluído</option>
            <option value={4}>Cancelado</option>
          </select>
          {errors.status_id && (
            <p className="mt-1 text-sm text-red-600">{errors.status_id}</p>
          )}
        </div>
      </div>

      {/* Descrição */}
      <div>
        <label
          htmlFor="description"
          className="block text-sm font-medium text-gray-700 mb-1"
        >
          Descrição do Serviço *
        </label>
        <textarea
          id="description"
          value={formData.description}
          onChange={(e) => handleInputChange('description', e.target.value)}
          rows={3}
          className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
            errors.description ? 'border-red-500' : 'border-gray-300'
          }`}
          placeholder="Descreva o serviço a ser realizado..."
        />
        {errors.description && (
          <p className="mt-1 text-sm text-red-600">{errors.description}</p>
        )}
      </div>

      {/* Reclamação e Diagnóstico */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label
            htmlFor="complaint"
            className="block text-sm font-medium text-gray-700 mb-1"
          >
            Reclamação do Cliente
          </label>
          <textarea
            id="complaint"
            value={formData.complaint}
            onChange={(e) => handleInputChange('complaint', e.target.value)}
            rows={3}
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Descreva a reclamação do cliente..."
          />
        </div>

        <div>
          <label
            htmlFor="diagnosis"
            className="block text-sm font-medium text-gray-700 mb-1"
          >
            Diagnóstico
          </label>
          <textarea
            id="diagnosis"
            value={formData.diagnosis}
            onChange={(e) => handleInputChange('diagnosis', e.target.value)}
            rows={3}
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Descreva o diagnóstico técnico..."
          />
        </div>
      </div>

      {/* Solução */}
      <div>
        <label
          htmlFor="solution"
          className="block text-sm font-medium text-gray-700 mb-1"
        >
          Solução Aplicada
        </label>
        <textarea
          id="solution"
          value={formData.solution}
          onChange={(e) => handleInputChange('solution', e.target.value)}
          rows={3}
          className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Descreva a solução aplicada..."
        />
      </div>

      {/* Datas */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <label
            htmlFor="scheduled_date"
            className="block text-sm font-medium text-gray-700 mb-1"
          >
            Data Agendada
          </label>
          <input
            type="datetime-local"
            id="scheduled_date"
            value={formData.scheduled_date}
            onChange={(e) =>
              handleInputChange('scheduled_date', e.target.value)
            }
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div>
          <label
            htmlFor="started_at"
            className="block text-sm font-medium text-gray-700 mb-1"
          >
            Início do Serviço
          </label>
          <input
            type="datetime-local"
            id="started_at"
            value={formData.started_at}
            onChange={(e) => handleInputChange('started_at', e.target.value)}
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div>
          <label
            htmlFor="finished_at"
            className="block text-sm font-medium text-gray-700 mb-1"
          >
            Fim do Serviço
          </label>
          <input
            type="datetime-local"
            id="finished_at"
            value={formData.finished_at}
            onChange={(e) => handleInputChange('finished_at', e.target.value)}
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
      </div>

      {/* Técnico e Atendente */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label
            htmlFor="technician_id"
            className="block text-sm font-medium text-gray-700 mb-1"
          >
            Técnico Responsável
          </label>
          <select
            id="technician_id"
            value={formData.technician_id || ''}
            onChange={(e) =>
              handleInputChange(
                'technician_id',
                e.target.value ? Number(e.target.value) : undefined
              )
            }
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Selecione um técnico</option>
            {/* TODO: Carregar técnicos dinamicamente */}
            <option value={1}>Carlos Técnico</option>
            <option value={2}>Ana Especialista</option>
          </select>
        </div>

        <div>
          <label
            htmlFor="attendant_id"
            className="block text-sm font-medium text-gray-700 mb-1"
          >
            Atendente
          </label>
          <select
            id="attendant_id"
            value={formData.attendant_id || ''}
            onChange={(e) =>
              handleInputChange(
                'attendant_id',
                e.target.value ? Number(e.target.value) : undefined
              )
            }
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Selecione um atendente</option>
            {/* TODO: Carregar atendentes dinamicamente */}
            <option value={1}>João Atendente</option>
            <option value={2}>Maria Atendente</option>
          </select>
        </div>
      </div>

      {/* Valores */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <label
            htmlFor="labor_cost"
            className="block text-sm font-medium text-gray-700 mb-1"
          >
            Custo da Mão de Obra
          </label>
          <input
            type="number"
            id="labor_cost"
            value={formData.labor_cost}
            onChange={(e) =>
              handleInputChange('labor_cost', Number(e.target.value))
            }
            step="0.01"
            min="0"
            className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
              errors.labor_cost ? 'border-red-500' : 'border-gray-300'
            }`}
            placeholder="0,00"
          />
          {errors.labor_cost && (
            <p className="mt-1 text-sm text-red-600">{errors.labor_cost}</p>
          )}
        </div>

        <div>
          <label
            htmlFor="discount"
            className="block text-sm font-medium text-gray-700 mb-1"
          >
            Desconto
          </label>
          <input
            type="number"
            id="discount"
            value={formData.discount}
            onChange={(e) =>
              handleInputChange('discount', Number(e.target.value))
            }
            step="0.01"
            min="0"
            className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
              errors.discount ? 'border-red-500' : 'border-gray-300'
            }`}
            placeholder="0,00"
          />
          {errors.discount && (
            <p className="mt-1 text-sm text-red-600">{errors.discount}</p>
          )}
        </div>

        <div>
          <label
            htmlFor="total_amount"
            className="block text-sm font-medium text-gray-700 mb-1"
          >
            Valor Total
          </label>
          <input
            type="number"
            id="total_amount"
            value={formData.total_amount}
            onChange={(e) =>
              handleInputChange('total_amount', Number(e.target.value))
            }
            step="0.01"
            min="0"
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="0,00"
          />
        </div>
      </div>

      {/* Informações do Veículo */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <label
            htmlFor="mileage"
            className="block text-sm font-medium text-gray-700 mb-1"
          >
            Quilometragem
          </label>
          <input
            type="number"
            id="mileage"
            value={formData.mileage}
            onChange={(e) =>
              handleInputChange('mileage', Number(e.target.value))
            }
            min="0"
            className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
              errors.mileage ? 'border-red-500' : 'border-gray-300'
            }`}
            placeholder="0"
          />
          {errors.mileage && (
            <p className="mt-1 text-sm text-red-600">{errors.mileage}</p>
          )}
        </div>

        <div>
          <label
            htmlFor="fuel_level"
            className="block text-sm font-medium text-gray-700 mb-1"
          >
            Nível de Combustível
          </label>
          <select
            id="fuel_level"
            value={formData.fuel_level}
            onChange={(e) => handleInputChange('fuel_level', e.target.value)}
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="empty">Vazio</option>
            <option value="1/4">1/4</option>
            <option value="1/2">1/2</option>
            <option value="3/4">3/4</option>
            <option value="full">Cheio</option>
          </select>
        </div>

        <div>
          <label
            htmlFor="priority"
            className="block text-sm font-medium text-gray-700 mb-1"
          >
            Prioridade
          </label>
          <select
            id="priority"
            value={formData.priority}
            onChange={(e) => handleInputChange('priority', e.target.value)}
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="low">Baixa</option>
            <option value="normal">Normal</option>
            <option value="high">Alta</option>
            <option value="urgent">Urgente</option>
          </select>
        </div>
      </div>

      {/* Observações */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label
            htmlFor="observations"
            className="block text-sm font-medium text-gray-700 mb-1"
          >
            Observações (Cliente)
          </label>
          <textarea
            id="observations"
            value={formData.observations}
            onChange={(e) => handleInputChange('observations', e.target.value)}
            rows={3}
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Observações visíveis para o cliente..."
          />
        </div>

        <div>
          <label
            htmlFor="internal_notes"
            className="block text-sm font-medium text-gray-700 mb-1"
          >
            Notas Internas
          </label>
          <textarea
            id="internal_notes"
            value={formData.internal_notes}
            onChange={(e) =>
              handleInputChange('internal_notes', e.target.value)
            }
            rows={3}
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Notas internas da equipe..."
          />
        </div>
      </div>

      {/* Garantia */}
      <div>
        <label
          htmlFor="warranty_months"
          className="block text-sm font-medium text-gray-700 mb-1"
        >
          Garantia (meses)
        </label>
        <input
          type="number"
          id="warranty_months"
          value={formData.warranty_months}
          onChange={(e) =>
            handleInputChange('warranty_months', Number(e.target.value))
          }
          min="0"
          className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="0"
        />
      </div>

      {/* Botões */}
      <div className="flex justify-end space-x-3 pt-6 border-t">
        <button
          type="button"
          onClick={onCancel}
          className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          Cancelar
        </button>
        <button
          type="submit"
          disabled={loading}
          className="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {loading
            ? 'Salvando...'
            : service
              ? 'Atualizar Serviço'
              : 'Criar Serviço'}
        </button>
      </div>
    </form>
  );
};
