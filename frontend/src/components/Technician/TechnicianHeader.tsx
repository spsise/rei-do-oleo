import React from 'react';
import { useAuth } from '../../hooks/useAuth';

interface TechnicianHeaderProps {
  title?: string;
  subtitle?: string;
}

export const TechnicianHeader: React.FC<TechnicianHeaderProps> = ({
  title = 'Área de Serviços',
  subtitle,
}) => {
  const { user } = useAuth();

  return (
    <div className="mb-8">
      <h1 className="text-3xl font-bold text-gray-900 mb-2">{title}</h1>
      <p className="text-gray-600">
        {subtitle ||
          `Busque clientes por placa ou documento para registrar serviços.`}
      </p>
    </div>
  );
};
