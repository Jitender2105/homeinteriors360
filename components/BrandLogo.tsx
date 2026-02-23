import Image from 'next/image';
import Link from 'next/link';

interface BrandLogoProps {
  href?: string;
  className?: string;
  variant?: 'nav' | 'footer';
}

export default function BrandLogo({ href = '/', className = '', variant = 'nav' }: BrandLogoProps) {
  const isFooter = variant === 'footer';
  const logoWidth = isFooter ? 340 : 280;
  const logoHeight = isFooter ? 68 : 56;

  return (
    <Link href={href} className={`inline-flex items-center ${className}`} aria-label="Home Interiors 360">
      <Image
        src="/logo.png"
        alt="Home Interiors 360"
        width={logoWidth}
        height={logoHeight}
        className={isFooter ? 'h-10 sm:h-12 w-auto object-contain' : 'h-8 sm:h-10 w-auto object-contain'}
        priority={variant === 'nav'}
      />
    </Link>
  );
}
