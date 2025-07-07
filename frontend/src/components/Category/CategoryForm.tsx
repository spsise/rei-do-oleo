import React, { useEffect, useState } from 'react';
import type {
  Category,
  CreateCategoryData,
  UpdateCategoryData,
} from '../../types/category';

interface CategoryFormProps {
  category?: Category;
  onSubmit: (data: CreateCategoryData | UpdateCategoryData) => void;
  onCancel: () => void;
  loading?: boolean;
}

export const CategoryForm: React.FC<CategoryFormProps> = ({
  category,
  onSubmit,
  onCancel,
  loading = false,
}) => {
  const [formData, setFormData] = useState<CreateCategoryData>({
    name: '',
    description: '',
    active: true,
  });

  const [errors, setErrors] = useState<Record<string, string>>({});

  useEffect(() => {
    if (category) {
      setFormData({
        name: category.name,
        description: category.description || '',
        active: category.active,
      });
    }
  }, [category]);

  const handleInputChange = (field: keyof CreateCategoryData, value: any) => {
    setFormData((prev) => ({ ...prev, [field]: value }));

    // Clear error when user starts typing
    if (errors[field]) {
      setErrors((prev) => ({ ...prev, [field]: '' }));
    }
  };

  const validateForm = (): boolean => {
    const newErrors: Record<string, string> = {};

    if (!formData.name.trim()) {
      newErrors.name = 'Nome é obrigatório';
    }

    if (formData.name.trim().length < 2) {
      newErrors.name = 'Nome deve ter pelo menos 2 caracteres';
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

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      {/* Nome */}
      <div>
        <label
          htmlFor="name"
          className="block text-sm font-medium text-gray-700 mb-1"
        >
          Nome da Categoria *
        </label>
        <input
          type="text"
          id="name"
          value={formData.name}
          onChange={(e) => handleInputChange('name', e.target.value)}
          className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
            errors.name ? 'border-red-500' : 'border-gray-300'
          }`}
          placeholder="Digite o nome da categoria..."
          maxLength={100}
        />
        {errors.name && (
          <p className="mt-1 text-sm text-red-600">{errors.name}</p>
        )}
        <p className="mt-1 text-sm text-gray-500">
          {formData.name.length}/100 caracteres
        </p>
      </div>

      {/* Descrição */}
      <div>
        <label
          htmlFor="description"
          className="block text-sm font-medium text-gray-700 mb-1"
        >
          Descrição
        </label>
        <textarea
          id="description"
          value={formData.description}
          onChange={(e) => handleInputChange('description', e.target.value)}
          rows={3}
          className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Descreva a categoria (opcional)..."
          maxLength={500}
        />
        <p className="mt-1 text-sm text-gray-500">
          {formData.description.length}/500 caracteres
        </p>
      </div>

      {/* Status */}
      <div>
        <label className="flex items-center">
          <input
            type="checkbox"
            checked={formData.active}
            onChange={(e) => handleInputChange('active', e.target.checked)}
            className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
          />
          <span className="ml-2 text-sm text-gray-700">Categoria ativa</span>
        </label>
        <p className="mt-1 text-sm text-gray-500">
          Categorias inativas não aparecerão nas listagens de produtos
        </p>
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
            : category
              ? 'Atualizar Categoria'
              : 'Criar Categoria'}
        </button>
      </div>
    </form>
  );
};
