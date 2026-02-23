import { cookies, headers } from 'next/headers';
import { redirect } from 'next/navigation';
import { verifyToken } from '@/lib/auth'; // make sure you have this (JWT verify)

export const dynamic = 'force-dynamic';

interface AuthTokenUser {
  id: number;
  username: string;
  email?: string;
}

export default async function AdminLayout({ children }: { children: React.ReactNode }) {
  // Get pathname (from your middleware)
  const headersList = headers();
  const pathname = headersList.get('x-pathname') || '';
  const isLoginPage = pathname === '/admin/login' || pathname.startsWith('/admin/login');

  // Allow login page
  if (isLoginPage) return <>{children}</>;

  // ✅ Read cookie directly
  const token = cookies().get('auth_token')?.value;

  if (!token) redirect('/admin/login');

  // ✅ Verify JWT (recommended)
  const user = (await verifyToken(token)) as AuthTokenUser | null; // should return { id, username, email } or null

  if (!user) redirect('/admin/login');

  return (
    <div className="min-h-screen bg-gray-50">
      <nav className="bg-white shadow-sm border-b">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="min-h-16 py-3 flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
            <div className="text-lg sm:text-xl font-bold text-primary-600">Admin Panel</div>

            <div className="flex flex-wrap items-center gap-x-4 gap-y-2">
              <span className="text-sm sm:text-base text-gray-700">Welcome, {user.username}</span>

              <a href="/" className="text-sm sm:text-base text-gray-700 hover:text-primary-600">
                View Site
              </a>

              <form action="/api/auth/logout" method="POST">
                <button type="submit" className="text-sm sm:text-base text-red-600 hover:text-red-700">
                  Logout
                </button>
              </form>
            </div>
          </div>
        </div>
      </nav>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">{children}</div>
    </div>
  );
}
