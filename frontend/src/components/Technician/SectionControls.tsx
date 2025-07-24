import { 
  ChevronDoubleDownIcon, 
  ChevronDoubleUpIcon,
  EyeIcon
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
              {expandedCount} expandida{expandedCount !== 1 ? 's' : ''}, {collapsedCount} minimizada{collapsedCount !== 1 ? 's' : ''}
            </p>
          </div>
        </div>

        <div className="flex items-center gap-2">
          <button
            onClick={expandAllSections}
            className="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 hover:bg-green-100 rounded-lg border border-green-200 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
            title="Expandir todas as seções"
          >
            <ChevronDoubleUpIcon className="h-3.5 w-3.5" />
            <span className="hidden sm:inline">Expandir Todas</span>
            <span className="sm:hidden">Todas</span>
          </button>

          <button
            onClick={collapseAllSections}
            className="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 rounded-lg border border-gray-200 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
            title="Minimizar todas as seções"
          >
            <ChevronDoubleDownIcon className="h-3.5 w-3.5" />
            <span className="hidden sm:inline">Minimizar Todas</span>
            <span className="sm:hidden">Todas</span>
          </button>
        </div>
      </div>
    </div>
  );
}; 