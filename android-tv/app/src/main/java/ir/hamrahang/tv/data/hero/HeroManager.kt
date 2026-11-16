package ir.hamrahang.tv.hero

import ir.hamrahang.tv.domain.hero.HeroContent

object HeroManager {

    val engine = HeroEngine()

    fun show(content: HeroContent) {

        val event = when (content) {

            is HeroContent.MusicVideoHero ->
                HeroEvent.VideoFocused(content.video)

            is HeroContent.SongHero ->
                HeroEvent.SongFocused(content.song)

            is HeroContent.PodcastHero -> return

            is HeroContent.ArtistHero ->
                HeroEvent.ArtistFocused(content.artist)

            is HeroContent.AlbumHero ->
                HeroEvent.AlbumFocused(content.album)

            is HeroContent.VideoHero -> return

        }

        engine.dispatch(event)
    }
}