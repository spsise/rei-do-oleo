import React from 'react';
import {
  ClientSearchForm,
  ClientSearchResults,
  NewServiceModal,
  TechnicianHeader,
} from '../components/Technician';
import { useTechnician } from '../hooks/useTechnician';

export const TechnicianPage: React.FC = () => {
  const {
    // Estado
    searchType,
    searchValue,
    isSearching,
    searchResult,
    showNewServiceForm,
    isCreatingService,
    newServiceData,

    // Ações
    setSearchType,
    setSearchValue,
    setNewServiceData,
    setShowNewServiceForm,
    handleSearch,
    handleVoiceResult,
    handleCreateNewService,
    handleSubmitService,
  } = useTechnician();

  return (
    <>
      <div className="max-w-6xl mx-auto p-6">
        {/* Header */}
        <TechnicianHeader />

        {/* Search Form */}
        <ClientSearchForm
          searchType={searchType}
          searchValue={searchValue}
          isSearching={isSearching}
          onSearchTypeChange={setSearchType}
          onSearchValueChange={setSearchValue}
          onSearch={handleSearch}
          onVoiceResult={handleVoiceResult}
        />

        {/* Search Results */}
        {searchResult && (
          <ClientSearchResults
            searchResult={searchResult}
            onCreateNewService={handleCreateNewService}
          />
        )}

        {/* New Service Modal */}
        <NewServiceModal
          isOpen={showNewServiceForm}
          onClose={() => setShowNewServiceForm(false)}
          serviceData={newServiceData}
          onServiceDataChange={setNewServiceData}
          vehicles={searchResult?.vehicles || []}
          onSubmit={handleSubmitService}
          isLoading={isCreatingService}
        />
      </div>
    </>
  );
};
