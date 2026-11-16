package ir.hamrahang.tv.data.search

import ir.hamrahang.tv.data.musicvideo.VideoSource

data class SearchVideoDto(
        val id: String?,
        val name: String?,
        val nameFa: String?,
        val artist: String?,
        val artistFa: String?,
        val thumb: String?,
        val url: String?,
        val video: List<VideoSource>?
)