package ir.hamrahang.tv.network

import ir.hamrahang.tv.data.album.AlbumDto
import ir.hamrahang.tv.data.artist.ArtistDto
import ir.hamrahang.tv.data.artist.ArtistResponseDto
import ir.hamrahang.tv.data.song.SongDto
import ir.hamrahang.tv.data.search.SearchResponseDto
import ir.hamrahang.tv.data.song.SongResponseDto
import ir.hamrahang.tv.data.album.AlbumResponseDto
import ir.hamrahang.tv.data.favorite.FavoriteResponseDto
import ir.hamrahang.tv.data.favorite.UacRequest
import ir.hamrahang.tv.data.favorite.UacResponse
import ir.hamrahang.tv.data.auth.AuthRequest
import ir.hamrahang.tv.data.auth.AuthResponse
import ir.hamrahang.tv.data.video.VideoDto
import ir.hamrahang.tv.data.video.VideoResponseDto
import ir.hamrahang.tv.data.playlist.PlaylistEnvelope
import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.Header
import retrofit2.http.POST
import retrofit2.http.Query

interface ApiService {

    @GET("app/ping")
    suspend fun ping(): Response<PingResponse>

    @GET("video/latest")
    suspend fun getLatestVideos(
        @Query("_qjs") qjs: String = "ep"
    ): List<VideoDto>

    @GET("artist/top")
    suspend fun getTopArtists(
        @Query("_qjs") qjs: String = "ep"
    ): List<ArtistDto>

    @GET("song/top")
    suspend fun getTopSongs(
        @Query("_qjs") qjs: String = "ep"
    ): List<SongDto>

    // Web API: /album/latest and /album/featured
    @GET("album/latest")
    suspend fun getLatestAlbums(
        @Query("_qjs") qjs: String = "ep"
    ): List<AlbumDto>

    @GET("album/featured")
    suspend fun getFeaturedAlbums(
        @Query("_qjs") qjs: String = "ep"
    ): List<AlbumDto>

    @GET("search/{query}")
    suspend fun search(
        @retrofit2.http.Path("query") query: String,
        @Query("_qjs") qjs: String = "ep"
    ): SearchResponseDto

    // Web API detail response: { "artist": { ... } }
    @GET("artist/{id}")
    suspend fun getArtist(
        @retrofit2.http.Path("id") id: String,
        @Query("_qjs") qjs: String = "ep"
    ): ArtistResponseDto

    @GET("video/{id}")
    suspend fun getVideo(
        @retrofit2.http.Path("id") id: String,
        @Query("_qjs") qjs: String = "ep"
    ): VideoResponseDto

    @GET("playlist/{id}")
    suspend fun getPlaylist(
        @retrofit2.http.Path("id") id: String,
        @Query("_qjs") qjs: String = "ep"
    ): PlaylistEnvelope

    @GET("song/{id}")
    suspend fun getSong(
        @retrofit2.http.Path("id") id: String,
        @Query("_qjs") qjs: String = "ep"
    ): SongResponseDto

    // Web API detail response: { "album": { ... } }
    @GET("album/{id}")
    suspend fun getAlbum(
        @retrofit2.http.Path("id") id: String,
        @Query("_qjs") qjs: String = "ep"
    ): AlbumResponseDto

    // Existing web endpoint: ?_qjs=own&ids=publicId,publicId
    @GET("/")
    suspend fun getOwners(
        @Query("_qjs") qjs: String = "own",
        @Query("ids") ids: String
    ): ir.hamrahang.tv.data.SourceOwnersResponse

    @GET("favorite")
    suspend fun getFavorites(
        @Query("_qjs") qjs: String = "ep"
    ): FavoriteResponseDto

    // Same authenticated account API used by the web client.
    @POST("/")
    suspend fun authAction(
        @Query("_qjs") qjs: String = "ual",
        @Body body: AuthRequest
    ): AuthResponse

    @POST("/")
    suspend fun favoriteAccountAction(
        @Query("_qjs") qjs: String = "uac",
        @Header("Authorization") authorization: String,
        @Body body: UacRequest
    ): UacResponse
}
