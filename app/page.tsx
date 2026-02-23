import { Metadata } from 'next';
import LeadForm from '@/components/LeadForm';
import BrandLogo from '@/components/BrandLogo';
import PublicNavbar from '@/components/PublicNavbar';
import { getPageContent } from '@/lib/db';
import { PageContent } from '@/lib/types';

// Fetch SEO metadata from database
export async function generateMetadata(): Promise<Metadata> {
  const content = await getPageContent('home') as PageContent;
  
  return {
    title: content.meta_title || 'Interior Design 360 - Premium Interior Design Services in Gurgaon',
    description: content.meta_description || 'Transform your space with expert interior design services in Gurgaon',
  };
}

export default async function Home() {
  // Fetch all page content from database
  const content = await getPageContent('home') as PageContent;

  return (
    <div className="min-h-screen bg-[#F9F8F6]">
      <PublicNavbar active="home" />

      {/* Cinematic Hero Section with First-Fold Lead Form */}
      <section className="relative min-h-screen flex items-center overflow-hidden parallax-container">
        <div
          className="absolute inset-0 parallax-image bg-cover bg-center"
          style={{
            backgroundImage:
              'url(https://images.unsplash.com/photo-1586023492125-27b2c045efd7?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80)',
            transform: 'scale(1.1)',
          }}
        />
        <div className="absolute inset-0 bg-[#1A1A1A]/45" />

        <div className="relative z-10 container-luxury px-6 md:px-12 lg:px-20 py-32">
          <div className="grid grid-cols-1 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)] gap-12 lg:gap-20 items-center">
            {/* Hero Copy */}
            <div className="animate-fade-in-up">
              <p className="text-sm tracking-[0.4em] uppercase text-[#F1E5AC] mb-6 font-light text-center lg:text-left">
                Gurgaon Excellence
              </p>
              <h1 className="font-playfair text-4xl sm:text-5xl md:text-7xl lg:text-8xl text-white mb-8 tracking-widest leading-tight text-center lg:text-left">
                {content.hero_title || 'Transform Your Space Into Excellence'}
              </h1>
              <div className="w-32 h-px bg-[#B8860B] mb-8 mx-auto lg:mx-0" />
              <p className="text-lg md:text-xl text-white/90 max-w-2xl font-light tracking-wide leading-relaxed text-center lg:text-left mx-auto lg:mx-0">
                {content.hero_subtitle || 'Premium Interior Design Services in Gurgaon'}
              </p>
            </div>

            {/* First-Fold Lead Form Card */}
            <div
              className="bg-white/95 rounded-2xl shadow-sm 
                         px-5 md:px-7 py-6 md:py-8 
                         backdrop-blur-sm animate-fade-in-up"
              style={{ animationDelay: '0.1s' as any }}
            >
              <div className="text-center mb-6">
                <h2 className="font-playfair text-xl md:text-2xl text-[#2C2C2C] tracking-widest">
                  {content.cta_title || 'Ready to Transform Your Space?'}
                </h2>
                <div className="w-24 h-px bg-[#B8860B] mx-auto mt-4" />
              </div>
              <LeadForm />
            </div>
          </div>
        </div>

        {/* Scroll Indicator */}
        <div className="absolute bottom-10 left-1/2 -translate-x-1/2 animate-bounce">
          <div className="w-px h-16 bg-[#B8860B]" />
        </div>
      </section>

      {/* About Section - Generous Spacing */}
      <section className="section-padding bg-[#F9F8F6]">
        <div className="container-luxury">
          <div className="max-w-4xl mx-auto text-center animate-fade-in-up">
            <p className="text-sm tracking-[0.3em] uppercase text-[#6B6B6B] mb-6 font-light">
              Our Heritage
            </p>
            <h2 className="font-playfair text-3xl sm:text-4xl md:text-6xl text-[#2C2C2C] mb-12 tracking-widest">
              {content.about_title || 'Gurgaon Excellence'}
            </h2>
            <div className="w-24 h-px bg-[#B8860B] mx-auto mb-12"></div>
            <p className="text-lg md:text-xl text-[#6B6B6B] leading-relaxed font-light max-w-3xl mx-auto">
              {content.about_description || 'With years of experience serving Gurgaon\'s premium localities including DLF, M3M, Emaar, and Golf Course Extension, we deliver exceptional interior design solutions tailored to your lifestyle.'}
            </p>
          </div>
        </div>
      </section>

      {/* Process Section - Editorial Style */}
      <section className="section-padding bg-white">
        <div className="container-luxury">
          <div className="text-center mb-20">
            <p className="text-sm tracking-[0.3em] uppercase text-[#6B6B6B] mb-6 font-light">
              How We Work
            </p>
            <h2 className="font-playfair text-3xl sm:text-4xl md:text-6xl text-[#2C2C2C] mb-8 tracking-widest">
              {content.process_title || 'Our Process'}
            </h2>
            <div className="w-24 h-px bg-[#B8860B] mx-auto"></div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-16 md:gap-20">
            <div className="text-center animate-fade-in-up">
              <div className="w-24 h-24 border border-[#B8860B] rounded-full flex items-center justify-center mx-auto mb-8 hover:bg-[#B8860B] hover:text-white transition-all duration-500">
                <span className="font-playfair text-3xl text-[#B8860B] hover:text-white transition-colors">01</span>
              </div>
              <h3 className="font-playfair text-xl sm:text-2xl text-[#2C2C2C] mb-4 tracking-wider">
                {content.process_step_1_title || 'Consultation'}
              </h3>
              <p className="text-[#6B6B6B] font-light leading-relaxed">
                {content.process_step_1_description || 'We understand your vision, lifestyle, and requirements through detailed consultation.'}
              </p>
            </div>

            <div className="text-center animate-fade-in-up" style={{ animationDelay: '0.1s' }}>
              <div className="w-24 h-24 border border-[#B8860B] rounded-full flex items-center justify-center mx-auto mb-8 hover:bg-[#B8860B] hover:text-white transition-all duration-500">
                <span className="font-playfair text-3xl text-[#B8860B] hover:text-white transition-colors">02</span>
              </div>
              <h3 className="font-playfair text-xl sm:text-2xl text-[#2C2C2C] mb-4 tracking-wider">
                {content.process_step_2_title || 'Design & Planning'}
              </h3>
              <p className="text-[#6B6B6B] font-light leading-relaxed">
                {content.process_step_2_description || 'Our expert designers create detailed plans and 3D visualizations for your approval.'}
              </p>
            </div>

            <div className="text-center animate-fade-in-up" style={{ animationDelay: '0.2s' }}>
              <div className="w-24 h-24 border border-[#B8860B] rounded-full flex items-center justify-center mx-auto mb-8 hover:bg-[#B8860B] hover:text-white transition-all duration-500">
                <span className="font-playfair text-3xl text-[#B8860B] hover:text-white transition-colors">03</span>
              </div>
              <h3 className="font-playfair text-xl sm:text-2xl text-[#2C2C2C] mb-4 tracking-wider">
                {content.process_step_3_title || 'Execution'}
              </h3>
              <p className="text-[#6B6B6B] font-light leading-relaxed">
                {content.process_step_3_description || 'Professional execution with quality materials and timely project completion.'}
              </p>
            </div>

            <div className="text-center animate-fade-in-up" style={{ animationDelay: '0.3s' }}>
              <div className="w-24 h-24 border border-[#B8860B] rounded-full flex items-center justify-center mx-auto mb-8 hover:bg-[#B8860B] hover:text-white transition-all duration-500">
                <span className="font-playfair text-3xl text-[#B8860B] hover:text-white transition-colors">04</span>
              </div>
              <h3 className="font-playfair text-xl sm:text-2xl text-[#2C2C2C] mb-4 tracking-wider">
                {content.process_step_4_title || 'Handover'}
              </h3>
              <p className="text-[#6B6B6B] font-light leading-relaxed">
                {content.process_step_4_description || 'Final walkthrough and handover of your beautifully transformed space.'}
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* Lead Form Section - Premium Consultation Request */}
      <section className="section-padding bg-[#F9F8F6]">
        <div className="container-luxury">
          <div className="max-w-3xl mx-auto">
            <div className="text-center mb-16 animate-fade-in-up">
              <p className="text-sm tracking-[0.3em] uppercase text-[#6B6B6B] mb-6 font-light">
                Begin Your Journey
              </p>
              <h2 className="font-playfair text-3xl sm:text-4xl md:text-6xl text-[#2C2C2C] mb-8 tracking-widest">
                {content.cta_title || 'Ready to Transform Your Space?'}
              </h2>
              <div className="w-24 h-px bg-[#B8860B] mx-auto mb-8"></div>
              <p className="text-lg text-[#6B6B6B] font-light leading-relaxed max-w-2xl mx-auto">
                {content.cta_description || 'Get a free consultation and quote for your interior design project.'}
              </p>
            </div>
            <div className="animate-fade-in-up" style={{ animationDelay: '0.2s' }}>
              <LeadForm />
            </div>
          </div>
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
