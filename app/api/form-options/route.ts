import { NextRequest, NextResponse } from 'next/server';
import { getFormOptions } from '@/lib/db';

/**
 * GET /api/form-options?type=work_type
 * Fetch form options from database
 */
export async function GET(request: NextRequest) {
  try {
    const searchParams = request.nextUrl.searchParams;
    const type = searchParams.get('type');

    if (!type) {
      return NextResponse.json(
        { error: 'Type parameter is required (e.g., work_type, budget)' },
        { status: 400 }
      );
    }

    const options = await getFormOptions(type);
    return NextResponse.json({ options });
  } catch (error) {
    console.error('Error fetching form options:', error);
    return NextResponse.json(
      { error: 'Failed to fetch form options' },
      { status: 500 }
    );
  }
}

