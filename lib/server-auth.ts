import { NextRequest } from 'next/server';
import { queryOne } from '@/lib/db';
import { verifyToken } from '@/lib/auth';

export interface AuthUser {
  id: number;
  username: string;
  email?: string;
  role: 'admin' | 'super_admin';
}

interface TokenPayload {
  id?: number;
  username?: string;
}

function getTokenPayload(token?: string): TokenPayload | null {
  if (!token) return null;
  const decoded = verifyToken(token) as TokenPayload | null;
  if (!decoded?.id) return null;
  return decoded;
}

async function getUserRoleSafe(userId: number): Promise<'admin' | 'super_admin'> {
  try {
    const roleRow = (await queryOne('SELECT role FROM users WHERE id = ?', [userId])) as
      | { role?: string }
      | null;
    return roleRow?.role === 'super_admin' ? 'super_admin' : 'admin';
  } catch {
    // Backward-compatible fallback when role column does not exist yet
    return 'admin';
  }
}

export async function getAuthUserFromToken(token?: string): Promise<AuthUser | null> {
  const payload = getTokenPayload(token);
  if (!payload?.id) return null;

  const user = (await queryOne('SELECT id, username, email FROM users WHERE id = ?', [payload.id])) as
    | { id: number; username: string; email?: string }
    | null;

  if (!user) return null;

  const role = await getUserRoleSafe(user.id);

  return {
    id: user.id,
    username: user.username,
    email: user.email,
    role,
  };
}

export async function getAuthUserFromRequest(request: NextRequest): Promise<AuthUser | null> {
  const token = request.cookies.get('auth_token')?.value;
  return getAuthUserFromToken(token);
}

export async function isSuperAdminRequest(request: NextRequest): Promise<boolean> {
  const user = await getAuthUserFromRequest(request);
  return user?.role === 'super_admin';
}
