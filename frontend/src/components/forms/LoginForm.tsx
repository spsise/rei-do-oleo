import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Eye, EyeOff, Shield } from 'lucide-react';
import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { useNavigate } from 'react-router-dom';
import { toast } from 'sonner';
import { useAuth } from '../../hooks/useAuth';
import { LoginCredentials } from '../../types';
import { useAuthContext } from '../providers/AuthProvider';
import { LoadingSpinner } from '../ui/LoadingSpinner';

export function LoginForm() {
  const navigate = useNavigate();
  const { login, loading } = useAuth();
  const { csrfInitialized, initializingCsrf } = useAuthContext();
  const [showPassword, setShowPassword] = useState(false);

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<LoginCredentials>();

  const onSubmit = async (data: LoginCredentials) => {
    try {
      await login(data);
      toast.success('Login realizado com sucesso!');
      navigate('/');
    } catch (error: unknown) {
      const errorMessage =
        error instanceof Error ? error.message : 'Erro ao fazer login';
      toast.error(errorMessage);
    }
  };

  // Mostrar loading enquanto CSRF está sendo inicializado
  if (initializingCsrf) {
    return (
      <div className='min-h-screen flex items-center justify-center bg-gray-50 px-4'>
        <Card className='w-full max-w-md'>
          <CardContent className='flex flex-col items-center justify-center py-8'>
            <LoadingSpinner size='lg' className='mb-4' />
            <p className='text-sm text-gray-600 flex items-center'>
              <Shield className='h-4 w-4 mr-2' />
              Inicializando segurança...
            </p>
          </CardContent>
        </Card>
      </div>
    );
  }

  return (
    <div className='min-h-screen flex items-center justify-center bg-gray-50 px-4'>
      <Card className='w-full max-w-md'>
        <CardHeader className='text-center'>
          <CardTitle className='text-2xl font-bold text-gray-900'>
            Sistema Rei do Óleo
          </CardTitle>
          <CardDescription>
            Faça login para acessar o sistema
            <br />
            {csrfInitialized && (
              <span className='ml-2 inline-flex items-center text-green-600'>
                <Shield className='h-3 w-3 mr-1' />
                Seguro
              </span>
            )}
          </CardDescription>
        </CardHeader>

        <CardContent>
          <form onSubmit={handleSubmit(onSubmit)} className='space-y-4'>
            <div className='space-y-2'>
              <Label htmlFor='email'>E-mail</Label>
              <Input
                id='email'
                type='email'
                placeholder='joao@example.com'
                {...register('email', {
                  required: 'E-mail é obrigatório',
                  pattern: {
                    value: /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i,
                    message: 'E-mail inválido',
                  },
                })}
                className={errors.email ? 'border-red-500' : ''}
              />
              {errors.email && (
                <p className='text-sm text-red-600'>{errors.email.message}</p>
              )}
            </div>

            <div className='space-y-2'>
              <Label htmlFor='password'>Senha</Label>
              <div className='relative'>
                <Input
                  id='password'
                  type={showPassword ? 'text' : 'password'}
                  placeholder='MinhaSenh@123'
                  {...register('password', {
                    required: 'Senha é obrigatória',
                    minLength: {
                      value: 6,
                      message: 'Senha deve ter pelo menos 6 caracteres',
                    },
                  })}
                  className={errors.password ? 'border-red-500 pr-10' : 'pr-10'}
                />
                <Button
                  type='button'
                  variant='ghost'
                  size='sm'
                  className='absolute right-0 top-0 h-full px-3 py-2 hover:bg-transparent'
                  onClick={() => setShowPassword(!showPassword)}
                >
                  {showPassword ? (
                    <EyeOff className='h-4 w-4 text-gray-400' />
                  ) : (
                    <Eye className='h-4 w-4 text-gray-400' />
                  )}
                </Button>
              </div>
              {errors.password && (
                <p className='text-sm text-red-600'>
                  {errors.password.message}
                </p>
              )}
            </div>

            <Button
              type='submit'
              className='w-full'
              disabled={loading || !csrfInitialized}
            >
              {loading ? (
                <>
                  <LoadingSpinner size='sm' className='mr-2' />
                  Entrando...
                </>
              ) : (
                'Entrar'
              )}
            </Button>

            {/* <div className='text-center'>
              <Button variant='link' size='sm' type='button'>
                Esqueci minha senha
              </Button>
            </div> */}
          </form>
        </CardContent>
      </Card>
    </div>
  );
}
