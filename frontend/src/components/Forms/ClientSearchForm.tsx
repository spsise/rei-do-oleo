import React, { useState } from 'react';

interface ClientSearchFormProps {
  onSearchByDocument: (document: string) => void;
  onSearchByPhone: (phone: string) => void;
  loading?: boolean;
}

export const ClientSearchForm: React.FC<ClientSearchFormProps> = ({
  onSearchByDocument,
  onSearchByPhone,
  loading = false,
}) => {
  const [searchType, setSearchType] = useState<'document' | 'phone'>(
    'document'
  );
  const [searchValue, setSearchValue] = useState('');
  const [errors, setErrors] = useState<Record<string, string>>({});

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();

    if (!searchValue.trim()) {
      setErrors({ searchValue: 'Campo obrigatório' });
      return;
    }

    setErrors({});

    if (searchType === 'document') {
      onSearchByDocument(searchValue);
    } else {
      onSearchByPhone(searchValue);
    }
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { value } = e.target;

    if (searchType === 'document') {
      // Formatar documento
      const cleanValue = value.replace(/\D/g, '');
      if (cleanValue.length <= 11) {
        // CPF
        const formattedValue = cleanValue
          .replace(/(\d{3})(\d)/, '$1.$2')
          .replace(/(\d{3})(\d)/, '$1.$2')
          .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        setSearchValue(formattedValue);
      } else {
        // CNPJ
        const formattedValue = cleanValue
          .replace(/^(\d{2})(\d)/, '$1.$2')
          .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
          .replace(/\.(\d{3})(\d)/, '.$1/$2')
          .replace(/(\d{4})(\d)/, '$1-$2');
        setSearchValue(formattedValue);
      }
    } else {
      // Formatar telefone
      const cleanValue = value.replace(/\D/g, '');
      if (cleanValue.length <= 10) {
        const formattedValue = cleanValue
          .replace(/(\d{2})(\d)/, '($1) $2')
          .replace(/(\d{4})(\d)/, '$1-$2');
        setSearchValue(formattedValue);
      } else {
        const formattedValue = cleanValue
          .replace(/(\d{2})(\d)/, '($1) $2')
          .replace(/(\d{5})(\d)/, '$1-$2');
        setSearchValue(formattedValue);
      }
    }

    if (errors.searchValue) {
      setErrors({});
    }
  };

  const handleSearchTypeChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    setSearchType(e.target.value as 'document' | 'phone');
    setSearchValue('');
    setErrors({});
  };

  return (
    <form
      onSubmit={handleSearch}
      className="bg-white p-6 rounded-lg shadow-sm border"
    >
      <h3 className="text-lg font-medium text-gray-900 mb-4">Buscar Cliente</h3>

      <div className="space-y-4">
        {/* Tipo de Busca */}
        <div>
          <label
            htmlFor="searchType"
            className="block text-sm font-medium text-gray-700 mb-2"
          >
            Buscar por
          </label>
          <select
            id="searchType"
            value={searchType}
            onChange={handleSearchTypeChange}
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900 bg-white"
          >
            <option value="document">Documento (CPF/CNPJ)</option>
            <option value="phone">Telefone</option>
          </select>
        </div>

        {/* Campo de Busca */}
        <div>
          <label
            htmlFor="searchValue"
            className="block text-sm font-medium text-gray-700 mb-2"
          >
            {searchType === 'document' ? 'Documento' : 'Telefone'} *
          </label>
          <input
            type="text"
            id="searchValue"
            value={searchValue}
            onChange={handleInputChange}
            className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900 bg-white ${
              errors.searchValue ? 'border-red-500' : 'border-gray-300'
            }`}
            placeholder={
              searchType === 'document'
                ? 'Digite CPF ou CNPJ'
                : 'Digite o telefone'
            }
            maxLength={searchType === 'document' ? 18 : 15}
          />
          {errors.searchValue && (
            <p className="mt-1 text-sm text-red-600">{errors.searchValue}</p>
          )}
        </div>

        {/* Botão de Busca */}
        <button
          type="submit"
          disabled={loading || !searchValue.trim()}
          className="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {loading ? (
            <span className="flex items-center justify-center">
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
              Buscando...
            </span>
          ) : (
            'Buscar Cliente'
          )}
        </button>
      </div>
    </form>
  );
};
