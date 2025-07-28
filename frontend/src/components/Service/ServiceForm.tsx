import { TrashIcon } from '@heroicons/react/24/outline';
import React, { useEffect, useState } from 'react';
import { useServiceFormChanges } from '../../hooks/useFormChanges';
import type {
  CreateServiceData,
  CreateServiceItemData,
  Service,
  UpdateServiceData,
} from '../../types/service';
import { NoChangesToast } from '../ui/NoChangesToast';

interface Product {
  id: number;
  name: string;
  sku: string;
  price: number;
  category: { name: string };
}

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
    service_center_id: service?.service_center?.id ?? 0,
    client_id: service?.client?.id ?? 0,
    vehicle_id: service?.vehicle?.id ?? 0,
    description: service?.description ?? '',
    complaint: '',
    diagnosis: '',
    solution: '',
    scheduled_date: '',
    started_at: '',
    finished_at: '',
    technician_id: service?.technician?.id,
    attendant_id: service?.attendant?.id,
    status_id: service?.status?.id ?? 0,
    payment_method_id: service?.payment_method?.id ?? 0,
    labor_cost: service?.financial?.labor_cost ?? 0,
    discount: service?.financial?.discount ?? 0,
    total_amount: Number(service?.financial?.total_amount ?? 0),
    mileage: service?.vehicle?.mileage_at_service ?? 0,
    estimated_duration: service?.estimated_duration ?? 60,
    fuel_level:
      (service?.vehicle?.fuel_level as
        | 'empty'
        | '1/4'
        | '1/2'
        | '3/4'
        | 'full') ?? '1/2',
    observations: '',
    internal_notes: '',
    warranty_months: 0,
    items: [],
  });

  const [errors, setErrors] = useState<Record<string, string>>({});

  // Hook para detectar mudanças no formulário
  const { hasChanges, getChangedData } = useServiceFormChanges(
    (service || {}) as Record<string, unknown>,
    formData as unknown as Record<string, unknown>
  );

  // Estado para toast de mudanças
  const [showNoChangesToast, setShowNoChangesToast] = useState(false);

  // Estados para produtos
  const [showProductModal, setShowProductModal] = useState(false);
  const [products, setProducts] = useState<Product[]>([]);
  const [selectedProduct, setSelectedProduct] = useState<Product | null>(null);
  const [productQuantity, setProductQuantity] = useState(1);
  const [productNotes, setProductNotes] = useState('');

  useEffect(() => {
    if (service) {
      setFormData({
        service_center_id: service.service_center?.id ?? 0,
        client_id: service.client?.id ?? 0,
        vehicle_id: service.vehicle?.id ?? 0,
        description: service.description ?? '',
        complaint: service.complaint || '',
        diagnosis: service.diagnosis || '',
        solution: service.solution || '',
        scheduled_date: service.scheduled_date || '',
        started_at: service.started_at || '',
        finished_at: service.finished_at || '',
        technician_id: service.technician?.id,
        attendant_id: service.attendant?.id,
        status_id: service.status?.id ?? 0,
        payment_method_id: service.payment_method?.id ?? 0,
        labor_cost: service.financial?.labor_cost ?? 0,
        discount: service.financial?.discount ?? 0,
        total_amount: Number(service.financial?.total_amount ?? 0),
        mileage: service.vehicle?.mileage_at_service ?? 0,
        estimated_duration: service.estimated_duration || 60,
        fuel_level:
          (service?.vehicle?.fuel_level as
            | 'empty'
            | '1/4'
            | '1/2'
            | '3/4'
            | 'full') ?? '1/2',
        observations: service.observations || '',
        internal_notes: service.internal_notes || '',
        warranty_months: service.warranty_months || 0,
        items: [], // TODO: Implementar carregamento de items do serviço
      });
    }
  }, [service]);

  // Carregar produtos
  useEffect(() => {
    const loadProducts = async () => {
      try {
        // TODO: Implementar chamada para API de produtos
        // const response = await productService.getProducts();
        // setProducts(response.data || []);
        setProducts([
          {
            id: 1,
            name: 'Óleo de Motor 5W30',
            sku: 'OLEO001',
            price: 45.9,
            category: { name: 'Óleos' },
          },
          {
            id: 2,
            name: 'Filtro de Óleo',
            sku: 'FILTRO001',
            price: 25.5,
            category: { name: 'Filtros' },
          },
          {
            id: 3,
            name: 'Filtro de Ar',
            sku: 'FILTRO002',
            price: 35.0,
            category: { name: 'Filtros' },
          },
        ]);
      } catch (error) {
        console.error('Erro ao carregar produtos:', error);
      }
    };

    loadProducts();
  }, []);

  const handleInputChange = (
    field: keyof CreateServiceData,
    value: string | number | undefined
  ) => {
    setFormData((prev) => ({ ...prev, [field]: value }));

    // Clear error when user starts typing
    if (errors[field]) {
      setErrors((prev) => ({ ...prev, [field]: '' }));
    }
  };

  const addProductToService = (
    product: Product,
    quantity: number = 1,
    notes?: string
  ) => {
    const newItem: CreateServiceItemData = {
      product_id: product.id,
      quantity,
      unit_price: product.price,
      discount: 0,
      notes: notes || '',
    };

    setFormData((prev) => ({
      ...prev,
      items: [...(prev.items || []), newItem],
    }));
  };

  const removeProductFromService = (index: number) => {
    setFormData((prev) => ({
      ...prev,
      items: prev.items?.filter((_, i) => i !== index) || [],
    }));
  };

  const updateProductQuantity = (index: number, quantity: number) => {
    setFormData((prev) => ({
      ...prev,
      items:
        prev.items?.map((item, i) =>
          i === index ? { ...item, quantity } : item
        ) || [],
    }));
  };

  const updateProductPrice = (index: number, price: number) => {
    setFormData((prev) => ({
      ...prev,
      items:
        prev.items?.map((item, i) =>
          i === index ? { ...item, unit_price: price } : item
        ) || [],
    }));
  };

  const updateProductDiscount = (index: number, discount: number) => {
    setFormData((prev) => ({
      ...prev,
      items:
        prev.items?.map((item, i) =>
          i === index ? { ...item, discount } : item
        ) || [],
    }));
  };

  const updateProductNotes = (index: number, notes: string) => {
    setFormData((prev) => ({
      ...prev,
      items:
        prev.items?.map((item, i) =>
          i === index ? { ...item, notes } : item
        ) || [],
    }));
  };

  const calculateItemsTotal = () => {
    return (
      formData.items?.reduce((total, item) => {
        const subtotal = item.quantity * item.unit_price;
        const discountAmount = subtotal * ((item.discount || 0) / 100);
        return total + (subtotal - discountAmount);
      }, 0) || 0
    );
  };

  const calculateFinalTotal = () => {
    const itemsTotal = calculateItemsTotal();
    const laborCost = formData.labor_cost || 0;
    const discount = formData.discount || 0;
    return itemsTotal + laborCost - discount;
  };

  const handleAddProduct = () => {
    if (selectedProduct) {
      addProductToService(selectedProduct, productQuantity, productNotes);
      setSelectedProduct(null);
      setProductQuantity(1);
      setProductNotes('');
      setShowProductModal(false);
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

    if (!validateForm()) {
      return;
    }

    // Se for edição e não há mudanças, mostrar toast
    if (service && !hasChanges) {
      setShowNoChangesToast(true);
      setTimeout(() => setShowNoChangesToast(false), 3000);
      return;
    }

    // Atualizar total_amount com o cálculo final
    const finalData = {
      ...formData,
      total_amount: calculateFinalTotal(),
    };

    // Se for edição, enviar apenas os dados que mudaram
    if (service) {
      const changedData = getChangedData();
      if (Object.keys(changedData).length > 0) {
        onSubmit(changedData as UpdateServiceData);
      }
    } else {
      onSubmit(finalData);
    }
  };

  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL',
    }).format(price);
  };

  const getProductName = (productId: number) => {
    const product = products.find((p) => p.id === productId);
    return product?.name || 'Produto não encontrado';
  };

  return (
    <>
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
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
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
              htmlFor="estimated_duration"
              className="block text-sm font-medium text-gray-700 mb-1"
            >
              Duração Estimada (min)
            </label>
            <input
              type="number"
              id="estimated_duration"
              value={formData.estimated_duration}
              onChange={(e) =>
                handleInputChange('estimated_duration', Number(e.target.value))
              }
              min="15"
              max="480"
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="60"
            />
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
              onChange={(e) =>
                handleInputChange('observations', e.target.value)
              }
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

        {/* Itens do Serviço */}
        <div className="mt-6 border-t pt-6">
          <h3 className="text-lg font-medium text-gray-900 mb-4">
            Itens do Serviço
          </h3>
          {!formData.items || formData.items.length === 0 ? (
            <p className="text-sm text-gray-500">
              Nenhum item adicionado ainda.
            </p>
          ) : (
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                  <tr>
                    <th
                      scope="col"
                      className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Produto
                    </th>
                    <th
                      scope="col"
                      className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Quantidade
                    </th>
                    <th
                      scope="col"
                      className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Preço Unitário
                    </th>
                    <th
                      scope="col"
                      className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Desconto
                    </th>
                    <th
                      scope="col"
                      className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Subtotal
                    </th>
                    <th
                      scope="col"
                      className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Observações
                    </th>
                    <th
                      scope="col"
                      className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Ações
                    </th>
                  </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                  {formData.items.map((item, index) => (
                    <tr key={index}>
                      <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {getProductName(item.product_id)}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <input
                          type="number"
                          value={item.quantity}
                          onChange={(e) =>
                            updateProductQuantity(index, Number(e.target.value))
                          }
                          min="1"
                          className="w-16 px-2 py-1 border border-gray-300 rounded-md text-center"
                        />
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <input
                          type="number"
                          value={item.unit_price}
                          onChange={(e) =>
                            updateProductPrice(index, Number(e.target.value))
                          }
                          step="0.01"
                          min="0"
                          className="w-24 px-2 py-1 border border-gray-300 rounded-md text-right"
                        />
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <input
                          type="number"
                          value={item.discount || 0}
                          onChange={(e) =>
                            updateProductDiscount(index, Number(e.target.value))
                          }
                          step="0.01"
                          min="0"
                          className="w-16 px-2 py-1 border border-gray-300 rounded-md text-right"
                        />
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {formatPrice(item.quantity * item.unit_price)}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <textarea
                          value={item.notes || ''}
                          onChange={(e) =>
                            updateProductNotes(index, e.target.value)
                          }
                          rows={1}
                          className="w-full px-2 py-1 border border-gray-300 rounded-md"
                        />
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button
                          type="button"
                          onClick={() => removeProductFromService(index)}
                          className="text-red-600 hover:text-red-900"
                        >
                          <TrashIcon className="h-5 w-5" />
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
          <button
            type="button"
            onClick={() => setShowProductModal(true)}
            className="mt-4 px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Adicionar Produto
          </button>
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
            disabled={loading || (service && !hasChanges)}
            className={`px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed ${
              service && !hasChanges
                ? 'bg-gray-400 cursor-not-allowed'
                : 'bg-blue-600 hover:bg-blue-700'
            }`}
          >
            {loading
              ? 'Salvando...'
              : service
                ? hasChanges
                  ? 'Atualizar Serviço'
                  : 'Nenhuma Alteração'
                : 'Criar Serviço'}
          </button>
        </div>
      </form>

      {/* Modal de Seleção de Produtos */}
      {showProductModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 className="text-lg font-medium text-gray-900 mb-4">
              Adicionar Produto
            </h3>

            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Produto
                </label>
                <select
                  value={selectedProduct?.id || ''}
                  onChange={(e) => {
                    const product = products.find(
                      (p) => p.id === Number(e.target.value)
                    );
                    setSelectedProduct(product || null);
                  }}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="">Selecione um produto</option>
                  {products.map((product) => (
                    <option key={product.id} value={product.id}>
                      {product.name} - {formatPrice(product.price)}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Quantidade
                </label>
                <input
                  type="number"
                  value={productQuantity}
                  onChange={(e) => setProductQuantity(Number(e.target.value))}
                  min="1"
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Observações
                </label>
                <textarea
                  value={productNotes}
                  onChange={(e) => setProductNotes(e.target.value)}
                  rows={2}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Observações sobre o produto..."
                />
              </div>
            </div>

            <div className="flex justify-end space-x-3 mt-6">
              <button
                type="button"
                onClick={() => setShowProductModal(false)}
                className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Cancelar
              </button>
              <button
                type="button"
                onClick={handleAddProduct}
                disabled={!selectedProduct}
                className="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Adicionar
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Toast para quando não há mudanças */}
      <NoChangesToast
        isVisible={showNoChangesToast}
        onClose={() => setShowNoChangesToast(false)}
      />
    </>
  );
};
