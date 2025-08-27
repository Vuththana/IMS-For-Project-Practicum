// resources/js/components/CreateDelivererForm.tsx

import React from 'react';
import { useForm } from '@inertiajs/react';
import { toast } from 'sonner';

type Props = {
  onSuccess: () => void;
};

export function CreateDelivererForm({ onSuccess }: Props) {
  const { data, setData, post, processing, errors, reset } = useForm({
    name: '',
    type: 'Internal', // Default value
    phone_number: '',
    email: '',
  });

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    post('/deliverers', {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Deliverer created successfully!');
        reset();
        onSuccess(); // Close the modal
      },
      onError: () => {
        toast.error('Failed to create deliverer. Please check the errors.');
      },
    });
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <div>
        <label htmlFor="deliverer-name" className="block text-sm font-medium text-gray-700">Name</label>
        <input type="text" id="deliverer-name" value={data.name} onChange={e => setData('name', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required />
        {errors.name && <p className="text-xs text-red-600 mt-1">{errors.name}</p>}
      </div>
      <div>
        <label htmlFor="deliverer-type" className="block text-sm font-medium text-gray-700">Type</label>
        <select id="deliverer-type" value={data.type} onChange={e => setData('type', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option>Internal</option>
            <option>External</option>
        </select>
        {errors.type && <p className="text-xs text-red-600 mt-1">{errors.type}</p>}
      </div>
      <div>
        <label htmlFor="deliverer-phone" className="block text-sm font-medium text-gray-700">Phone Number</label>
        <input type="text" id="deliverer-phone" value={data.phone_number} onChange={e => setData('phone_number', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        {errors.phone_number && <p className="text-xs text-red-600 mt-1">{errors.phone_number}</p>}
      </div>
       <div className="mt-6 flex justify-end">
        <button type="submit" disabled={processing} className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400">
          {processing ? 'Saving...' : 'Save Deliverer'}
        </button>
      </div>
    </form>
  );
}