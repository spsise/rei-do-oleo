import {
  ArrowsPointingInIcon,
  ArrowsPointingOutIcon,
  CalendarIcon,
  ClockIcon,
  CurrencyDollarIcon,
  DocumentTextIcon,
  PlusIcon,
  ShoppingCartIcon,
  TruckIcon,
  XMarkIcon,
} from '@heroicons/react/24/outline';
import React, { useState } from 'react';
import '../../styles/Technician.css';
import {
  type CreateTechnicianServiceData,
  type TechnicianProduct,
  type TechnicianVehicle,
} from '../../types/technician';
import { ProductSelectionModal } from './ProductSelectionModal';
import { ServiceItemsList } from './ServiceItemsList';

interface NewServiceModalProps {
  isOpen: boolean;
  onClose: () => void;
  serviceData: CreateTechnicianServiceData;
  onServiceDataChange: (data: CreateTechnicianServiceData) => void;
  vehicles: TechnicianVehicle[];
  onSubmit: () => void;
  isLoading?: boolean;
  // Props para produtos
  products: TechnicianProduct[];
  categories: Array<{ id: number; name: string }>;
  isLoadingProducts: boolean;
  productSearchTerm: string;
  onProductSearch: (search: string) => void;
  onAddProduct: (
    product: TechnicianProduct,
    quantity: number,
    notes?: string
  ) => void;
  onRemoveProduct: (productId: number) => void;
  onUpdateProductQuantity: (productId: number, quantity: number) => void;
  onUpdateProductPrice: (productId: number, price: number) => void;
  onUpdateProductNotes: (productId: number, notes: string) => void;
  calculateItemsTotal: () => number;
  calculateFinalTotal: () => number;
}

export const NewServiceModal: React.FC<NewServiceModalProps> = ({
  isOpen,
  onClose,
  serviceData,
  onServiceDataChange,
  vehicles,
  onSubmit,
  isLoading = false,
  products,
  categories,
  isLoadingProducts,
  productSearchTerm,
  onProductSearch,
  onAddProduct,
  onRemoveProduct,
  onUpdateProductQuantity,
  onUpdateProductPrice,
  onUpdateProductNotes,
  calculateItemsTotal,
  calculateFinalTotal,
}) => {
  const [activeTab, setActiveTab] = useState<'details' | 'products'>('details');
  const [showProductSelection, setShowProductSelection] = useState(false);
  const [isMaximized, setIsMaximized] = useState(false);

  const formatLicensePlate = (plate: string) => {
    if (!plate) return 'N/A';
    return plate.replace(/([A-Z]{3})(\d{4})/, '$1-$2');
  };

  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL',
    }).format(price);
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50 animate-modalFadeIn">
      <div
        className={`bg-white rounded-2xl shadow-2xl overflow-hidden animate-modalSlideInUp transition-all duration-300 ${
          isMaximized
            ? 'w-full h-full max-w-none max-h-none rounded-none'
            : 'w-full max-w-6xl max-h-[90vh]'
        }`}
      >
        {/* Header */}
        <div className="sticky top-0 bg-white rounded-t-2xl p-6 border-b border-gray-100 z-10">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              <div className="p-3 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl shadow-lg">
                <PlusIcon className="h-7 w-7 text-white" />
              </div>
              <div>
                <h3 className="text-2xl font-bold text-gray-900">
                  Novo Serviço
                </h3>
                <p className="text-gray-600 text-sm">
                  Registre um novo serviço para o cliente
                </p>
              </div>
            </div>
            <div className="flex items-center gap-2">
              <button
                onClick={() => setIsMaximized(!isMaximized)}
                className="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                title={isMaximized ? 'Restaurar' : 'Maximizar'}
              >
                {isMaximized ? (
                  <ArrowsPointingInIcon className="h-5 w-5" />
                ) : (
                  <ArrowsPointingOutIcon className="h-5 w-5" />
                )}
              </button>
              <button
                onClick={onClose}
                disabled={isLoading}
                className="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors disabled:opacity-50"
              >
                <XMarkIcon className="h-6 w-6" />
              </button>
            </div>
          </div>

          {/* Tabs */}
          <div className="flex space-x-1 mt-6">
            <button
              onClick={() => setActiveTab('details')}
              className={`flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors ${
                activeTab === 'details'
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
              }`}
            >
              <DocumentTextIcon className="h-4 w-4" />
              Detalhes do Serviço
            </button>
            <button
              onClick={() => setActiveTab('products')}
              className={`flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors ${
                activeTab === 'products'
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
              }`}
            >
              <ShoppingCartIcon className="h-4 w-4" />
              Produtos ({serviceData.items?.length || 0})
            </button>
          </div>
        </div>

        {/* Content */}
        <div
          className={`p-6 ${isMaximized ? 'h-[calc(100vh-200px)] overflow-y-auto' : 'max-h-[60vh] overflow-y-auto'}`}
        >
          {activeTab === 'details' ? (
            <div className="space-y-6">
              {/* Seleção de Veículo */}
              <div className="space-y-3">
                <label className="block text-sm font-semibold text-gray-700 flex items-center gap-2">
                  <TruckIcon className="h-4 w-4 text-blue-600" />
                  Veículo
                </label>
                <div className="relative">
                  <select
                    value={serviceData.vehicle_id}
                    onChange={(e) =>
                      onServiceDataChange({
                        ...serviceData,
                        vehicle_id: Number(e.target.value),
                      })
                    }
                    className="w-full px-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/80 backdrop-blur-sm transition-all duration-200 shadow-sm hover:shadow-md appearance-none"
                  >
                    <option value={0}>Selecione um veículo</option>
                    {vehicles?.map((vehicle) => (
                      <option
                        key={vehicle.id || `vehicle-${Math.random()}`}
                        value={vehicle.id || 0}
                      >
                        {vehicle.brand || 'N/A'} {vehicle.model || 'N/A'} -{' '}
                        {formatLicensePlate(vehicle.license_plate || '')}
                      </option>
                    ))}
                  </select>
                  <div className="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <svg
                      className="h-4 w-4 text-gray-400"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M19 9l-7 7-7-7"
                      />
                    </svg>
                  </div>
                </div>
              </div>

              {/* Descrição do Serviço */}
              <div className="space-y-3">
                <label className="block text-sm font-semibold text-gray-700">
                  Descrição do Serviço
                </label>
                <textarea
                  value={serviceData.description}
                  onChange={(e) =>
                    onServiceDataChange({
                      ...serviceData,
                      description: e.target.value,
                    })
                  }
                  placeholder="Descreva o serviço a ser realizado..."
                  className="w-full px-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/80 backdrop-blur-sm resize-none transition-all duration-200 shadow-sm hover:shadow-md"
                  rows={3}
                />
              </div>

              {/* Grid de Campos */}
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {/* Duração Estimada */}
                <div className="space-y-3">
                  <label className="block text-sm font-semibold text-gray-700 flex items-center gap-2">
                    <ClockIcon className="h-4 w-4 text-purple-600" />
                    Duração Estimada (min)
                  </label>
                  <input
                    type="number"
                    value={serviceData.estimated_duration}
                    onChange={(e) =>
                      onServiceDataChange({
                        ...serviceData,
                        estimated_duration: Number(e.target.value),
                      })
                    }
                    min="15"
                    step="15"
                    className="w-full px-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/80 backdrop-blur-sm transition-all duration-200 shadow-sm hover:shadow-md"
                  />
                </div>

                {/* Quilometragem */}
                <div className="space-y-3">
                  <label className="block text-sm font-semibold text-gray-700 flex items-center gap-2">
                    <TruckIcon className="h-4 w-4 text-gray-600" />
                    Quilometragem
                  </label>
                  <input
                    type="number"
                    value={serviceData.mileage_at_service || ''}
                    onChange={(e) =>
                      onServiceDataChange({
                        ...serviceData,
                        mileage_at_service: e.target.value
                          ? Number(e.target.value)
                          : undefined,
                      })
                    }
                    min="0"
                    placeholder="Ex: 50000"
                    className="w-full px-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/80 backdrop-blur-sm transition-all duration-200 shadow-sm hover:shadow-md"
                  />
                </div>

                {/* Data de Agendamento */}
                <div className="space-y-3">
                  <label className="block text-sm font-semibold text-gray-700 flex items-center gap-2">
                    <CalendarIcon className="h-4 w-4 text-green-600" />
                    Data de Agendamento
                  </label>
                  <input
                    type="datetime-local"
                    value={serviceData.scheduled_at || ''}
                    onChange={(e) =>
                      onServiceDataChange({
                        ...serviceData,
                        scheduled_at: e.target.value || undefined,
                      })
                    }
                    className="w-full px-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/80 backdrop-blur-sm transition-all duration-200 shadow-sm hover:shadow-md"
                  />
                </div>

                {/* Valor Total */}
                <div className="space-y-3">
                  <label className="block text-sm font-semibold text-gray-700 flex items-center gap-2">
                    <CurrencyDollarIcon className="h-4 w-4 text-green-600" />
                    Valor Total (R$)
                  </label>
                  <input
                    type="number"
                    value={serviceData.total_amount || ''}
                    onChange={(e) =>
                      onServiceDataChange({
                        ...serviceData,
                        total_amount: e.target.value
                          ? Number(e.target.value)
                          : undefined,
                      })
                    }
                    min="0"
                    step="0.01"
                    placeholder="0.00"
                    className="w-full px-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/80 backdrop-blur-sm transition-all duration-200 shadow-sm hover:shadow-md"
                  />
                </div>

                {/* Desconto */}
                <div className="space-y-3">
                  <label className="block text-sm font-semibold text-gray-700 flex items-center gap-2">
                    <CurrencyDollarIcon className="h-4 w-4 text-red-600" />
                    Desconto (R$)
                  </label>
                  <input
                    type="number"
                    value={serviceData.discount_amount || ''}
                    onChange={(e) =>
                      onServiceDataChange({
                        ...serviceData,
                        discount_amount: e.target.value
                          ? Number(e.target.value)
                          : undefined,
                      })
                    }
                    min="0"
                    step="0.01"
                    placeholder="0.00"
                    className="w-full px-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/80 backdrop-blur-sm transition-all duration-200 shadow-sm hover:shadow-md"
                  />
                </div>
              </div>

              {/* Observações */}
              <div className="space-y-3">
                <label className="block text-sm font-semibold text-gray-700 flex items-center gap-2">
                  <DocumentTextIcon className="h-4 w-4 text-blue-600" />
                  Observações Adicionais
                </label>
                <textarea
                  value={serviceData.notes || ''}
                  onChange={(e) =>
                    onServiceDataChange({
                      ...serviceData,
                      notes: e.target.value || undefined,
                    })
                  }
                  placeholder="Observações, instruções especiais ou detalhes adicionais..."
                  className="w-full px-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/80 backdrop-blur-sm resize-none transition-all duration-200 shadow-sm hover:shadow-md"
                  rows={3}
                />
              </div>

              {/* Observações Detalhadas */}
              <div className="space-y-3">
                <label className="block text-sm font-semibold text-gray-700 flex items-center gap-2">
                  <DocumentTextIcon className="h-4 w-4 text-purple-600" />
                  Observações Detalhadas
                </label>
                <textarea
                  value={serviceData.observations || ''}
                  onChange={(e) =>
                    onServiceDataChange({
                      ...serviceData,
                      observations: e.target.value || undefined,
                    })
                  }
                  placeholder="Observações detalhadas sobre o serviço, diagnóstico, solução aplicada..."
                  className="w-full px-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/80 backdrop-blur-sm resize-none transition-all duration-200 shadow-sm hover:shadow-md"
                  rows={3}
                />
              </div>

              {/* Dicas */}
              <div className="p-4 bg-blue-50/50 rounded-xl border border-blue-100">
                <div className="flex items-start gap-3">
                  <div className="p-1.5 bg-blue-100 rounded-lg">
                    <svg
                      className="w-4 h-4 text-blue-600"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                      />
                    </svg>
                  </div>
                  <div className="text-sm text-blue-800">
                    <p className="font-medium mb-1">
                      💡 Dicas para um bom registro:
                    </p>
                    <ul className="space-y-1 text-xs">
                      <li>• Seja específico na descrição do serviço</li>
                      <li>
                        • Estime a duração com precisão para melhor planejamento
                      </li>
                      <li>• Registre a quilometragem atual do veículo</li>
                      <li>• Use observações para detalhes importantes</li>
                      <li>• Agende o serviço se necessário</li>
                      <li>• Adicione produtos utilizados na aba "Produtos"</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          ) : (
            <div className="space-y-6">
              {/* Lista de Itens (Carrinho) */}
              <ServiceItemsList
                items={serviceData.items || []}
                onRemoveItem={onRemoveProduct}
                onUpdateQuantity={onUpdateProductQuantity}
                onUpdatePrice={onUpdateProductPrice}
                onUpdateNotes={onUpdateProductNotes}
                onAddProduct={() => setShowProductSelection(true)}
                isLoading={isLoading}
              />

              {/* Modal de Seleção de Produtos */}
              <ProductSelectionModal
                isOpen={showProductSelection}
                onClose={() => setShowProductSelection(false)}
                products={products}
                categories={categories}
                isLoading={isLoadingProducts}
                searchTerm={productSearchTerm}
                onSearch={onProductSearch}
                onAddProduct={onAddProduct}
                selectedProductIds={
                  serviceData.items?.map((item) => item.product_id) || []
                }
              />

              {/* Resumo Financeiro */}
              <div className="p-4 bg-gray-50 rounded-xl space-y-3">
                <h4 className="font-semibold text-gray-900">
                  Resumo Financeiro
                </h4>
                <div className="space-y-2">
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-600">Total dos Produtos:</span>
                    <span className="font-medium">
                      {formatPrice(calculateItemsTotal())}
                    </span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-600">Desconto:</span>
                    <span className="font-medium text-red-600">
                      -{formatPrice(serviceData.discount_amount || 0)}
                    </span>
                  </div>
                  <div className="border-t pt-2 flex justify-between font-semibold text-lg">
                    <span className="text-gray-900">Total Final:</span>
                    <span className="text-green-600">
                      {formatPrice(calculateFinalTotal())}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          )}
        </div>

        {/* Footer */}
        <div className="sticky bottom-0 bg-white rounded-b-2xl p-6 border-t border-gray-100">
          <div className="flex justify-between items-center">
            <div className="text-sm text-gray-600">
              {activeTab === 'products' && (
                <span>
                  Total:{' '}
                  <span className="font-semibold text-green-600">
                    {formatPrice(calculateFinalTotal())}
                  </span>
                </span>
              )}
            </div>
            <div className="flex gap-3">
              <button
                onClick={onClose}
                disabled={isLoading}
                className="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 font-medium disabled:opacity-50"
              >
                Cancelar
              </button>
              <button
                onClick={onSubmit}
                disabled={
                  isLoading ||
                  !serviceData.vehicle_id ||
                  !serviceData.description.trim()
                }
                className="px-8 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 disabled:transform-none disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-3"
              >
                {isLoading ? (
                  <>
                    <div className="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent"></div>
                    <span>Salvando...</span>
                  </>
                ) : (
                  <>
                    <PlusIcon className="h-5 w-5" />
                    <span>Salvar Serviço</span>
                  </>
                )}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
