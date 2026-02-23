import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

export function middleware(request: NextRequest) {
  const pathname = request.nextUrl.pathname;

  // ✅ Admin auth guard (cookie is HttpOnly, so middleware can read it)
  if (pathname.startsWith('/admin')) {
    // Allow login page
    if (pathname.startsWith('/admin/login')) {
      // still attach x-pathname header
      const headers = new Headers(request.headers);
      headers.set('x-pathname', pathname);

      return NextResponse.next({
        request: { headers },
      });
    }
    console.log('MW:', request.nextUrl.pathname, 'token:', !!request.cookies.get('auth_token')?.value);

    const token = request.cookies.get('auth_token')?.value;

    // If no token, redirect to login (DO NOT redirect to logout)
    if (!token) {
      const url = request.nextUrl.clone();
      url.pathname = '/admin/login';
      url.searchParams.set('from', pathname);
      return NextResponse.redirect(url);
    }
  }

  // Keep your existing behavior (x-pathname header)
  const requestHeaders = new Headers(request.headers);
  requestHeaders.set('x-pathname', pathname);

  return NextResponse.next({
    request: {
      headers: requestHeaders,
    },
  });
}

export const config = {
  matcher: [
    /*
     * Match all request paths except for the ones starting with:
     * - api (API routes)
     * - _next/static (static files)
     * - _next/image (image optimization files)
     * - favicon.ico (favicon file)
     */
    '/((?!api|_next/static|_next/image|favicon.ico).*)',
  ],
};
