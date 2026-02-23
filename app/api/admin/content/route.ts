export const dynamic = "force-dynamic";
import { NextRequest, NextResponse } from 'next/server';
import { requireAuth } from '@/lib/auth';
import { query } from '@/lib/db';

/**
 * GET /api/admin/content
 * Get all site content (admin only)
 */
export async function GET(request: NextRequest) {
  const user = await requireAuth(request);
  if (!user) {
    return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  }

  try {
    const searchParams = request.nextUrl.searchParams;
    const page = searchParams.get('page');

    let sql = 'SELECT * FROM site_content';
    const params: (string | number)[] = [];

    if (page) {
      sql += ' WHERE page_name = ?';
      params.push(page);
    }

    sql += ' ORDER BY page_name, display_order, id';

    const content = await query(sql, params);
    return NextResponse.json({ content });
  } catch (error) {
    console.error('Error fetching content:', error);
    return NextResponse.json(
      { error: 'Failed to fetch content' },
      { status: 500 }
    );
  }
}

/**
 * PUT /api/admin/content
 * Update site content (admin only)
 */
export async function PUT(request: NextRequest) {
  const user = await requireAuth(request);
  if (!user) {
    return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  }

  try {
    const body = await request.json();
    const { id, content_value, is_active, display_order } = body;

    if (!id) {
      return NextResponse.json(
        { error: 'ID is required' },
        { status: 400 }
      );
    }

    await query(
      'UPDATE site_content SET content_value = ?, is_active = ?, display_order = ?, updated_at = NOW() WHERE id = ?',
      [content_value, is_active !== undefined ? is_active : true, display_order || 0, id]
    );

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error updating content:', error);
    return NextResponse.json(
      { error: 'Failed to update content' },
      { status: 500 }
    );
  }
}

/**
 * POST /api/admin/content
 * Create new site content (admin only)
 */
export async function POST(request: NextRequest) {
  const user = await requireAuth(request);
  if (!user) {
    return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  }

  try {
    const body = await request.json();
    const { page_name, section_key, content_value, content_type, display_order } = body;

    if (!page_name || !section_key || !content_value) {
      return NextResponse.json(
        { error: 'Page name, section key, and content value are required' },
        { status: 400 }
      );
    }

    const result = await query(
      'INSERT INTO site_content (page_name, section_key, content_value, content_type, display_order) VALUES (?, ?, ?, ?, ?)',
      [page_name, section_key, content_value, content_type || 'text', display_order || 0]
    );

    return NextResponse.json({ success: true, id: result.insertId });
  } catch (error) {
    console.error('Error creating content:', error);
    return NextResponse.json(
      { error: 'Failed to create content' },
      { status: 500 }
    );
  }
}

