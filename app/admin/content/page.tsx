'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';

interface ContentItem {
  id: number;
  page_name: string;
  section_key: string;
  content_value: string;
  content_type: string;
  display_order: number;
  is_active: boolean;
}

export default function AdminContent() {
  const [content, setContent] = useState<ContentItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [editingId, setEditingId] = useState<number | null>(null);
  const [editForm, setEditForm] = useState<Partial<ContentItem>>({});

  useEffect(() => {
    fetchContent();
  }, []);

  const fetchContent = async () => {
    try {
      const response = await fetch('/api/admin/content');
      const data = await response.json();
      if (data.content) {
        setContent(data.content);
      }
    } catch (error) {
      console.error('Error fetching content:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleEdit = (item: ContentItem) => {
    setEditingId(item.id);
    setEditForm({ ...item });
  };

  const handleSave = async () => {
    if (!editingId) return;

    try {
      const response = await fetch('/api/admin/content', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          id: editingId,
          content_value: editForm.content_value,
          is_active: editForm.is_active,
          display_order: editForm.display_order,
        }),
      });

      if (response.ok) {
        setEditingId(null);
        fetchContent();
      }
    } catch (error) {
      console.error('Error updating content:', error);
    }
  };

  const handleCancel = () => {
    setEditingId(null);
    setEditForm({});
  };

  if (loading) {
    return <div>Loading...</div>;
  }

  // Group content by page
  const groupedContent = content.reduce((acc, item) => {
    if (!acc[item.page_name]) {
      acc[item.page_name] = [];
    }
    acc[item.page_name].push(item);
    return acc;
  }, {} as Record<string, ContentItem[]>);

  return (
    <div>
      <div className="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center mb-6">
        <h1 className="text-2xl sm:text-3xl font-bold text-gray-900">Site Content Management</h1>
        <Link href="/admin" className="text-primary-600 hover:text-primary-700 text-sm sm:text-base">
          ← Back to Dashboard
        </Link>
      </div>

      {Object.entries(groupedContent).map(([pageName, items]) => (
        <div key={pageName} className="bg-white rounded-lg shadow mb-6 p-4 sm:p-6">
          <h2 className="text-lg sm:text-xl font-bold text-gray-900 mb-4 capitalize">{pageName} Page</h2>
          <div className="space-y-4">
            {items.map((item) => (
              <div key={item.id} className="border-b pb-4 last:border-b-0">
                {editingId === item.id ? (
                  <div className="space-y-2">
                    <label className="block text-sm font-medium text-gray-700">
                      {item.section_key}
                    </label>
                    <textarea
                      value={editForm.content_value || ''}
                      onChange={(e) => setEditForm({ ...editForm, content_value: e.target.value })}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md"
                      rows={3}
                    />
                    <div className="flex flex-wrap items-center gap-x-4 gap-y-2">
                      <label className="flex items-center">
                        <input
                          type="checkbox"
                          checked={editForm.is_active}
                          onChange={(e) => setEditForm({ ...editForm, is_active: e.target.checked })}
                          className="mr-2"
                        />
                        Active
                      </label>
                      <input
                        type="number"
                        value={editForm.display_order || 0}
                        onChange={(e) => setEditForm({ ...editForm, display_order: parseInt(e.target.value) })}
                        placeholder="Order"
                        className="w-20 px-2 py-1 border border-gray-300 rounded-md"
                      />
                    </div>
                    <div className="flex flex-wrap gap-2">
                      <button
                        onClick={handleSave}
                        className="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700"
                      >
                        Save
                      </button>
                      <button
                        onClick={handleCancel}
                        className="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                      >
                        Cancel
                      </button>
                    </div>
                  </div>
                ) : (
                  <div className="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-start">
                    <div className="flex-1">
                      <div className="text-sm font-medium text-gray-500">{item.section_key}</div>
                      <div className="mt-1 text-gray-900">{item.content_value}</div>
                      <div className="mt-1 text-xs text-gray-400">
                        {item.is_active ? 'Active' : 'Inactive'} • Order: {item.display_order}
                      </div>
                    </div>
                    <button
                      onClick={() => handleEdit(item)}
                      className="sm:ml-4 px-3 py-1 text-sm bg-primary-600 text-white rounded-md hover:bg-primary-700 w-fit"
                    >
                      Edit
                    </button>
                  </div>
                )}
              </div>
            ))}
          </div>
        </div>
      ))}
    </div>
  );
}
