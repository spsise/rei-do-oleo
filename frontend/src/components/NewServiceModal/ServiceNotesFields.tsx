import { DocumentTextIcon } from '@heroicons/react/24/outline';
import React from 'react';

interface ServiceNotesFieldsProps {
  notes?: string;
  observations?: string;
  onChange: (field: 'notes' | 'observations', value: string) => void;
}

export const ServiceNotesFields: React.FC<ServiceNotesFieldsProps> = ({
  notes,
  observations,
  onChange,
}) => {
  return (
    <>
      {/* Observações */}
      <div className="space-y-3">
        <label className="block text-sm font-semibold text-gray-700 flex items-center gap-2">
          <DocumentTextIcon className="h-4 w-4 text-blue-600" />
          Observações Adicionais
        </label>
        <textarea
          value={notes || ''}
          onChange={(e) => onChange('notes', e.target.value)}
          placeholder="Observações, instruções especiais ou detalhes adicionais..."
          className="w-full px-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/80 backdrop-blur-sm resize-none transition-all duration-200 shadow-sm hover:shadow-md"
          rows={3}
        />
      </div>

      {/* Observações Detalhadas */}
      <div className="space-y-3">
        <label className="block text-sm font-semibold text-gray-700 flex items-center gap-2">
          <DocumentTextIcon className="h-4 w-4 text-purple-600" />
          Observações Detalhadas
        </label>
        <textarea
          value={observations || ''}
          onChange={(e) => onChange('observations', e.target.value)}
          placeholder="Observações detalhadas sobre o serviço, diagnóstico, solução aplicada..."
          className="w-full px-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/80 backdrop-blur-sm resize-none transition-all duration-200 shadow-sm hover:shadow-md"
          rows={3}
        />
      </div>
    </>
  );
};
