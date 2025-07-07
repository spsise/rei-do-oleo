import React, { useState } from 'react';
import type { Product, UpdateStockData } from '../../types/product';

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
    quantity: 0,
    type: 'add' as 'add' | 'subtract' | 'set',
  });

  const [errors, setErrors] = useState<Record<string, string>>({});

  const handleInputChange = (field: string, value: any) => {
    setFormData((prev) => ({ ...prev, [field]: value }));
    // Clear error when user starts typing
    if (errors[field]) {
      setErrors((prev) => ({ ...prev, [field]: '' }));
    }
  };

  const validateForm = (): boolean => {
    const newErrors: Record<string, string> = {};

    if (formData.quantity <= 0) {
      newErrors.quantity = 'Quantidade deve ser maior que zero';
    }

    if (
      formData.type === 'subtract' &&
      formData.quantity > product.stock_quantity
    ) {
      newErrors.quantity = `Não é possível subtrair mais que o estoque atual (${product.stock_quantity})`;
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    if (!validateForm()) {
      return;
    }

    onSubmit(formData);
  };

  const getNewStockQuantity = () => {
    switch (formData.type) {
      case 'add':
        return product.stock_quantity + formData.quantity;
      case 'subtract':
        return product.stock_quantity - formData.quantity;
      case 'set':
        return formData.quantity;
      default:
        return product.stock_quantity;
    }
  };

  const getOperationDescription = () => {
    switch (formData.type) {
      case 'add':
        return `Adicionar ${formData.quantity} ao estoque atual`;
      case 'subtract':
        return `Subtrair ${formData.quantity} do estoque atual`;
      case 'set':
        return `Definir estoque como ${formData.quantity}`;
      default:
        return '';
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      {/* Product Info */}
      <div className="bg-gray-50 p-4 rounded-lg">
        <h4 className="text-lg font-medium text-gray-900 mb-2">
          Informações do Produto
        </h4>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <span className="text-sm font-medium text-gray-500">Nome:</span>
            <p className="text-sm text-gray-900">{product.name}</p>
          </div>
          <div>
            <span className="text-sm font-medium text-gray-500">SKU:</span>
            <p className="text-sm text-gray-900">
              {product.sku || 'Não informado'}
            </p>
          </div>
          <div>
            <span className="text-sm font-medium text-gray-500">
              Estoque Atual:
            </span>
            <p className="text-sm text-gray-900">
              {product.stock_quantity} {product.unit && product.unit}
            </p>
          </div>
          <div>
            <span className="text-sm font-medium text-gray-500">
              Estoque Mínimo:
            </span>
            <p className="text-sm text-gray-900">
              {product.min_stock} {product.unit && product.unit}
            </p>
          </div>
        </div>
      </div>

      {/* Stock Update Form */}
      <div>
        <h4 className="text-lg font-medium text-gray-900 mb-4">
          Ajuste de Estoque
        </h4>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Tipo de Operação
            </label>
            <select
              value={formData.type}
              onChange={(e) => handleInputChange('type', e.target.value as any)}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="add">Adicionar ao estoque</option>
              <option value="subtract">Subtrair do estoque</option>
              <option value="set">Definir estoque</option>
            </select>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Quantidade *
            </label>
            <input
              type="number"
              min="1"
              value={formData.quantity}
              onChange={(e) =>
                handleInputChange('quantity', Number(e.target.value))
              }
              className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                errors.quantity ? 'border-red-500' : 'border-gray-300'
              }`}
              placeholder="0"
            />
            {errors.quantity && (
              <p className="text-red-500 text-xs mt-1">{errors.quantity}</p>
            )}
          </div>
        </div>

        {/* Preview */}
        {formData.quantity > 0 && (
          <div className="mt-4 bg-blue-50 border border-blue-200 rounded-md p-4">
            <h5 className="text-sm font-medium text-blue-800 mb-2">
              Resumo da Operação
            </h5>
            <div className="space-y-2 text-sm text-blue-700">
              <p>
                <strong>Operação:</strong> {getOperationDescription()}
              </p>
              <p>
                <strong>Estoque Atual:</strong> {product.stock_quantity}{' '}
                {product.unit && product.unit}
              </p>
              <p>
                <strong>Novo Estoque:</strong> {getNewStockQuantity()}{' '}
                {product.unit && product.unit}
              </p>

              {/* Stock Status Warning */}
              {getNewStockQuantity() <= 0 && (
                <p className="text-red-600 font-medium">
                  ⚠️ Atenção: O estoque ficará zerado após esta operação
                </p>
              )}

              {getNewStockQuantity() > 0 &&
                getNewStockQuantity() <= product.min_stock && (
                  <p className="text-yellow-600 font-medium">
                    ⚠️ Atenção: O estoque ficará abaixo ou igual ao mínimo
                  </p>
                )}
            </div>
          </div>
        )}
      </div>

      {/* Buttons */}
      <div className="flex justify-end space-x-3 pt-6 border-t">
        <button
          type="button"
          onClick={onCancel}
          className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          disabled={loading}
        >
          Cancelar
        </button>
        <button
          type="submit"
          disabled={loading || formData.quantity <= 0}
          className="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
        >
          {loading ? 'Atualizando...' : 'Atualizar Estoque'}
        </button>
      </div>
    </form>
  );
};
