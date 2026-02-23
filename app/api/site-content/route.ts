export const dynamic = "force-dynamic";
import { NextRequest, NextResponse } from 'next/server';
import { getPageContent, getSiteContent } from '@/lib/db';

/**
 * GET /api/site-content?page=home&section=hero_title
 * Fetch site content from database
 */
export async function GET(request: NextRequest) {
  try {
    const searchParams = request.nextUrl.searchParams;
    const page = searchParams.get('page');
    const section = searchParams.get('section');

    if (page && section) {
      // Get specific section
      const content = await getSiteContent(page, section);
      return NextResponse.json({ content });
    } else if (page) {
      // Get all content for a page
      const content = await getPageContent(page);
      return NextResponse.json({ content });
    } else {
      return NextResponse.json(
        { error: 'Page parameter is required' },
        { status: 400 }
      );
    }
  } catch (error) {
    console.error('Error fetching site content:', error);
    return NextResponse.json(
      { error: 'Failed to fetch site content' },
      { status: 500 }
    );
  }
}

