import React, { useState } from 'react';
import {
  type CreateTechnicianServiceData,
  type TechnicianProduct,
  type TechnicianVehicle,
} from '../../types/technician';
import { ModalFooter } from './ModalFooter';
import { ModalHeader } from './ModalHeader';
import { ServiceDetailsTab } from './ServiceDetailsTab';
import { ServiceProductsTab } from './ServiceProductsTab';

interface NewServiceModalProps {
  isOpen: boolean;
  onClose: () => void;
  serviceData: CreateTechnicianServiceData;
  onServiceDataChange: (data: CreateTechnicianServiceData) => void;
  vehicles: TechnicianVehicle[];
  onSubmit: () => void;
  isLoading?: boolean;
  // Props para produtos
  products: TechnicianProduct[];
  categories: Array<{ id: number; name: string }>;
  isLoadingProducts: boolean;
  productSearchTerm: string;
  onProductSearch: (search: string) => void;
  onAddProduct: (
    product: TechnicianProduct,
    quantity: number,
    notes?: string
  ) => void;
  onRemoveProduct: (productId: number) => void;
  onUpdateProductQuantity: (productId: number, quantity: number) => void;
  onUpdateProductPrice: (productId: number, price: number) => void;
  onUpdateProductNotes: (productId: number, notes: string) => void;
  calculateItemsTotal: () => number;
  calculateFinalTotal: () => number;
}

export const NewServiceModal: React.FC<NewServiceModalProps> = ({
  isOpen,
  onClose,
  serviceData,
  onServiceDataChange,
  vehicles,
  onSubmit,
  isLoading = false,
  products,
  categories,
  isLoadingProducts,
  productSearchTerm,
  onProductSearch,
  onAddProduct,
  onRemoveProduct,
  onUpdateProductQuantity,
  onUpdateProductPrice,
  onUpdateProductNotes,
  calculateItemsTotal,
  calculateFinalTotal,
}) => {
  const [activeTab, setActiveTab] = useState<'details' | 'products'>('details');
  const [isMaximized, setIsMaximized] = useState(false);

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50 animate-modalFadeIn">
      <div
        className={`bg-white rounded-2xl shadow-2xl overflow-hidden animate-modalSlideInUp transition-all duration-300 ${
          isMaximized
            ? 'w-full h-full max-w-none max-h-none rounded-none'
            : 'w-full max-w-6xl max-h-[90vh]'
        }`}
      >
        {/* Header */}
        <ModalHeader
          isMaximized={isMaximized}
          setIsMaximized={setIsMaximized}
          onClose={onClose}
          isLoading={isLoading}
          activeTab={activeTab}
          setActiveTab={setActiveTab}
          itemsCount={serviceData.items?.length || 0}
        />

        {/* Content */}
        <div
          className={`p-6 ${isMaximized ? 'h-[calc(100vh-200px)] overflow-y-auto' : 'max-h-[60vh] overflow-y-auto'}`}
        >
          {activeTab === 'details' ? (
            <ServiceDetailsTab
              serviceData={serviceData}
              onServiceDataChange={onServiceDataChange}
              vehicles={vehicles}
            />
          ) : (
            <ServiceProductsTab
              serviceData={serviceData}
              products={products}
              categories={categories}
              isLoadingProducts={isLoadingProducts}
              productSearchTerm={productSearchTerm}
              onProductSearch={onProductSearch}
              onAddProduct={onAddProduct}
              onRemoveProduct={onRemoveProduct}
              onUpdateProductQuantity={onUpdateProductQuantity}
              onUpdateProductPrice={onUpdateProductPrice}
              onUpdateProductNotes={onUpdateProductNotes}
              calculateItemsTotal={calculateItemsTotal}
              calculateFinalTotal={calculateFinalTotal}
            />
          )}
        </div>

        {/* Footer */}
        <ModalFooter
          onClose={onClose}
          onSubmit={onSubmit}
          isLoading={isLoading}
          activeTab={activeTab}
          serviceData={serviceData}
          calculateFinalTotal={calculateFinalTotal}
        />
      </div>
    </div>
  );
};
