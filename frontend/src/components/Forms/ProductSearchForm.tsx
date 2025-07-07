import React, { useState } from 'react';
import { useCategories } from '../../hooks/useCategories';

interface ProductSearchFormProps {
  onSearchByName: (name: string) => void;
  onSearchBySku: (sku: string) => void;
  onSearchByBarcode: (barcode: string) => void;
  onSearchByCategory: (categoryId: number) => void;
  loading?: boolean;
}

export const ProductSearchForm: React.FC<ProductSearchFormProps> = ({
  onSearchByName,
  onSearchBySku,
  onSearchByBarcode,
  onSearchByCategory,
  loading = false,
}) => {
  const { data: categoriesData } = useCategories();
  const categories = categoriesData?.data || [];

  const [searchData, setSearchData] = useState({
    name: '',
    sku: '',
    barcode: '',
    category_id: 0,
  });

  const [activeTab, setActiveTab] = useState<
    'name' | 'sku' | 'barcode' | 'category'
  >('name');

  const handleInputChange = (field: string, value: string | number) => {
    setSearchData((prev) => ({ ...prev, [field]: value }));
  };

  const handleSearch = () => {
    switch (activeTab) {
      case 'name':
        if (searchData.name.trim()) {
          onSearchByName(searchData.name.trim());
        }
        break;
      case 'sku':
        if (searchData.sku.trim()) {
          onSearchBySku(searchData.sku.trim());
        }
        break;
      case 'barcode':
        if (searchData.barcode.trim()) {
          onSearchByBarcode(searchData.barcode.trim());
        }
        break;
      case 'category':
        if (searchData.category_id > 0) {
          onSearchByCategory(searchData.category_id);
        }
        break;
    }
  };

  const handleKeyPress = (e: React.KeyboardEvent) => {
    if (e.key === 'Enter') {
      handleSearch();
    }
  };

  const isSearchDisabled = () => {
    switch (activeTab) {
      case 'name':
        return !searchData.name.trim();
      case 'sku':
        return !searchData.sku.trim();
      case 'barcode':
        return !searchData.barcode.trim();
      case 'category':
        return searchData.category_id === 0;
      default:
        return true;
    }
  };

  return (
    <div className="space-y-6">
      {/* Tabs */}
      <div className="border-b border-gray-200">
        <nav className="-mb-px flex space-x-8">
          {[
            { id: 'name', label: 'Por Nome' },
            { id: 'sku', label: 'Por SKU' },
            { id: 'barcode', label: 'Por Código de Barras' },
            { id: 'category', label: 'Por Categoria' },
          ].map((tab) => (
            <button
              key={tab.id}
              onClick={() => setActiveTab(tab.id as any)}
              className={`py-2 px-1 border-b-2 font-medium text-sm ${
                activeTab === tab.id
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              }`}
            >
              {tab.label}
            </button>
          ))}
        </nav>
      </div>

      {/* Search Fields */}
      <div className="space-y-4">
        {/* Search by Name */}
        {activeTab === 'name' && (
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Nome do Produto
            </label>
            <div className="flex space-x-2">
              <input
                type="text"
                value={searchData.name}
                onChange={(e) => handleInputChange('name', e.target.value)}
                onKeyPress={handleKeyPress}
                className="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Digite o nome do produto..."
                disabled={loading}
              />
              <button
                onClick={handleSearch}
                disabled={isSearchDisabled() || loading}
                className="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
              >
                {loading ? 'Buscando...' : 'Buscar'}
              </button>
            </div>
            <p className="text-xs text-gray-500 mt-1">
              Busca produtos que contenham o termo no nome
            </p>
          </div>
        )}

        {/* Search by SKU */}
        {activeTab === 'sku' && (
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Código SKU
            </label>
            <div className="flex space-x-2">
              <input
                type="text"
                value={searchData.sku}
                onChange={(e) => handleInputChange('sku', e.target.value)}
                onKeyPress={handleKeyPress}
                className="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Digite o código SKU..."
                disabled={loading}
              />
              <button
                onClick={handleSearch}
                disabled={isSearchDisabled() || loading}
                className="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
              >
                {loading ? 'Buscando...' : 'Buscar'}
              </button>
            </div>
            <p className="text-xs text-gray-500 mt-1">
              Busca produtos pelo código SKU exato
            </p>
          </div>
        )}

        {/* Search by Barcode */}
        {activeTab === 'barcode' && (
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Código de Barras
            </label>
            <div className="flex space-x-2">
              <input
                type="text"
                value={searchData.barcode}
                onChange={(e) => handleInputChange('barcode', e.target.value)}
                onKeyPress={handleKeyPress}
                className="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Digite o código de barras..."
                disabled={loading}
              />
              <button
                onClick={handleSearch}
                disabled={isSearchDisabled() || loading}
                className="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
              >
                {loading ? 'Buscando...' : 'Buscar'}
              </button>
            </div>
            <p className="text-xs text-gray-500 mt-1">
              Busca produtos pelo código de barras exato
            </p>
          </div>
        )}

        {/* Search by Category */}
        {activeTab === 'category' && (
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Categoria
            </label>
            <div className="flex space-x-2">
              <select
                value={searchData.category_id}
                onChange={(e) =>
                  handleInputChange('category_id', Number(e.target.value))
                }
                className="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                disabled={loading}
              >
                <option value={0}>Selecione uma categoria</option>
                {categories.map((category) => (
                  <option key={category.id} value={category.id}>
                    {category.name}
                  </option>
                ))}
              </select>
              <button
                onClick={handleSearch}
                disabled={isSearchDisabled() || loading}
                className="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
              >
                {loading ? 'Buscando...' : 'Buscar'}
              </button>
            </div>
            <p className="text-xs text-gray-500 mt-1">
              Lista todos os produtos de uma categoria específica
            </p>
          </div>
        )}
      </div>

      {/* Instructions */}
      <div className="bg-blue-50 border border-blue-200 rounded-md p-4">
        <div className="flex">
          <div className="flex-shrink-0">
            <svg
              className="h-5 w-5 text-blue-400"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fillRule="evenodd"
                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                clipRule="evenodd"
              />
            </svg>
          </div>
          <div className="ml-3">
            <h3 className="text-sm font-medium text-blue-800">
              Como usar a busca
            </h3>
            <div className="mt-2 text-sm text-blue-700">
              <ul className="list-disc list-inside space-y-1">
                <li>
                  <strong>Por Nome:</strong> Digite parte do nome do produto
                  para encontrar correspondências
                </li>
                <li>
                  <strong>Por SKU:</strong> Digite o código SKU exato do produto
                </li>
                <li>
                  <strong>Por Código de Barras:</strong> Digite o código de
                  barras exato do produto
                </li>
                <li>
                  <strong>Por Categoria:</strong> Selecione uma categoria para
                  ver todos os produtos dela
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
