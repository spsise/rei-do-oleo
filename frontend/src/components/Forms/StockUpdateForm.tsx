import React, { useState } from 'react';
import type { Product, UpdateStockData } from '../../types/product';
import { getDisplaySku } from '../../utils/sku';

interface StockUpdateFormProps {
  product: Product;
  onSubmit: (data: UpdateStockData) => void;
  onCancel: () => void;
  loading?: boolean;
}

export const StockUpdateForm: React.FC<StockUpdateFormProps> = ({
  product,
  onSubmit,
  onCancel,
  loading = false,
}) => {
  const [formData, setFormData] = useState({
    quantity: product.stock_quantity,
    type: 'set' as 'add' | 'subtract' | 'set',
    reason: '',
  });

  const [errors, setErrors] = useState<Record<string, string>>({});

  const handleInputChange = (field: string, value: string | number) => {
    // Se for o campo 'type', resetar a quantidade para um valor padrão
    if (field === 'type') {
      let newQuantity = 1;
      if (value === 'set') {
        newQuantity = product.stock_quantity;
      }
      setFormData((prev) => ({
        ...prev,
        type: value as 'add' | 'subtract' | 'set',
        quantity: newQuantity,
      }));
      if (errors['quantity']) {
        setErrors((prev) => ({ ...prev, quantity: '' }));
      }
      return;
    }
    // Garantir que a quantidade seja sempre número
    if (field === 'quantity') {
      const num = typeof value === 'string' ? Number(value) : value;
      setFormData((prev) => ({ ...prev, quantity: isNaN(num) ? 0 : num }));
      if (errors['quantity']) {
        setErrors((prev) => ({ ...prev, quantity: '' }));
      }
      return;
    }
    setFormData((prev) => ({ ...prev, [field]: value }));
    if (errors[field]) {
      setErrors((prev) => ({ ...prev, [field]: '' }));
    }
  };

  const validateForm = (): boolean => {
    const newErrors: Record<string, string> = {};

    if (
      formData.type === 'subtract' &&
      formData.quantity > product.stock_quantity
    ) {
      newErrors.quantity = `Não é possível subtrair mais que o estoque atual (${product.stock_quantity})`;
    }

    if (formData.quantity < 0) {
      newErrors.quantity = 'Quantidade não pode ser negativa';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    if (!validateForm()) {
      return;
    }

    const submitData: UpdateStockData = {
      quantity: formData.quantity,
      type: formData.type,
      reason: formData.reason.trim() || undefined,
    };

    onSubmit(submitData);
  };

  const getNewStockQuantity = (): number => {
    switch (formData.type) {
      case 'add':
        return product.stock_quantity + formData.quantity;
      case 'subtract':
        return Math.max(0, product.stock_quantity - formData.quantity);
      case 'set':
        return formData.quantity;
      default:
        return product.stock_quantity;
    }
  };

  const getOperationIcon = () => {
    switch (formData.type) {
      case 'add':
        return (
          <svg
            className="w-4 h-4 text-green-600"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M12 6v6m0 0v6m0-6h6m-6 0H6"
            />
          </svg>
        );
      case 'subtract':
        return (
          <svg
            className="w-4 h-4 text-red-600"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M20 12H4"
            />
          </svg>
        );
      case 'set':
        return (
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
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
        );
    }
  };

  const getOperationColor = () => {
    switch (formData.type) {
      case 'add':
        return 'border-green-200 bg-green-50';
      case 'subtract':
        return 'border-red-200 bg-red-50';
      case 'set':
        return 'border-blue-200 bg-blue-50';
    }
  };

  return (
    <form onSubmit={handleSubmit} className="h-full flex flex-col">
      <div className="flex-1 grid grid-cols-1 lg:grid-cols-3 gap-6 min-h-0">
        {/* Left Column - Product Info & Form */}
        <div className="lg:col-span-2 space-y-4">
          {/* Product Info - Compact */}
          <div className="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4">
            <div className="flex items-center justify-between mb-3">
              <h4 className="text-lg font-semibold text-gray-900">
                {product.name}
              </h4>
              <span
                className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${
                  product.active
                    ? 'bg-green-100 text-green-800'
                    : 'bg-red-100 text-red-800'
                }`}
              >
                {product.active ? 'Ativo' : 'Inativo'}
              </span>
            </div>

            <div className="grid grid-cols-3 gap-4 text-sm">
              <div>
                <span className="text-gray-500">SKU:</span>
                <p className="font-mono text-gray-900 bg-white px-2 py-1 rounded border text-xs">
                  {getDisplaySku(product.sku)}
                </p>
              </div>
              <div>
                <span className="text-gray-500">Estoque Atual:</span>
                <p className="font-bold text-gray-900">
                  {product.stock_quantity} {product.unit}
                </p>
              </div>
              <div>
                <span className="text-gray-500">Estoque Mínimo:</span>
                <p className="text-gray-700">
                  {product.min_stock} {product.unit}
                </p>
              </div>
            </div>
          </div>

          {/* Stock Update Form - Compact */}
          <div className="bg-white border border-gray-200 rounded-lg p-4">
            <h5 className="text-base font-semibold text-gray-900 mb-4">
              Ajuste de Estoque
            </h5>

            <div className="space-y-4">
              {/* Operation Type - Horizontal */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Tipo de Operação *
                </label>
                <div className="flex space-x-2">
                  {[
                    {
                      value: 'add',
                      label: 'Adicionar',
                      shortLabel: '+',
                    },
                    {
                      value: 'subtract',
                      label: 'Subtrair',
                      shortLabel: '-',
                    },
                    {
                      value: 'set',
                      label: 'Definir',
                      shortLabel: '=',
                    },
                  ].map((option) => (
                    <button
                      key={option.value}
                      type="button"
                      onClick={() =>
                        handleInputChange(
                          'type',
                          option.value as 'add' | 'subtract' | 'set'
                        )
                      }
                      className={`flex-1 py-2 px-3 border-2 rounded-md text-sm font-medium transition-all duration-200 ${
                        formData.type === option.value
                          ? 'border-blue-500 bg-blue-50 text-blue-700'
                          : 'border-gray-200 bg-gray-50 text-gray-600 hover:border-gray-300 hover:bg-gray-100'
                      }`}
                    >
                      <span className="hidden sm:inline">{option.label}</span>
                      <span className="sm:hidden">{option.shortLabel}</span>
                    </button>
                  ))}
                </div>
              </div>

              {/* Quantity & Reason - Side by side */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Quantidade *
                  </label>
                  <div className="relative">
                    <input
                      type="number"
                      min="0"
                      step="1"
                      value={formData.quantity}
                      onChange={(e) =>
                        handleInputChange('quantity', e.target.value)
                      }
                      className={`w-full pr-3 pl-3 py-2 border-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base font-medium ${
                        errors.quantity
                          ? 'border-red-500 bg-red-50'
                          : 'border-gray-300'
                      }`}
                      placeholder="0"
                    />
                    <div className="absolute right-7 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm pointer-events-none">
                      {product.unit}
                    </div>
                  </div>
                  {errors.quantity && (
                    <p className="text-red-500 text-xs mt-1 flex items-center">
                      <svg
                        className="w-3 h-3 mr-1"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                      >
                        <path
                          fillRule="evenodd"
                          d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                          clipRule="evenodd"
                        />
                      </svg>
                      {errors.quantity}
                    </p>
                  )}
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Motivo (opcional)
                  </label>
                  <input
                    type="text"
                    value={formData.reason}
                    onChange={(e) =>
                      handleInputChange('reason', e.target.value)
                    }
                    className="w-full px-3 py-2 border-2 border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                    placeholder="Ex: Ajuste de inventário"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Right Column - Preview & Actions */}
        <div className="space-y-4">
          {/* Preview Card - Compact */}
          <div
            className={`border-2 rounded-lg p-4 ${getOperationColor()} h-fit`}
          >
            <div className="flex items-center justify-between mb-3">
              <h5 className="text-base font-semibold text-gray-900">Resumo</h5>
              {getOperationIcon()}
            </div>

            <div className="space-y-3">
              <div className="flex justify-between items-center">
                <span className="text-sm text-gray-600">Atual:</span>
                <span className="font-semibold text-gray-900">
                  {product.stock_quantity} {product.unit}
                </span>
              </div>

              <div className="flex justify-between items-center">
                <span className="text-sm text-gray-600">Operação:</span>
                <span className="font-semibold text-gray-900">
                  {formData.type === 'add' && '+'}
                  {formData.type === 'subtract' && '-'}
                  {formData.type === 'set' && '='}
                  {formData.quantity} {product.unit}
                </span>
              </div>

              <div className="border-t pt-2">
                <div className="flex justify-between items-center">
                  <span className="text-sm font-medium text-gray-700">
                    Novo:
                  </span>
                  <span className="text-lg font-bold text-blue-600">
                    {getNewStockQuantity()} {product.unit}
                  </span>
                </div>
              </div>
            </div>

            {/* Warning Messages - Compact */}
            {getNewStockQuantity() <= 0 && (
              <div className="mt-3 p-2 bg-red-100 border border-red-300 rounded text-xs">
                <div className="flex items-center text-red-800">
                  <svg
                    className="w-3 h-3 mr-1 flex-shrink-0"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path
                      fillRule="evenodd"
                      d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                      clipRule="evenodd"
                    />
                  </svg>
                  <span>Estoque ficará zerado</span>
                </div>
              </div>
            )}

            {getNewStockQuantity() > 0 &&
              getNewStockQuantity() <= product.min_stock && (
                <div className="mt-3 p-2 bg-yellow-100 border border-yellow-300 rounded text-xs">
                  <div className="flex items-center text-yellow-800">
                    <svg
                      className="w-3 h-3 mr-1 flex-shrink-0"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        fillRule="evenodd"
                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                        clipRule="evenodd"
                      />
                    </svg>
                    <span>Abaixo do mínimo</span>
                  </div>
                </div>
              )}
          </div>

          {/* Action Buttons - Compact */}
          <div className="space-y-2">
            <button
              type="submit"
              disabled={loading}
              className="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 border-2 border-blue-600 rounded-md hover:bg-blue-700 hover:border-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200 disabled:opacity-50 flex items-center justify-center"
            >
              {loading ? (
                <>
                  <svg
                    className="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                  >
                    <circle
                      className="opacity-25"
                      cx="12"
                      cy="12"
                      r="10"
                      stroke="currentColor"
                      strokeWidth="4"
                    ></circle>
                    <path
                      className="opacity-75"
                      fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    ></path>
                  </svg>
                  Atualizando...
                </>
              ) : (
                'Atualizar Estoque'
              )}
            </button>
            <button
              type="button"
              onClick={onCancel}
              disabled={loading}
              className="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border-2 border-gray-300 rounded-md hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200 disabled:opacity-50"
            >
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </form>
  );
};
