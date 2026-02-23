export const dynamic = "force-dynamic";
import { NextResponse } from 'next/server';
import { getLocalities } from '@/lib/db';

/**
 * GET /api/localities
 * Fetch all active localities from database
 */
export async function GET() {
  try {
    const localities = await getLocalities();
    return NextResponse.json({ localities });
  } catch (error) {
    console.error('Error fetching localities:', error);
    return NextResponse.json(
      { error: 'Failed to fetch localities' },
      { status: 500 }
    );
  }
}

