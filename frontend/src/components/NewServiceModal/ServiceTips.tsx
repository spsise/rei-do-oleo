import React from 'react';

export const ServiceTips: React.FC = () => {
  return (
    <div className="p-4 bg-blue-50/50 rounded-xl border border-blue-100">
      <div className="flex items-start gap-3">
        <div className="p-1.5 bg-blue-100 rounded-lg">
          <svg
            className="w-4 h-4 text-blue-600"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
        </div>
        <div className="text-sm text-blue-800">
          <p className="font-medium mb-1">💡 Dicas para um bom registro:</p>
          <ul className="space-y-1 text-xs">
            <li>• Seja específico na descrição do serviço</li>
            <li>• Estime a duração com precisão para melhor planejamento</li>
            <li>• Registre a quilometragem atual do veículo</li>
            <li>• Use observações para detalhes importantes</li>
            <li>• Agende o serviço se necessário</li>
            <li>• Adicione produtos utilizados na aba "Produtos"</li>
          </ul>
        </div>
      </div>
    </div>
  );
};
