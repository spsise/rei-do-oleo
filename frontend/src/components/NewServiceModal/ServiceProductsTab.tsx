import React, { useState } from 'react';
import {
  type CreateTechnicianServiceData,
  type TechnicianProduct,
} from '../../types/technician';
import { ProductSelectionModal } from '../Product/ProductSelectionModal';
import { ServiceItemsList } from '../Technician/ServiceItemsList';
import { FinancialSummary } from './FinancialSummary';

interface ServiceProductsTabProps {
  serviceData: CreateTechnicianServiceData;
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

export const ServiceProductsTab: React.FC<ServiceProductsTabProps> = ({
  serviceData,
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
  const [showProductSelection, setShowProductSelection] = useState(false);

  return (
    <div className="space-y-6">
      {/* Lista de Itens (Carrinho) */}
      <ServiceItemsList
        items={serviceData.items || []}
        onRemoveItem={onRemoveProduct}
        onUpdateQuantity={onUpdateProductQuantity}
        onUpdatePrice={onUpdateProductPrice}
        onUpdateNotes={onUpdateProductNotes}
        onAddProduct={() => setShowProductSelection(true)}
        isLoading={false}
      />

      {/* Modal de Seleção de Produtos */}
      <ProductSelectionModal
        isOpen={showProductSelection}
        onClose={() => setShowProductSelection(false)}
        products={products}
        categories={categories}
        isLoading={isLoadingProducts}
        searchTerm={productSearchTerm}
        onSearch={onProductSearch}
        onAddProduct={onAddProduct}
        selectedProductIds={
          serviceData.items?.map((item) => item.product_id) || []
        }
        compact={true}
      />

      {/* Resumo Financeiro */}
      <FinancialSummary
        itemsTotal={calculateItemsTotal()}
        discountAmount={serviceData.discount_amount || 0}
        finalTotal={calculateFinalTotal()}
      />
    </div>
  );
};
