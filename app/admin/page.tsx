import Link from 'next/link';
import { query } from '@/lib/db';
import { cookies } from 'next/headers';
import { redirect } from 'next/navigation';
import { getAuthUserFromToken } from '@/lib/server-auth';

interface CountResult {
  count: number;
}

export const dynamic = 'force-dynamic';

export default async function AdminDashboard() {
  // ✅ Auth guard (cookie is HttpOnly so must be checked server-side)
  const token = cookies().get('auth_token')?.value;
  if (!token) redirect('/admin/login');
  const user = await getAuthUserFromToken(token);
  if (!user) redirect('/admin/login');

  // Get statistics
  const leadsCountResult = (await query('SELECT COUNT(*) as count FROM leads')) as CountResult[];
  const newLeadsCountResult = (await query("SELECT COUNT(*) as count FROM leads WHERE status = 'new'")) as CountResult[];
  const articlesCountResult = (await query('SELECT COUNT(*) as count FROM articles')) as CountResult[];
  const designsCountResult = (await query('SELECT COUNT(*) as count FROM designs')) as CountResult[];

  const leadsCount = leadsCountResult[0] || { count: 0 };
  const newLeadsCount = newLeadsCountResult[0] || { count: 0 };
  const articlesCount = articlesCountResult[0] || { count: 0 };
  const designsCount = designsCountResult[0] || { count: 0 };

  return (
    <div>
      <h1 className="text-2xl sm:text-3xl font-bold text-gray-900 mb-8">Admin Dashboard</h1>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div className="bg-white p-6 rounded-lg shadow">
          <h3 className="text-lg font-semibold text-gray-700 mb-2">Total Leads</h3>
          <p className="text-2xl sm:text-3xl font-bold text-primary-600">{leadsCount.count}</p>
        </div>
        <div className="bg-white p-6 rounded-lg shadow">
          <h3 className="text-lg font-semibold text-gray-700 mb-2">New Leads</h3>
          <p className="text-2xl sm:text-3xl font-bold text-yellow-600">{newLeadsCount.count}</p>
        </div>
        <div className="bg-white p-6 rounded-lg shadow">
          <h3 className="text-lg font-semibold text-gray-700 mb-2">Articles</h3>
          <p className="text-2xl sm:text-3xl font-bold text-blue-600">{articlesCount.count}</p>
        </div>
        <div className="bg-white p-6 rounded-lg shadow">
          <h3 className="text-lg font-semibold text-gray-700 mb-2">Designs</h3>
          <p className="text-2xl sm:text-3xl font-bold text-green-600">{designsCount.count}</p>
        </div>
      </div>

      <div className="bg-white p-6 rounded-lg shadow">
        <h2 className="text-xl sm:text-2xl font-bold text-gray-900 mb-4">Quick Actions</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <Link href="/admin/leads" className="p-4 border border-gray-200 rounded-lg hover:border-primary-500 hover:bg-primary-50 transition-colors">
            <h3 className="font-semibold text-gray-900 mb-1">Manage Leads</h3>
            <p className="text-sm text-gray-600">View and manage customer inquiries</p>
          </Link>

          <Link href="/admin/content" className="p-4 border border-gray-200 rounded-lg hover:border-primary-500 hover:bg-primary-50 transition-colors">
            <h3 className="font-semibold text-gray-900 mb-1">Site Content</h3>
            <p className="text-sm text-gray-600">Edit page text and sections</p>
          </Link>

          <Link href="/admin/form-options" className="p-4 border border-gray-200 rounded-lg hover:border-primary-500 hover:bg-primary-50 transition-colors">
            <h3 className="font-semibold text-gray-900 mb-1">Form Options</h3>
            <p className="text-sm text-gray-600">Manage dropdown options</p>
          </Link>

          <Link href="/admin/localities" className="p-4 border border-gray-200 rounded-lg hover:border-primary-500 hover:bg-primary-50 transition-colors">
            <h3 className="font-semibold text-gray-900 mb-1">Localities</h3>
            <p className="text-sm text-gray-600">Manage Gurgaon areas</p>
          </Link>

          <Link href="/admin/articles" className="p-4 border border-gray-200 rounded-lg hover:border-primary-500 hover:bg-primary-50 transition-colors">
            <h3 className="font-semibold text-gray-900 mb-1">Articles</h3>
            <p className="text-sm text-gray-600">Manage blog posts</p>
          </Link>

          <Link href="/admin/designs" className="p-4 border border-gray-200 rounded-lg hover:border-primary-500 hover:bg-primary-50 transition-colors">
            <h3 className="font-semibold text-gray-900 mb-1">Designs</h3>
            <p className="text-sm text-gray-600">Manage portfolio projects</p>
          </Link>

          {user.role === 'super_admin' && (
            <Link href="/admin/interior-designers" className="p-4 border border-gray-200 rounded-lg hover:border-primary-500 hover:bg-primary-50 transition-colors">
              <h3 className="font-semibold text-gray-900 mb-1">Interior Designers</h3>
              <p className="text-sm text-gray-600">Onboard designers and generate microsites</p>
            </Link>
          )}
        </div>
      </div>
    </div>
  );
}
