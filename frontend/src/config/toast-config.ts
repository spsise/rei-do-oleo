import type { ToastOptions } from 'react-hot-toast';

export const toastConfig: ToastOptions = {
  position: 'top-right',
  duration: 4000,
  style: {
    background: '#363636',
    color: '#fff',
  },
};

export const successToastConfig: ToastOptions = {
  duration: 3000,
  iconTheme: {
    primary: '#10B981',
    secondary: '#fff',
  },
};

export const errorToastConfig: ToastOptions = {
  duration: 5000,
  iconTheme: {
    primary: '#EF4444',
    secondary: '#fff',
  },
};
