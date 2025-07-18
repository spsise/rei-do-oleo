import React from 'react';
import {
  ClientSearchForm,
  ClientSearchResults,
  NewServiceModal,
  TechnicianHeader,
} from '../components/Technician';
import { useTechnician } from '../hooks/useTechnician';
import '../styles/Technician.css';

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
    products,
    categories,
    isLoadingProducts,
    productSearchTerm,

    // Ações
    setSearchType,
    setSearchValue,
    setNewServiceData,
    setShowNewServiceForm,
    handleSearch,
    handleVoiceResult,
    handleCreateNewService,
    handleSubmitService,
    resetSearch,

    // Métodos para produtos
    searchProducts,
    addProductToService,
    removeProductFromService,
    updateServiceItemQuantity,
    updateServiceItemPrice,
    updateServiceItemNotes,
    calculateItemsTotal,
    calculateFinalTotal,
  } = useTechnician();

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">
      {/* Background Pattern */}
      <div className="absolute inset-0 bg-grid-pattern opacity-5"></div>

      <div className="relative max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
        {/* Header com design melhorado */}
        <div className="mb-2">
          <TechnicianHeader />
        </div>

        {/* Container principal com animações */}
        <div className="space-y-6 animate-fadeIn">
          {/* Search Form com design aprimorado */}
          <div className="transform transition-all duration-300 hover:scale-[1.01]">
            <ClientSearchForm
              searchType={searchType}
              searchValue={searchValue}
              isSearching={isSearching}
              onSearchTypeChange={setSearchType}
              onSearchValueChange={setSearchValue}
              onSearch={handleSearch}
              onVoiceResult={handleVoiceResult}
            />
          </div>

          {/* Search Results com animação de entrada */}
          {searchResult && (
            <div className="animate-slideInUp">
              <ClientSearchResults
                searchResult={searchResult}
                onCreateNewService={handleCreateNewService}
              />
            </div>
          )}

          {/* Estado vazio melhorado */}
          {!searchResult && !isSearching && searchValue && (
            <div className="bg-white rounded-2xl shadow-xl border border-gray-100 p-12 text-center animate-fadeIn">
              <div className="max-w-md mx-auto">
                <div className="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                  <svg
                    className="w-8 h-8 text-red-500"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
                    />
                  </svg>
                </div>
                <h3 className="text-xl font-semibold text-gray-900 mb-2">
                  Cliente não encontrado
                </h3>
                <p className="text-gray-600 mb-6">
                  Verifique se os dados estão corretos e tente novamente.
                </p>
                <button
                  onClick={resetSearch}
                  className="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors font-medium"
                >
                  Nova Busca
                </button>
              </div>
            </div>
          )}

          {/* Estado inicial com instruções */}
          {!searchResult && !searchValue && (
            <div className="bg-white rounded-2xl shadow-xl border border-gray-100 p-12 text-center animate-fadeIn">
              <div className="max-w-lg mx-auto">
                <div className="w-20 h-20 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center mx-auto mb-6">
                  <svg
                    className="w-10 h-10 text-white"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                    />
                  </svg>
                </div>
                <h3 className="text-2xl font-bold text-gray-900 mb-4">
                  Busque um Cliente
                </h3>
                <p className="text-gray-600 mb-8 leading-relaxed">
                  Digite a placa do veículo ou documento do cliente para começar
                  a registrar serviços. Você também pode usar o microfone para
                  busca por voz.
                </p>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-500">
                  <div className="flex items-center justify-center gap-2">
                    <div className="w-2 h-2 bg-blue-500 rounded-full"></div>
                    <span>Placa do veículo (ex: ABC1234)</span>
                  </div>
                  <div className="flex items-center justify-center gap-2">
                    <div className="w-2 h-2 bg-indigo-500 rounded-full"></div>
                    <span>CPF/CNPJ do cliente</span>
                  </div>
                </div>
              </div>
            </div>
          )}
        </div>

        {/* New Service Modal */}
        <NewServiceModal
          isOpen={showNewServiceForm}
          onClose={() => setShowNewServiceForm(false)}
          serviceData={newServiceData}
          onServiceDataChange={setNewServiceData}
          vehicles={searchResult?.vehicles || []}
          onSubmit={handleSubmitService}
          isLoading={isCreatingService}
          // Props para produtos
          products={products}
          categories={categories}
          isLoadingProducts={isLoadingProducts}
          productSearchTerm={productSearchTerm}
          onProductSearch={searchProducts}
          onAddProduct={addProductToService}
          onRemoveProduct={removeProductFromService}
          onUpdateProductQuantity={updateServiceItemQuantity}
          onUpdateProductPrice={updateServiceItemPrice}
          onUpdateProductNotes={updateServiceItemNotes}
          calculateItemsTotal={calculateItemsTotal}
          calculateFinalTotal={calculateFinalTotal}
        />
      </div>
    </div>
  );
};
