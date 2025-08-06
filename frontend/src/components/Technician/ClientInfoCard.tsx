import {
  EnvelopeIcon,
  IdentificationIcon,
  PhoneIcon,
  UserIcon,
} from '@heroicons/react/24/outline';
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

  const formatPhone = (phone: string) => {
    if (!phone) return 'N/A';
    const cleaned = phone.replace(/\D/g, '');
    if (cleaned.length === 11) {
      return cleaned.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    } else if (cleaned.length === 10) {
      return cleaned.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
    }
    return phone;
  };

  return (
    <div className="space-y-3">
      {/* Nome */}
      <div className="bg-white/80 backdrop-blur-sm rounded-lg p-3 border border-blue-100 hover:border-blue-200 transition-colors">
        <div className="flex items-center gap-2">
          <UserIcon className="h-4 w-4 text-blue-600" />
          <div className="flex-1 min-w-0">
            <div className="text-xs font-medium text-gray-500 uppercase tracking-wide mb-0.5">
              Nome
            </div>
            <div className="text-gray-900 font-semibold text-sm truncate">
              {client.name || 'N/A'}
            </div>
          </div>
        </div>
      </div>

      {/* Email */}
      <div className="bg-white/80 backdrop-blur-sm rounded-lg p-3 border border-blue-100 hover:border-blue-200 transition-colors">
        <div className="flex items-center gap-2">
          <EnvelopeIcon className="h-4 w-4 text-green-600" />
          <div className="flex-1 min-w-0">
            <div className="text-xs font-medium text-gray-500 uppercase tracking-wide mb-0.5">
              Email
            </div>
            <div className="text-gray-900 font-semibold text-sm truncate">
              {client.email || 'N/A'}
            </div>
          </div>
        </div>
      </div>

      {/* Telefone */}
      <div className="bg-white/80 backdrop-blur-sm rounded-lg p-3 border border-blue-100 hover:border-blue-200 transition-colors">
        <div className="flex items-center gap-2">
          <PhoneIcon className="h-4 w-4 text-purple-600" />
          <div className="flex-1 min-w-0">
            <div className="text-xs font-medium text-gray-500 uppercase tracking-wide mb-0.5">
              Telefone
            </div>
            <div className="text-gray-900 font-semibold text-sm">
              {formatPhone(client.phone || '')}
            </div>
          </div>
        </div>
      </div>

      {/* Documento */}
      <div className="bg-white/80 backdrop-blur-sm rounded-lg p-3 border border-blue-100 hover:border-blue-200 transition-colors">
        <div className="flex items-center gap-2">
          <IdentificationIcon className="h-4 w-4 text-orange-600" />
          <div className="flex-1 min-w-0">
            <div className="text-xs font-medium text-gray-500 uppercase tracking-wide mb-0.5">
              Documento
            </div>
            <div className="text-gray-900 font-semibold text-sm font-mono">
              {formatDocument(client.document || '')}
            </div>
          </div>
        </div>
      </div>

      {/* Status do cliente */}
      <div className="p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border border-green-200">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-2">
            <div className="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
            <span className="text-xs font-medium text-green-800">
              Cliente Ativo
            </span>
          </div>
          <div className="text-xs text-green-600 bg-green-100 px-2 py-0.5 rounded-full font-medium">
            Verificado
          </div>
        </div>
      </div>
    </div>
  );
};
