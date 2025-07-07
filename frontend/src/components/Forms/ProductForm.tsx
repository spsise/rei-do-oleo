import React, { useState } from 'react';
import { useCategories } from '../../hooks/useCategories';
import type {
  CreateProductData,
  Product,
  UpdateProductData,
} from '../../types/product';

interface ProductFormProps {
  product?: Product;
  onSubmit: (data: CreateProductData | UpdateProductData) => void;
  onCancel: () => void;
  loading?: boolean;
  backendErrors?: Record<string, string[]>;
}

export const ProductForm: React.FC<ProductFormProps> = ({
  product,
  onSubmit,
  onCancel,
  loading = false,
  backendErrors = {},
}) => {
  const { data: categoriesData } = useCategories();
  const categories = categoriesData || [];

  const [formData, setFormData] = useState({
    name: product?.name || '',
    description: product?.description || '',
    sku: product?.sku || '',
    barcode: product?.barcode || '',
    price: product?.price || 0,
    stock_quantity: product?.stock_quantity || 0,
    min_stock: product?.min_stock || 0,
    unit: product?.unit || '',
    brand: product?.brand || '',
    supplier: product?.supplier || '',
    location: product?.location || '',
    weight: product?.weight || 0,
    dimensions: product?.dimensions || '',
    warranty_months: product?.warranty_months || 0,
    active: product?.active ?? true,
    featured: product?.featured || false,
    observations: product?.observations || '',
    category_id: product?.category_id || 0,
  });

  const [errors, setErrors] = useState<Record<string, string>>({});

  // Função para obter erro de um campo (prioriza erros do backend)
  const getFieldError = (fieldName: string): string => {
    // Primeiro verifica se há erro do backend
    if (backendErrors[fieldName] && backendErrors[fieldName].length > 0) {
      return backendErrors[fieldName][0];
    }
    // Depois verifica erros locais
    return errors[fieldName] || '';
  };

  // Função para verificar se um campo tem erro
  const hasFieldError = (fieldName: string): boolean => {
    return !!(backendErrors[fieldName]?.length || errors[fieldName]);
  };

  const handleInputChange = (
    field: string,
    value: string | number | boolean
  ) => {
    setFormData((prev) => ({ ...prev, [field]: value }));
    // Clear error when user starts typing
    if (errors[field] || backendErrors[field]) {
      setErrors((prev) => ({ ...prev, [field]: '' }));
    }
  };

  const validateForm = (): boolean => {
    const newErrors: Record<string, string> = {};

    if (!formData.name.trim()) {
      newErrors.name = 'Nome é obrigatório';
    }

    if (formData.price <= 0) {
      newErrors.price = 'Preço deve ser maior que zero';
    }

    if (formData.stock_quantity < 0) {
      newErrors.stock_quantity = 'Quantidade em estoque não pode ser negativa';
    }

    if (formData.min_stock < 0) {
      newErrors.min_stock = 'Estoque mínimo não pode ser negativo';
    }

    if (formData.category_id === 0) {
      newErrors.category_id = 'Categoria é obrigatória';
    }

    if (!formData.sku.trim()) {
      newErrors.sku = 'SKU é obrigatório';
    }

    if (!formData.unit.trim()) {
      newErrors.unit = 'Unidade é obrigatória';
    }

    if (formData.weight < 0) {
      newErrors.weight = 'Peso não pode ser negativo';
    }

    if (formData.warranty_months < 0) {
      newErrors.warranty_months = 'Garantia não pode ser negativa';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    if (!validateForm()) {
      return;
    }

    // Remove empty strings and convert to proper types
    const submitData = Object.fromEntries(
      Object.entries(formData).map(([key, value]) => {
        if (typeof value === 'string' && value.trim() === '') {
          return [key, undefined];
        }
        if (
          typeof value === 'number' &&
          value === 0 &&
          ['weight', 'warranty_months'].includes(key)
        ) {
          return [key, undefined];
        }
        return [key, value];
      })
    );

    onSubmit(submitData as CreateProductData | UpdateProductData);
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      {/* Informações Básicas */}
      <div>
        <h4 className="text-lg font-medium text-gray-900 mb-4">
          Informações Básicas
        </h4>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Nome do Produto *
            </label>
            <input
              type="text"
              value={formData.name}
              onChange={(e) => handleInputChange('name', e.target.value)}
              className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                hasFieldError('name') ? 'border-red-500' : 'border-gray-300'
              }`}
              placeholder="Nome do produto"
            />
            {getFieldError('name') && (
              <p className="text-red-500 text-xs mt-1">
                {getFieldError('name')}
              </p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Categoria *
            </label>
            <select
              value={formData.category_id}
              onChange={(e) =>
                handleInputChange('category_id', Number(e.target.value))
              }
              className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                hasFieldError('category_id')
                  ? 'border-red-500'
                  : 'border-gray-300'
              }`}
            >
              <option value={0}>Selecione uma categoria</option>
              {categories.map((category) => (
                <option key={category.id} value={category.id}>
                  {category.name}
                </option>
              ))}
            </select>
            {getFieldError('category_id') && (
              <p className="text-red-500 text-xs mt-1">
                {getFieldError('category_id')}
              </p>
            )}
          </div>

          <div className="md:col-span-2">
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Descrição
            </label>
            <textarea
              value={formData.description}
              onChange={(e) => handleInputChange('description', e.target.value)}
              rows={3}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Descrição detalhada do produto"
            />
          </div>
        </div>
      </div>

      {/* Códigos e Identificação */}
      <div>
        <h4 className="text-lg font-medium text-gray-900 mb-4">
          Códigos e Identificação
        </h4>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              SKU *
            </label>
            <input
              type="text"
              value={formData.sku}
              onChange={(e) => handleInputChange('sku', e.target.value)}
              className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                hasFieldError('sku') ? 'border-red-500' : 'border-gray-300'
              }`}
              placeholder="Código SKU"
            />
            {getFieldError('sku') && (
              <p className="text-red-500 text-xs mt-1">
                {getFieldError('sku')}
              </p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Código de Barras
            </label>
            <input
              type="text"
              value={formData.barcode}
              onChange={(e) => handleInputChange('barcode', e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Código de barras"
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Unidade *
            </label>
            <input
              type="text"
              value={formData.unit}
              onChange={(e) => handleInputChange('unit', e.target.value)}
              className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                hasFieldError('unit') ? 'border-red-500' : 'border-gray-300'
              }`}
              placeholder="Ex: un, kg, l"
            />
            {getFieldError('unit') && (
              <p className="text-red-500 text-xs mt-1">
                {getFieldError('unit')}
              </p>
            )}
          </div>
        </div>
      </div>

      {/* Preços */}
      <div>
        <h4 className="text-lg font-medium text-gray-900 mb-4">Preços</h4>
        <div className="grid grid-cols-1 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Preço *
            </label>
            <input
              type="number"
              step="0.01"
              min="0"
              value={formData.price}
              onChange={(e) =>
                handleInputChange('price', Number(e.target.value))
              }
              className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                hasFieldError('price') ? 'border-red-500' : 'border-gray-300'
              }`}
              placeholder="0.00"
            />
            {getFieldError('price') && (
              <p className="text-red-500 text-xs mt-1">
                {getFieldError('price')}
              </p>
            )}
          </div>
        </div>
      </div>

      {/* Estoque */}
      <div>
        <h4 className="text-lg font-medium text-gray-900 mb-4">Estoque</h4>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Quantidade em Estoque *
            </label>
            <input
              type="number"
              min="0"
              value={formData.stock_quantity}
              onChange={(e) =>
                handleInputChange('stock_quantity', Number(e.target.value))
              }
              className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                hasFieldError('stock_quantity')
                  ? 'border-red-500'
                  : 'border-gray-300'
              }`}
              placeholder="0"
            />
            {getFieldError('stock_quantity') && (
              <p className="text-red-500 text-xs mt-1">
                {getFieldError('stock_quantity')}
              </p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Estoque Mínimo
            </label>
            <input
              type="number"
              min="0"
              value={formData.min_stock}
              onChange={(e) =>
                handleInputChange('min_stock', Number(e.target.value))
              }
              className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                hasFieldError('min_stock')
                  ? 'border-red-500'
                  : 'border-gray-300'
              }`}
              placeholder="0"
            />
            {getFieldError('min_stock') && (
              <p className="text-red-500 text-xs mt-1">
                {getFieldError('min_stock')}
              </p>
            )}
          </div>
        </div>
      </div>

      {/* Informações Adicionais */}
      {/* <div>
        <h4 className="text-lg font-medium text-gray-900 mb-4">
          Informações Adicionais
        </h4>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Marca
            </label>
            <input
              type="text"
              value={formData.brand}
              onChange={(e) => handleInputChange('brand', e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Marca do produto"
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Fornecedor
            </label>
            <input
              type="text"
              value={formData.supplier}
              onChange={(e) => handleInputChange('supplier', e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Nome do fornecedor"
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Localização
            </label>
            <input
              type="text"
              value={formData.location}
              onChange={(e) => handleInputChange('location', e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Localização no estoque"
            />
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Peso (kg)
            </label>
            <input
              type="number"
              step="0.01"
              min="0"
              value={formData.weight}
              onChange={(e) =>
                handleInputChange('weight', Number(e.target.value))
              }
              className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                errors.weight ? 'border-red-500' : 'border-gray-300'
              }`}
              placeholder="0.00"
            />
            {errors.weight && (
              <p className="text-red-500 text-xs mt-1">{errors.weight}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Dimensões
            </label>
            <input
              type="text"
              value={formData.dimensions}
              onChange={(e) => handleInputChange('dimensions', e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Ex: 10x20x30cm"
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Garantia (meses)
            </label>
            <input
              type="number"
              min="0"
              value={formData.warranty_months}
              onChange={(e) =>
                handleInputChange('warranty_months', Number(e.target.value))
              }
              className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                errors.warranty_months ? 'border-red-500' : 'border-gray-300'
              }`}
              placeholder="0"
            />
            {errors.warranty_months && (
              <p className="text-red-500 text-xs mt-1">
                {errors.warranty_months}
              </p>
            )}
          </div>
        </div>
      </div> */}

      {/* Configurações */}
      <div>
        <h4 className="text-lg font-medium text-gray-900 mb-4">
          Configurações
        </h4>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div className="flex items-center space-x-4">
            <label className="flex items-center">
              <input
                type="checkbox"
                checked={formData.active}
                onChange={(e) => handleInputChange('active', e.target.checked)}
                className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <span className="ml-2 text-sm text-gray-700">Produto Ativo</span>
            </label>
          </div>

          <div className="flex items-center space-x-4">
            <label className="flex items-center">
              <input
                type="checkbox"
                checked={formData.featured}
                onChange={(e) =>
                  handleInputChange('featured', e.target.checked)
                }
                className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <span className="ml-2 text-sm text-gray-700">
                Produto em Destaque
              </span>
            </label>
          </div>
        </div>

        <div className="mt-4">
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Observações
          </label>
          <textarea
            value={formData.observations}
            onChange={(e) => handleInputChange('observations', e.target.value)}
            rows={3}
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Observações adicionais sobre o produto"
          />
        </div>
      </div>

      {/* Botões */}
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
          disabled={loading}
          className="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
        >
          {loading
            ? 'Salvando...'
            : product
              ? 'Atualizar Produto'
              : 'Criar Produto'}
        </button>
      </div>
    </form>
  );
};
