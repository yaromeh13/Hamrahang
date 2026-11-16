package ir.hamrahang.tv.ui.search
/*
import ir.hamrahang.tv.data.search.SearchItem

fun Map<SearchTab, List<SearchItem>>.toRows(): List<SearchRow> {

    val rows = mutableListOf<SearchRow>()

    (this[SearchTab.ARTISTS].orEmpty())
        .filterIsInstance<SearchItem.Artist>()
        .takeIf { it.isNotEmpty() }
        ?.let { rows.add(SearchRow.ArtistRow(it)) }

    (this[SearchTab.SONGS].orEmpty())
        .filterIsInstance<SearchItem.Song>()
        .takeIf { it.isNotEmpty() }
        ?.let { rows.add(SearchRow.SongRow(it)) }

    (this[SearchTab.VIDEOS].orEmpty())
        .filterIsInstance<SearchItem.Video>()
        .takeIf { it.isNotEmpty() }
        ?.let { rows.add(SearchRow.VideoRow(it)) }

    (this[SearchTab.ALBUMS].orEmpty())
        .filterIsInstance<SearchItem.Album>()
        .takeIf { it.isNotEmpty() }
        ?.let { rows.add(SearchRow.AlbumRow(it)) }

    return rows
}*/