import { NextRequest, NextResponse } from 'next/server';
import { requireAuth } from '@/lib/auth';
import { query } from '@/lib/db';

/**
 * GET /api/admin/form-options
 * Get all form options (admin only)
 */
export async function GET(request: NextRequest) {
  const user = await requireAuth(request);
  if (!user) {
    return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  }

  try {
    const options = await query(
      'SELECT * FROM form_options ORDER BY category_name, field_type, display_order, id',
      []
    );
    return NextResponse.json({ options });
  } catch (error) {
    console.error('Error fetching form options:', error);
    return NextResponse.json(
      { error: 'Failed to fetch form options' },
      { status: 500 }
    );
  }
}

/**
 * POST /api/admin/form-options
 * Create new form option (admin only)
 */
export async function POST(request: NextRequest) {
  const user = await requireAuth(request);
  if (!user) {
    return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  }

  try {
    const body = await request.json();
    const { category_name, field_type, option_value, display_order, is_active } = body;

    if (!category_name || !field_type || !option_value) {
      return NextResponse.json(
        { error: 'Category name, field type, and option value are required' },
        { status: 400 }
      );
    }

    const result = await query(
      'INSERT INTO form_options (category_name, field_type, option_value, display_order, is_active) VALUES (?, ?, ?, ?, ?)',
      [category_name, field_type, option_value, display_order || 0, is_active !== undefined ? is_active : true]
    );

    return NextResponse.json({ success: true, id: result.insertId });
  } catch (error) {
    console.error('Error creating form option:', error);
    return NextResponse.json(
      { error: 'Failed to create form option' },
      { status: 500 }
    );
  }
}

/**
 * PUT /api/admin/form-options
 * Update form option (admin only)
 */
export async function PUT(request: NextRequest) {
  const user = await requireAuth(request);
  if (!user) {
    return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  }

  try {
    const body = await request.json();
    const { id, option_value, display_order, is_active } = body;

    if (!id) {
      return NextResponse.json(
        { error: 'ID is required' },
        { status: 400 }
      );
    }

    await query(
      'UPDATE form_options SET option_value = ?, display_order = ?, is_active = ?, updated_at = NOW() WHERE id = ?',
      [option_value, display_order || 0, is_active !== undefined ? is_active : true, id]
    );

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error updating form option:', error);
    return NextResponse.json(
      { error: 'Failed to update form option' },
      { status: 500 }
    );
  }
}

/**
 * DELETE /api/admin/form-options
 * Delete form option (admin only)
 */
export async function DELETE(request: NextRequest) {
  const user = await requireAuth(request);
  if (!user) {
    return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  }

  try {
    const searchParams = request.nextUrl.searchParams;
    const id = searchParams.get('id');

    if (!id) {
      return NextResponse.json(
        { error: 'ID is required' },
        { status: 400 }
      );
    }

    await query('DELETE FROM form_options WHERE id = ?', [id]);

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error deleting form option:', error);
    return NextResponse.json(
      { error: 'Failed to delete form option' },
      { status: 500 }
    );
  }
}

