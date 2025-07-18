import React from 'react';

interface FinancialSummaryProps {
  itemsTotal: number;
  discountAmount: number;
  finalTotal: number;
}

export const FinancialSummary: React.FC<FinancialSummaryProps> = ({
  itemsTotal,
  discountAmount,
  finalTotal,
}) => {
  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL',
    }).format(price);
  };

  return (
    <div className="p-4 bg-gray-50 rounded-xl space-y-3">
      <h4 className="font-semibold text-gray-900">Resumo Financeiro</h4>
      <div className="space-y-2">
        <div className="flex justify-between text-sm">
          <span className="text-gray-600">Total dos Produtos:</span>
          <span className="font-medium">{formatPrice(itemsTotal)}</span>
        </div>
        <div className="flex justify-between text-sm">
          <span className="text-gray-600">Desconto:</span>
          <span className="font-medium text-red-600">
            -{formatPrice(discountAmount)}
          </span>
        </div>
        <div className="border-t pt-2 flex justify-between font-semibold text-lg">
          <span className="text-gray-900">Total Final:</span>
          <span className="text-green-600">{formatPrice(finalTotal)}</span>
        </div>
      </div>
    </div>
  );
};
