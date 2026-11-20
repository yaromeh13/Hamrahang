package ir.hamrahang.tv.data.local

enum class ContentType(
    val value: Int
) {
    SONG(0),
    MUSICVIDEO(1),
    PODCAST(2),
    VIDEO(3),
    ARTIST(4),
    ALBUM(5),
    PLAYLIST(6);

    val apiKey: String
        get() = when (this) {
            SONG -> "song"
            MUSICVIDEO -> "musicvideo"
            PODCAST -> "podcast"
            VIDEO -> "video"
            ARTIST -> "artist"
            ALBUM -> "album"
            PLAYLIST -> "playlist"
        }
}