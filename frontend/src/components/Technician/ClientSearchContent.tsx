import {
  UserIcon,
  TruckIcon,
  WrenchScrewdriverIcon,
  ChartBarIcon,
} from '@heroicons/react/24/outline';
import React from 'react';
import {
  type TechnicianSearchResult,
  type TechnicianService,
} from '../../types/technician';
import { ClientInfoCard } from './ClientInfoCard';
import { RecentServicesCard } from './RecentServicesCard';
import { VehicleListCard } from './VehicleListCard';
import { CollapsibleSection } from './CollapsibleSection';
import { SectionControls } from './SectionControls';
import { useSectionCollapse } from '../../hooks/useSectionCollapse';

interface ClientSearchContentProps {
  searchResult: TechnicianSearchResult;
  onServiceClick?: (service: TechnicianService) => void;
  onUpdateStatus?: (service: TechnicianService) => void;
  onEditService?: (service: TechnicianService) => void;
}

export const ClientSearchContent: React.FC<ClientSearchContentProps> = ({
  searchResult,
  onServiceClick,
  onUpdateStatus,
  onEditService,
}) => {
  const {
    collapsedSections,
    toggleSection,
    isSectionCollapsed,
    expandAllSections,
    collapseAllSections,
  } = useSectionCollapse();

  const totalSections = 4; // client, vehicles, services, summary

  return (
    <>
      {/* Controles de seção */}
      <SectionControls
        expandAllSections={expandAllSections}
        collapseAllSections={collapseAllSections}
        collapsedSections={collapsedSections}
        totalSections={totalSections}
      />

      {/* Grid responsivo de informações */}
      <div className="grid grid-cols-1 xl:grid-cols-2 gap-4 sm:gap-6 mb-6">
        {/* Seção de Dados do Cliente */}
        {searchResult.client && (
          <CollapsibleSection
            sectionType="client"
            title="Dados do Cliente"
            subtitle="Informações pessoais"
            icon={<UserIcon className="h-5 w-5 text-white" />}
            isCollapsed={isSectionCollapsed('client')}
            onToggle={toggleSection}
          >
            <ClientInfoCard client={searchResult.client} />
          </CollapsibleSection>
        )}

        {/* Seção de Veículos */}
        {searchResult.vehicles && (
          <CollapsibleSection
            sectionType="vehicles"
            title="Veículos"
            subtitle={`${searchResult.vehicles?.length || 0} veículo${searchResult.vehicles?.length !== 1 ? 's' : ''} cadastrado${searchResult.vehicles?.length !== 1 ? 's' : ''}`}
            icon={<TruckIcon className="h-5 w-5 text-white" />}
            isCollapsed={isSectionCollapsed('vehicles')}
            onToggle={toggleSection}
          >
            <VehicleListCard vehicles={searchResult.vehicles} />
          </CollapsibleSection>
        )}
      </div>

      {/* Seção de Serviços Recentes */}
      {searchResult.recent_services && (
        <CollapsibleSection
          sectionType="services"
          title="Serviços Recentes"
          subtitle={`${searchResult.recent_services?.length || 0} serviço${searchResult.recent_services?.length !== 1 ? 's' : ''}`}
          icon={<WrenchScrewdriverIcon className="h-5 w-5 text-white" />}
          isCollapsed={isSectionCollapsed('services')}
          onToggle={toggleSection}
          className="mb-6"
        >
          <RecentServicesCard
            services={searchResult.recent_services}
            onServiceClick={onServiceClick}
            onUpdateStatus={onUpdateStatus}
            onEditService={onEditService}
          />
        </CollapsibleSection>
      )}

      {/* Seção de Resumo do Cliente */}
      <CollapsibleSection
        sectionType="summary"
        title="Resumo do Cliente"
        subtitle="Estatísticas e informações gerais"
        icon={<ChartBarIcon className="h-5 w-5 text-white" />}
        isCollapsed={isSectionCollapsed('summary')}
        onToggle={toggleSection}
      >
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div className="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
            <div className="flex items-center gap-3">
              <div className="p-2 bg-blue-500 rounded-lg">
                <TruckIcon className="h-4 w-4 text-white" />
              </div>
              <div>
                <div className="text-2xl font-bold text-blue-600">
                  {searchResult.vehicles?.length || 0}
                </div>
                <div className="text-sm text-gray-600">Veículos</div>
              </div>
            </div>
          </div>

          <div className="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200">
            <div className="flex items-center gap-3">
              <div className="p-2 bg-green-500 rounded-lg">
                <WrenchScrewdriverIcon className="h-4 w-4 text-white" />
              </div>
              <div>
                <div className="text-2xl font-bold text-green-600">
                  {searchResult.recent_services?.length || 0}
                </div>
                <div className="text-sm text-gray-600">Serviços</div>
              </div>
            </div>
          </div>

          <div className="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-4 border border-purple-200">
            <div className="flex items-center gap-3">
              <div className="p-2 bg-purple-500 rounded-lg">
                <UserIcon className="h-4 w-4 text-white" />
              </div>
              <div>
                <div className="text-2xl font-bold text-purple-600">
                  {searchResult.client ? 'Ativo' : 'N/A'}
                </div>
                <div className="text-sm text-gray-600">Status</div>
              </div>
            </div>
          </div>
        </div>
      </CollapsibleSection>
    </>
  );
};
