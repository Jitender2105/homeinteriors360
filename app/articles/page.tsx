import { Metadata } from 'next';
import Link from 'next/link';
import BrandLogo from '@/components/BrandLogo';
import PublicNavbar from '@/components/PublicNavbar';
import { query } from '@/lib/db';
import { getPageContent } from '@/lib/db';
import { PageContent } from '@/lib/types';

export async function generateMetadata(): Promise<Metadata> {
  const content = await getPageContent('articles') as PageContent;
  
  return {
    title: content.meta_title || 'Articles - Interior Design 360',
    description: content.meta_description || 'Read our latest articles on interior design trends and tips',
  };
}

interface ArticleListItem {
  id: number;
  title: string;
  slug: string;
  excerpt?: string;
  featured_image?: string;
  published_at?: string;
}

export default async function ArticlesPage() {
  const articles = await query(
    "SELECT id, title, slug, excerpt, featured_image, published_at FROM articles WHERE is_published = TRUE ORDER BY published_at DESC LIMIT 20",
    []
  ) as ArticleListItem[];

  return (
    <div className="min-h-screen bg-[#F9F8F6]">
      <PublicNavbar active="articles" />

      {/* Hero Header */}
      <section className="pt-32 pb-20 bg-[#F9F8F6]">
        <div className="container-luxury text-center">
          <p className="text-sm tracking-[0.3em] uppercase text-[#6B6B6B] mb-6 font-light">
            Design Insights
          </p>
          <h1 className="font-playfair text-4xl sm:text-5xl md:text-7xl text-[#2C2C2C] mb-8 tracking-widest">
            Journal
          </h1>
          <div className="w-24 h-px bg-[#B8860B] mx-auto"></div>
        </div>
      </section>

      {/* Articles Grid */}
      <section className="section-padding bg-[#F9F8F6]">
        <div className="container-luxury">
          {articles.length === 0 ? (
            <div className="text-center py-24">
              <p className="text-[#6B6B6B] font-light text-lg">No articles published yet. Check back soon!</p>
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12 md:gap-16">
              {articles.map((article, index) => (
                <Link
                  key={article.id}
                  href={`/articles/${article.slug}`}
                  className="group animate-fade-in-up"
                  style={{ animationDelay: `${index * 0.1}s` }}
                >
                  <div className="relative overflow-hidden mb-6">
                    {article.featured_image ? (
                      <div className="relative h-64 overflow-hidden">
                        <img
                          src={article.featured_image}
                          alt={article.title}
                          className="w-full h-full object-cover hover-scale-soft"
                        />
                        <div className="absolute inset-0 bg-gradient-to-t from-[#1A1A1A]/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                      </div>
                    ) : (
                      <div className="h-64 bg-[#E8E6E1] flex items-center justify-center">
                        <span className="text-[#6B6B6B] font-light">No Image</span>
                      </div>
                    )}
                  </div>
                  <div className="space-y-3">
                    {article.published_at && (
                      <p className="text-xs tracking-widest uppercase text-[#6B6B6B] font-light">
                        {new Date(article.published_at).toLocaleDateString('en-US', { 
                          year: 'numeric', 
                          month: 'long', 
                          day: 'numeric' 
                        })}
                      </p>
                    )}
                    <h2 className="font-playfair text-xl sm:text-2xl text-[#2C2C2C] tracking-wider group-hover:text-[#B8860B] transition-colors">
                      {article.title}
                    </h2>
                    {article.excerpt && (
                      <p className="text-[#6B6B6B] font-light leading-relaxed line-clamp-3">
                        {article.excerpt}
                      </p>
                    )}
                    <p className="text-xs tracking-widest uppercase text-[#B8860B] font-light pt-2">
                      Read More →
                    </p>
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
