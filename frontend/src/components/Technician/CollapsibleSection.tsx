import { ChevronDownIcon, ChevronUpIcon } from '@heroicons/react/24/outline';
import React from 'react';
import { type SectionType } from '../../hooks/useSectionCollapse';

interface CollapsibleSectionProps {
  sectionType: SectionType;
  title: string;
  subtitle?: string;
  icon: React.ReactNode;
  isCollapsed: boolean;
  onToggle: (section: SectionType) => void;
  children: React.ReactNode;
  className?: string;
}

export const CollapsibleSection: React.FC<CollapsibleSectionProps> = ({
  sectionType,
  title,
  subtitle,
  icon,
  isCollapsed,
  onToggle,
  children,
  className = '',
}) => {
  return (
    <div className={`transform transition-all duration-300 hover:scale-[1.01] ${className}`}>
      <div className="bg-white/90 backdrop-blur-sm rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        {/* Header da seção */}
        <div className="p-4 sm:p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className="p-2 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg shadow-sm">
                {icon}
              </div>
              <div>
                <h3 className="font-bold text-gray-900 text-base sm:text-lg">
                  {title}
                </h3>
                {subtitle && (
                  <p className="text-blue-600 text-xs sm:text-sm font-medium">
                    {subtitle}
                  </p>
                )}
              </div>
            </div>
            
            {/* Botão de minimizar */}
            <button
              onClick={() => onToggle(sectionType)}
              className="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
              title={isCollapsed ? 'Expandir seção' : 'Minimizar seção'}
            >
              {isCollapsed ? (
                <ChevronDownIcon className="h-5 w-5" />
              ) : (
                <ChevronUpIcon className="h-5 w-5" />
              )}
            </button>
          </div>
        </div>

        {/* Conteúdo da seção */}
        <div
          className={`transition-all duration-300 ease-in-out ${
            isCollapsed
              ? 'max-h-0 opacity-0 overflow-hidden'
              : 'max-h-[2000px] opacity-100'
          }`}
        >
          <div className="p-4 sm:p-6">
            {children}
          </div>
        </div>

        {/* Indicador de seção minimizada */}
        {isCollapsed && (
          <div className="px-4 py-2 bg-gray-50 border-t border-gray-100">
            <div className="flex items-center justify-between">
              <span className="text-sm text-gray-500 font-medium">
                Seção minimizada
              </span>
              <button
                onClick={() => onToggle(sectionType)}
                className="text-xs text-blue-600 hover:text-blue-700 font-medium"
              >
                Expandir
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}; 