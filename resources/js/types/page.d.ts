import { PageProps as InertiaPageProps } from '@inertiajs/core';

export type User = {
    id: number;
    name: string;
    email: string;
    
  };
  
  export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
      user: User | null;
      token: string | null;
    };
    flash: {
      success: string | null;
    }
  };

  declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps, SharedProps {}
  }