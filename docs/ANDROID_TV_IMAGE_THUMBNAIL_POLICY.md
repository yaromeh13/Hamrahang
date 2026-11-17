# Android TV Image & Thumbnail Policy

This document is the canonical image-selection rule for Hamrahang Android TV.
The selection order is intentional: use the first non-empty metadata key in each
row. Do not silently fall back to another media type's image or to an unrelated
artist image.

## 1. Standard media artwork

| Content type | Primary image | Fallback image |
|---|---|---|
| Song | `meta.tmb` | Song icon (`svg_sng`) |
| Podcast | `meta.tmb` | Song icon (`svg_sng`) |
| Artist | `meta.tmb` | Artist/user icon (`svg_usr`) |
| Album | `meta.tmb` | Disk icon (`svg_dsk`) |
| Playlist | `meta.tmb` | Playlist icon (`svg_pls`) |

For these types, no image is borrowed from an associated artist. The type's
own icon is shown when `meta.tmb` is absent or the image fails to load.

## 2. Video artwork

| Content type | Priority 1 | Priority 2 | Priority 3 |
|---|---|---|---|
| MusicVideo | `meta.tmv` | `meta.tmb` | Video icon (`svg_vdo`) |
| Video | `meta.cvr` | `meta.tmb` | Video icon (`svg_vdo`) |

MusicVideo and Video remain distinct when resolving artwork. Video uses
`cvr` before `tmb`; MusicVideo uses `tmv` before `tmb`.

## 3. Source owner artwork

For the owner/provider attached to a playback source:

1. `owner.meta.ico`
2. `owner.meta.tmb`
3. The source's own icon, only if the source payload provides one.
4. No image if none of the above exists.

The owner `ico` key follows the web convention: if it has no dot, its last
three characters encode the extension (for example, `u11210725ipng` becomes
`u11210725i.png`). A `tmb` key always receives `.jpg`.

The owner profile artwork and source/provider icon are different concepts.
Do not store a source icon as the owner's profile icon. Current Android
`MediaSource` does not declare a source-icon field, so that fallback is not
assumed until the API payload/model confirms one.

## 4. Hero wide background

For the wide Hero background only:

1. `meta.art`
2. `meta.tmv`
3. No background image.

Do not use `tmb`, `cvr`, owner artwork, or generic type icons as Hero
background fallbacks.

## 5. Resolution and display requirements

- Treat null, empty, and whitespace-only keys as missing.
- Preserve the exact priority order above.
- Resolve thumbnail keys using the existing Android convention:
  `https://hamrahang.ir/thumb/{key}.jpg`.
- For Hero backgrounds, both `meta.art` and `meta.tmv` use the thumbnail
  URL builder, matching the web reference's `resolveHeroBgUrl()` behavior.
- A missing/broken image falls back to the appropriate type icon and must not
  break the card, list, Hero, or playback UI.
- Keep image-key selection in the shared `ImageResolver`; card loaders use
  the same resolver request helper and a type-specific placeholder/error.
- Hero resolution is separate because its priority differs from thumbnails.
- Playlist artwork resolution is available in `ImageResolver`, but the current
  Android TV tree has no Playlist domain model/card adapter; the fallback is
  ready for the point where Playlist UI is introduced.
- Image loading remains asynchronous and independent of playback.

## 6. Web reference

The reference `index.html` contains these SVG variables:

- Song icon: `svg_sng`
- Artist/user icon: `svg_usr`
- Album disk icon: `svg_dsk`
- Video icon: `svg_vdo`
- Playlist icon: `svg_pls`

The existing web thumbnail helper currently uses `tmv → tmb` for generic
video-like content. The Android policy intentionally distinguishes Video
(`cvr → tmb`) from MusicVideo (`tmv → tmb`), as specified above.
