import React, { useEffect, useState } from 'react';
import type {
  Client,
  CreateClientData,
  UpdateClientData,
} from '../../types/client';

interface ClientFormProps {
  client?: Client;
  onSubmit: (data: CreateClientData | UpdateClientData) => void;
  onCancel: () => void;
  loading?: boolean;
}

export const ClientForm: React.FC<ClientFormProps> = ({
  client,
  onSubmit,
  onCancel,
  loading = false,
}) => {
  const [formData, setFormData] = useState<CreateClientData>({
    name: '',
    email: '',
    document: '',
    phone: '',
    type: 'pessoa_fisica',
    active: true,
  });

  const [errors, setErrors] = useState<Record<string, string>>({});

  useEffect(() => {
    if (client) {
      setFormData({
        name: client.name,
        email: client.email,
        document: client.document,
        phone: client.phone || '',
        type: client.type,
        active: client.active,
      });
    }
  }, [client]);

  const handleChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>
  ) => {
    const { name, value, type } = e.target;
    const checked = (e.target as HTMLInputElement).checked;

    setFormData((prev) => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value,
    }));

    // Limpar erro do campo quando o usuário começa a digitar
    if (errors[name]) {
      setErrors((prev) => ({
        ...prev,
        [name]: '',
      }));
    }
  };

  const validateForm = (): boolean => {
    const newErrors: Record<string, string> = {};

    if (!formData.name.trim()) {
      newErrors.name = 'Nome é obrigatório';
    }

    if (!formData.email?.trim()) {
      newErrors.email = 'Email é obrigatório';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
      newErrors.email = 'Email inválido';
    }

    if (!formData.document.trim()) {
      newErrors.document = 'Documento é obrigatório';
    } else {
      // Validação básica de CPF/CNPJ
      const cleanDocument = formData.document.replace(/\D/g, '');
      if (formData.type === 'pessoa_fisica' && cleanDocument.length !== 11) {
        newErrors.document = 'CPF deve ter 11 dígitos';
      } else if (
        formData.type === 'pessoa_juridica' &&
        cleanDocument.length !== 14
      ) {
        newErrors.document = 'CNPJ deve ter 14 dígitos';
      }
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

  const formatDocument = (value: string, type: string): string => {
    const cleanValue = value.replace(/\D/g, '');

    if (type === 'pessoa_fisica') {
      // Formato CPF: XXX.XXX.XXX-XX
      return cleanValue
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    } else {
      // Formato CNPJ: XX.XXX.XXX/XXXX-XX
      return cleanValue
        .replace(/^(\d{2})(\d)/, '$1.$2')
        .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
        .replace(/\.(\d{3})(\d)/, '.$1/$2')
        .replace(/(\d{4})(\d)/, '$1-$2');
    }
  };

  const handleDocumentChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { value } = e.target;
    const formattedValue = formatDocument(value, formData.type);

    setFormData((prev) => ({
      ...prev,
      document: formattedValue,
    }));

    if (errors.document) {
      setErrors((prev) => ({
        ...prev,
        document: '',
      }));
    }
  };

  const formatPhone = (value: string): string => {
    const cleanValue = value.replace(/\D/g, '');

    if (cleanValue.length <= 10) {
      return cleanValue
        .replace(/(\d{2})(\d)/, '($1) $2')
        .replace(/(\d{4})(\d)/, '$1-$2');
    } else {
      return cleanValue
        .replace(/(\d{2})(\d)/, '($1) $2')
        .replace(/(\d{5})(\d)/, '$1-$2');
    }
  };

  const handlePhoneChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { value } = e.target;
    const formattedValue = formatPhone(value);

    setFormData((prev) => ({
      ...prev,
      phone: formattedValue,
    }));

    if (errors.phone) {
      setErrors((prev) => ({
        ...prev,
        phone: '',
      }));
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {/* Nome */}
        <div>
          <label
            htmlFor="name"
            className="block text-sm font-medium text-gray-700 mb-2"
          >
            Nome *
          </label>
          <input
            type="text"
            id="name"
            name="name"
            value={formData.name}
            onChange={handleChange}
            className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900 bg-white ${
              errors.name ? 'border-red-500' : 'border-gray-300'
            }`}
            placeholder="Nome completo"
          />
          {errors.name && (
            <p className="mt-1 text-sm text-red-600">{errors.name}</p>
          )}
        </div>

        {/* Email */}
        <div>
          <label
            htmlFor="email"
            className="block text-sm font-medium text-gray-700 mb-2"
          >
            Email *
          </label>
          <input
            type="email"
            id="email"
            name="email"
            value={formData.email}
            onChange={handleChange}
            className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900 bg-white ${
              errors.email ? 'border-red-500' : 'border-gray-300'
            }`}
            placeholder="email@exemplo.com"
          />
          {errors.email && (
            <p className="mt-1 text-sm text-red-600">{errors.email}</p>
          )}
        </div>

        {/* Tipo de Pessoa */}
        <div>
          <label
            htmlFor="type"
            className="block text-sm font-medium text-gray-700 mb-2"
          >
            Tipo de Pessoa *
          </label>
          <select
            id="type"
            name="type"
            value={formData.type}
            onChange={handleChange}
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900 bg-white"
          >
            <option value="pessoa_fisica">Pessoa Física</option>
            <option value="pessoa_juridica">Pessoa Jurídica</option>
          </select>
        </div>

        {/* Documento */}
        <div>
          <label
            htmlFor="document"
            className="block text-sm font-medium text-gray-700 mb-2"
          >
            {formData.type === 'pessoa_fisica' ? 'CPF' : 'CNPJ'} *
          </label>
          <input
            type="text"
            id="document"
            name="document"
            value={formData.document}
            onChange={handleDocumentChange}
            className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900 bg-white ${
              errors.document ? 'border-red-500' : 'border-gray-300'
            }`}
            placeholder={
              formData.type === 'pessoa_fisica'
                ? '000.000.000-00'
                : '00.000.000/0000-00'
            }
            maxLength={formData.type === 'pessoa_fisica' ? 14 : 18}
          />
          {errors.document && (
            <p className="mt-1 text-sm text-red-600">{errors.document}</p>
          )}
        </div>

        {/* Telefone */}
        <div>
          <label
            htmlFor="phone"
            className="block text-sm font-medium text-gray-700 mb-2"
          >
            Telefone
          </label>
          <input
            type="text"
            id="phone"
            name="phone"
            value={formData.phone}
            onChange={handlePhoneChange}
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900 bg-white"
            placeholder="(00) 00000-0000"
            maxLength={15}
          />
        </div>

        {/* Status Ativo */}
        <div className="flex items-center">
          <input
            type="checkbox"
            id="active"
            name="active"
            checked={formData.active}
            onChange={handleChange}
            className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
          />
          <label htmlFor="active" className="ml-2 block text-sm text-gray-700">
            Cliente ativo
          </label>
        </div>
      </div>

      {/* Botões */}
      <div className="flex justify-end space-x-4 pt-6 border-t">
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
          className="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {loading ? (
            <span className="flex items-center">
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
              Salvando...
            </span>
          ) : client ? (
            'Atualizar Cliente'
          ) : (
            'Criar Cliente'
          )}
        </button>
      </div>
    </form>
  );
};
