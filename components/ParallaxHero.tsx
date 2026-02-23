'use client';

import { useEffect, useRef } from 'react';

interface ParallaxHeroProps {
  imageUrl?: string;
  title: string;
  subtitle?: string;
  description?: string;
}

export default function ParallaxHero({ imageUrl, title, subtitle, description }: ParallaxHeroProps) {
  const parallaxRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const handleScroll = () => {
      if (parallaxRef.current) {
        const scrolled = window.pageYOffset;
        const rate = scrolled * 0.5;
        parallaxRef.current.style.transform = `translateY(${rate}px) scale(1.1)`;
      }
    };

    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  return (
    <section className="relative h-screen flex items-center justify-center overflow-hidden">
      <div 
        ref={parallaxRef}
        className="absolute inset-0 bg-cover bg-center parallax-image"
        style={{
          backgroundImage: imageUrl ? `url(${imageUrl})` : 'url(https://images.unsplash.com/photo-1586023492125-27b2c045efd7?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80)',
        }}
      ></div>
      <div className="absolute inset-0 bg-[#1A1A1A]/40"></div>
      
      <div className="relative z-10 text-center px-6 animate-fade-in-up">
        {subtitle && (
          <p className="text-sm tracking-[0.4em] uppercase text-[#F1E5AC] mb-6 font-light">
            {subtitle}
          </p>
        )}
        <h1 className="font-playfair text-6xl md:text-8xl lg:text-9xl text-white mb-8 tracking-widest leading-tight">
          {title}
        </h1>
        <div className="w-32 h-px bg-[#B8860B] mx-auto mb-8"></div>
        {description && (
          <p className="text-lg md:text-xl text-white/90 max-w-2xl mx-auto font-light tracking-wide leading-relaxed">
            {description}
          </p>
        )}
      </div>

      {/* Scroll Indicator */}
      <div className="absolute bottom-12 left-1/2 transform -translate-x-1/2 animate-bounce">
        <div className="w-px h-16 bg-[#B8860B]"></div>
      </div>
    </section>
  );
}

