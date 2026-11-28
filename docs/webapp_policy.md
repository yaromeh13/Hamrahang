# Web App — Reference & Development Rules

> **Scope:** This document describes the browser web app in `ha.php` (with `main.css` and `h_api.php`). It is a modern single-page app for phone and desktop browsers. It is **not** the legacy Web TV in `index.html`.

This is the living reference for routes, playback, lyrics, local data, and shared helpers. Update it when an established behavior or storage contract changes.

Do not copy Web TV rules into this app: there is no ES5 requirement, no D-pad / `spatial_navigation.js` navigation model, and no dual base-CSS + `tv.css` layer. The Web App has its own Live routes and data flow; those must not be confused with the legacy Web TV implementation.

---

## 1. Platform

- Runtime is modern JavaScript in `ha.php` (`const` / `let`, `async`/`await`, `fetch`).
- UI shell is built in JavaScript (`CRSB`, `CRPL`, `CRDX`, and related builders). Static HTML only keeps the roots those builders fill.
- Styles live in the inline `<style>` block of `ha.php` and in `main.css`. There is no separate legacy fallback stylesheet.
- Canonical origin for pages and API: **`https://hamrahang.ir`**. Do not add another API host without an explicit decision.
- Navigation is pointer, touch, and normal browser history (`pushState` / `popstate`). Do not add a spatial-focus engine here.

---

## 2. Application structure

`chp(url)` is the router. It pushes history when a URL is given, pulls video out of the page before clearing content (`beforePageClear`), then dispatches on the first path segment.

| Path | Handler | Role |
|---|---|---|
| `/` and list paths such as `/song/top` | `CRHP` | Home and catalog lists |
| `/song/{id}`, `/album/{id}` | `CREP` | Song, podcast, or album detail |
| `/video/{id}` | `CRVP` | Video / music-video detail |
| `/artist/{id}` | `CRAP` | Artist detail |
| `/playlist/{id}` | `CRPP` | Playlist detail |
| `/search/...` | `CRSR` | Search |
| `/library/...` | `CRLP` | Library views |
| `/live` | `CRLV` | Live channel directory |
| `/live/{id}/{slug}` | `openLiveDetail` via `CRLV` | Audio Live → `CREP(id, 'live')`; video Live → `CRVP(id, 'live')` |
| `/setting/...` | `CRSA` | Account, history, favorites, playlists, settings |
| `/pair/{token}` | `CRPair` | Device pairing |
| `/verify`, `/verify-email`, `/link-email` | `CRVerify` | Email confirm / link |
| `/reset/{token}` | `CRReset` | Password reset |
| anything else | `CRNF` | Not-found page |

Unknown catalog keys inside `CRHP` also call `CRNF`. A JSON body `{ "error": "not_found" }` from the API must not render an empty home.

### 2.1 Home

Home order is the `homeOrder` list inside `CRHP`: featured videos, top songs, new tracks, new music videos, per-type recently played rows, top playlists, top artists. User rows read `SN.profile`, not a second cache.

### 2.2 Cards and lists

- Grids: `CRGC` returns the section node; the caller appends it.
- Horizontal cards: `CRSC`.
- Track lines: `CRGL` (play state, duration, more-menu).
- Artwork goes through `resolveThumb` / `resolveThumbUrl`. Missing art keeps the placeholder span; do not leave an `<img>` with an empty `src`.
- Video-like items prefer `meta.tmv`, then `meta.tmb`. Other items prefer `meta.tmb`. Player spinning art prefers `tmb`, then `tmv`, then `cvr`. Stage background prefers `art`, then `tmv`, then `tmb`.

### 2.3 Sidebar hero (desktop only)

`UHERO` fills `#hero` on hover and adds `row` on the menu. `clearHeroMenu` removes that state on navigation and when the pointer enters the menu. Hero is hidden on small screens; do not add a mobile hero flow.

Playlist hover must not invent an artist prefix on the URL. Playlist thumbs use the playlist placeholder icon when there is no image.

### 2.4 Settings

`CRSA` builds accordion sections: Profile, History, Favorites, Playlists, Settings. Paths `audio`, `video`, and `devices` fold into the Settings section. Audio and video quality lists live inside that section, not as separate pages.

### 2.5 Live

Live is a first-class route in the Web App, separate from the on-demand catalog routes. The top-level router dispatches the `live` path to `CRLV` and passes the remaining path segments through; it does not decide which Live subpage should render:

- The router calls `CRLV(LPS[2], LPS[3], done)` for every `/live...` URL. Keep Live subroute selection inside `CRLV`, not in the top-level `chp` switch.
- `CRLV(P, QS, callback)` owns a `routeMap` for Live directory routes. It resolves the first segment (`P`) against that map and uses the route configuration for its path, data request, and ID source.
- The current `latest` route is configured in `routeMap` with `fetch: ['live', 'latest']` and `SN.latest_live` as its ID list. Both `/live` and `/live/latest` render this route; `/live` is the root alias.
- A numeric `P` is treated as a channel ID and delegated by `CRLV` to `openLiveDetail(P, callback)`. That helper resolves the record when needed, then sends audio Live to `CREP(P, 'live', callback)` and video Live to `CRVP(P, 'live')`. The optional following segment is the descriptive slug; the numeric ID remains the record key.
- Unknown nonnumeric route keys are sent to `CRNF`. Add future Live directory pages such as `trending` or `popular` as entries in `CRLV`'s `routeMap`, rather than adding more branches to `chp`.
- `HLV` (`"/live/"`) is the single canonical base-path constant for Live directory links and channel routes. Do not hardcode `/live/` in new Web App links; use `HLV` for the directory and `liveRoute(item)` for a channel.
- `liveRoute(item)` builds `HLV + encodeURIComponent(id) + '/' + encodeURIComponent(slug)`. The slug is `item.url`, then `item.slug`, then the ID as fallback. If the item or its ID is missing, it returns `HLV`.
- `liveMediaType(item)` reads the top-level `media_type`. Only `video` is classified as video; missing or other values default to audio. Do not infer this from `meta.type`.

#### Data loading and cache

- `SN.live` is the in-memory map of Live records keyed by ID. `SN.latest_live` is the ordered list of latest Live IDs.
- `CRLV` loads the Live cache and reads cached IDs through the selected route's `getIds()` function. It uses the route's `fetch` configuration for both initial loading and background refresh; do not hardcode the Latest endpoint or `SN.latest_live` into shared route-loading logic.
- Cached records are painted first when available. Background refresh redraws only while the browser is still on the selected Live route. With no cached IDs, the page shows its loading state and requests the selected route before rendering. Failed initial loading displays the Live-specific error state.
- Records that are not fully resolved are loaded through the existing `EnsureItems('live', ids)` / `gjs(['live', 'resolve'], ...)` flow. `mergeResolvedItems` stores resolved Live records with `full: true`.
- The localStorage cache is `ha_live_cache_v1`. It stores up to 100 latest IDs and lightweight record metadata. `SaveLiveCache()` deliberately removes `sources` and resets `full` to `false`, because stream URLs may expire and must be resolved again when required. `LoadLiveCache()` restores the cached records into `SN.live` and restores the available IDs into `SN.latest_live`.

#### Grouping and cards

- Ownerless/system channels are displayed first. Their fixed sections are video (**پخش زنده تصویری**) followed by audio (**پخش زنده صوتی**).
- Owned channels are grouped by owner. Each owner retains a name and, when available, an avatar thumbnail. If an owner has both media types, separate video and audio rows are rendered under that owner.
- Live directory rows are rendered by the shared `CRSC(ids, 'live', ...)` card builder; there is no separate `CRLVCards` renderer. `CRSC` supplies the same card structure and controls used by the rest of the Web App.
- Video rows pass `{ aspectRatio: '16:9' }`; audio rows use the default square `1:1` ratio. Live video thumbnails prefer `meta.tmv`, then `meta.tmb`; Live audio thumbnails use `meta.tmb`.
- The Live SVG placeholder remains underneath the artwork when no thumbnail is available.
- `CRLV()` renders ownerless/system channels first (video, then audio), followed by owner groups. Each owner can have separate video and audio rows.
- The Library's Live favorites use the same `splitLiveMediaIds()` grouping and `CRSC(ids, 'live', null, { aspectRatio })` renderer, with separate TV Live (`16:9`) and Radio Live (`1:1`) rows. The `See All` link remains `/library/favorite/live`.
- `CRLV()` guards its initial and background redraws against route changes so a delayed Live response cannot replace another page.

#### Detail and player integration

- `openLiveDetail(id, callback)` resolves the selected channel when needed and dispatches by `liveMediaType(item)`: audio channels use `CREP(id, 'live', callback)`, while video channels use `CRVP(id, 'live')`. Missing records and resolution failures use the normal error flow; the callback is completed on these paths.
- `openLiveDetail(id, callback)` is the single owner of Live record resolution: it loads the cache, calls `EnsureItems('live', [id])` when the record is missing or not full, verifies that the requested Live route is still current after the asynchronous resolve, and dispatches only a full record to the correct renderer.
- `CREP(id, 'live', callback)` is the audio Live detail renderer. It reads the already-resolved full channel from `SN.live`, sets the Live document title, renders the shared audio-page layout, and keeps playback/favorites/menu actions typed as `live`. It does not resolve Live data or render catalog lyrics, songs, or related videos, and it does not redirect video channels.
- `CRVP(id, 'live')` is the video Live detail renderer. It reads the already-resolved full channel from `SN.live` and checks that the channel route is still current before building the page. It does not perform a second Live resolve. The regular catalog-video path still uses `resolveVideoItem(q)`.
- `CRVP(id, 'live')` owns the video Live detail renderer after `openLiveDetail` resolves the full record, reusing the standard video-stage, inline playback, and fullscreen-player behavior. No parallel Live detail renderer is maintained.
- Live is integrated into the app's existing queue and player infrastructure. Keep its media type distinction intact and reuse the current player / queue functions rather than creating a separate playback pipeline.
- Stream source URLs are runtime data. Do not persist them in `ha_live_cache_v1`; resolve the channel again when a cached card is opened or needs full playback data.

---

## 3. Playback

One player state object (`ensurePlayer`). Do not start a second playback pipeline.

- Playable types: `song`, `podcast`, `musicvideo`, `video`.
- `getPlayableSources(item)` is the only list the player indexes. It drops demo sources (`type === "demo"`) and rows without `url`. `nowPlaying.sourceIndex` is an index into **that** list, never into the raw `item.sources` array (download lists may still include demos).
- Manual quality change calls `PlayMediaSourceAt(index, { preserveTime: true })`.
- Automatic failover in `handleSourceFailure` must use the same `preserveTime: true` so a failed source does not restart the track at 0 when `currentTime` is already past 0.25s. A brand-new queue item still starts through `PlayPreferredSource` without preserving time.
- Quality preference is `audioQuality` or `videoQuality` from settings (`high` / `medium` / `low`), mapped onto real bitrates by `pickSourceByQuality`.
- Repeat and shuffle are settings (`repeatMode`, `shuffleMode`) plus in-memory shuffle history on the player object. The queue itself is memory-only and is cleared on a full reload.
- Desktop chrome is `maximum-player-desktop` (`CRDX`). Fullscreen chrome is `#player` / `#player-box` (`CRPL`). Minimize/maximize must not drop the current item.
- Owner badge on the desktop bar and `#player-owner` reads the **playable** source at `sourceIndex` (`resolveNowPlayingOwner`). Fullscreen badge shows an image only when the owner has a thumb; it has no placeholder. The desktop badge may show the download icon placeholder.

---

## 4. Lyrics

`LyricsEngine` is the only lyric renderer. Callers mount a container; they do not parse LRC themselves.

- Source data is `item.lyrics[]` with `lng` and `body` (`knd` / `fmt` may mark LRC).
- Timed text uses `parseLrc`. A positive `[offset:…]` is applied in `findIndex` (timestamp + offset compared with current time).
- Untimed text uses `parsePlain`.
- `mount(el, mode, view, bindItem)`: if `bindItem` is set, that box keeps a snapshot of **that** item. It must not follow whatever the player starts next. Highlight runs only when the playing item id matches the snapshot.
- Player surfaces (`mountDefaults`, media-stage, panels) stay unbound and follow the current item.
- Views: `normal`, `line`, `focus`, `card`, plus `static` when there are no timestamps. Video playback forces the `line` view from `getMainSettings` even if the user picked another mode.
- Font size and family apply only where a mount opts into the lyric skin. Other page text keeps the default font.
- Clicks on a timed line seek only when that mount belongs to the item that is actually playing.
- Intro lines (`[intro]` … `[/intro]`) stay on player mounts and are hidden on bound page mounts.

---

## 5. Data, memory, and localStorage

`SN` is the in-memory store for the whole session. Only the keys below are written to `localStorage`. Catalog objects are **not** persisted: a reload refetches them.

`canUseLocalStorage()` probes `localStorage` once (`HAS_LOCAL`). If it throws, profile and the other writes are skipped instead of breaking the page.

### 5.1 What stays only in `SN` (lost on reload)

| Key | Contents |
|---|---|
| `SN.song`, `SN.podcast`, `SN.video`, `SN.musicvideo`, `SN.album`, `SN.artist` | Entities merged by `UARDB` / `gjs`. `full` means the detailed payload is already loaded. |
| `SN.search` | Last search result payload. |
| `SN.user` (live session) | Logged-in user after refresh. `SN.accessToken` is memory-only. |
| `SN.player` / `ensurePlayer()` | Queue, index, now-playing, shuffle history, play generation. |
| `SN.state` | `ready`, `online`. |
| `SN.config` | `home` and `api` origins. |
| `SN.live`, `SN.latest_live` | Live record map and latest Live ID list; Live metadata may also be restored from `ha_live_cache_v1`. |

List endpoints also stash id lists such as `SN.top_song` on the `SN` object. Those ids are useless after reload until the list is fetched again.

### 5.2 localStorage keys

| Key | Memory mirror | What is stored | When it is written |
|---|---|---|---|
| `ha_profile` | `SN.profile` | `favorite` (one id array per `ITEM_TYPES` entry), `playlist` (id list), `playlistEntities` (full local playlist objects), `history` (`{id, type, at}`, max 50 rows **per playable type**), `search` | `SaveProfile()` after favorite, playlist, history, or search changes |
| `ha_settings` | `SN.settings` | Audio/video quality, lyric font/mode/lang, `playerShowLyrics`, `rotatingHeader`, `keepControllerVisible`, `repeatMode`, `shuffleMode`, sleep timer, view flags | `SetSetting` → `SaveSettingsToStorage` |
| `ha_owner` | `SN.owners` | Map of source owners `{ id, name, meta.tmb }`. New ids are inserted once; existing rows are not overwritten. Id `0` is Hamrahang and is not labeled in download rows. | `saveOwnersToLocal` after `putOwnerIfNew` |
| `ha_user` | cache read by `CheckLoginStatus` | Small cached profile so the UI can paint before refresh | Login / profile update |
| `ha_rftk` | — | Refresh token. Access token is not stored here. | Login; removed on logout or rejected refresh |
| `ha_live_cache_v1` | `SN.live`, `SN.latest_live` | Up to 100 latest Live IDs and lightweight Live metadata; `sources` are excluded and cached rows are marked `full: false`. | `SaveLiveCache()` after Live list refresh / Live resolve |
| `did` | — | Stable device id created once | `GetOrCreateDeviceId` |

`LoadProfile()` fills missing favorite types with empty arrays and copies `playlistEntities` back into `SN.playlist` so local playlists survive reload. Server playlists are still loaded from the API when the user is logged in.

Logout removes `ha_rftk` and `ha_user`. It does not wipe `ha_profile`, `ha_settings`, or `ha_owner` (those are device-local, not only account data).

### 5.3 API

- JSON reads go through `gjs`. Authenticated writes go through `gja` with `Authorization: Bearer` when `SN.accessToken` is set.
- Live listing uses `gjs(['live', 'latest'])`; full channel data is loaded through `gjs(['live', 'resolve'], ...)` via `EnsureItems`. Inspect `h_api.php` and a real response before changing either action or its response contract.
- Inspect `h_api.php` and a real response before changing a query or action name.
- Do not refetch an entity that is already `full` in `SN` unless the caller passed an explicit refresh.
- Optional `meta` fields (`tmb`, `tmv`, `cvr`, `art`, `dur`, `des`, `rls`, `ncp`) must be checked before use.

### 5.4 Accounts (short)

- Email login, register, verify, forgot-password, and change-password are API actions. Confirmation mail is plain English text.
- Profile edit can set name and avatar. Avatar bytes are cropped to a square in the browser before upload.
- Adding an email to an account that already owns that email is a confirm-by-link merge, not an instant overwrite.
- User `act`: `1` normal, `2`–`5` commercial (5 = recycled row), `6` blocked, `7`–`9` admin. Document this in `h_api.php` comments if the codes change.

---

## 6. Shared helpers (web app)

Add a row when a helper’s job changes. Do not document a guess.

| Helper | Responsibility |
|---|---|
| `chp(url)` | SPA navigation and history. |
| `CRHP` / `CREP` / `CRVP` / `CRAP` / `CRPP` | Home, song/album, video, artist, playlist pages. |
| `CRNF()` | Not-found content and a control back to home. |
| `CRGC` / `CRSC` / `CRGL` | Grid, carousel card, track line. |
| `HLV` / `liveRoute` | Canonical Live base path and encoded channel URL builder. |
| `CRLV` / `openLiveDetail` / `liveMediaType` | Live directory and media-aware channel dispatch. |
| `liveMediaType` / `splitLiveMediaIds` | Live media classification and audio/video grouping. |
| `liveMediaType` / `liveRoute` / `splitLiveMediaIds` | Live media classification, canonical channel route, and audio/video grouping. |
| `LoadLiveCache` / `SaveLiveCache` | Restore and persist lightweight Live metadata without expiring stream sources. |
| `resolveThumb` / `resolveThumbUrl` | Artwork key and URL. |
| `UARDB(type, list)` | Merge API rows into `SN[type]`. |
| `gjs` / `gja` | Public JSON read / authorized JSON action. |
| `LoadProfile` / `SaveProfile` | `SN.profile` ↔ `ha_profile`. |
| `GetSetting` / `SetSetting` | `SN.settings` ↔ `ha_settings`. |
| `ensureOwnersMap` / `getOwner` / `putOwnerIfNew` | `SN.owners` ↔ `ha_owner`. |
| `getPlayableSources` / `PlayMediaSourceAt` / `PlayPreferredSource` | Source list and playback. |
| `resolveNowPlayingOwner` | Owner of the source currently indexed for playback. |
| `LyricsEngine.mount` / `loadFromItem` / `sync` | Lyric parse, bind, and time sync. |
| `setPlayQueue` / `playQueueAt` | Queue and current item. |
| `UHERO` / `clearHeroMenu` | Desktop sidebar preview. |

---

## 7. Card click paths

This is the runtime map for a click on a rendered card. Hover (`UHERO`) is not part of these paths. Groups share one tree; the type list on each tree is the set of cards that take that path.

Cards are built by `CRSC` (carousel), `CRGC` (grid), and `CRGL` (track line). After a page finishes drawing, `ADE()` binds every `.item-box a`, `.artist-box a`, `.track-line a`, `.artists a`, `.artists-list a`, and `.section-header a`. The attribute `listener="true"` stops a second bind on the same node. `ADE()` always calls `preventDefault()` and `chp(href)`, so the browser does not do a full reload.

### 7.1 Open the item page

**Cards:** song, podcast, album, playlist, music video, video, artist, and Live.  
**Hit area:** artwork link and title link. Artist cards have no play button, so the whole card uses this path. A name under a card (`artists-inline` / `CRAL`) is the same path with an artist URL.

```text
click on card <a>
  └─ ADE() click listener
       └─ preventDefault()
            └─ chp(href)
                 ├─ history.pushState(href)
                 ├─ if the mobile max player is open → minimizeMaxToMini()
                 ├─ beforePageClear()          pull video out of the page stage
                 ├─ clearMainContent()         main column only; shell stays
                 └─ switch on the first path segment
                      ├─ /song/{id} or /podcast detail via CRHP
                      │    └─ CREP(id, 'song' | 'album' for /album/{id})
                      ├─ /video/{id} or /musicvideo detail via CRHP
                      │    └─ CRVP(id)
                      ├─ /artist/{id}
                      │    └─ CRAP(id)
                      ├─ /playlist/{id}
                      │    └─ CRPP(id, 'playlist')
                      ├─ /live... (all Live routes)
                      │    └─ CRLV(LPS[2], LPS[3], done)
                      │         ├─ numeric ID → openLiveDetail(id, done)
                      │         │    ├─ audio → CREP(id, 'live', done)
                      │         │    └─ video → CRVP(id, 'live')
                      │         └─ route key → CRLV routeMap (e.g. latest)
                      ├─ list URLs such as /song/top (See all on a section header)
                      │    └─ CRHP(section, query)
                      └─ unknown id / unknown list key
                           └─ CRNF()
                 └─ done()
                      ├─ song or artist detail → CHHD() sets document.title and preload thumb
                      └─ the new page calls ADE() again for its own links
```

**Result:** the main column is the item or list page. The player queue and the current track are not changed. Playback keeps running under the new page.

`CRHP` sends a numeric id to the detail renderer above. A word such as `top` or `latest` stays a list and does not open a detail page.

### 7.2 Live cards

Live directory cards and Library Live-favorite cards are both built by `CRSC(ids, 'live', ...)`; `CRLVCards` is removed and must not be reintroduced as a parallel renderer.

- A Live Video detail page is rendered by `CRVP(id, 'live')`, reusing the existing video-stage, inline playback and fullscreen-player behavior. `CRVP` resolves the record from `SN.live`/`EnsureItems` when called with `T === 'live'`; it must not send the Live ID through `resolveVideoItem`.

- `CRSC` creates the Live artwork and title anchors with `href: liveRoute(item)`. Both anchors use the normal `ADE()` navigation handler described in section 7.1; Live cards do not have a separate card-navigation listener.
- `HLV` is the canonical Live directory base path. Use `HLV` for the directory and `liveRoute(item)` for a channel URL; do not hand-build Live channel paths.
- `liveRoute(item)` returns `HLV` if the item or its ID is missing. Otherwise it builds the channel path from the encoded ID and encoded slug, using `item.url`, then `item.slug`, then the ID as fallback.
- The Live placeholder icon stays underneath the artwork when no thumbnail is available.
- Video Live cards use the `16:9` aspect ratio and prefer `meta.tmv`, then `meta.tmb`. Audio Live cards use the square `1:1` ratio and `meta.tmb`.
- The Live directory and Library favorites share this card renderer. Library favorites are split into TV Live (`16:9`) and Radio Live (`1:1`) rows; the `See All` destination remains `/library/favorite/live`.

**Artwork/title navigation:**

```text
click Live card artwork or title <a>
  └─ ADE() click listener
       └─ preventDefault() → chp(liveRoute(item))
            └─ /live/{id}/{slug} → CRLV(LPS[2], LPS[3], done)
                 └─ openLiveDetail(id, done) resolves SN.live[id] if needed
                      ├─ audio → CREP(id, 'live', done)
                      └─ video → CRVP(id, 'live')
                           missing record → CRNF()
```

### 7.3 Play from the card

**Hit area:** the play control on `CRSC`, `CRGC`, and `CRGL`. The handler calls `stopPropagation()`, so `ADE()` does not also open the page. Artist cards do not render this control.

**Playable cards:** song, podcast, music video, video, and Live.

```text
click .play
  └─ stopPropagation()
       └─ setPlayQueue(cardList, item.id, type)
            ├─ onQueueReplaced()
            ├─ normalizeQueueRows()
            ├─ keep only QUEUEABLE_TYPES
            ├─ replace the in-memory queue and redraw it (CRQT)
            ├─ same id + type + index already playing
            │    └─ TogglePlayPause()
            └─ otherwise
                 └─ playQueueAt(index)
                      └─ ensureQueueItemLoaded()
                           └─ PlayPreferredSource()
                                └─ PlayMediaSourceAt(picked index)
```

For Live cards, `CRSC` calls `setPlayQueue(ar, item.id, 'live')`. The queue list is the current Live row's IDs, and the selected channel becomes the starting item. The normal queue loading and player flow is used; Live does not have a separate playback pipeline.

**Result:** that item starts, or pauses if it was already the current row. The page does not change. The queue is the list that built the card (`ar`), starting at the clicked id.

**Collection cards:** album, playlist.

```text
click .play
  └─ stopPropagation()
       └─ playCollectionFromCard(id, 'album' | 'playlist')
            ├─ album  → ULBM(['album', id])  → e.songs
            └─ playlist → UPLS(['playlist', id]) → e.items
                 └─ setPlayQueue(children, firstChildId, 'song')
                      └─ same tree as the playable cards above
```

**Result:** the page stays. The queue becomes the album or playlist tracks and playback starts at the first child. An empty collection returns without changing the queue.

### 7.4 More menu on the card

**Cards:** every non-artist card from `CRSC`, `CRGC`, and `CRGL` (the dot control), including Live cards.

```text
click .menu / .more
  └─ CRBW(id, type)
       └─ bottom sheet for that item
            ├─ Go to {type}
            │    └─ close sheet → chp(item URL)
            │         └─ same page tree as section 7.1
            ├─ Go to artist (when the item has artists)
            │    └─ CRBW(id, type, 'art')
            └─ Download (playable types only)
                 └─ CRBW(id, type, 'don')
                      └─ renderDownloadSources()
```

For Live cards, the standard `CRBW(item.id, 'live')` sheet is rendered by `renderItemSheetMenu()`. Its Live-relevant actions are:

- **Like / Unlike:** toggles the Live favorite state.
- **Add to Queue:** adds the selected Live channel to the current queue.
- **Go to Live:** closes the sheet and calls `chp(liveRoute(item))`. It must use `liveRoute(item)`, not a separately assembled URL.
- **Share / Report:** the standard sheet actions, where available.

The Live menu deliberately does not offer **Add to Playlist**. This is a Live-specific exception; do not add that action unless the runtime behavior is intentionally changed.

**Result:** the sheet opens on top of the current page. Only a Go to action changes the route, and it reuses section 7.1. Download does not navigate and does not change the queue.
 
## 8. Change checklist

1. Name the page, helper, or API action.
2. Read the current function in `ha.php` and its callers.
3. For Live links, use `HLV` for the directory base and `liveRoute(item)` for a channel; do not hand-build `/live/{id}/{slug}`.
4. If data is involved, read `h_api.php` and one real JSON body first.
5. Keep playback on `getPlayableSources` indexes.
6. Keep page lyrics bound to their item; do not let the player overwrite them.
7. Persist only through the keys in section 5. Do not write the catalog into `localStorage`.
8. Update this file when a route, storage key, player/lyric rule, or card click path changes.

---

## 9. Not in this document

These belong to the legacy Web TV document (`docs/webtv_policy.md`) / `index.html` and must not be treated as Web App rules:

- ES5-only syntax and XMLHttpRequest-only networking
- `spatial_navigation.js`, D-pad sections, and `tv.css` progressive enhancement
- PNG icon fallbacks for TV asset limits

The Web App's `/live` routes, `SN.live` / `SN.latest_live`, Live cache, and Live resolution flow are documented in section 2.5 above. They are distinct from the legacy Web TV implementation.

Still to document only after reading the code, not from memory: every `gjs`/`gja` action name, and the full favorite/playlist sync sequence after login.
