package ir.hamrahang.app.ui.home

import android.content.Context
import androidx.annotation.StringRes
import ir.hamrahang.app.R
import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.data.local.entity.PlaylistEntity
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.domain.home.HomeFeedSection
import ir.hamrahang.tv.domain.video.Video
import ir.hamrahang.tv.playback.PlaybackSource
import ir.hamrahang.tv.util.displayTitle

enum class CardStyle(val heightRatio: Float) {
    SQUARE(1f),
    CIRCLE(1f),
    WIDE(9f / 16f)
}

data class HomeCard(
    val id: String,
    val title: String,
    val subtitle: String,
    val image: String?,
    /** Null for items that cannot be played yet (artist, album, playlist). */
    val contentType: ContentType?,
    val artists: List<Artist> = emptyList(),
    val playCount: Long? = null
)

data class HomeSection(
    val id: String,
    val title: String,
    val source: PlaybackSource,
    val style: CardStyle,
    val cards: List<HomeCard>
)

fun HomeData.toUi(context: Context): List<HomeSection> = buildList {
    sections.forEach { add(it.toUi(context)) }
    playlistSection(context, "home_playlists", R.string.your_playlists, playlists)?.let(::add)
    playlistSection(
        context, "home_favorite_playlists", R.string.bookmarked_playlists, favoritePlaylists
    )?.let(::add)
}

private fun playlistSection(
    context: Context,
    id: String,
    @StringRes title: Int,
    items: List<PlaylistEntity>
): HomeSection? {
    if (items.isEmpty()) return null
    return HomeSection(
        id = id,
        title = context.getString(title),
        source = PlaybackSource.HOME,
        style = CardStyle.SQUARE,
        cards = items.map { HomeCard(it.localId, it.name, "", it.thumbnail, null) }
    )
}

private fun HomeFeedSection.toUi(context: Context): HomeSection {
    val style: CardStyle
    val cards: List<HomeCard>
    when (this) {
        is HomeFeedSection.Songs -> {
            style = CardStyle.SQUARE
            cards = items.map {
                HomeCard(
                    it.id, it.displayTitle(), names(it.artists), it.thumb,
                    ContentType.SONG, it.artists, it.meta?.ncp
                )
            }
        }
        is HomeFeedSection.Podcasts -> {
            style = CardStyle.SQUARE
            cards = items.map {
                HomeCard(
                    it.id, it.displayTitle(), names(it.artists), it.thumb,
                    ContentType.PODCAST, it.artists, it.meta?.ncp
                )
            }
        }
        is HomeFeedSection.Videos -> {
            style = CardStyle.WIDE
            cards = items.map { videoCard(it, ContentType.VIDEO) }
        }
        is HomeFeedSection.MusicVideos -> {
            style = CardStyle.WIDE
            cards = items.map { videoCard(it, ContentType.MUSICVIDEO) }
        }
        is HomeFeedSection.Artists -> {
            style = CardStyle.CIRCLE
            cards = items.map { HomeCard(it.id, it.displayTitle(), "", it.thumb, null) }
        }
        is HomeFeedSection.Albums -> {
            style = CardStyle.SQUARE
            cards = items.map { HomeCard(it.id, it.displayTitle(), "", it.thumb, null) }
        }
    }
    val titleRes = sectionTitle(sessionId)
    return HomeSection(
        id = sessionId,
        title = titleRes?.let { context.getString(it) } ?: title ?: sessionTitle,
        source = sessionSource,
        style = style,
        cards = cards
    )
}

private fun videoCard(video: Video, type: ContentType) = HomeCard(
    video.id, video.displayTitle(), names(video.artists), video.thumb,
    type, video.artists, video.meta?.ncp
)

private fun names(artists: List<Artist>): String =
    artists.joinToString(" - ") { it.displayTitle() }

/** Core section titles are English; translate the known ones. */
@StringRes
private fun sectionTitle(id: String): Int? = when (id) {
    "home_history_songs" -> R.string.section_history_songs
    "home_history_musicvideo" -> R.string.section_history_musicvideos
    "home_history_videos" -> R.string.section_history_videos
    "home_song" -> R.string.section_top_songs
    "home_musicvideo" -> R.string.section_musicvideos
    "home_videos" -> R.string.section_videos
    "home_artist" -> R.string.section_artists
    "home_favorite_musicvideos" -> R.string.section_favorite_musicvideos
    "home_favorite_songs" -> R.string.section_favorite_songs
    "home_favorite_podcasts" -> R.string.section_favorite_podcasts
    "home_favorite_artists" -> R.string.section_favorite_artists
    "home_favorite_videos" -> R.string.section_favorite_videos
    "home_favorite_albums" -> R.string.section_favorite_albums
    else -> null
}
