interface LoadingSpinnerProps {
  size?: 'sm' | 'md' | 'lg';
  className?: string;
}

export const LoadingSpinner = ({
  size = 'md',
  className = '',
}: LoadingSpinnerProps) => {
  const sizeClasses = {
    sm: 'h-6 w-6',
    md: 'h-12 w-12',
    lg: 'h-16 w-16',
  };

  return (
    <div className="min-h-screen flex items-center justify-center">
      <div
        className={`animate-spin rounded-full border-b-2 border-brand-600 ${sizeClasses[size]} ${className}`}
      />
    </div>
  );
};
