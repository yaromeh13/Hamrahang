package ir.hamrahang.tv.data.search

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.album.AlbumDto
import ir.hamrahang.tv.data.artist.ArtistDto
import ir.hamrahang.tv.data.musicvideo.VideoDto
import ir.hamrahang.tv.data.song.SongDto
/*
class HorizontalCardAdapter(
    private val onClick: (Any) -> Unit
) : RecyclerView.Adapter<RecyclerView.ViewHolder>() {

    private val items = mutableListOf<Any>()

    fun submit(list: List<Any>) {
        items.clear()
        items.addAll(list)
        notifyDataSetChanged()
    }

    override fun getItemCount() = items.size

    override fun getItemViewType(position: Int): Int = 0

   // override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): RecyclerView.ViewHolder {

   //     val view = LayoutInflater.from(parent.context)
    //        .inflate(R.layout.item_artist, parent, false) // فعلاً تست

       // return CardVH(view)
  //  }

    override fun onBindViewHolder(holder: RecyclerView.ViewHolder, position: Int) {
      //  (holder as CardVH).bind(items[position])
    }
}

/*
class HorizontalCardAdapter(
    private val onClick: (Any) -> Unit
) : RecyclerView.Adapter<RecyclerView.ViewHolder>() {

    private val artists = mutableListOf<ArtistDto>()
    private val songs = mutableListOf<SongDto>()
    private val albums = mutableListOf<AlbumDto>()
    private val videos = mutableListOf<VideoDto>()

    fun submitArtists(list: List<ArtistDto>) {
        clearAll()
        artists.addAll(list)
        notifyDataSetChanged()
    }

    fun submitSongs(list: List<SongDto>) {
        clearAll()
        songs.addAll(list)
        notifyDataSetChanged()
    }

    fun submitAlbums(list: List<AlbumDto>) {
        clearAll()
        albums.addAll(list)
        notifyDataSetChanged()
    }

    fun submitVideos(list: List<VideoDto>) {
        clearAll()
        videos.addAll(list)
        notifyDataSetChanged()
    }

    private fun clearAll() {
        artists.clear()
        songs.clear()
        albums.clear()
        videos.clear()
    }

    override fun getItemCount(): Int =
        artists.size + songs.size + albums.size + videos.size

    override fun getItemViewType(position: Int): Int = 0

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): RecyclerView.ViewHolder {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_card, parent, false)

        return CardVH(view)
    }

    override fun onBindViewHolder(holder: RecyclerView.ViewHolder, position: Int) {
        (holder as CardVH).bind("TODO")
    }
}*/