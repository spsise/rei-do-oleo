import React from 'react';

interface ServiceDescriptionFieldProps {
  description: string;
  onChange: (description: string) => void;
}

export const ServiceDescriptionField: React.FC<
  ServiceDescriptionFieldProps
> = ({ description, onChange }) => {
  return (
    <div className="space-y-3">
      <label className="block text-sm font-semibold text-gray-700">
        Descrição do Serviço
      </label>
      <textarea
        value={description}
        onChange={(e) => onChange(e.target.value)}
        placeholder="Descreva o serviço a ser realizado..."
        className="w-full px-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/80 backdrop-blur-sm resize-none transition-all duration-200 shadow-sm hover:shadow-md"
        rows={3}
      />
    </div>
  );
};
