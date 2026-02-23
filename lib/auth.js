import bcrypt from 'bcryptjs';
import jwt from 'jsonwebtoken';
import { queryOne } from './db';

const JWT_SECRET = process.env.JWT_SECRET || 'your-secret-key-change-in-production';

/**
 * Hash a password using bcrypt
 * @param {string} password - Plain text password
 * @returns {Promise<string>} Hashed password
 */
export async function hashPassword(password) {
  return bcrypt.hash(password, 10);
}

/**
 * Compare password with hash
 * @param {string} password - Plain text password
 * @param {string} hash - Hashed password
 * @returns {Promise<boolean>} True if password matches
 */
export async function comparePassword(password, hash) {
  return bcrypt.compare(password, hash);
}

/**
 * Verify user credentials
 * @param {string} username - Username
 * @param {string} password - Plain text password
 * @returns {Promise<Object|null>} User object if valid, null otherwise
 */
export async function verifyUser(username, password) {
  const user = await queryOne(
    'SELECT id, username, password_hash, email FROM users WHERE username = ?',
    [username]
  );

  if (!user) {
    return null;
  }

  const isValid = await comparePassword(password, user.password_hash);
  if (!isValid) {
    return null;
  }

  // Remove password hash from returned object
  delete user.password_hash;
  return user;
}

/**
 * Generate JWT token for user
 * @param {Object} user - User object with id and username
 * @returns {string} JWT token
 */
export function generateToken(user) {
  return jwt.sign(
    { id: user.id, username: user.username },
    JWT_SECRET,
    { expiresIn: '7d' }
  );
}

/**
 * Verify JWT token
 * @param {string} token - JWT token
 * @returns {Promise<Object|null>} Decoded token or null
 */
export function verifyToken(token) {
  try {
    return jwt.verify(token, JWT_SECRET);
  } catch (error) {
    return null;
  }
}

/**
 * Middleware to check if user is authenticated
 * @param {Request} req - Next.js request object
 * @returns {Promise<Object|null>} User object if authenticated, null otherwise
 */
export async function requireAuth(req) {
  const token = req.cookies?.auth_token || req.headers.authorization?.replace('Bearer ', '');

  if (!token) {
    return null;
  }

  const decoded = verifyToken(token);
  if (!decoded) {
    return null;
  }

  // Verify user still exists
  const user = await queryOne(
    'SELECT id, username, email FROM users WHERE id = ?',
    [decoded.id]
  );

  return user;
}

