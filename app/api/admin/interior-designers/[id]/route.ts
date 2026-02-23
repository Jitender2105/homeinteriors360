import { NextRequest, NextResponse } from 'next/server';
import { query } from '@/lib/db';
import { isSuperAdminRequest } from '@/lib/server-auth';
import { ensureDesignerSchema, getFriendlyDesignerSchemaError } from '@/lib/designer-schema';

export async function PUT(request: NextRequest, { params }: { params: { id: string } }) {
  const allowed = await isSuperAdminRequest(request);
  if (!allowed) return NextResponse.json({ error: 'Super admin access required' }, { status: 403 });

  try {
    await ensureDesignerSchema();

    const body = await request.json();
    const {
      full_name,
      profile_title,
      bio,
      profile_image,
      years_experience,
      total_projects,
      is_active,
    } = body;

    await query(
      `UPDATE interior_designers
       SET full_name = ?, profile_title = ?, bio = ?, profile_image = ?, years_experience = ?, total_projects = ?, is_active = ?, updated_at = NOW()
       WHERE id = ?`,
      [
        full_name,
        profile_title || null,
        bio || null,
        profile_image || null,
        Number.isFinite(Number(years_experience)) ? Number(years_experience) : 0,
        Number.isFinite(Number(total_projects)) ? Number(total_projects) : 0,
        is_active !== false,
        Number(params.id),
      ]
    );

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error updating interior designer:', error);
    const friendly = getFriendlyDesignerSchemaError(error);
    return NextResponse.json({ error: friendly.error, code: friendly.code }, { status: friendly.status });
  }
}

export async function DELETE(request: NextRequest, { params }: { params: { id: string } }) {
  const allowed = await isSuperAdminRequest(request);
  if (!allowed) return NextResponse.json({ error: 'Super admin access required' }, { status: 403 });

  try {
    await ensureDesignerSchema();

    await query('DELETE FROM interior_designers WHERE id = ?', [Number(params.id)]);
    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error deleting interior designer:', error);
    const friendly = getFriendlyDesignerSchemaError(error);
    return NextResponse.json({ error: friendly.error, code: friendly.code }, { status: friendly.status });
  }
}
