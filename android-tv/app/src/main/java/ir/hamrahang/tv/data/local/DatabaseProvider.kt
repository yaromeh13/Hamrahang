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


    private val MIGRATION_29_30 = object : Migration(29, 30) {
        override fun migrate(database: SupportSQLiteDatabase) {
            database.execSQL("""
                CREATE TABLE IF NOT EXISTS musicvideos (
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

            // Move every cached music video out of videos. Legacy rows without
            // an explicit regular-video type are treated as music videos.
            database.execSQL("""
                INSERT OR REPLACE INTO musicvideos (
                    id, title, titleFa, artistIds, songIds,
                    meta, sources, isFull, updatedAt
                )
                SELECT
                    id, title, titleFa,
                    COALESCE(artistIds, '[]'),
                    COALESCE(songIds, '[]'),
                    COALESCE(meta, '{"type":"musicvideo"}'),
                    COALESCE(sources, '[]'),
                    isFull, updatedAt
                FROM videos
                WHERE meta NOT LIKE '%"type":"video"%'
            """.trimIndent())

            database.execSQL("""
                DELETE FROM videos
                WHERE meta NOT LIKE '%"type":"video"%'
            """.trimIndent())
        }
    }

    private val MIGRATION_30_31 = object : Migration(30, 31) {
        override fun migrate(database: SupportSQLiteDatabase) {
            database.execSQL("""
                CREATE TABLE IF NOT EXISTS owners (
                    id INTEGER NOT NULL,
                    name TEXT NOT NULL,
                    meta TEXT NOT NULL,
                    updatedAt INTEGER NOT NULL,
                    PRIMARY KEY(id)
                )
            """.trimIndent())

            database.execSQL("""
                INSERT OR IGNORE INTO owners (id, name, meta, updatedAt)
                VALUES (0, 'Hamrahang', '{}', 0)
            """.trimIndent())
        }
    }


    private val MIGRATION_31_32 = object : Migration(31, 32) {
        override fun migrate(database: SupportSQLiteDatabase) {
            val definitions = listOf(
                arrayOf("songs", "artistIds TEXT NOT NULL, parentIds TEXT NOT NULL, meta TEXT NOT NULL, sources TEXT NOT NULL, isFull INTEGER NOT NULL, updatedAt INTEGER NOT NULL", "artistIds, parentIds, meta, sources, isFull, updatedAt"),
                arrayOf("podcasts", "artistIds TEXT NOT NULL, parentIds TEXT NOT NULL, meta TEXT NOT NULL, sources TEXT NOT NULL, isFull INTEGER NOT NULL, updatedAt INTEGER NOT NULL", "artistIds, parentIds, meta, sources, isFull, updatedAt"),
                arrayOf("albums", "artistIds TEXT NOT NULL, songIds TEXT NOT NULL, meta TEXT NOT NULL, isFull INTEGER NOT NULL, updatedAt INTEGER NOT NULL", "artistIds, songIds, meta, isFull, updatedAt"),
                arrayOf("artists", "meta TEXT NOT NULL, albumIds TEXT NOT NULL, songIds TEXT NOT NULL, musicvideoIds TEXT NOT NULL, videoIds TEXT NOT NULL, podcastIds TEXT NOT NULL, isFull INTEGER NOT NULL, updatedAt INTEGER NOT NULL", "meta, albumIds, songIds, musicvideoIds, videoIds, podcastIds, isFull, updatedAt"),
                arrayOf("videos", "artistIds TEXT NOT NULL, songIds TEXT NOT NULL, meta TEXT NOT NULL, sources TEXT NOT NULL, isFull INTEGER NOT NULL, updatedAt INTEGER NOT NULL", "artistIds, songIds, meta, sources, isFull, updatedAt"),
                arrayOf("musicvideos", "artistIds TEXT NOT NULL, songIds TEXT NOT NULL, meta TEXT NOT NULL, sources TEXT NOT NULL, isFull INTEGER NOT NULL, updatedAt INTEGER NOT NULL", "artistIds, songIds, meta, sources, isFull, updatedAt")
            )

            definitions.forEach { definition ->
                val table = definition[0]
                val newColumns = definition[1]
                val oldColumns = definition[2]
                val legacy = "${table}_legacy_31"

                database.execSQL("ALTER TABLE $table RENAME TO $legacy")
                database.execSQL("""
                    CREATE TABLE $table (
                        id TEXT NOT NULL,
                        name TEXT,
                        names TEXT NOT NULL,
                        $newColumns,
                        PRIMARY KEY(id)
                    )
                """.trimIndent())
                database.execSQL("""
                    INSERT INTO $table (id, name, names, $oldColumns)
                    SELECT id, title, CASE WHEN titleFa IS NULL OR titleFa = '' THEN '{}' ELSE '{"fa":"' || replace(replace(titleFa, char(92), char(92) || char(92)), '"', char(92) || '"') || '"}' END, $oldColumns FROM $legacy
                """.trimIndent())
                database.execSQL("DROP TABLE $legacy")
            }
        }
    }

    private val MIGRATION_32_33 = object : Migration(32, 33) {
        override fun migrate(database: SupportSQLiteDatabase) {
            listOf("songs", "podcasts", "videos", "musicvideos").forEach { table ->
                database.execSQL(
                    "ALTER TABLE $table ADD COLUMN lyrics TEXT NOT NULL DEFAULT '[]'"
                )
            }
        }
    }

    fun init(context: Context) {
        db = Room.databaseBuilder(
            context.applicationContext,
            AppDatabase::class.java,
            "hamrahang.db"
        )
            .addMigrations(MIGRATION_28_29, MIGRATION_29_30, MIGRATION_30_31, MIGRATION_31_32, MIGRATION_32_33)
            .fallbackToDestructiveMigration()
            .build()
    }

    fun database() = db
}
