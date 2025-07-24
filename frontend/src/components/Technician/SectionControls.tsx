import {
  ArrowsPointingInIcon,
  ArrowsPointingOutIcon,
  EyeIcon,
} from '@heroicons/react/24/outline';
import React from 'react';
import { type SectionType } from '../../hooks/useSectionCollapse';

interface SectionControlsProps {
  expandAllSections: () => void;
  collapseAllSections: () => void;
  collapsedSections: Set<SectionType>;
  totalSections: number;
}

export const SectionControls: React.FC<SectionControlsProps> = ({
  expandAllSections,
  collapseAllSections,
  collapsedSections,
  totalSections,
}) => {
  const collapsedCount = collapsedSections.size;
  const expandedCount = totalSections - collapsedCount;

  // Determina se a maioria das seções está expandida ou minimizada
  const isMostlyExpanded = expandedCount >= collapsedCount;

  // Função para alternar entre expandir e minimizar todas
  const handleToggleAll = () => {
    if (isMostlyExpanded) {
      collapseAllSections();
    } else {
      expandAllSections();
    }
  };

  return (
    <div className="bg-white/90 backdrop-blur-sm rounded-xl shadow-lg border border-gray-200 p-3 sm:p-4 mb-4">
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-3">
          <div className="p-1.5 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg">
            <EyeIcon className="h-4 w-4 text-white" />
          </div>
          <div>
            <h4 className="text-sm font-semibold text-gray-900">
              Controle de Seções
            </h4>
            <p className="text-xs text-gray-600">
              {expandedCount} expandida{expandedCount !== 1 ? 's' : ''},{' '}
              {collapsedCount} minimizada{collapsedCount !== 1 ? 's' : ''}
            </p>
          </div>
        </div>

        <div className="flex items-center">
          <button
            onClick={handleToggleAll}
            className={`flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg border transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 ${
              isMostlyExpanded
                ? 'text-orange-700 bg-orange-50 hover:bg-orange-100 border-orange-200 focus:ring-orange-500'
                : 'text-green-700 bg-green-50 hover:bg-green-100 border-green-200 focus:ring-green-500'
            }`}
            title={
              isMostlyExpanded
                ? 'Minimizar todas as seções'
                : 'Expandir todas as seções'
            }
          >
            {isMostlyExpanded ? (
              <>
                <ArrowsPointingInIcon className="h-4 w-4" />
                <span className="hidden sm:inline">Minimizar Todas</span>
                <span className="sm:hidden">Minimizar</span>
              </>
            ) : (
              <>
                <ArrowsPointingOutIcon className="h-4 w-4" />
                <span className="hidden sm:inline">Expandir Todas</span>
                <span className="sm:hidden">Expandir</span>
              </>
            )}
          </button>
        </div>
      </div>
    </div>
  );
};
