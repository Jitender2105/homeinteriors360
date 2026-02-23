'use client';

import { useState } from 'react';
import BrandLogo from '@/components/BrandLogo';

interface PublicNavbarProps {
  active?: 'home' | 'designs' | 'articles';
}

export default function PublicNavbar({ active }: PublicNavbarProps) {
  const [isOpen, setIsOpen] = useState(false);

  const getLinkClass = (key: 'home' | 'designs' | 'articles') =>
    `text-sm tracking-widest uppercase font-light transition-colors ${
      active === key ? 'text-[#B8860B]' : 'text-[#6B6B6B] hover:text-[#B8860B]'
    }`;

  return (
    <nav className="fixed top-0 left-0 right-0 z-50 bg-[#F9F8F6]/95 backdrop-blur-sm border-b border-[#E8E6E1]">
      <div className="container-luxury">
        <div className="h-20 px-4 sm:px-6 md:px-12 lg:px-20 flex items-center justify-between gap-4">
          <BrandLogo className="shrink-0" />

          <div className="hidden md:flex space-x-12">
            <a href="/" className={getLinkClass('home')}>
              Home
            </a>
            <a href="/designs" className={getLinkClass('designs')}>
              Portfolio
            </a>
            <a href="/articles" className={getLinkClass('articles')}>
              Journal
            </a>
            <a href="/admin" className="text-sm tracking-widest uppercase text-[#6B6B6B] hover:text-[#B8860B] transition-colors font-light">
              Admin
            </a>
          </div>

          <button
            type="button"
            className="md:hidden inline-flex items-center justify-center rounded border border-[#E8E6E1] px-3 py-2 text-xs tracking-[0.16em] uppercase text-[#2C2C2C]"
            onClick={() => setIsOpen((prev) => !prev)}
            aria-expanded={isOpen}
            aria-label="Toggle navigation menu"
          >
            Menu
          </button>
        </div>

        {isOpen && (
          <div className="md:hidden border-t border-[#E8E6E1] px-4 sm:px-6 pb-5 pt-4">
            <div className="flex flex-col gap-4">
              <a href="/" className={getLinkClass('home')} onClick={() => setIsOpen(false)}>
                Home
              </a>
              <a href="/designs" className={getLinkClass('designs')} onClick={() => setIsOpen(false)}>
                Portfolio
              </a>
              <a href="/articles" className={getLinkClass('articles')} onClick={() => setIsOpen(false)}>
                Journal
              </a>
              <a
                href="/admin"
                className="text-sm tracking-widest uppercase text-[#6B6B6B] hover:text-[#B8860B] transition-colors font-light"
                onClick={() => setIsOpen(false)}
              >
                Admin
              </a>
            </div>
          </div>
        )}
      </div>
    </nav>
  );
}
