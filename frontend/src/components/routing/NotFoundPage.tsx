import { Link } from 'react-router-dom';

export const NotFoundPage = () => {
  return (
    <div className="min-h-screen flex items-center justify-center">
      <div className="text-center">
        <h1 className="text-4xl font-bold text-gray-900 mb-4">404</h1>
        <p className="text-gray-600 mb-4">Página não encontrada</p>
        <Link
          to="/home"
          className="text-brand-600 hover:text-brand-500 transition-colors"
        >
          Voltar ao Dashboard
        </Link>
      </div>
    </div>
  );
};
