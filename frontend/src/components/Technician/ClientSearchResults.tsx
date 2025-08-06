import React from 'react';
import {
  type TechnicianSearchResult,
  type TechnicianService,
} from '../../types/technician';
import { ClientSearchContainer } from './ClientSearchContainer';
import { ClientSearchContent } from './ClientSearchContent';
import { ClientSearchHeader } from './ClientSearchHeader';

interface ClientSearchResultsProps {
  searchResult: TechnicianSearchResult;
  onCreateNewService: () => void;
  onServiceClick?: (service: TechnicianService) => void;
  onUpdateStatus?: (service: TechnicianService) => void;
  onEditService?: (service: TechnicianService) => void;
}

export const ClientSearchResults: React.FC<ClientSearchResultsProps> = ({
  searchResult,
  onCreateNewService,
  onServiceClick,
  onUpdateStatus,
  onEditService,
}) => {
  return (
    <ClientSearchContainer>
      <ClientSearchHeader onCreateNewService={onCreateNewService} />
      <ClientSearchContent
        searchResult={searchResult}
        onServiceClick={onServiceClick}
        onUpdateStatus={onUpdateStatus}
        onEditService={onEditService}
      />
    </ClientSearchContainer>
  );
};
