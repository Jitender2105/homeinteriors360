'use client';

import { useEffect, useState } from 'react';

interface LeadFormProps {
  onSubmit?: () => void;
  interiorDesignerId?: number;
}

export default function LeadForm({ onSubmit, interiorDesignerId }: LeadFormProps) {
  const [formData, setFormData] = useState({
    name: '',
    phone: '',
    email: '',
    work_type: '',
    budget: '',
    locality: '',
    message: '',
  });

  const [workTypes, setWorkTypes] = useState<string[]>([]);
  const [budgets, setBudgets] = useState<string[]>([]);
  const [localities, setLocalities] = useState<Array<{ name: string; area_type: string }>>([]);

  const [loading, setLoading] = useState(false);
  const [submitStatus, setSubmitStatus] = useState<'idle' | 'success' | 'error'>('idle');

  // Fetch form options from database
  useEffect(() => {
    let isMounted = true;

    async function fetchOptions() {
      try {
        const [workTypeRes, budgetRes, localityRes] = await Promise.all([
          fetch('/api/form-options?type=work_type'),
          fetch('/api/form-options?type=budget'),
          fetch('/api/localities'),
        ]);

        const [workTypeData, budgetData, localityData] = await Promise.all([
          workTypeRes.json(),
          budgetRes.json(),
          localityRes.json(),
        ]);

        if (!isMounted) return;

        if (workTypeData?.options) setWorkTypes(workTypeData.options);
        if (budgetData?.options) setBudgets(budgetData.options);
        if (localityData?.localities) setLocalities(localityData.localities);
      } catch (error) {
        console.error('Error fetching form options:', error);
      }
    }

    fetchOptions();
    return () => {
      isMounted = false;
    };
  }, []);

  const handleChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>
  ) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const resetForm = () => {
    setFormData({
      name: '',
      phone: '',
      email: '',
      work_type: '',
      budget: '',
      locality: '',
      message: '',
    });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setSubmitStatus('idle');

    try {
      const response = await fetch('/api/leads', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          ...formData,
          interior_designer_id: interiorDesignerId || null,
        }),
      });

      await response.json();

      if (response.ok) {
        setSubmitStatus('success');
        resetForm();
        onSubmit?.();
      } else {
        setSubmitStatus('error');
      }
    } catch (error) {
      console.error('Error submitting form:', error);
      setSubmitStatus('error');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="bg-white">
      {/* Compact header (one-fold friendly) */}
      <div className="mb-6 text-center">
        <p className="text-[11px] tracking-[0.25em] uppercase text-[#6B6B6B] mb-2 font-light">
          Personal Consultation Request
        </p>
        <div className="w-20 h-px bg-[#B8860B] mx-auto" />
      </div>

      <form onSubmit={handleSubmit} className="space-y-4">
        {/* One-fold layout: 2 columns on desktop */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-3">
          {/* Name */}
          <div className="space-y-1.5">
            <label
              htmlFor="name"
              className="block text-[11px] tracking-[0.18em] uppercase text-[#6B6B6B] font-light"
            >
              Full Name <span className="text-[#B8860B]">*</span>
            </label>
            <input
              type="text"
              id="name"
              name="name"
              required
              value={formData.name}
              onChange={handleChange}
              className="luxury-input w-full py-2"
              placeholder="Enter your full name"
            />
          </div>

          {/* Phone */}
          <div className="space-y-1.5">
            <label
              htmlFor="phone"
              className="block text-[11px] tracking-[0.18em] uppercase text-[#6B6B6B] font-light"
            >
              Contact Number <span className="text-[#B8860B]">*</span>
            </label>
            <input
              type="tel"
              id="phone"
              name="phone"
              required
              value={formData.phone}
              onChange={handleChange}
              className="luxury-input w-full py-2"
              placeholder="+91 98765 43210"
            />
          </div>

          {/* Email */}
          <div className="space-y-1.5">
            <label
              htmlFor="email"
              className="block text-[11px] tracking-[0.18em] uppercase text-[#6B6B6B] font-light"
            >
              Email Address
            </label>
            <input
              type="email"
              id="email"
              name="email"
              value={formData.email}
              onChange={handleChange}
              className="luxury-input w-full py-2"
              placeholder="your.email@example.com"
            />
          </div>

          {/* Work Type */}
          <div className="space-y-1.5">
            <label
              htmlFor="work_type"
              className="block text-[11px] tracking-[0.18em] uppercase text-[#6B6B6B] font-light"
            >
              Project Type <span className="text-[#B8860B]">*</span>
            </label>
            <select
              id="work_type"
              name="work_type"
              required
              value={formData.work_type}
              onChange={handleChange}
              className="luxury-select w-full py-2"
            >
              <option value="">Select project type</option>
              {workTypes.map((type) => (
                <option key={type} value={type}>
                  {type}
                </option>
              ))}
            </select>
          </div>

          {/* Budget */}
          <div className="space-y-1.5">
            <label
              htmlFor="budget"
              className="block text-[11px] tracking-[0.18em] uppercase text-[#6B6B6B] font-light"
            >
              Budget Range <span className="text-[#B8860B]">*</span>
            </label>
            <select
              id="budget"
              name="budget"
              required
              value={formData.budget}
              onChange={handleChange}
              className="luxury-select w-full py-2"
            >
              <option value="">Select budget range</option>
              {budgets.map((budget) => (
                <option key={budget} value={budget}>
                  {budget}
                </option>
              ))}
            </select>
          </div>

          {/* Locality */}
          <div className="space-y-1.5">
            <label
              htmlFor="locality"
              className="block text-[11px] tracking-[0.18em] uppercase text-[#6B6B6B] font-light"
            >
              Locality / Society
            </label>
            <select
              id="locality"
              name="locality"
              value={formData.locality}
              onChange={handleChange}
              className="luxury-select w-full py-2"
            >
              <option value="">Select locality</option>
              {localities.map((locality) => (
                <option key={locality.name} value={locality.name}>
                  {locality.name} ({locality.area_type})
                </option>
              ))}
            </select>
          </div>
        </div>

        {/* Message - always visible, full width */}
        <div className="space-y-1.5">
          <label
            htmlFor="message"
            className="block text-[11px] tracking-[0.18em] uppercase text-[#6B6B6B] font-light"
          >
            Additional Details
          </label>
          <textarea
            id="message"
            name="message"
            rows={2}
            value={formData.message}
            onChange={handleChange}
            className="luxury-input w-full resize-none py-2"
            placeholder="Tell us about your vision..."
          />
        </div>

        {/* Status Messages */}
        {submitStatus === 'success' && (
          <div className="p-4 border border-[#B8860B] bg-[#F1E5AC]/10 text-center animate-fade-in-up">
            <p className="text-[#B8860B] tracking-wider font-light text-sm">
              Thank you. Our team will contact you shortly.
            </p>
          </div>
        )}

        {submitStatus === 'error' && (
          <div className="p-4 border border-red-300 bg-red-50/50 text-center">
            <p className="text-red-600 tracking-wider font-light text-sm">
              Something went wrong. Please try again or contact us directly.
            </p>
          </div>
        )}

        {/* Submit Button */}
        <div className="pt-2">
          <button
            type="submit"
            disabled={loading}
            className="luxury-button w-full disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {loading ? 'Submitting...' : 'Request Consultation'}
          </button>
        </div>
      </form>
    </div>
  );
}
