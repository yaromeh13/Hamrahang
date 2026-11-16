package ir.hamrahang.tv.data.local

import android.content.Context
import androidx.room.Room
import androidx.room.migration.Migration
import androidx.sqlite.db.SupportSQLiteDatabase

object DatabaseProvider {

    private lateinit var db: AppDatabase

    private val MIGRATION_28_29 = object : Migration(28, 29) {
        override fun migrate(database: SupportSQLiteDatabase) {

            database.execSQL("ALTER TABLE videos RENAME TO videos_legacy_28")

            database.execSQL("""
                CREATE TABLE IF NOT EXISTS videos (
                    id TEXT NOT NULL,
                    title TEXT,
                    titleFa TEXT,
                    artistIds TEXT NOT NULL,
                    songIds TEXT NOT NULL,
                    meta TEXT NOT NULL,
                    sources TEXT NOT NULL,
                    isFull INTEGER NOT NULL,
                    updatedAt INTEGER NOT NULL,
                    PRIMARY KEY(id)
                )
            """.trimIndent())

            // Preserve the newer music-video cache first.
            database.execSQL("""
                INSERT OR IGNORE INTO videos (
                    id, title, titleFa, artistIds, songIds,
                    meta, sources, isFull, updatedAt
                )
                SELECT
                    CAST(id AS TEXT),
                    title,
                    titleFa,
                    COALESCE(artistIds, '[]'),
                    COALESCE(songIds, '[]'),
                    COALESCE(meta, '{}'),
                    COALESCE(sources, '[]'),
                    isFull,
                    updatedAt
                FROM musicvideos
            """.trimIndent())

            // Migrate the old, limited video cache and retain its artwork and source URLs.
            database.execSQL("""
                INSERT OR IGNORE INTO videos (
                    id, title, titleFa, artistIds, songIds,
                    meta, sources, isFull, updatedAt
                )
                SELECT
                    CAST(id AS TEXT),
                    title,
                    titleFa,
                    CASE
                        WHEN artistId > 0 THEN '[ "' || CAST(artistId AS TEXT) || '" ]'
                        ELSE '[]'
                    END,
                    '[]',
                    '{"type":"video","tmb":' ||
                    CASE
                        WHEN thumb IS NULL OR thumb = '' THEN 'null'
                        ELSE '"' || replace(thumb, '"', char(92) || '"') || '"'
                    END ||
                    ',"dur":' || CAST(duration AS TEXT) || '}',
                    '[' ||
                    CASE
                        WHEN source720 IS NOT NULL AND source720 != ''
                        THEN '{"url":"' || replace(source720, '"', char(92) || '"') || '","bit":"720"}'
                        ELSE ''
                    END ||
                    CASE
                        WHEN source1080 IS NOT NULL AND source1080 != ''
                        THEN CASE WHEN source720 IS NOT NULL AND source720 != '' THEN ',' ELSE '' END ||
                             '{"url":"' || replace(source1080, '"', char(92) || '"') || '","bit":"1080"}'
                        ELSE ''
                    END ||
                    CASE
                        WHEN source4k IS NOT NULL AND source4k != ''
                        THEN CASE
                            WHEN (source720 IS NOT NULL AND source720 != '') OR
                                 (source1080 IS NOT NULL AND source1080 != '')
                            THEN ',' ELSE '' END ||
                             '{"url":"' || replace(source4k, '"', char(92) || '"') || '","bit":"2160"}'
                        ELSE ''
                    END ||
                    ']',
                    0,
                    updatedAt
                FROM videos_legacy_28
            """.trimIndent())

            database.execSQL("DROP TABLE videos_legacy_28")
            database.execSQL("DROP TABLE IF EXISTS musicvideos")

            // Older playback code recorded every video as MUSICVIDEO.
            // Remove a duplicate legacy row first to respect the unique history index.
            database.execSQL("""
                DELETE FROM playback_history
                WHERE type = 1
                  AND mediaId IN (
                      SELECT CAST(id AS INTEGER)
                      FROM videos
                      WHERE meta LIKE '%"type":"video"%'
                  )
                  AND EXISTS (
                      SELECT 1
                      FROM playback_history AS existing
                      WHERE existing.mediaId = playback_history.mediaId
                        AND existing.userId = playback_history.userId
                        AND existing.type = 3
                  )
            """.trimIndent())

            // Reclassify remaining regular-video history entries using meta.type.
            database.execSQL("""
                UPDATE playback_history
                SET type = 3
                WHERE type = 1
                  AND mediaId IN (
                      SELECT CAST(id AS INTEGER)
                      FROM videos
                      WHERE meta LIKE '%"type":"video"%'
                  )
            """.trimIndent())
        }
    }

    fun init(context: Context) {
        db = Room.databaseBuilder(
            context.applicationContext,
            AppDatabase::class.java,
            "hamrahang.db"
        )
            .addMigrations(MIGRATION_28_29)
            .fallbackToDestructiveMigration()
            .build()
    }

    fun database() = db
}
