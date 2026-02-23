export const dynamic = "force-dynamic";
import { NextRequest, NextResponse } from 'next/server';
import { query, queryOne } from '@/lib/db';
import { isSuperAdminRequest } from '@/lib/server-auth';
import { ensureDesignerSchema, getFriendlyDesignerSchemaError } from '@/lib/designer-schema';

function slugify(input: string) {
  return input
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-');
}

async function buildUniqueSlug(base: string) {
  const cleanBase = slugify(base) || `designer-${Date.now()}`;
  let candidate = cleanBase;
  let suffix = 1;

  // Keep trying until slug is unique
  while (true) {
    const existing = await queryOne('SELECT id FROM interior_designers WHERE slug = ?', [candidate]);
    if (!existing) return candidate;
    suffix += 1;
    candidate = `${cleanBase}-${suffix}`;
  }
}

export async function GET(request: NextRequest) {
  const allowed = await isSuperAdminRequest(request);
  if (!allowed) return NextResponse.json({ error: 'Super admin access required' }, { status: 403 });

  try {
    await ensureDesignerSchema();

    const designers = await query(
      `SELECT id, full_name, slug, profile_title, bio, profile_image, years_experience, total_projects, is_active, created_at, updated_at
       FROM interior_designers
       ORDER BY created_at DESC`,
      []
    );
    return NextResponse.json({ designers });
  } catch (error) {
    console.error('Error fetching interior designers:', error);
    const friendly = getFriendlyDesignerSchemaError(error);
    return NextResponse.json({ error: friendly.error, code: friendly.code }, { status: friendly.status });
  }
}

export async function POST(request: NextRequest) {
  const allowed = await isSuperAdminRequest(request);
  if (!allowed) return NextResponse.json({ error: 'Super admin access required' }, { status: 403 });

  try {
    await ensureDesignerSchema();

    const body = await request.json();
    const {
      full_name,
      slug,
      profile_title,
      bio,
      profile_image,
      years_experience,
      total_projects,
      is_active,
    } = body;

    if (!full_name) {
      return NextResponse.json({ error: 'Designer name is required' }, { status: 400 });
    }

    const finalSlug = await buildUniqueSlug(slug || full_name);

    const result = await query(
      `INSERT INTO interior_designers
      (full_name, slug, profile_title, bio, profile_image, years_experience, total_projects, is_active)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        full_name,
        finalSlug,
        profile_title || null,
        bio || null,
        profile_image || null,
        Number.isFinite(Number(years_experience)) ? Number(years_experience) : 0,
        Number.isFinite(Number(total_projects)) ? Number(total_projects) : 0,
        is_active !== false,
      ]
    );

    return NextResponse.json({ success: true, id: result.insertId, slug: finalSlug });
  } catch (error) {
    console.error('Error creating interior designer:', error);
    const friendly = getFriendlyDesignerSchemaError(error);
    return NextResponse.json({ error: friendly.error, code: friendly.code }, { status: friendly.status });
  }
}
