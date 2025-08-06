import React from 'react';

interface ClientSearchContainerProps {
  children: React.ReactNode;
}

export const ClientSearchContainer: React.FC<ClientSearchContainerProps> = ({
  children,
}) => {
  return (
    <div className="bg-white/90 backdrop-blur-sm rounded-xl shadow-lg border border-white/20 p-4 sm:p-6 relative overflow-hidden">
      {/* Background gradient overlay */}
      <div className="absolute inset-0 bg-gradient-to-br from-green-50/30 to-emerald-50/30"></div>

      {/* Content */}
      <div className="relative z-10">{children}</div>
    </div>
  );
};
