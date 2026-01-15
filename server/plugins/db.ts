import { initDatabase } from '../utils/db'

export default defineNitroPlugin(async () => {
  try {
    await initDatabase()
    console.log('Database initialized successfully')
  } catch (error) {
    console.error('Failed to initialize database:', error)
  }
})
