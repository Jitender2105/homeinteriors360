'use client';

import { useRef } from 'react';

interface ContentSliderProps {
  children: React.ReactNode;
}

export default function ContentSlider({ children }: ContentSliderProps) {
  const trackRef = useRef<HTMLDivElement>(null);

  const scrollByCards = (direction: 'left' | 'right') => {
    if (!trackRef.current) return;
    const distance = Math.round(trackRef.current.clientWidth * 0.9);
    trackRef.current.scrollBy({
      left: direction === 'left' ? -distance : distance,
      behavior: 'smooth',
    });
  };

  return (
    <div className="relative">
      <div className="absolute -top-14 right-0 z-10 hidden sm:flex gap-2">
        <button
          type="button"
          onClick={() => scrollByCards('left')}
          className="h-9 w-9 rounded-full border border-[#B8860B] text-[#B8860B] hover:bg-[#B8860B] hover:text-white"
          aria-label="Scroll left"
        >
          ←
        </button>
        <button
          type="button"
          onClick={() => scrollByCards('right')}
          className="h-9 w-9 rounded-full border border-[#B8860B] text-[#B8860B] hover:bg-[#B8860B] hover:text-white"
          aria-label="Scroll right"
        >
          →
        </button>
      </div>

      <div
        ref={trackRef}
        className="flex gap-6 overflow-x-auto snap-x snap-mandatory pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
      >
        {children}
      </div>
    </div>
  );
}
