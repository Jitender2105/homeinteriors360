// Type definitions for database-driven content

export interface PageContent {
  [key: string]: string | undefined;
  meta_title?: string;
  meta_description?: string;
  hero_title?: string;
  hero_subtitle?: string;
  hero_description?: string;
  about_title?: string;
  about_description?: string;
  process_title?: string;
  process_step_1_title?: string;
  process_step_1_description?: string;
  process_step_2_title?: string;
  process_step_2_description?: string;
  process_step_3_title?: string;
  process_step_3_description?: string;
  process_step_4_title?: string;
  process_step_4_description?: string;
  cta_title?: string;
  cta_description?: string;
}

export interface Locality {
  name: string;
  area_type: 'society' | 'sector' | 'locality';
}

export interface FormOption {
  id: number;
  category_name: string;
  field_type: string;
  option_value: string;
  display_order: number;
  is_active: boolean;
}

export interface Lead {
  id: number;
  name: string;
  phone: string;
  email?: string;
  work_type: string;
  budget: string;
  locality?: string;
  interior_designer_id?: number | null;
  message?: string;
  status: 'new' | 'contacted' | 'converted' | 'closed';
  created_at: string;
  updated_at: string;
}

export interface InteriorDesigner {
  id: number;
  full_name: string;
  slug: string;
  profile_title?: string;
  bio?: string;
  profile_image?: string;
  years_experience: number;
  total_projects: number;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

export interface InteriorDesignerProject {
  id: number;
  interior_designer_id: number;
  image_url: string;
  location?: string;
  cost_range?: string;
  work_type?: string;
  project_title?: string;
  display_order: number;
  is_active: boolean;
}

export interface InteriorDesignerTestimonial {
  id: number;
  interior_designer_id: number;
  customer_name: string;
  customer_location?: string;
  testimonial_text: string;
  rating: number;
  display_order: number;
  is_active: boolean;
}

export interface InteriorDesignerPoint {
  id: number;
  interior_designer_id: number;
  title: string;
  description?: string;
  display_order: number;
  is_active: boolean;
}

export interface Article {
  id: number;
  title: string;
  slug: string;
  excerpt?: string;
  content: string;
  meta_title?: string;
  meta_description?: string;
  featured_image?: string;
  author_id?: number;
  is_published: boolean;
  published_at?: string;
  created_at: string;
  updated_at: string;
}

export interface Design {
  id: number;
  title: string;
  slug: string;
  description?: string;
  work_type?: string;
  locality?: string;
  cost_range?: string;
  society_name?: string;
  images?: string[];
  videos?: string[];
  featured_image?: string;
  is_featured: boolean;
  display_order: number;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}
