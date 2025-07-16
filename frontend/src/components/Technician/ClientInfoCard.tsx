import React from 'react';
import { type TechnicianClient } from '../../types/technician';

interface ClientInfoCardProps {
  client: TechnicianClient;
}

export const ClientInfoCard: React.FC<ClientInfoCardProps> = ({ client }) => {
  const formatDocument = (document: string) => {
    if (!document) return 'N/A';
    if (document.length === 11) {
      return document.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    } else if (document.length === 14) {
      return document.replace(
        /(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/,
        '$1.$2.$3/$4-$5'
      );
    }
    return document;
  };

  return (
    <div className="bg-gray-50 rounded-xl p-6 border border-gray-200">
      <h3 className="font-semibold text-gray-900 mb-4 text-lg">
        Dados do Cliente
      </h3>
      <div className="space-y-3">
        <div className="flex justify-between items-center py-2 border-b border-gray-200 last:border-b-0">
          <span className="font-medium text-gray-700">Nome:</span>
          <span className="text-gray-900">{client.name || 'N/A'}</span>
        </div>
        <div className="flex justify-between items-center py-2 border-b border-gray-200 last:border-b-0">
          <span className="font-medium text-gray-700">Email:</span>
          <span className="text-gray-900">{client.email || 'N/A'}</span>
        </div>
        <div className="flex justify-between items-center py-2 border-b border-gray-200 last:border-b-0">
          <span className="font-medium text-gray-700">Telefone:</span>
          <span className="text-gray-900">{client.phone || 'N/A'}</span>
        </div>
        <div className="flex justify-between items-center py-2 border-b border-gray-200 last:border-b-0">
          <span className="font-medium text-gray-700">Documento:</span>
          <span className="text-gray-900">
            {formatDocument(client.document || '')}
          </span>
        </div>
      </div>
    </div>
  );
};
