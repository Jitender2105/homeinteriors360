export const dynamic = "force-dynamic";
import { NextRequest, NextResponse } from 'next/server';
import { query } from '@/lib/db';

/**
 * POST /api/leads
 * Submit a new lead
 */
export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    const { name, phone, email, work_type, budget, locality, message, interior_designer_id } = body;

    // Validation
    if (!name || !phone || !work_type || !budget) {
      return NextResponse.json(
        { error: 'Name, phone, work type, and budget are required' },
        { status: 400 }
      );
    }

    // Insert lead using prepared statement
    let result;
    try {
      result = await query(
        `INSERT INTO leads (name, phone, email, work_type, budget, locality, interior_designer_id, message, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'new')`,
        [
          name,
          phone || null,
          email || null,
          work_type,
          budget,
          locality || null,
          interior_designer_id || null,
          message || null,
        ]
      );
    } catch (error: any) {
      // Backward compatibility for environments where migration is not run yet
      if (error?.code === 'ER_BAD_FIELD_ERROR') {
        result = await query(
          `INSERT INTO leads (name, phone, email, work_type, budget, locality, message, status)
           VALUES (?, ?, ?, ?, ?, ?, ?, 'new')`,
          [name, phone || null, email || null, work_type, budget, locality || null, message || null]
        );
      } else {
        throw error;
      }
    }

    return NextResponse.json({
      success: true,
      leadId: result.insertId,
      message: 'Lead submitted successfully',
    });
  } catch (error) {
    console.error('Error submitting lead:', error);
    return NextResponse.json(
      { error: 'Failed to submit lead' },
      { status: 500 }
    );
  }
}

/**
 * GET /api/leads
 * Get all leads (admin only - will add auth later)
 */
export async function GET(request: NextRequest) {
  try {
    const searchParams = request.nextUrl.searchParams;
    const status = searchParams.get('status');
    const limit = parseInt(searchParams.get('limit') || '50');
    const offset = parseInt(searchParams.get('offset') || '0');

    let sql = 'SELECT * FROM leads';
    const params: (string | number)[] = [];

    if (status) {
      sql += ' WHERE status = ?';
      params.push(status);
    }

    sql += ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
    params.push(limit, offset);

    const leads = await query(sql, params);
    const totalResult = await query('SELECT COUNT(*) as total FROM leads' + (status ? ' WHERE status = ?' : ''), status ? [status] : []);
    const total = totalResult[0]?.total || 0;

    return NextResponse.json({ leads, total, limit, offset });
  } catch (error) {
    console.error('Error fetching leads:', error);
    return NextResponse.json(
      { error: 'Failed to fetch leads' },
      { status: 500 }
    );
  }
}
