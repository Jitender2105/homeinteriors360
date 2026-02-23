import { NextRequest, NextResponse } from 'next/server';
import { query } from '@/lib/db';
import { isSuperAdminRequest } from '@/lib/server-auth';
import { ensureDesignerSchema, getFriendlyDesignerSchemaError } from '@/lib/designer-schema';

type SectionType = 'projects' | 'testimonials' | 'trust_points' | 'usps';

function getSectionType(value: string | null): SectionType | null {
  if (value === 'projects' || value === 'testimonials' || value === 'trust_points' || value === 'usps') {
    return value;
  }
  return null;
}

function getTableName(type: SectionType) {
  if (type === 'projects') return 'interior_designer_projects';
  if (type === 'testimonials') return 'interior_designer_testimonials';
  if (type === 'trust_points') return 'interior_designer_trust_points';
  return 'interior_designer_usps';
}

export async function GET(request: NextRequest, { params }: { params: { id: string } }) {
  const allowed = await isSuperAdminRequest(request);
  if (!allowed) return NextResponse.json({ error: 'Super admin access required' }, { status: 403 });

  const type = getSectionType(request.nextUrl.searchParams.get('type'));
  if (!type) return NextResponse.json({ error: 'Invalid type' }, { status: 400 });

  try {
    await ensureDesignerSchema();

    const table = getTableName(type);
    const items = await query(
      `SELECT * FROM ${table} WHERE interior_designer_id = ? ORDER BY display_order, id`,
      [Number(params.id)]
    );
    return NextResponse.json({ items });
  } catch (error) {
    console.error('Error fetching section data:', error);
    const friendly = getFriendlyDesignerSchemaError(error);
    return NextResponse.json({ error: friendly.error, code: friendly.code }, { status: friendly.status });
  }
}

export async function POST(request: NextRequest, { params }: { params: { id: string } }) {
  const allowed = await isSuperAdminRequest(request);
  if (!allowed) return NextResponse.json({ error: 'Super admin access required' }, { status: 403 });

  const type = getSectionType(request.nextUrl.searchParams.get('type'));
  if (!type) return NextResponse.json({ error: 'Invalid type' }, { status: 400 });

  try {
    await ensureDesignerSchema();

    const body = await request.json();
    const designerId = Number(params.id);

    if (type === 'projects') {
      const result = await query(
        `INSERT INTO interior_designer_projects
        (interior_designer_id, image_url, location, cost_range, work_type, project_title, display_order, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
        [
          designerId,
          body.image_url,
          body.location || null,
          body.cost_range || null,
          body.work_type || null,
          body.project_title || null,
          Number(body.display_order) || 0,
          body.is_active !== false,
        ]
      );
      return NextResponse.json({ success: true, id: result.insertId });
    }

    if (type === 'testimonials') {
      const result = await query(
        `INSERT INTO interior_designer_testimonials
        (interior_designer_id, customer_name, customer_location, testimonial_text, rating, display_order, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?)`,
        [
          designerId,
          body.customer_name,
          body.customer_location || null,
          body.testimonial_text,
          Number(body.rating) || 5,
          Number(body.display_order) || 0,
          body.is_active !== false,
        ]
      );
      return NextResponse.json({ success: true, id: result.insertId });
    }

    const table = type === 'trust_points' ? 'interior_designer_trust_points' : 'interior_designer_usps';
    const result = await query(
      `INSERT INTO ${table}
      (interior_designer_id, title, description, display_order, is_active)
      VALUES (?, ?, ?, ?, ?)`,
      [
        designerId,
        body.title,
        body.description || null,
        Number(body.display_order) || 0,
        body.is_active !== false,
      ]
    );

    return NextResponse.json({ success: true, id: result.insertId });
  } catch (error) {
    console.error('Error creating section data:', error);
    const friendly = getFriendlyDesignerSchemaError(error);
    return NextResponse.json({ error: friendly.error, code: friendly.code }, { status: friendly.status });
  }
}

export async function PUT(request: NextRequest, { params }: { params: { id: string } }) {
  const allowed = await isSuperAdminRequest(request);
  if (!allowed) return NextResponse.json({ error: 'Super admin access required' }, { status: 403 });

  const type = getSectionType(request.nextUrl.searchParams.get('type'));
  if (!type) return NextResponse.json({ error: 'Invalid type' }, { status: 400 });

  try {
    await ensureDesignerSchema();

    const body = await request.json();
    const designerId = Number(params.id);

    if (!body.id) return NextResponse.json({ error: 'Item id is required' }, { status: 400 });

    if (type === 'projects') {
      await query(
        `UPDATE interior_designer_projects
         SET image_url = ?, location = ?, cost_range = ?, work_type = ?, project_title = ?, display_order = ?, is_active = ?, updated_at = NOW()
         WHERE id = ? AND interior_designer_id = ?`,
        [
          body.image_url,
          body.location || null,
          body.cost_range || null,
          body.work_type || null,
          body.project_title || null,
          Number(body.display_order) || 0,
          body.is_active !== false,
          Number(body.id),
          designerId,
        ]
      );
      return NextResponse.json({ success: true });
    }

    if (type === 'testimonials') {
      await query(
        `UPDATE interior_designer_testimonials
         SET customer_name = ?, customer_location = ?, testimonial_text = ?, rating = ?, display_order = ?, is_active = ?, updated_at = NOW()
         WHERE id = ? AND interior_designer_id = ?`,
        [
          body.customer_name,
          body.customer_location || null,
          body.testimonial_text,
          Number(body.rating) || 5,
          Number(body.display_order) || 0,
          body.is_active !== false,
          Number(body.id),
          designerId,
        ]
      );
      return NextResponse.json({ success: true });
    }

    const table = type === 'trust_points' ? 'interior_designer_trust_points' : 'interior_designer_usps';
    await query(
      `UPDATE ${table}
       SET title = ?, description = ?, display_order = ?, is_active = ?, updated_at = NOW()
       WHERE id = ? AND interior_designer_id = ?`,
      [
        body.title,
        body.description || null,
        Number(body.display_order) || 0,
        body.is_active !== false,
        Number(body.id),
        designerId,
      ]
    );
    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error updating section data:', error);
    const friendly = getFriendlyDesignerSchemaError(error);
    return NextResponse.json({ error: friendly.error, code: friendly.code }, { status: friendly.status });
  }
}

export async function DELETE(request: NextRequest, { params }: { params: { id: string } }) {
  const allowed = await isSuperAdminRequest(request);
  if (!allowed) return NextResponse.json({ error: 'Super admin access required' }, { status: 403 });

  const type = getSectionType(request.nextUrl.searchParams.get('type'));
  const itemId = Number(request.nextUrl.searchParams.get('itemId'));

  if (!type || !itemId) return NextResponse.json({ error: 'Invalid type or itemId' }, { status: 400 });

  try {
    await ensureDesignerSchema();

    const table = getTableName(type);
    await query(`DELETE FROM ${table} WHERE id = ? AND interior_designer_id = ?`, [itemId, Number(params.id)]);
    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error deleting section data:', error);
    const friendly = getFriendlyDesignerSchemaError(error);
    return NextResponse.json({ error: friendly.error, code: friendly.code }, { status: friendly.status });
  }
}
