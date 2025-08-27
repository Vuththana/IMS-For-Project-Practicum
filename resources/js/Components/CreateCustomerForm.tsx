// resources/js/components/CreateCustomerForm.tsx

import React, { useEffect } from 'react';
import { useForm } from '@inertiajs/react';
import { toast } from 'sonner';

type Props = {
  onSuccess: () => void; // A function to call after successful creation
};

export function CreateCustomerForm({ onSuccess }: Props) {
  const { data, setData, post, processing, errors, wasSuccessful, reset } = useForm({
    name: '',
    phone: '',
    email: '',
    address: '',
  });

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    post('/customers', {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Customer created successfully!');
        reset(); // Clear the form fields
        onSuccess(); // Close the modal
      },
      onError: () => {
        toast.error('Failed to create customer. Please check the errors.');
      },
    });
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <div>
        <label htmlFor="name" className="block text-sm font-medium text-gray-700">Name</label>
        <input type="text" id="name" value={data.name} onChange={e => setData('name', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required />
        {errors.name && <p className="text-xs text-red-600 mt-1">{errors.name}</p>}
      </div>
      <div>
        <label htmlFor="phone" className="block text-sm font-medium text-gray-700">Phone</label>
        <input type="text" id="phone" value={data.phone} onChange={e => setData('phone', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        {errors.phone && <p className="text-xs text-red-600 mt-1">{errors.phone}</p>}
      </div>
      <div>
        <label htmlFor="email" className="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" id="email" value={data.email} onChange={e => setData('email', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        {errors.email && <p className="text-xs text-red-600 mt-1">{errors.email}</p>}
      </div>
       <div className="mt-6 flex justify-end">
        <button type="submit" disabled={processing} className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400">
          {processing ? 'Saving...' : 'Save Customer'}
        </button>
      </div>
    </form>
  );
}