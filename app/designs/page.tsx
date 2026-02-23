import { Metadata } from 'next';
import Link from 'next/link';
import BrandLogo from '@/components/BrandLogo';
import PublicNavbar from '@/components/PublicNavbar';
import { query } from '@/lib/db';
import { getPageContent } from '@/lib/db';
import { PageContent } from '@/lib/types';

export async function generateMetadata(): Promise<Metadata> {
  const content = await getPageContent('designs') as PageContent;
  
  return {
    title: content.meta_title || 'Portfolio - Interior Design 360',
    description: content.meta_description || 'View our portfolio of interior design projects in Gurgaon',
  };
}

interface DesignListItem {
  id: number;
  title: string;
  slug: string;
  description?: string;
  work_type?: string;
  locality?: string;
  cost_range?: string;
  society_name?: string;
  featured_image?: string;
  is_featured: boolean;
}

export default async function DesignsPage() {
  const designs = await query(
    "SELECT id, title, slug, description, work_type, locality, cost_range, society_name, featured_image, is_featured FROM designs WHERE is_active = TRUE ORDER BY is_featured DESC, display_order, created_at DESC LIMIT 30",
    []
  ) as DesignListItem[];

  return (
    <div className="min-h-screen bg-[#F9F8F6]">
      <PublicNavbar active="designs" />

      {/* Hero Header */}
      <section className="pt-32 pb-20 bg-[#F9F8F6]">
        <div className="container-luxury text-center">
          <p className="text-sm tracking-[0.3em] uppercase text-[#6B6B6B] mb-6 font-light">
            Our Work
          </p>
          <h1 className="font-playfair text-4xl sm:text-5xl md:text-7xl text-[#2C2C2C] mb-8 tracking-widest">
            Portfolio
          </h1>
          <div className="w-24 h-px bg-[#B8860B] mx-auto"></div>
        </div>
      </section>

      {/* Designs Grid */}
      <section className="section-padding bg-[#F9F8F6]">
        <div className="container-luxury">
          {designs.length === 0 ? (
            <div className="text-center py-24">
              <p className="text-[#6B6B6B] font-light text-lg">No projects to display yet. Check back soon!</p>
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12 md:gap-16">
              {designs.map((design, index) => (
                <Link
                  key={design.id}
                  href={`/designs/${design.slug}`}
                  className="group animate-fade-in-up"
                  style={{ animationDelay: `${index * 0.1}s` }}
                >
                  <div className="relative overflow-hidden mb-6">
                    {design.featured_image ? (
                      <div className="relative h-80 overflow-hidden">
                        <img
                          src={design.featured_image}
                          alt={design.title}
                          className="w-full h-full object-cover hover-scale-soft"
                        />
                        <div className="absolute inset-0 bg-gradient-to-t from-[#1A1A1A]/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        {design.is_featured && (
                          <div className="absolute top-4 right-4">
                            <span className="luxury-badge">Gurgaon Signature</span>
                          </div>
                        )}
                      </div>
                    ) : (
                      <div className="h-80 bg-[#E8E6E1] flex items-center justify-center">
                        <span className="text-[#6B6B6B] font-light">No Image</span>
                      </div>
                    )}
                  </div>
                  <div className="space-y-3">
                    <h2 className="font-playfair text-xl sm:text-2xl text-[#2C2C2C] tracking-wider group-hover:text-[#B8860B] transition-colors">
                      {design.title}
                    </h2>
                    {design.description && (
                      <p className="text-[#6B6B6B] font-light leading-relaxed line-clamp-2">
                        {design.description}
                      </p>
                    )}
                    <div className="pt-4 border-t border-[#E8E6E1] space-y-2 text-xs text-[#6B6B6B] font-light tracking-wider uppercase">
                      {design.work_type && <p>{design.work_type}</p>}
                      {design.locality && <p>{design.locality}</p>}
                      {design.society_name && <p className="text-[#B8860B]">{design.society_name}</p>}
                      {design.cost_range && <p>{design.cost_range}</p>}
                    </div>
                  </div>
                </Link>
              ))}
            </div>
          )}
        </div>
      </section>

      {/* Premium Footer */}
      <footer className="bg-[#1A1A1A] text-white py-16">
        <div className="container-luxury">
          <div className="text-center">
            <div className="mb-6 inline-block rounded-md bg-white/95 px-3 py-2">
              <BrandLogo variant="footer" />
            </div>
            <div className="w-24 h-px bg-[#B8860B] mx-auto mb-6"></div>
            <p className="text-sm tracking-widest uppercase text-white/60 font-light mb-4">
              Serving Gurgaon with Excellence
            </p>
            <p className="text-xs text-white/40 font-light">
              &copy; {new Date().getFullYear()} Interior Design 360. All rights reserved.
            </p>
          </div>
        </div>
      </footer>
    </div>
  );
}
