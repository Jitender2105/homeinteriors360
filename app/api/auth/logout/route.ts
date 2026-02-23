export const dynamic = "force-dynamic";
import { NextRequest, NextResponse } from 'next/server';

/**
 * POST /api/auth/logout
 * Admin logout endpoint
 */
export async function POST(request: NextRequest) {
  const response = NextResponse.json({ success: true, message: 'Logged out successfully' });
  response.cookies.delete('auth_token');
  return response;
}

