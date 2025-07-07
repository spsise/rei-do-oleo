import React, { useState } from 'react';

interface ServiceSearchFormProps {
  onSearchByNumber: (serviceNumber: string) => void;
  loading?: boolean;
}

export const ServiceSearchForm: React.FC<ServiceSearchFormProps> = ({
  onSearchByNumber,
  loading = false,
}) => {
  const [serviceNumber, setServiceNumber] = useState('');

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (serviceNumber.trim()) {
      onSearchByNumber(serviceNumber.trim());
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <div>
        <label
          htmlFor="service_number"
          className="block text-sm font-medium text-gray-700 mb-1"
        >
          Número do Serviço
        </label>
        <input
          type="text"
          id="service_number"
          value={serviceNumber}
          onChange={(e) => setServiceNumber(e.target.value)}
          placeholder="Digite o número do serviço (ex: OS-2023-001)"
          className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          required
        />
      </div>

      <div className="flex justify-end space-x-3 pt-4">
        <button
          type="submit"
          disabled={loading || !serviceNumber.trim()}
          className="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {loading ? 'Buscando...' : 'Buscar Serviço'}
        </button>
      </div>
    </form>
  );
};
