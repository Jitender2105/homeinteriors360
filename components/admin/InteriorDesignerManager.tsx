'use client';

import { useEffect, useMemo, useState } from 'react';

type SectionType = 'projects' | 'testimonials' | 'trust_points' | 'usps';

interface InteriorDesigner {
  id: number;
  full_name: string;
  slug: string;
  profile_title?: string;
  bio?: string;
  profile_image?: string;
  years_experience: number;
  total_projects: number;
  is_active: boolean;
}

interface SectionItem {
  id: number;
  [key: string]: unknown;
}

const SECTION_LABELS: Record<SectionType, string> = {
  projects: 'Projects Slider',
  testimonials: 'Testimonials Slider',
  trust_points: 'Why Trust Us Slider',
  usps: 'USP Slider',
};

export default function InteriorDesignerManager() {
  const [designers, setDesigners] = useState<InteriorDesigner[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedDesignerId, setSelectedDesignerId] = useState<number | null>(null);
  const [sections, setSections] = useState<Record<SectionType, SectionItem[]>>({
    projects: [],
    testimonials: [],
    trust_points: [],
    usps: [],
  });
  const [status, setStatus] = useState<string>('');

  const [designerForm, setDesignerForm] = useState({
    full_name: '',
    slug: '',
    profile_title: '',
    bio: '',
    profile_image: '',
    years_experience: 0,
    total_projects: 0,
    is_active: true,
  });

  const [projectForm, setProjectForm] = useState({
    image_url: '',
    location: '',
    cost_range: '',
    work_type: '',
    project_title: '',
    display_order: 0,
  });
  const [testimonialForm, setTestimonialForm] = useState({
    customer_name: '',
    customer_location: '',
    testimonial_text: '',
    rating: 5,
    display_order: 0,
  });
  const [trustForm, setTrustForm] = useState({
    title: '',
    description: '',
    display_order: 0,
  });
  const [uspForm, setUspForm] = useState({
    title: '',
    description: '',
    display_order: 0,
  });

  const selectedDesigner = useMemo(
    () => designers.find((item) => item.id === selectedDesignerId) || null,
    [designers, selectedDesignerId]
  );

  const fetchDesigners = async () => {
    const res = await fetch('/api/admin/interior-designers');
    const data = await res.json();
    if (res.ok) {
      setDesigners(data.designers || []);
      if (!selectedDesignerId && data.designers?.length) {
        setSelectedDesignerId(data.designers[0].id);
      }
    } else {
      setStatus(data.error || 'Failed to fetch designers');
    }
  };

  const fetchSection = async (designerId: number, type: SectionType) => {
    const res = await fetch(`/api/admin/interior-designers/${designerId}/sections?type=${type}`);
    const data = await res.json();
    if (res.ok) {
      setSections((prev) => ({ ...prev, [type]: data.items || [] }));
    }
  };

  const fetchAllSections = async (designerId: number) => {
    await Promise.all((Object.keys(SECTION_LABELS) as SectionType[]).map((type) => fetchSection(designerId, type)));
  };

  useEffect(() => {
    (async () => {
      try {
        await fetchDesigners();
      } finally {
        setLoading(false);
      }
    })();
  }, []);

  useEffect(() => {
    if (selectedDesignerId) {
      fetchAllSections(selectedDesignerId);
    }
  }, [selectedDesignerId]);

  const handleCreateDesigner = async () => {
    if (!designerForm.full_name.trim()) return;
    const res = await fetch('/api/admin/interior-designers', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(designerForm),
    });
    const data = await res.json();
    if (!res.ok) {
      setStatus(data.error || 'Failed to create designer');
      return;
    }
    setStatus('Designer created successfully.');
    setDesignerForm({
      full_name: '',
      slug: '',
      profile_title: '',
      bio: '',
      profile_image: '',
      years_experience: 0,
      total_projects: 0,
      is_active: true,
    });
    await fetchDesigners();
  };

  const handleDeleteDesigner = async (designerId: number) => {
    if (!confirm('Delete this interior designer and all microsite content?')) return;
    const res = await fetch(`/api/admin/interior-designers/${designerId}`, { method: 'DELETE' });
    const data = await res.json();
    if (!res.ok) {
      setStatus(data.error || 'Failed to delete designer');
      return;
    }
    setStatus('Designer deleted.');
    if (selectedDesignerId === designerId) setSelectedDesignerId(null);
    await fetchDesigners();
  };

  const createSectionItem = async (type: SectionType, payload: Record<string, unknown>) => {
    if (!selectedDesignerId) return;
    const res = await fetch(`/api/admin/interior-designers/${selectedDesignerId}/sections?type=${type}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const data = await res.json();
    if (!res.ok) {
      setStatus(data.error || `Failed to create ${type}`);
      return;
    }
    setStatus(`${SECTION_LABELS[type]} item added.`);
    await fetchSection(selectedDesignerId, type);
  };

  const deleteSectionItem = async (type: SectionType, itemId: number) => {
    if (!selectedDesignerId) return;
    const res = await fetch(
      `/api/admin/interior-designers/${selectedDesignerId}/sections?type=${type}&itemId=${itemId}`,
      { method: 'DELETE' }
    );
    const data = await res.json();
    if (!res.ok) {
      setStatus(data.error || `Failed to delete ${type} item`);
      return;
    }
    await fetchSection(selectedDesignerId, type);
  };

  if (loading) return <div>Loading...</div>;

  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <h1 className="text-2xl sm:text-3xl font-bold text-gray-900">Interior Designer Microsites</h1>
        <a href="/admin" className="text-primary-600 hover:text-primary-700 text-sm sm:text-base">
          ← Back to Dashboard
        </a>
      </div>

      {status && <div className="rounded-md border border-primary-200 bg-primary-50 px-4 py-2 text-sm text-primary-700">{status}</div>}

      <div className="bg-white p-4 sm:p-6 rounded-lg shadow space-y-4">
        <h2 className="text-lg sm:text-xl font-bold text-gray-900">Onboard New Interior Designer</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <input
            value={designerForm.full_name}
            onChange={(e) => setDesignerForm((prev) => ({ ...prev, full_name: e.target.value }))}
            placeholder="Designer full name"
            className="px-3 py-2 border rounded-md"
          />
          <input
            value={designerForm.slug}
            onChange={(e) => setDesignerForm((prev) => ({ ...prev, slug: e.target.value }))}
            placeholder="Slug (optional, auto-generated)"
            className="px-3 py-2 border rounded-md"
          />
          <input
            value={designerForm.profile_title}
            onChange={(e) => setDesignerForm((prev) => ({ ...prev, profile_title: e.target.value }))}
            placeholder="Profile title"
            className="px-3 py-2 border rounded-md"
          />
          <input
            value={designerForm.profile_image}
            onChange={(e) => setDesignerForm((prev) => ({ ...prev, profile_image: e.target.value }))}
            placeholder="Profile image URL"
            className="px-3 py-2 border rounded-md"
          />
          <input
            type="number"
            value={designerForm.years_experience}
            onChange={(e) => setDesignerForm((prev) => ({ ...prev, years_experience: Number(e.target.value) || 0 }))}
            placeholder="Years experience"
            className="px-3 py-2 border rounded-md"
          />
          <input
            type="number"
            value={designerForm.total_projects}
            onChange={(e) => setDesignerForm((prev) => ({ ...prev, total_projects: Number(e.target.value) || 0 }))}
            placeholder="Total projects"
            className="px-3 py-2 border rounded-md"
          />
        </div>
        <textarea
          value={designerForm.bio}
          onChange={(e) => setDesignerForm((prev) => ({ ...prev, bio: e.target.value }))}
          placeholder="Short bio"
          className="w-full px-3 py-2 border rounded-md"
          rows={3}
        />
        <button onClick={handleCreateDesigner} className="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
          Create Designer Microsite
        </button>
      </div>

      <div className="bg-white p-4 sm:p-6 rounded-lg shadow">
        <h2 className="text-lg sm:text-xl font-bold text-gray-900 mb-4">Onboarded Designers</h2>
        {designers.length === 0 ? (
          <p className="text-gray-600">No interior designers added yet.</p>
        ) : (
          <div className="space-y-3">
            {designers.map((item) => (
              <div key={item.id} className="border rounded-md p-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                  <p className="font-semibold text-gray-900">{item.full_name}</p>
                  <p className="text-sm text-gray-600">Slug: {item.slug}</p>
                  <p className="text-sm text-gray-600">Landing URL: /designer/{item.slug}</p>
                </div>
                <div className="flex gap-2">
                  <button
                    onClick={() => setSelectedDesignerId(item.id)}
                    className="px-3 py-1.5 text-sm bg-gray-900 text-white rounded-md hover:bg-black"
                  >
                    Manage Content
                  </button>
                  <button
                    onClick={() => handleDeleteDesigner(item.id)}
                    className="px-3 py-1.5 text-sm bg-red-600 text-white rounded-md hover:bg-red-700"
                  >
                    Delete
                  </button>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      {selectedDesigner && (
        <div className="space-y-6">
          <div className="bg-white p-4 sm:p-6 rounded-lg shadow">
            <h2 className="text-lg sm:text-xl font-bold text-gray-900 mb-2">
              Manage Microsite Content: {selectedDesigner.full_name}
            </h2>
            <p className="text-sm text-gray-600">Public page: /designer/{selectedDesigner.slug}</p>
          </div>

          <div className="bg-white p-4 sm:p-6 rounded-lg shadow space-y-3">
            <h3 className="font-semibold text-gray-900">{SECTION_LABELS.projects}</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
              <input value={projectForm.image_url} onChange={(e) => setProjectForm((p) => ({ ...p, image_url: e.target.value }))} placeholder="Image URL" className="px-3 py-2 border rounded-md" />
              <input value={projectForm.project_title} onChange={(e) => setProjectForm((p) => ({ ...p, project_title: e.target.value }))} placeholder="Project title" className="px-3 py-2 border rounded-md" />
              <input value={projectForm.location} onChange={(e) => setProjectForm((p) => ({ ...p, location: e.target.value }))} placeholder="Location" className="px-3 py-2 border rounded-md" />
              <input value={projectForm.cost_range} onChange={(e) => setProjectForm((p) => ({ ...p, cost_range: e.target.value }))} placeholder="Cost range" className="px-3 py-2 border rounded-md" />
              <input value={projectForm.work_type} onChange={(e) => setProjectForm((p) => ({ ...p, work_type: e.target.value }))} placeholder="Work type" className="px-3 py-2 border rounded-md" />
              <input type="number" value={projectForm.display_order} onChange={(e) => setProjectForm((p) => ({ ...p, display_order: Number(e.target.value) || 0 }))} placeholder="Display order" className="px-3 py-2 border rounded-md" />
            </div>
            <button onClick={() => createSectionItem('projects', projectForm)} className="px-4 py-2 bg-primary-600 text-white rounded-md">Add Project</button>
            <div className="space-y-2">
              {sections.projects.map((item) => (
                <div key={item.id as number} className="border rounded p-2 flex justify-between items-center">
                  <span className="text-sm text-gray-700">{String(item.project_title || item.image_url || 'Project')}</span>
                  <button onClick={() => deleteSectionItem('projects', item.id as number)} className="text-sm text-red-600">Delete</button>
                </div>
              ))}
            </div>
          </div>

          <div className="bg-white p-4 sm:p-6 rounded-lg shadow space-y-3">
            <h3 className="font-semibold text-gray-900">{SECTION_LABELS.testimonials}</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
              <input value={testimonialForm.customer_name} onChange={(e) => setTestimonialForm((p) => ({ ...p, customer_name: e.target.value }))} placeholder="Customer name" className="px-3 py-2 border rounded-md" />
              <input value={testimonialForm.customer_location} onChange={(e) => setTestimonialForm((p) => ({ ...p, customer_location: e.target.value }))} placeholder="Customer location" className="px-3 py-2 border rounded-md" />
              <input type="number" value={testimonialForm.rating} onChange={(e) => setTestimonialForm((p) => ({ ...p, rating: Number(e.target.value) || 5 }))} placeholder="Rating (1-5)" className="px-3 py-2 border rounded-md" />
              <input type="number" value={testimonialForm.display_order} onChange={(e) => setTestimonialForm((p) => ({ ...p, display_order: Number(e.target.value) || 0 }))} placeholder="Display order" className="px-3 py-2 border rounded-md" />
            </div>
            <textarea value={testimonialForm.testimonial_text} onChange={(e) => setTestimonialForm((p) => ({ ...p, testimonial_text: e.target.value }))} placeholder="Testimonial text" className="w-full px-3 py-2 border rounded-md" rows={3} />
            <button onClick={() => createSectionItem('testimonials', testimonialForm)} className="px-4 py-2 bg-primary-600 text-white rounded-md">Add Testimonial</button>
            <div className="space-y-2">
              {sections.testimonials.map((item) => (
                <div key={item.id as number} className="border rounded p-2 flex justify-between items-center">
                  <span className="text-sm text-gray-700">{String(item.customer_name || 'Testimonial')}</span>
                  <button onClick={() => deleteSectionItem('testimonials', item.id as number)} className="text-sm text-red-600">Delete</button>
                </div>
              ))}
            </div>
          </div>

          <div className="bg-white p-4 sm:p-6 rounded-lg shadow space-y-3">
            <h3 className="font-semibold text-gray-900">{SECTION_LABELS.trust_points}</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
              <input value={trustForm.title} onChange={(e) => setTrustForm((p) => ({ ...p, title: e.target.value }))} placeholder="Title" className="px-3 py-2 border rounded-md" />
              <input type="number" value={trustForm.display_order} onChange={(e) => setTrustForm((p) => ({ ...p, display_order: Number(e.target.value) || 0 }))} placeholder="Display order" className="px-3 py-2 border rounded-md" />
            </div>
            <textarea value={trustForm.description} onChange={(e) => setTrustForm((p) => ({ ...p, description: e.target.value }))} placeholder="Description" className="w-full px-3 py-2 border rounded-md" rows={2} />
            <button onClick={() => createSectionItem('trust_points', trustForm)} className="px-4 py-2 bg-primary-600 text-white rounded-md">Add Trust Point</button>
            <div className="space-y-2">
              {sections.trust_points.map((item) => (
                <div key={item.id as number} className="border rounded p-2 flex justify-between items-center">
                  <span className="text-sm text-gray-700">{String(item.title || 'Trust Point')}</span>
                  <button onClick={() => deleteSectionItem('trust_points', item.id as number)} className="text-sm text-red-600">Delete</button>
                </div>
              ))}
            </div>
          </div>

          <div className="bg-white p-4 sm:p-6 rounded-lg shadow space-y-3">
            <h3 className="font-semibold text-gray-900">{SECTION_LABELS.usps}</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
              <input value={uspForm.title} onChange={(e) => setUspForm((p) => ({ ...p, title: e.target.value }))} placeholder="Title" className="px-3 py-2 border rounded-md" />
              <input type="number" value={uspForm.display_order} onChange={(e) => setUspForm((p) => ({ ...p, display_order: Number(e.target.value) || 0 }))} placeholder="Display order" className="px-3 py-2 border rounded-md" />
            </div>
            <textarea value={uspForm.description} onChange={(e) => setUspForm((p) => ({ ...p, description: e.target.value }))} placeholder="Description" className="w-full px-3 py-2 border rounded-md" rows={2} />
            <button onClick={() => createSectionItem('usps', uspForm)} className="px-4 py-2 bg-primary-600 text-white rounded-md">Add USP</button>
            <div className="space-y-2">
              {sections.usps.map((item) => (
                <div key={item.id as number} className="border rounded p-2 flex justify-between items-center">
                  <span className="text-sm text-gray-700">{String(item.title || 'USP')}</span>
                  <button onClick={() => deleteSectionItem('usps', item.id as number)} className="text-sm text-red-600">Delete</button>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
