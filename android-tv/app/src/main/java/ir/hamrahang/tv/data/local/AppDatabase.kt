package ir.hamrahang.tv.data.local

import androidx.room.Database
import androidx.room.RoomDatabase
import ir.hamrahang.tv.data.local.dao.AlbumDao
import ir.hamrahang.tv.data.local.dao.ArtistDao
import ir.hamrahang.tv.data.local.dao.SongDao
import ir.hamrahang.tv.data.local.dao.VideoDao
import ir.hamrahang.tv.data.local.dao.MusicVideoDao
import ir.hamrahang.tv.data.local.dao.PlaybackHistoryDao
import ir.hamrahang.tv.data.local.entity.AlbumEntity
import ir.hamrahang.tv.data.local.entity.ArtistEntity
import ir.hamrahang.tv.data.local.entity.SongEntity
import ir.hamrahang.tv.data.local.entity.VideoEntity
import ir.hamrahang.tv.data.local.entity.MusicVideoEntity
import ir.hamrahang.tv.data.local.entity.UserEntity
import ir.hamrahang.tv.data.local.entity.FavoriteEntity
import androidx.room.TypeConverters
import ir.hamrahang.tv.data.local.dao.FavoriteDao
import ir.hamrahang.tv.data.local.dao.UserDao
import ir.hamrahang.tv.data.local.playback.PlaybackHistoryEntity
@Database(
    entities = [
        SongEntity::class,
        ArtistEntity::class,
        AlbumEntity::class,
        VideoEntity::class,
        MusicVideoEntity::class,
        PlaybackHistoryEntity::class,
        FavoriteEntity::class,
        UserEntity::class
    ],
    version = 27,
    exportSchema = false
)
@TypeConverters(RoomConverters::class)
abstract class AppDatabase : RoomDatabase() {

    abstract fun songDao(): SongDao

    abstract fun artistDao(): ArtistDao

    abstract fun albumDao(): AlbumDao

    abstract fun videoDao(): VideoDao

    abstract fun musicvideoDao(): MusicVideoDao

    abstract fun playbackHistoryDao(): PlaybackHistoryDao
    abstract fun favoriteDao(): FavoriteDao
    abstract fun userDao(): UserDao
}