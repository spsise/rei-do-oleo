import React from 'react';
import {
  type TechnicianSearchResult,
  type TechnicianService,
} from '../../types/technician';
import { ClientSearchContainer } from './ClientSearchContainer';
import { ClientSearchContent } from './ClientSearchContent';
import { ClientSearchHeader } from './ClientSearchHeader';
import { ClientStatisticsCard } from './ClientStatisticsCard';

interface ClientSearchResultsProps {
  searchResult: TechnicianSearchResult;
  onCreateNewService: () => void;
  onServiceClick?: (service: TechnicianService) => void;
}

export const ClientSearchResults: React.FC<ClientSearchResultsProps> = ({
  searchResult,
  onCreateNewService,
  onServiceClick,
}) => {
  return (
    <ClientSearchContainer>
      <ClientSearchHeader onCreateNewService={onCreateNewService} />
      <ClientSearchContent
        searchResult={searchResult}
        onServiceClick={onServiceClick}
      />
      <ClientStatisticsCard searchResult={searchResult} />
    </ClientSearchContainer>
  );
};
