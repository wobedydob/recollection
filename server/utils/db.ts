import mysql from 'mysql2/promise'

const pool = mysql.createPool({
  host: process.env.DB_HOST || 'localhost',
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASSWORD || '',
  database: process.env.DB_NAME || 'recollection',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0
})

export async function query<T>(sql: string, params?: any[]): Promise<T> {
  const [rows] = await pool.execute(sql, params)
  return rows as T
}

export async function initDatabase() {
  // Create users table
  await query(`
    CREATE TABLE IF NOT EXISTS users (
      id VARCHAR(36) PRIMARY KEY,
      email VARCHAR(255) UNIQUE NOT NULL,
      password VARCHAR(255) NOT NULL,
      name VARCHAR(255) NOT NULL,
      avatar VARCHAR(255),
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
  `)

  // Create tags table
  await query(`
    CREATE TABLE IF NOT EXISTS tags (
      id VARCHAR(36) PRIMARY KEY,
      user_id VARCHAR(36) NOT NULL,
      name VARCHAR(255) NOT NULL,
      color VARCHAR(7) NOT NULL,
      emoji VARCHAR(10),
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )
  `)

  // Create ideas table
  await query(`
    CREATE TABLE IF NOT EXISTS ideas (
      id VARCHAR(36) PRIMARY KEY,
      user_id VARCHAR(36) NOT NULL,
      content TEXT NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )
  `)

  // Create idea_tags junction table
  await query(`
    CREATE TABLE IF NOT EXISTS idea_tags (
      idea_id VARCHAR(36) NOT NULL,
      tag_id VARCHAR(36) NOT NULL,
      PRIMARY KEY (idea_id, tag_id),
      FOREIGN KEY (idea_id) REFERENCES ideas(id) ON DELETE CASCADE,
      FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
    )
  `)
}

export default pool
