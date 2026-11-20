package ir.hamrahang.tv.data

/**
 * Public owner card attached to a media source by the web API.
 * IDs are public IDs (the same IDs used by the web player).
 */
data class SourceOwner(
    val id: Long,
    val name: String? = null,
    val meta: OwnerMeta? = null
)

data class OwnerMeta(
    val tmb: String? = null,
    val ico: String? = null,
    val photo: String? = null,
    val des: String? = null,
    val url: String? = null
)


data class SourceOwnersResponse(
    val owners: List<SourceOwner> = emptyList()
)
