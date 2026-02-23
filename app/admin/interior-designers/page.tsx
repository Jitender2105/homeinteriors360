import { cookies } from 'next/headers';
import { redirect } from 'next/navigation';
import InteriorDesignerManager from '@/components/admin/InteriorDesignerManager';
import { getAuthUserFromToken } from '@/lib/server-auth';

export const dynamic = 'force-dynamic';

export default async function InteriorDesignersAdminPage() {
  const token = cookies().get('auth_token')?.value;
  const user = await getAuthUserFromToken(token);

  if (!user) {
    redirect('/admin/login');
  }

  if (user.role !== 'super_admin') {
    redirect('/admin');
  }

  return <InteriorDesignerManager />;
}
