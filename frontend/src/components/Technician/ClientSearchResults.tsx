import React from 'react';
import { type TechnicianSearchResult } from '../../types/technician';
import { ClientSearchContainer } from './ClientSearchContainer';
import { ClientSearchContent } from './ClientSearchContent';
import { ClientSearchHeader } from './ClientSearchHeader';
import { ClientStatisticsCard } from './ClientStatisticsCard';

interface ClientSearchResultsProps {
  searchResult: TechnicianSearchResult;
  onCreateNewService: () => void;
}

export const ClientSearchResults: React.FC<ClientSearchResultsProps> = ({
  searchResult,
  onCreateNewService,
}) => {
  return (
    <ClientSearchContainer>
      <ClientSearchHeader onCreateNewService={onCreateNewService} />
      <ClientSearchContent searchResult={searchResult} />
      <ClientStatisticsCard searchResult={searchResult} />
    </ClientSearchContainer>
  );
};
