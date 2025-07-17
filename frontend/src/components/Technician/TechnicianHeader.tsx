import {
  SparklesIcon,
  WrenchScrewdriverIcon,
} from '@heroicons/react/24/outline';
import React from 'react';

interface TechnicianHeaderProps {
  title?: string;
  subtitle?: string;
}

export const TechnicianHeader: React.FC<TechnicianHeaderProps> = ({
  title = 'Área de Serviços',
  subtitle,
}) => {
  return (
    <div className="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl p-3 sm:p-4 md:p-4 text-white shadow-lg relative overflow-hidden mb-4">
      {/* Background pattern sutil */}
      <div className="absolute inset-0 bg-black/5"></div>
      <div className="absolute top-0 right-0 w-20 h-20 bg-white/10 rounded-full -translate-y-8 translate-x-8 hidden sm:block"></div>
      <div className="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-6 -translate-x-6 hidden sm:block"></div>
      {/* Content */}
      <div className="relative z-10 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
        <div className="flex items-center gap-2 mb-2 sm:mb-0">
          <div className="p-2 bg-white/20 backdrop-blur-sm rounded-lg shadow">
            <WrenchScrewdriverIcon className="h-6 w-6 text-white" />
          </div>
          <div>
            <h1 className="text-xl sm:text-2xl md:text-3xl font-bold leading-tight mb-0.5">
              {title}
            </h1>
            <p className="text-blue-100 text-sm sm:text-base">
              {subtitle ||
                'Busque clientes por placa ou documento para registrar serviços.'}
            </p>
          </div>
        </div>
        {/* Features só em telas médias+ */}
        <div className="hidden md:flex items-center gap-3 ml-auto">
          <div className="flex items-center gap-1 text-blue-100 bg-white/10 px-3 py-1 rounded-full text-xs">
            <SparklesIcon className="h-3 w-3" />
            <span>Sistema Inteligente</span>
          </div>
          <div className="flex items-center gap-1 text-blue-100 bg-white/10 px-3 py-1 rounded-full text-xs">
            <div className="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
            <span>Busca por voz</span>
          </div>
          <div className="flex items-center gap-1 text-blue-100 bg-white/10 px-3 py-1 rounded-full text-xs">
            <div className="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></div>
            <span>Histórico</span>
          </div>
        </div>
      </div>
    </div>
  );
};
