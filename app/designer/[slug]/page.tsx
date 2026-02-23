import { Metadata } from 'next';
import { notFound } from 'next/navigation';
import LeadForm from '@/components/LeadForm';
import PublicNavbar from '@/components/PublicNavbar';
import ContentSlider from '@/components/ContentSlider';
import { query, queryOne } from '@/lib/db';

interface Designer {
  id: number;
  full_name: string;
  slug: string;
  profile_title?: string;
  bio?: string;
  profile_image?: string;
  years_experience: number;
  total_projects: number;
}

interface DesignerProject {
  id: number;
  image_url: string;
  location?: string;
  cost_range?: string;
  work_type?: string;
  project_title?: string;
}

interface DesignerTestimonial {
  id: number;
  customer_name: string;
  customer_location?: string;
  testimonial_text: string;
  rating: number;
}

interface DesignerPoint {
  id: number;
  title: string;
  description?: string;
}

async function getDesigner(slug: string) {
  return (await queryOne(
    `SELECT id, full_name, slug, profile_title, bio, profile_image, years_experience, total_projects
     FROM interior_designers
     WHERE slug = ? AND is_active = TRUE`,
    [slug]
  )) as Designer | null;
}

export async function generateMetadata({ params }: { params: { slug: string } }): Promise<Metadata> {
  const designer = await getDesigner(params.slug);
  if (!designer) {
    return {
      title: 'Designer Not Found',
      description: 'The requested interior designer page was not found.',
    };
  }

  return {
    title: `${designer.full_name} | Interior Designer | Home Interiors 360`,
    description:
      designer.bio || `${designer.full_name} interior designer microsite with portfolio, testimonials, and lead form.`,
  };
}

export default async function DesignerLandingPage({ params }: { params: { slug: string } }) {
  const designer = await getDesigner(params.slug);
  if (!designer) notFound();

  const [projects, testimonials, trustPoints, usps] = await Promise.all([
    query(
      `SELECT id, image_url, location, cost_range, work_type, project_title
       FROM interior_designer_projects
       WHERE interior_designer_id = ? AND is_active = TRUE
       ORDER BY display_order, id`,
      [designer.id]
    ) as Promise<DesignerProject[]>,
    query(
      `SELECT id, customer_name, customer_location, testimonial_text, rating
       FROM interior_designer_testimonials
       WHERE interior_designer_id = ? AND is_active = TRUE
       ORDER BY display_order, id`,
      [designer.id]
    ) as Promise<DesignerTestimonial[]>,
    query(
      `SELECT id, title, description
       FROM interior_designer_trust_points
       WHERE interior_designer_id = ? AND is_active = TRUE
       ORDER BY display_order, id`,
      [designer.id]
    ) as Promise<DesignerPoint[]>,
    query(
      `SELECT id, title, description
       FROM interior_designer_usps
       WHERE interior_designer_id = ? AND is_active = TRUE
       ORDER BY display_order, id`,
      [designer.id]
    ) as Promise<DesignerPoint[]>,
  ]);

  return (
    <div className="min-h-screen bg-[#F9F8F6]">
      <PublicNavbar />

      <section className="pt-32 pb-14 px-4 sm:px-6 md:px-12 lg:px-20 bg-white">
        <div className="container-luxury">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-10 items-start">
            <div>
              <p className="text-sm tracking-[0.3em] uppercase text-[#6B6B6B] mb-4 font-light">
                Dedicated Designer Microsite
              </p>
              <h1 className="font-playfair text-3xl sm:text-5xl text-[#2C2C2C] mb-4 tracking-widest">
                {designer.full_name}
              </h1>
              <p className="text-[#B8860B] uppercase tracking-widest text-xs mb-6">
                {designer.profile_title || 'Interior Designer'}
              </p>
              <p className="text-[#6B6B6B] leading-relaxed font-light mb-8">
                {designer.bio ||
                  `Connect directly with ${designer.full_name} for design consultation, planning, and turnkey execution.`}
              </p>
              <div className="flex flex-wrap gap-3">
                <span className="luxury-badge">{designer.years_experience || 0}+ Years Experience</span>
                <span className="luxury-badge">{designer.total_projects || 0}+ Projects</span>
              </div>
            </div>

            <div className="bg-white rounded-2xl border border-[#E8E6E1] p-5 sm:p-8 shadow-sm">
              <div className="text-center mb-5">
                <h2 className="font-playfair text-xl sm:text-2xl text-[#2C2C2C] tracking-widest">
                  Start Your Project
                </h2>
                <div className="w-20 h-px bg-[#B8860B] mx-auto mt-4" />
              </div>
              <LeadForm interiorDesignerId={designer.id} />
            </div>
          </div>
        </div>
      </section>

      <section className="section-padding bg-[#F9F8F6]">
        <div className="container-luxury">
          <div className="mb-8">
            <p className="text-sm tracking-[0.3em] uppercase text-[#6B6B6B] mb-3 font-light">Work Done</p>
            <h2 className="font-playfair text-3xl sm:text-4xl text-[#2C2C2C] tracking-widest">
              Project Portfolio
            </h2>
          </div>
          <ContentSlider>
            {projects.length === 0 && (
              <div className="min-w-full bg-white border border-[#E8E6E1] rounded-xl p-6 text-[#6B6B6B]">
                No project cards added yet.
              </div>
            )}
            {projects.map((item) => (
              <div key={item.id} className="snap-start min-w-[85%] sm:min-w-[48%] lg:min-w-[32%] bg-white rounded-xl overflow-hidden border border-[#E8E6E1]">
                <div className="h-56 bg-[#E8E6E1]">
                  {/* eslint-disable-next-line @next/next/no-img-element */}
                  <img src={item.image_url} alt={item.project_title || 'Project image'} className="h-full w-full object-cover" />
                </div>
                <div className="p-4 space-y-2">
                  <h3 className="font-playfair text-lg text-[#2C2C2C] tracking-wider">
                    {item.project_title || 'Completed Project'}
                  </h3>
                  {item.location && <p className="text-sm text-[#6B6B6B]">Location: {item.location}</p>}
                  {item.cost_range && <p className="text-sm text-[#6B6B6B]">Cost: {item.cost_range}</p>}
                  {item.work_type && <p className="text-sm text-[#6B6B6B]">Type: {item.work_type}</p>}
                </div>
              </div>
            ))}
          </ContentSlider>
        </div>
      </section>

      <section className="section-padding bg-white">
        <div className="container-luxury">
          <div className="mb-8">
            <p className="text-sm tracking-[0.3em] uppercase text-[#6B6B6B] mb-3 font-light">Client Voice</p>
            <h2 className="font-playfair text-3xl sm:text-4xl text-[#2C2C2C] tracking-widest">Testimonials</h2>
          </div>
          <ContentSlider>
            {testimonials.length === 0 && (
              <div className="min-w-full bg-[#F9F8F6] border border-[#E8E6E1] rounded-xl p-6 text-[#6B6B6B]">
                No testimonials added yet.
              </div>
            )}
            {testimonials.map((item) => (
              <div key={item.id} className="snap-start min-w-[85%] sm:min-w-[48%] lg:min-w-[32%] bg-[#F9F8F6] rounded-xl border border-[#E8E6E1] p-5">
                <p className="text-[#2C2C2C] font-light leading-relaxed mb-4">&quot;{item.testimonial_text}&quot;</p>
                <p className="text-sm text-[#B8860B] tracking-wide">{'★'.repeat(Math.max(1, Math.min(5, item.rating || 5)))}</p>
                <p className="text-sm text-[#2C2C2C] mt-2">{item.customer_name}</p>
                {item.customer_location && <p className="text-xs text-[#6B6B6B]">{item.customer_location}</p>}
              </div>
            ))}
          </ContentSlider>
        </div>
      </section>

      <section className="section-padding bg-[#F9F8F6]">
        <div className="container-luxury">
          <div className="mb-8">
            <p className="text-sm tracking-[0.3em] uppercase text-[#6B6B6B] mb-3 font-light">Why Trust Us</p>
            <h2 className="font-playfair text-3xl sm:text-4xl text-[#2C2C2C] tracking-widest">Trust Factors</h2>
          </div>
          <ContentSlider>
            {trustPoints.length === 0 && (
              <div className="min-w-full bg-white border border-[#E8E6E1] rounded-xl p-6 text-[#6B6B6B]">
                No trust points added yet.
              </div>
            )}
            {trustPoints.map((item) => (
              <div key={item.id} className="snap-start min-w-[85%] sm:min-w-[48%] lg:min-w-[32%] bg-white rounded-xl border border-[#E8E6E1] p-5">
                <h3 className="font-playfair text-xl text-[#2C2C2C] mb-3 tracking-wider">{item.title}</h3>
                {item.description && <p className="text-[#6B6B6B] font-light leading-relaxed">{item.description}</p>}
              </div>
            ))}
          </ContentSlider>
        </div>
      </section>

      <section className="section-padding bg-white">
        <div className="container-luxury">
          <div className="mb-8">
            <p className="text-sm tracking-[0.3em] uppercase text-[#6B6B6B] mb-3 font-light">Our USP</p>
            <h2 className="font-playfair text-3xl sm:text-4xl text-[#2C2C2C] tracking-widest">Why We Are Different</h2>
          </div>
          <ContentSlider>
            {usps.length === 0 && (
              <div className="min-w-full bg-[#F9F8F6] border border-[#E8E6E1] rounded-xl p-6 text-[#6B6B6B]">
                No USP points added yet.
              </div>
            )}
            {usps.map((item) => (
              <div key={item.id} className="snap-start min-w-[85%] sm:min-w-[48%] lg:min-w-[32%] bg-[#F9F8F6] rounded-xl border border-[#E8E6E1] p-5">
                <h3 className="font-playfair text-xl text-[#2C2C2C] mb-3 tracking-wider">{item.title}</h3>
                {item.description && <p className="text-[#6B6B6B] font-light leading-relaxed">{item.description}</p>}
              </div>
            ))}
          </ContentSlider>
        </div>
      </section>
    </div>
  );
}
