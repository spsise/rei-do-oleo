import {
  ChevronDownIcon,
  ChevronUpIcon,
  FunnelIcon,
  MagnifyingGlassIcon,
} from '@heroicons/react/24/outline';
import React, { useState } from 'react';

interface Category {
  id: number;
  name: string;
}

interface ProductFiltersProps {
  searchTerm: string;
  onSearch: (search: string) => void;
  selectedCategory: number | null;
  onCategoryChange: (categoryId: number | null) => void;
  categories: Category[];
  products: Array<{ category?: { id: number } }>;
  placeholder?: string;
  compact?: boolean;
}

export const ProductFilters: React.FC<ProductFiltersProps> = ({
  searchTerm,
  onSearch,
  selectedCategory,
  onCategoryChange,
  categories,
  products,
  placeholder = 'Buscar produtos por nome ou SKU...',
  compact = false,
}) => {
  const [isCategoryFiltersExpanded, setIsCategoryFiltersExpanded] =
    useState(false);

  const getCategoryProductCount = (categoryId: number | null) => {
    if (categoryId === null) return products.length;
    return products.filter((product) => product.category?.id === categoryId)
      .length;
  };

  const toggleCategoryFilters = () => {
    setIsCategoryFiltersExpanded(!isCategoryFiltersExpanded);
  };

  if (compact) {
    return (
      <div className="space-y-3">
        <div className="relative">
          <MagnifyingGlassIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
          <input
            type="text"
            value={searchTerm}
            onChange={(e) => onSearch(e.target.value)}
            placeholder={placeholder}
            className="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/80 backdrop-blur-sm transition-all duration-200 shadow-sm hover:shadow-md text-sm"
          />
        </div>

        {/* Botão para expandir/colapsar filtros de categoria */}
        <div className="space-y-2">
          <button
            onClick={toggleCategoryFilters}
            className="flex items-center gap-2 text-xs font-medium text-gray-700 hover:text-gray-900 transition-colors"
          >
            <FunnelIcon className="h-3 w-3" />
            Filtrar por categoria
            {isCategoryFiltersExpanded ? (
              <ChevronUpIcon className="h-3 w-3" />
            ) : (
              <ChevronDownIcon className="h-3 w-3" />
            )}
          </button>

          {/* Filtros de categoria - colapsados por padrão */}
          {isCategoryFiltersExpanded && (
            <div className="flex flex-wrap gap-1.5 transition-all duration-200 ease-in-out">
              <button
                onClick={() => onCategoryChange(null)}
                className={`px-3 py-1.5 rounded-md text-xs font-medium transition-all duration-200 ${
                  selectedCategory === null
                    ? 'bg-blue-100 text-blue-700 border border-blue-300'
                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200 border border-transparent'
                }`}
              >
                Todas ({getCategoryProductCount(null)})
              </button>
              {categories.map((category) => (
                <button
                  key={category.id}
                  onClick={() => onCategoryChange(category.id)}
                  className={`px-3 py-1.5 rounded-md text-xs font-medium transition-all duration-200 ${
                    selectedCategory === category.id
                      ? 'bg-blue-100 text-blue-700 border border-blue-300'
                      : 'bg-gray-100 text-gray-600 hover:bg-gray-200 border border-transparent'
                  }`}
                >
                  {category.name} ({getCategoryProductCount(category.id)})
                </button>
              ))}
            </div>
          )}
        </div>
      </div>
    );
  }

  // Layout padrão (não compacto)
  return (
    <div className="space-y-4">
      <div className="relative">
        <MagnifyingGlassIcon className="absolute left-4 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
        <input
          type="text"
          value={searchTerm}
          onChange={(e) => onSearch(e.target.value)}
          placeholder={placeholder}
          className="w-full pl-12 pr-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white transition-all duration-200 shadow-sm hover:shadow-md"
        />
      </div>

      {/* Botão para expandir/colapsar filtros de categoria */}
      <div className="space-y-3">
        <button
          onClick={toggleCategoryFilters}
          className="flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors"
        >
          <FunnelIcon className="h-4 w-4" />
          Filtrar por categoria
          {isCategoryFiltersExpanded ? (
            <ChevronUpIcon className="h-4 w-4" />
          ) : (
            <ChevronDownIcon className="h-4 w-4" />
          )}
        </button>

        {/* Filtros de categoria - colapsados por padrão */}
        {isCategoryFiltersExpanded && (
          <div className="flex flex-wrap gap-2 transition-all duration-200 ease-in-out">
            <button
              onClick={() => onCategoryChange(null)}
              className={`px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 ${
                selectedCategory === null
                  ? 'bg-blue-100 text-blue-700 border border-blue-300'
                  : 'bg-gray-100 text-gray-600 hover:bg-gray-200 border border-transparent'
              }`}
            >
              Todas as categorias ({getCategoryProductCount(null)})
            </button>
            {categories.map((category) => (
              <button
                key={category.id}
                onClick={() => onCategoryChange(category.id)}
                className={`px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 ${
                  selectedCategory === category.id
                    ? 'bg-blue-100 text-blue-700 border border-blue-300'
                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200 border border-transparent'
                }`}
              >
                {category.name} ({getCategoryProductCount(category.id)})
              </button>
            ))}
          </div>
        )}
      </div>
    </div>
  );
};
