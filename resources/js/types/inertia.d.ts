// resources/js/types/inertia.d.ts

import { PageProps as InertiaPageProps } from '@inertiajs/core';

// Define the shape of the User object
export type User = {
  id: number;
  name: string;
  email: string;
};

// Define the shape of the props that are shared on every page
export type SharedProps = {
  auth: {
    user: User | null; // User can be null if they are a guest
  };
  // You can add other shared props here, like flash messages
  flash: {
    success: string | null;
  }
};

// Augment Inertia's PageProps interface
declare module '@inertiajs/core' {
  interface PageProps extends InertiaPageProps, SharedProps {}
}