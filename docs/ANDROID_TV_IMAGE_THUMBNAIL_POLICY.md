# Android TV Image & Thumbnail Policy

This document is the canonical image-selection rule for Hamrahang Android TV.
The selection order is intentional: use the first non-empty metadata key in each
row. Do not silently fall back to another media type's image or to an unrelated
artist image.

## 1. Standard media artwork

| Content type | Primary image | Fallback image | Final fallback |
|---|---|---|---|
| Song | `meta.tmb` | Song icon | — |
| Podcast | `meta.tmb` | Song icon | — |
| Artist | `meta.tmb` | Artist icon | — |
| Album | `meta.tmb` | Album icon | — |
| Playlist | `meta.tmb` | Playlist icon | — |

For these types, no image is borrowed from an associated artist. The type's
own icon is shown when `meta.tmb` is absent.

## 2. Video artwork

| Content type | Priority 1 | Priority 2 | Priority 3 |
|---|---|---|---|
| MusicVideo | `meta.tmv` | `meta.tmb` | Video icon |
| Video | `meta.cvr` | `meta.tmb` | Video icon |

MusicVideo and Video must remain distinct when resolving artwork. In
particular, Video uses `cvr` before `tmb`, while MusicVideo uses `tmv`
before `tmb`.

## 3. Source owner artwork

For the owner/provider attached to a playback source:

1. `owner.meta.ico`
2. `owner.meta.tmb`
3. The source's own icon, if the source payload provides one.
4. No image if none of the above exists.

The owner icon key may contain its file extension (the web implementation
supports the owner `ico` key's extension convention). Do not automatically
append `.jpg` to an `ico` key when it already encodes an extension.

The owner profile and the source/provider icon are different concepts. Do not
store a source icon as the owner's profile icon.

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
- Resolve image keys using the project's existing media-image URL convention;
  do not invent a new API host or image domain.
- A missing/broken image must fall back to the appropriate type icon and must
  not break the card, list, Hero, or playback UI.
- Keep image selection in one shared resolver so cards, detail pages, search,
  favorites, history, mini-player, and other surfaces do not implement
  conflicting priorities.
- Hero resolution is a separate resolver because its priority differs from
  thumbnail resolution.
- Keep image loading/cache behavior non-blocking and independent of playback.

## 6. Web reference and icon mapping

The reference `index.html` defines the following relevant SVG variables:

- Song icon: `svg_sng`
- Artist/user icon currently available: `svg_usr`
- Video icon: `svg_vdo`
- Playlist icon currently available: `svg_pls`

The inspected `index.html` does not define a dedicated `svg_alb` album
icon variable. Before Android implementation, locate the intended album SVG
in the web assets or choose an existing project asset; do not substitute an
unrelated icon without recording that decision.

The web reference's current helper uses `tmv → tmb` for video-like artwork
and `art → tmv` for Hero backgrounds. This Android policy deliberately
extends the type-specific rules requested for Video (`cvr → tmb`) and the
explicit fallback icons above.
