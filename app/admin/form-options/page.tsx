'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';

interface FormOption {
  id: number;
  category_name: string;
  field_type: string;
  option_value: string;
  display_order: number;
  is_active: boolean;
}

export default function AdminFormOptions() {
  const [options, setOptions] = useState<FormOption[]>([]);
  const [loading, setLoading] = useState(true);
  const [showAddForm, setShowAddForm] = useState(false);
  const [newOption, setNewOption] = useState({
    category_name: '',
    field_type: '',
    option_value: '',
    display_order: 0,
    is_active: true,
  });

  useEffect(() => {
    fetchOptions();
  }, []);

  const fetchOptions = async () => {
    try {
      const response = await fetch('/api/admin/form-options');
      const data = await response.json();
      if (data.options) {
        setOptions(data.options);
      }
    } catch (error) {
      console.error('Error fetching options:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleAdd = async () => {
    try {
      const response = await fetch('/api/admin/form-options', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(newOption),
      });

      if (response.ok) {
        setShowAddForm(false);
        setNewOption({
          category_name: '',
          field_type: '',
          option_value: '',
          display_order: 0,
          is_active: true,
        });
        fetchOptions();
      }
    } catch (error) {
      console.error('Error adding option:', error);
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Are you sure you want to delete this option?')) return;

    try {
      const response = await fetch(`/api/admin/form-options?id=${id}`, {
        method: 'DELETE',
      });

      if (response.ok) {
        fetchOptions();
      }
    } catch (error) {
      console.error('Error deleting option:', error);
    }
  };

  if (loading) {
    return <div>Loading...</div>;
  }

  const groupedOptions = options.reduce((acc, option) => {
    const key = `${option.category_name}-${option.field_type}`;
    if (!acc[key]) {
      acc[key] = [];
    }
    acc[key].push(option);
    return acc;
  }, {} as Record<string, FormOption[]>);

  return (
    <div>
      <div className="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center mb-6">
        <h1 className="text-2xl sm:text-3xl font-bold text-gray-900">Form Options Management</h1>
        <div className="flex flex-wrap gap-3 sm:gap-4">
          <button
            onClick={() => setShowAddForm(!showAddForm)}
            className="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700"
          >
            {showAddForm ? 'Cancel' : 'Add Option'}
          </button>
          <Link href="/admin" className="text-primary-600 hover:text-primary-700">
            ← Back to Dashboard
          </Link>
        </div>
      </div>

      {showAddForm && (
        <div className="bg-white p-4 sm:p-6 rounded-lg shadow mb-6">
          <h2 className="text-lg sm:text-xl font-bold mb-4">Add New Option</h2>
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <input
              type="text"
              placeholder="Category Name (e.g., Work Type)"
              value={newOption.category_name}
              onChange={(e) => setNewOption({ ...newOption, category_name: e.target.value })}
              className="px-3 py-2 border border-gray-300 rounded-md"
            />
            <input
              type="text"
              placeholder="Field Type (e.g., work_type)"
              value={newOption.field_type}
              onChange={(e) => setNewOption({ ...newOption, field_type: e.target.value })}
              className="px-3 py-2 border border-gray-300 rounded-md"
            />
            <input
              type="text"
              placeholder="Option Value (e.g., Kitchen)"
              value={newOption.option_value}
              onChange={(e) => setNewOption({ ...newOption, option_value: e.target.value })}
              className="px-3 py-2 border border-gray-300 rounded-md"
            />
            <input
              type="number"
              placeholder="Display Order"
              value={newOption.display_order}
              onChange={(e) => setNewOption({ ...newOption, display_order: parseInt(e.target.value) || 0 })}
              className="px-3 py-2 border border-gray-300 rounded-md"
            />
          </div>
          <button
            onClick={handleAdd}
            className="mt-4 px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700"
          >
            Add Option
          </button>
        </div>
      )}

      {Object.entries(groupedOptions).map(([key, items]) => (
        <div key={key} className="bg-white rounded-lg shadow mb-6 p-4 sm:p-6">
          <h2 className="text-lg sm:text-xl font-bold text-gray-900 mb-4">
            {items[0].category_name} ({items[0].field_type})
          </h2>
          <div className="space-y-2">
            {items.map((option) => (
              <div key={option.id} className="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center border-b pb-2">
                <div>
                  <span className="font-medium">{option.option_value}</span>
                  <span className="text-sm text-gray-500 sm:ml-2 block sm:inline">
                    Order: {option.display_order} • {option.is_active ? 'Active' : 'Inactive'}
                  </span>
                </div>
                <button
                  onClick={() => handleDelete(option.id)}
                  className="px-3 py-1 text-sm bg-red-600 text-white rounded-md hover:bg-red-700 w-fit"
                >
                  Delete
                </button>
              </div>
            ))}
          </div>
        </div>
      ))}
    </div>
  );
}
