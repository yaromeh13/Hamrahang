# Hamrahang Android TV — Global Behavior & Media Policy

This document is the evolving, canonical behavior reference for the Hamrahang Android TV application.

It is intentionally broader than image selection. New, verified product scenarios are added here as separate policy sections so that developers and collaborators can find the expected behavior and the related source files without reconstructing decisions from chat history.

## Document rules

- Product behavior and user-approved scenarios are normative requirements.
- The implementation audit records what the current source code actually does; it must not be mistaken for the desired behavior.
- Do not introduce a new behavior merely because it is common in other applications.
- Before changing code, reopen the current source files listed in the relevant section and check whether paths or implementation have changed.
- Keep Android TV native, D-pad-friendly, and compatible with the project's existing Gradle, Kotlin, and Android SDK constraints.
- All API requests must use the main API host `https://hamrahang.ir`. Do not add another API host or fallback API domain without explicit approval.
- Image/media CDN URLs are a separate concern from API endpoints; preserve the currently verified image host unless a deliberate, verified policy change is approved.
- Prefer small, architecture-aligned changes. Preserve local data, current screen state, focus, scroll, playback queue, and active playback unless a scenario explicitly requires otherwise.


## Mandatory development workflow

Before implementing or changing Android TV behavior, follow this sequence:

1. **Compare the product scenarios first.** Inspect the relevant behavior in the current Web TV reference, especially `index.html` on the repository's `main` branch, and inspect the corresponding Android implementation. Record the actual differences between Web and Android before deciding what needs to change.
2. **Audit existing APIs before API work.** Before creating, changing, or adding an API request/endpoint, inspect the existing Web implementation and API behavior (including the relevant code in `index.html` and the API implementation such as `h_api.php`). Reuse the existing contract where appropriate; do not assume a new endpoint, parameter, response field, or behavior exists.
3. **Inspect the current source.** Re-fetch/read all relevant files before editing. Do not rely solely on an old chat excerpt, a previous patch, or an earlier source snapshot.
4. **Keep Android native.** Match the Web product scenario and user-visible behavior, but implement it with the existing native Android TV architecture and D-pad interaction patterns. Do not port the Web UI or its browser-specific implementation into Android.
5. **Make minimal, architecture-aligned changes.** Avoid unnecessary rewrites, broad refactors, duplicate infrastructure, and unapproved behavior changes. Preserve existing data, focus, scroll, navigation, and playback state.
6. **Explain substantial changes before editing.** For a significant behavior or architecture change, first describe the observed Web/Android difference, the proposed approach, affected files, and relevant risks; wait for approval when the change goes beyond the user's explicit request.
7. **Preserve the toolchain constraints.** Do not raise or otherwise change the established Gradle, Android Gradle Plugin, Kotlin, JDK, compileSdk, minSdk, or targetSdk versions, or add dependencies that violate them, unless explicitly approved.
8. **Report changes precisely.** When presenting a code change, identify each exact repository-relative file path and provide the complete corrected function/block (not an ambiguous partial snippet). State what was and was not build-tested or device-tested.

### API host and contract requirements

- Every API request made by Android TV must target `https://hamrahang.ir`.
- Do not use another domain as an API host or fallback unless the user explicitly authorizes it.
- This restriction concerns API requests; it does not automatically prohibit separately verified image/media CDN URLs where the existing media policy permits them.
- The Web implementation is the first place to inspect for an existing API contract. Do not create or modify API behavior until the relevant Web behavior and server-side API implementation have been reviewed.


## Contents

1. [Network, Offline Mode & Recovery Policy](#1-network-offline-mode--recovery-policy)
2. [Image & Media Policy](#2-image--media-policy)
3. [Code Reference Index](#3-code-reference-index)
4. [Live — Audio & Video Behavior](#4-live--audio--video-behavior)
5. [Future Policy Sections](#5-future-policy-sections)

---

# 1. Network, Offline Mode & Recovery Policy

## 1.1 Scope and core principles

Network behavior has two separate responsibilities:

1. **NetworkMonitor** observes device network connectivity and reports connectivity changes.
2. **API health validation** determines whether the Hamrahang API is actually reachable by making a real request to `https://hamrahang.ir`.

A Wi-Fi/network connection is not proof that the API is available. NetworkMonitor must never be treated as the authority for API health.

Offline mode is a supported application mode, not an exceptional state that should force the user out of the app.

### Non-negotiable rules

- API calls use `https://hamrahang.ir` only, unless the user explicitly authorizes another API domain.
- Do not use a secondary API host as an automatic fallback.
- Do not show the startup Retry screen during normal app use.
- Do not reload/recreate the app or current screen after runtime reconnection.
- Do not interrupt or restart audio/video playback for network recovery, metadata sync, or image-cache completion.
- Failed API requests must not prevent use of available Room/local/cache data.
- Do not add mandatory periodic Ping requests. Avoid unnecessary health checks and duplicate resource requests.

## 1.2 Scenario A — Initial startup check

This is the only scenario in which the full Retry/Offline decision screen is allowed.

1. The app starts and displays the Splash screen.
2. After the Splash animation, send a Ping request to:
   `https://hamrahang.ir/app/ping`
3. The complete startup Ping attempt must have a maximum timeout of **15 seconds**.
4. If the Ping succeeds and the response is valid, continue to the main application.
5. If the Ping fails or times out, show the startup decision screen with exactly these choices:
   - **Retry:** the user manually starts another Ping attempt.
   - **Offline:** enter the main application without requiring an internet connection.
6. Do not automatically retry after a failed startup Ping.
7. Choosing Offline must set/retain the app's offline mode and allow locally cached data to be used.

The Retry screen belongs to Splash/startup only. It must not be reused for runtime network loss.

## 1.3 Scenario B — Network loss while the app is running

When the app has already entered its main UI:

1. Do not display the full Retry screen or a blocking network overlay.
2. Show only a small, non-blocking status message, such as **Connection lost**.
3. Keep the current screen and all locally available content usable.
4. Continue local interactions and playback where their required data is already available.
5. Do not close the app, navigate away, reload the current screen, or clear cached data.
6. A failed individual API request must be handled locally; it must not automatically be interpreted as proof that all network connectivity is lost.

## 1.4 Scenario C — Device network becomes available again

When NetworkMonitor reports that the device network has become available:

1. Show a small, non-blocking message such as **Network connected**.
2. Do not assume that the Hamrahang API is healthy just because Android reports an available network.
3. Validate the API by making a real request to the main API host, `https://hamrahang.ir`.
4. If that request fails, remain in runtime/offline behavior:
   - no Retry screen;
   - no full-screen blocking overlay;
   - no app reload;
   - no forced navigation;
   - no interruption to playback.
5. Recovery work may be attempted again on a meaningful subsequent connectivity/request event, but do not introduce an unnecessary periodic Ping loop.

A successful device connectivity callback and a successful API health check are different events and must remain separate in the architecture.

## 1.5 Scenario D — API becomes reachable: background recovery

After a real request confirms that the main API is reachable, start non-blocking recovery work.

### A. Background data synchronization

- Synchronize missing or stale data from the main API.
- Reuse existing repositories, Room entities, DAOs, and merge/update mechanisms where possible.
- Preserve locally available data if a request fails.
- Do not overwrite useful/full cached records with incomplete responses.
- Handle partial failures independently; one failed content type must not cancel unrelated synchronization.
- Avoid duplicate requests for data that is already current.

### B. Refresh the currently visible screen

- Determine which screen/fragment is currently visible.
- Refresh only the data that is missing or stale for that screen.
- Apply updated data to the existing screen where possible.
- Do not replace/recreate the whole fragment or reload the application just to refresh content.
- Preserve D-pad focus, selected item, scroll position, navigation state, menus, dialogs, and other active interaction state.
- If the current screen does not need an update, leave it untouched.

### C. Complete image and media cache independently

- Missing images, covers, thumbnails, and other cacheable media resources may be fetched after connectivity/API recovery.
- Image/media completion is independent of the primary metadata/data synchronization.
- A failed image or media resource must not block other images, metadata synchronization, or normal app use.
- Do not re-fetch resources that are already successfully cached unless an explicit freshness policy requires it.
- Keep image loading asynchronous and non-blocking.

### D. Playback continuity

Throughout recovery and cache completion, preserve:

- the current audio/video item;
- the active ExoPlayer instance and playback state;
- current playback position;
- play/pause intent;
- playback queue and queue index;
- selected source where possible;
- current player UI and its visibility.

Background sync and image loading must not stop, clear, prepare again, or replace the active media item.

If a stream itself failed because its source became unreachable, recovery behavior must be designed separately and must use the saved media identity, source and playback position. It must not silently restart or switch the user's playback during unrelated background synchronization.

## 1.6 Scenario E — User continues using the app in Offline mode

- All available Room/local/cache data remains usable.
- A request failure must fall back to local data wherever the relevant repository supports it.
- Offline mode must not be treated as a reason to clear data or disable local functionality.
- Pending local changes, such as playlist operations, must remain available for later synchronization according to their existing repository rules.
- When the API becomes reachable, use the recovery flow in Sections 1.4–1.5 without forcing the user to restart the app.

## 1.7 Runtime state model

The intended runtime flow is:

```text
Splash
  |
  v
Startup Ping (hamrahang.ir/app/ping, max 15 seconds)
  |
  +-- success ------------------------------> Main App
  |
  +-- failure --> Startup Retry / Offline
                    |
                    +-- Retry (manual) -----> Startup Ping
                    |
                    +-- Offline ------------> Main App (local data)

Main App
  |
  +-- device network lost ------------------> small status message
  |                                           continue local use
  |
  +-- device network available -------------> small connected message
                                              |
                                              v
                                    Real request to hamrahang.ir
                                              |
                          +-------------------+-------------------+
                          |                                       |
                       API fails                              API succeeds
                          |                                       |
                          v                                       v
                  remain in runtime                         background recovery
                  offline behavior                           |
                  no Retry screen                             +-- data sync
                  no reload                                   +-- current-screen
                  no playback interruption                        data refresh
                                                                  +-- image/media
                                                                      cache completion
                                                                  +-- preserve player
```

## 1.8 Current implementation audit — verified against the current main branch

This subsection records the implementation that exists in the source snapshot inspected for this update. It distinguishes implemented behavior from the remaining requirements. A source change is not considered build- or device-tested unless that test is explicitly recorded.

| Area | Current implementation and exact path | Status against the required scenario |
|---|---|---|
| Startup Ping endpoint | `android-tv/app/src/main/java/ir/hamrahang/tv/network/ApiService.kt` declares `GET app/ping`. `ApiClient.kt` uses the base URL `https://hamrahang.ir/`. | Matches the approved API host and endpoint. |
| Startup Ping deadline | `android-tv/app/src/main/java/ir/hamrahang/tv/network/ApiClient.kt` creates `pingClient` with `callTimeout(15, SECONDS)` and `retryOnConnectionFailure(false)`. `SplashActivity.kt` injects `ApiClient.pingApi` into `AppRepository`; `SplashViewModel.kt` calls `repo.ping()`. | The total call deadline is now configured to 15 seconds, rather than relying only on connect/read/write timeouts. Manual Retry creates a new attempt. Build/runtime verification is still pending. |
| Startup decision | `SplashActivity.kt` observes `SplashViewModel.state`. `SplashViewModel.kt` moves to Error on unsuccessful HTTP response or exception; Retry calls `viewModel.retry()`; Offline sets `AppState.offlineMode = true` and opens MainActivity. | Manual Retry and Offline choices exist. No automatic retry loop is present in this state flow. |
| Successful startup | In `SplashActivity.kt`, Success clears `AppState.offlineMode` and opens MainActivity. | Prevents a previous Offline selection from leaking into a later successful startup. |
| Device network observation | `android-tv/app/src/main/java/ir/hamrahang/tv/network/NetworkMonitor.kt` registers Android connectivity callbacks and checks `NET_CAPABILITY_INTERNET`. `App.kt` initializes NetworkMonitor. | This observes device network capability only; it does not establish API health. |
| Network permission | `android-tv/app/src/main/AndroidManifest.xml` declares both `INTERNET` and `ACCESS_NETWORK_STATE`. | The required connectivity-state permission is now declared. |
| Runtime request timeout | `NetworkInterceptor.kt` emits `NetworkEvents.Event.Timeout` for a `SocketTimeoutException`. `MainActivity.kt` currently handles this event as `Unit`. | A single request timeout no longer opens a blocking overlay or reloads the screen through this MainActivity event handler. The interceptor still emits the event; other collectors/call sites must be checked before removing the event type. |
| Runtime network loss | `MainActivity.kt` handles `InternetLost` by setting `apiReachable = false` and showing a non-blocking message through `MessageManager.warning`. | The current screen is not replaced by this handler. Local data/playback continuity still requires end-to-end testing. |
| Device network recovery | `MainActivity.kt` handles `InternetAvailable` by showing a connected message and calling `HomeChannelPublisher.publish()`. If `apiReachable` is false and no recovery job is active, it launches a health check using `ApiClient.pingApi.ping()`. | Device connectivity and API health are now treated as separate checks. The connected message means device network available, not that the API is healthy. |
| API health result | In `MainActivity.kt`, recovery proceeds only when the Ping response is successful and its body has `status == "ok"`. A failed check logs a warning and returns without showing the startup Retry UI. | No blocking UI or reload is performed on failed runtime health validation. A later device-network-available event can start another check; there is no periodic Ping loop. |
| Recovery work currently performed | After a successful reconnect Ping, `MainActivity.kt` calls `AuthSessionManager.refreshSession()` and `PlaylistRepository.refreshAndSync()`. | Session and Playlist are the only explicit recovery tasks in this flow. This is not yet a complete background sync for every content repository. |
| Current-screen update | After Playlist sync succeeds, `MainActivity.kt` calls `HomeFragment.refreshPlaylists()` only if the fragment in `R.id.contentContainer` is a `HomeFragment`. | This is a Home/Playlist-specific refresh, not a general refresh contract for whichever screen is visible. Other screens are not yet refreshed by this recovery path. |
| Image/media cache completion | `android-tv/app/src/main/java/ir/hamrahang/tv/util/ImageResolver.kt` resolves artwork and uses asynchronous Glide loading. | No independent reconnect-triggered queue or retry pass for missing image/media cache entries has been implemented in the inspected flow. |
| Player continuity | `PlayerManager.kt` owns the reusable ExoPlayer; `PlayerState.kt` stores shared player UI state. The current reconnect handler does not call PlayerManager to stop or prepare media. | General recovery does not explicitly manipulate the player, but preservation of active playback during real network loss/recovery has not yet been verified on a device. Stream-source recovery is not implemented by this reconnect flow. |
| Database/cache preservation | `AppDatabase.kt` declares Room version 35. `DatabaseProvider.kt` registers migrations through 33→34 and uses `fallbackToDestructiveMigration()`; the inspected provider did not show a 34→35 migration. | Do not claim that local data is guaranteed across every database upgrade. Verify the current schema/migration history and add the correct migration before relying on destructive fallback behavior. |

### Important runtime limitations and edge cases

- `NetworkEvents` uses a `MutableSharedFlow` with `replay = 0`. `NetworkMonitor.init()` runs from `App.onCreate()`, while `MainActivity` starts collecting later. A connectivity event emitted before the collector subscribes can be missed. The initial network state is available through `NetworkMonitor.isConnected`, but the current MainActivity recovery code does not use that StateFlow to trigger an initial API health check when the app was entered Offline and the device is already connected.
- `NetworkMonitor` checks `NET_CAPABILITY_INTERNET`, not API reachability and not necessarily validated public internet access. The explicit Ping remains the API-health check.
- `apiReachable` is initialized to true when MainActivity is entered after a successful startup Ping, and false when entered through Offline. It is reset to false on `InternetLost`. If the API becomes unavailable while the device network remains continuously available, a request timeout is ignored by MainActivity and no automatic health check is triggered until another device-network-available event occurs. This is a remaining recovery gap; do not add periodic Ping without approval.
- The reconnect Ping uses `pingApi`, which has a 15-second total call timeout and disables OkHttp connection retry. The ordinary `api` client still has `retryOnConnectionFailure(true)`; that setting is separate from the startup/reconnect Ping client.
- `HomeChannelPublisher.publish()` is invoked on the device-network-available event before the API Ping. This is a separate Home Channel operation and must not be interpreted as proof that API recovery succeeded.
- `NetworkOverlay.kt` and `ReloadManager.kt` remain in the repository. The current MainActivity event handler no longer invokes the timeout overlay/reload path, but these files must not be deleted until all registration and call sites are checked.
- The source changes recorded here have not yet been confirmed by a successful Android Gradle build or physical Android TV test.

## 1.9 Current code paths — startup and reconnect

### Startup path

```text
App.onCreate()
  └─ NetworkMonitor.init()
       └─ observes Android device connectivity

SplashActivity
  └─ plays splash animation
       └─ SplashViewModel.checkServer()
            └─ AppRepository.ping()
                 └─ ApiClient.pingApi
                      └─ GET https://hamrahang.ir/app/ping
                           ├─ HTTP/response status valid + body.status == "ok"
                           │    └─ SplashState.Success
                           │         └─ AppState.offlineMode = false
                           │              └─ MainActivity
                           └─ failure / timeout / invalid response
                                └─ SplashState.Error
                                     ├─ Retry → SplashViewModel.retry() → new manual Ping
                                     └─ Offline → AppState.offlineMode = true → MainActivity
```

### Runtime reconnect path currently implemented

```text
NetworkMonitor callback
  └─ NetworkEvents.InternetAvailable
       └─ MainActivity collector
            ├─ show non-blocking "Internet Connected" message
            ├─ HomeChannelPublisher.publish()
            └─ if apiReachable == false and no recovery job is active:
                 └─ ApiClient.pingApi.ping()
                      ├─ failed / body.status != "ok"
                      │    └─ log and return; no Retry screen, no reload
                      └─ successful
                           ├─ apiReachable = true
                           ├─ AuthSessionManager.refreshSession()
                           └─ PlaylistRepository.refreshAndSync()
                                └─ if sync succeeded and current fragment is HomeFragment:
                                     └─ HomeFragment.refreshPlaylists()
```

### Still-required recovery work

The current path above does **not** yet implement:

1. A reliable initial recovery check when the app entered Offline but the device already had network connectivity before MainActivity began collecting events.
2. A controlled API re-check after API-only outages where Android device connectivity never changes (without introducing an unapproved periodic Ping).
3. General background synchronization for all relevant content repositories.
4. In-place stale/missing-data refresh for every supported current screen, preserving focus and scroll.
5. An independent image/media cache completion queue after reconnect.
6. Device-tested guarantees for uninterrupted playback and preservation of current item/position/queue.
7. A verified Room migration path that protects existing local cache.


---

# 2. Image & Media Policy

This section preserves the existing media-artwork selection policy. Its priority order is intentional: use the first non-empty metadata key in each row. Do not silently fall back to another media type's image or to an unrelated artist image.

## 2.1 Standard media artwork

| Content type | Primary image | Fallback image |
|---|---|---|
| Song | `meta.tmb` | Song icon (`svg_sng`) |
| Podcast | `meta.tmb` | Song icon (`svg_sng`) |
| Artist | `meta.tmb` | Artist/user icon (`svg_usr`) |
| Album | `meta.tmb` | Disk icon (`svg_dsk`) |
| Playlist | `meta.tmb` | Playlist icon (`svg_pls`) |

For these types, no image is borrowed from an associated artist. The type's own icon is shown when `meta.tmb` is absent or the image fails to load.

## 2.2 Video artwork

| Content type | Priority 1 | Priority 2 | Priority 3 |
|---|---|---|---|
| MusicVideo | `meta.tmv` | `meta.tmb` | Video icon (`svg_vdo`) |
| Video | `meta.cvr` | `meta.tmb` | Video icon (`svg_vdo`) |

MusicVideo and Video remain distinct when resolving artwork. Video uses `cvr` before `tmb`; MusicVideo uses `tmv` before `tmb`.

## 2.3 Owner and User artwork

Owner/provider and User profile images are separate artwork categories. Both use the user/profile SVG fallback when their own image is unavailable.

| Entity | Priority 1 | Priority 2 | Priority 3 | SVG fallback |
|---|---|---|---|---|
| Owner / Provider | `owner.meta.ico` | `owner.meta.tmb` | Source icon, only when the source payload provides one | User icon (`svg_usr`) |
| User | `user.meta.tmb` | — | — | User icon (`svg_usr`) |

### Owner image-key rules

- The owner `ico` key follows the web convention: if it has no dot, its last three characters encode the extension (for example, `u11210725ipng` becomes `u11210725i.png`).
- An owner `tmb` key always receives `.jpg`.
- The source/provider icon is distinct from the owner's profile artwork. Do not store a source icon as the owner's profile icon.
- Current Android `MediaSource` does not declare a source-icon field, so this optional fallback is only usable when the API payload/model actually provides one.
- If no applicable image/source icon exists, show `svg_usr` rather than leaving the owner/user artwork area without a fallback.


## 2.4 Hero wide background

For the wide Hero background only:

1. `meta.art`
2. `meta.tmv`
3. No background image.

Do not use `tmb`, `cvr`, owner artwork, or generic type icons as Hero background fallbacks.

## 2.5 Resolution and display requirements

- Treat null, empty, and whitespace-only keys as missing.
- Preserve the exact priority order above.
- Resolve thumbnail keys using the existing Android convention documented here: `https://cdn.hamrahang.ir/thumb/{key}.jpg`.
- For Hero backgrounds, both `meta.art` and `meta.tmv` use the thumbnail URL builder, matching the web reference's `resolveHeroBgUrl()` behavior.
- A missing/broken image falls back to the appropriate type icon and must not break the card, list, Hero, or playback UI.
- Keep image-key selection in the shared `ImageResolver`; card loaders use the same resolver request helper and a type-specific placeholder/error.
- Hero resolution is separate because its priority differs from thumbnails.
- Playlist artwork resolution is available in `ImageResolver`, but the current Android TV tree has no Playlist domain model/card adapter; the fallback is ready for the point where Playlist UI is introduced.
- Image loading remains asynchronous and independent of playback.

### Image host

The verified Android image base URL is `https://cdn.hamrahang.ir/thumb/`, matching `THUMB_BASE` in `ImageResolver.kt`. This is an image CDN, not an API endpoint; API requests remain restricted to `https://hamrahang.ir`.

## 2.8 Android TV card thumbnail area and missing-image fallback

This section defines the visual treatment for content cards when the entity has no thumbnail key or the requested image fails to load. The thumbnail surface and focus border are separate visual layers. The dark surface must occupy only the actual image viewport; it must not appear as an extra frame around a successfully loaded image.

### Required visual behavior

- The image viewport has a **`#222222`** base surface, matching the default unselected Settings category-card color in `bg_setting_category.xml`.
- The base surface is drawn directly behind the image/placeholder, at the same bounds as the image viewport. A real opaque thumbnail covers it completely; only a missing/error/transparent image reveals it.
- The red focus border is a separate outer frame. The space between the red border and the image viewport remains transparent, not dark gray.
- The square and landscape card layouts currently inset the image viewport by 6dp inside the outer focus frame. This preserves the visual breathing room between the artwork and red focus border without creating a gray band.
- Artist cards use the same layering principle with a circular dark surface inset 4dp inside the transparent outer focus frame.
- The type-specific fallback icon is centered within the image viewport and must not be enlarged to fill it.
- Successfully loaded thumbnails continue to use their existing Glide transformations (for example `CenterCrop()`, `RoundedCorners(7)`, or `circleCrop()`). Do not remove those transformations.
- Card dimensions, titles/subtitles, D-pad navigation, focus callbacks, click behavior, image metadata priority and Hero behavior must remain unchanged.

### Current resource and layout implementation

| Resource | Responsibility / current rule |
|---|---|
| `android-tv/app/src/main/res/drawable/card_bg.xml` | Transparent outer focus frame for rectangular cards. Only the selected state draws the 3dp red border; it does not paint a dark fill. |
| `android-tv/app/src/main/res/drawable/card_thumbnail_surface.xml` | Rounded `#222222` surface behind the square/landscape image viewport. |
| `android-tv/app/src/main/res/drawable/art_focus_border.xml` | Transparent outer circular artist focus frame; selected state draws the 3dp red border. |
| `android-tv/app/src/main/res/drawable/artist_thumbnail_surface.xml` | Circular `#222222` surface behind the artist image viewport. |
| `android-tv/app/src/main/res/layout/item_content.xml` | Shared square card layout. `coverFrame` owns the outer focus border; `itemCover` is inset 6dp and owns the dark image surface. |
| `android-tv/app/src/main/res/layout/item_musicvideo.xml` | Shared landscape Video/MusicVideo layout. `coverFrame` owns the outer focus border; `cover` is inset 6dp and owns the dark image surface. |
| `android-tv/app/src/main/res/layout/item_artist.xml` | Artist image layout. `artistContainer` owns the outer circular focus border; `artistImage` is inset 4dp and owns the circular dark surface. |

### Adapter focus ownership

The focus state must be applied to the outer frame, not the image view:

- Song, Podcast, Album, Playlist and PlaylistMedia adapters set `coverFrame.isSelected`.
- Video and MusicVideo adapters set `coverFrame.isSelected`.
- ArtistAdapter sets `artistContainer.isSelected`.

This keeps the red border outside the artwork and leaves the inset gap transparent. Do not move the selected state back onto the ImageView.

### Acceptance criteria

1. A missing/broken Song, Podcast, Album or Playlist thumbnail shows its type-specific icon over the dark surface, within the image viewport.
2. A missing/broken Video or MusicVideo thumbnail shows the video icon over the dark surface, within the landscape image viewport.
3. With a valid opaque image, no dark gray strip or frame is visible around the artwork; only the intentional transparent gap and red focus border remain.
4. When focused, the red border is drawn by the outer frame and does not overlay or shrink the image itself.
5. When not focused, the outer frame is transparent and does not add a visible gray outline.
6. Artist cards retain their circular crop, dark fallback surface and separate red focus border.
7. Existing Glide transformations, card dimensions, focus navigation, titles, subtitles and click behavior remain unchanged.
8. The Hero wide background remains independent and continues to use its own `art → tmv` policy.
9. Confirm all criteria in an Android build and on a TV/device; source changes alone are not a device-test result.


## 2.7 API JSON image metadata and six media roles

### Where image keys appear in API JSON

Image keys are exposed in the `meta` object of the relevant JSON entity. For a playback source's owner/provider, the image metadata is nested one level deeper under `source.owner.meta`. The exact keys depend on the entity and the media role; the API does not promise that every entity contains all six keys.

Examples of the JSON shape:

```json
{
  "id": 123,
  "name": "Example content",
  "meta": {
    "tmb": "s123t",
    "tmv": "s123v",
    "cvr": "s123c",
    "art": "s123a",
    "gal": ["s123g0", "s123g1"]
  },
  "sources": [
    {
      "url": "https://example.invalid/stream",
      "owner": {
        "id": 456,
        "name": "Example provider",
        "meta": {
          "tmb": "u456t",
          "ico": "u456i"
        }
      }
    }
  ]
}
```

This is a structural example, not a literal API response. Keys are optional and are returned only when the corresponding data exists. The example stream URL is illustrative only; Android API requests must still use `https://hamrahang.ir`.

### The six API media roles

The API helper `MediaRoleToAct()` in `h_api.php` defines exactly six stored media roles:

| Role | Meaning / use | JSON location when exposed |
|---|---|---|
| `tmb` | Standard thumbnail | `entity.meta.tmb`; owner/user profile card may expose `meta.tmb` |
| `tmv` | Music-video artwork | `entity.meta.tmv` |
| `cvr` | Cover artwork | `entity.meta.cvr` |
| `art` | Artwork / wide art | `entity.meta.art` where the entity exposes it |
| `gal` | Gallery images; this is a collection, not one thumbnail | `entity.meta.gal[]` |
| `ico` | Owner/provider icon | `source.owner.meta.ico` (or the corresponding owner card's `meta.ico`) |

The six roles above are confirmed by the API's role-to-action map. They are six **media roles**, not six guaranteed fields on every JSON object. In particular:

- `gal` is an array/collection when multiple gallery images are returned.
- `ico` belongs to the owner/provider profile metadata; it is not a generic content thumbnail.
- `art` has legacy behavior in some content records. For example, the song card builder can expose `meta.art` as a numeric value, so clients must not assume every `art` value is an image key without checking the entity contract.
- Owner cards expose `meta.tmb`, `meta.ico`, and may expose `meta.photo`. The `photo` value is an external URL and is not a seventh stored media role.
- `photo` and `off` map to action `0` in `MediaRoleToAct()`; they are not part of the six stored media roles.

### Supported image file formats

The API ingestion helper `IngestThumbFromSource()` accepts these image formats: **JPG/JPEG, PNG, SVG, WEBP, and GIF**. Therefore, there are five supported file formats, while there are six semantic media roles in the API. Do not confuse the number of roles with the number of file formats.

### Media-role flow

```text
API entity JSON
      |
      +-- entity.meta
      |      |
      |      +-- tmb  : standard thumbnail
      |      +-- tmv  : music-video artwork
      |      +-- cvr  : cover artwork
      |      +-- art  : artwork (check entity/legacy value type)
      |      +-- gal[]: gallery image collection
      |
      +-- source.owner.meta
             |
             +-- tmb : owner profile thumbnail
             +-- ico : owner/provider icon

Media upload / ingestion
      |
      +-- six stored roles: tmb, tmv, cvr, art, gal, ico
      |
      +-- image file format: JPG / PNG / SVG / WEBP / GIF
      |
      +-- photo: external URL only (not a stored role)
      +-- off  : disabled (not an image role)
```

### Verified source references

- `h_api.php`: `MediaRoleToAct()`, `MediaPlanBKey()`, `IngestThumbFromSource()`, `OwnerCard()`, and the `SONG_CARD()`, `ARTIST_CARD()`, `ALBUM_CARD()`, and `VIDEO_CARD()` builders.
- `index.html`: web image-key resolution and owner image fallback behavior.
- `android-tv/app/src/main/java/ir/hamrahang/tv/util/ImageResolver.kt`: Android image URL construction and artwork resolution.

## 2.6 Web reference

The reference `index.html` contains these SVG variables:

- Song icon: `svg_sng`
- Artist/user icon: `svg_usr`
- Album disk icon: `svg_dsk`
- Video icon: `svg_vdo`
- Playlist icon: `svg_pls`

The existing web thumbnail helper currently uses `tmv → tmb` for generic video-like content. The Android policy intentionally distinguishes Video (`cvr → tmb`) from MusicVideo (`tmv → tmb`), as specified above.

---

## 2.10 Shared Hero artwork layer

- All Hero types share one wide artwork view: `heroArt`, declared once in `layout_hero.xml` behind the Hero content layouts.
- Any Hero backed by media metadata should use the shared artwork resolver (`ImageResolver.hero(meta)`), which prefers `meta.art` and then `meta.tmv`. The content-specific cover/thumbnail remains a separate view and keeps its existing sizing and crop behavior.
- MusicVideo retains its existing thumbnail fallback when neither wide artwork key is available. Other media Hero types show the shared wide layer only when a wide artwork URL resolves; do not promote a square cover to wide artwork automatically.
- Artist uses the same shared artwork layer and resolver. Its circular `artistCover` remains the portrait from `meta.tmb`.
- The shared artwork view uses the common 70%-width, full-height, center-crop layout and `fadeStart = 0.4f`. Its placement is defined in XML, not changed by per-Hero Kotlin layout code.
- The shared layer is anchored with `endToEnd` and inherits the locale layout direction, so it automatically mirrors between LTR and RTL. Do not add runtime positioning functions for individual Hero types.
- When a Hero has no wide artwork, hide and clear the shared image so the previous Hero's artwork cannot remain visible. Playlist currently exposes only its local thumbnail field and has no `meta.art`/`meta.tmv` metadata; keep that thumbnail in its existing cover position unless the data model is extended with wide artwork.

# 3. Code Reference Index

Paths below are repository-relative and were checked against the current `main` branch while this document was updated. Kotlin package declarations do not always mirror directory names exactly; use the exact paths shown here.


## 2.9 Artist Hero behavior

- The Artist detail screen keeps the selected artist as its Hero identity while the user moves through its content rows. Row focus must not replace the Artist Hero with a Song, Album, Video, or MusicVideo Hero.
- The artist portrait remains the circular thumbnail from `meta.tmb`, displayed in the existing `artistCover` position.
- The wide artwork is rendered by the single shared `heroArt` layer in `layout_hero.xml`, not by an Artist-specific image view. When `meta.art` exists, the Artist Hero uses the same artwork placement, 70%-width sizing, locale-aware alignment, and fade behavior as every other Hero type. This is separate from the portrait thumbnail.
- The Artist detail page background uses `meta.art` first. If `meta.art` is absent or blank, it falls back to the artist's `meta.tmb`. Both are resolved through the existing thumbnail CDN URL helper.
- The Artist detail background is set when the artist is loaded and remains fixed while browsing its rows. The detail page's row-focus background callback must not replace it with a focused card's background.
- Home may continue to update the Hero/background when an Artist card receives focus; the fixed-background rule above applies to the dedicated Artist detail page.
- The Artist Hero actions are placed together in one horizontal row below the artist details. Play remains the existing icon action; Follow is a text-only, focusable MaterialButton beside it.
- Follow uses localized resources: `follow` and `following`. Its selected state is read from and toggled through the existing `FavoriteManager` with `ContentType.ARTIST`; do not introduce a new API endpoint for this control.
- On Home, an Artist Hero exposes Follow only. Other Hero types hide that action. On the Artist detail page, Play and Follow are both shown.
- Both actions support D-pad focus. Moving down from either action enters the first artist content row; horizontal navigation moves between Play and Follow on the Artist detail page.
- The Artist detail Hero and background remain stable while its Videos, Music Videos, Top Songs, and Albums rows are browsed. The rows remain interactive and continue to dispatch their normal item-click actions.

## 3.1 Startup and API

| File | Responsibility |
|---|---|
| `android-tv/app/src/main/java/ir/hamrahang/tv/SplashActivity.kt` | Splash animation, observing startup state, startup Retry/Offline UI, transition to MainActivity. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/SplashViewModel.kt` | Startup Ping state machine and manual retry. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/AppRepository.kt` | Startup repository wrapper for Ping. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/network/ApiService.kt` | Retrofit API routes, including `GET app/ping`. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/network/ApiClient.kt` | Main API client plus a dedicated Ping client with a 15-second total call timeout and no connection retry. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/network/PingResponse.kt` | Startup Ping response model. |
| `android-tv/app/src/main/AndroidManifest.xml` | Declared Android permissions and activities; check network permissions here. |

## 3.2 Runtime network and recovery

| File | Responsibility |
|---|---|
| `android-tv/app/src/main/java/ir/hamrahang/tv/App.kt` | Application initialization; initializes DatabaseProvider, NetworkMonitor and other app-wide components. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/network/NetworkMonitor.kt` | Observes Android device network callbacks. Must remain a connectivity observer, not an API-health checker. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/network/NetworkEvents.kt` | Shared network event stream (timeout, available, lost). |
| `android-tv/app/src/main/java/ir/hamrahang/tv/network/NetworkInterceptor.kt` | Converts request socket timeouts into network events. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/network/NetworkOverlay.kt` | Existing network overlay wrapper; its Kotlin package is `ir.hamrahang.tv.ui.network`. MainActivity's current Timeout handler does not invoke it; inspect all other call sites before removal. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/network/ReloadManager.kt` | Callback holder for reload actions; it is not invoked by the current MainActivity Timeout handler. Inspect all registration/call sites before removal. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/MainActivity.kt` | Ignores individual timeout events for UI purposes; handles device network loss/availability, validates API health on reconnect, then refreshes session and Playlist; applies Playlist changes only to HomeFragment. |

## 3.3 Data, local cache and synchronization

| File / area | Responsibility |
|---|---|
| `android-tv/app/src/main/java/ir/hamrahang/tv/data/local/AppDatabase.kt` | Room database schema and current database version (38 in the inspected source). |
| `android-tv/app/src/main/java/ir/hamrahang/tv/data/local/DatabaseProvider.kt` | Room construction and registered schema migrations, including Live media-type migration 37→38. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/data/local/DatabaseUpdater.kt` | Maps/merges received DTO data into local Room entities. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/data/auth/AuthSessionManager.kt` | Account/session restoration used by MainActivity during startup/recovery. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/data/playlist/PlaylistRepository.kt` | Playlist refresh and synchronization used by the current reconnect flow. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/data/` | Content DTOs, repositories, Room entities and DAOs; inspect the specific repository before changing a content type's offline behavior. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/data/live/` | Live DTO/domain models, repository, Room mapper and latest/search/resolve data flow. |

## 3.4 Images and playback

| File | Responsibility |
|---|---|
| `android-tv/app/src/main/res/drawable/card_bg.xml` | Transparent outer rectangular focus frame; red border only when selected. |
| `android-tv/app/src/main/res/drawable/art_focus_border.xml` | Transparent outer circular artist focus frame; red border only when selected. |
| `android-tv/app/src/main/res/layout/item_content.xml` | Shared square content card image area and centered fallback display. |
| `android-tv/app/src/main/res/layout/item_musicvideo.xml` | Shared landscape Video/MusicVideo image area and centered fallback display. |
| `android-tv/app/src/main/res/layout/item_live_video.xml` | Landscape 16:9 card layout used by Live video items. |
| `android-tv/app/src/main/res/layout/item_artist.xml` | Circular artist image card layout. |
| `android-tv/app/src/main/res/drawable/card_thumbnail_surface.xml` | Dark rounded background directly behind square/landscape thumbnail content. |
| `android-tv/app/src/main/res/drawable/artist_thumbnail_surface.xml` | Dark circular background directly behind artist artwork/fallback. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/util/ImageResolver.kt` | Artwork-key priority, thumbnail URL construction, Hero/owner resolution, shared Glide request helper. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/ui/LiveFragment.kt` | Live page loading/search, owner and media-type grouping, and Audio/Video playback entry points. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/ui/LiveRowsAdapter.kt` | Owner/system section headers and horizontal Live card rows. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/ui/LiveAdapter.kt` | Live card binding, thumbnail fallback, D-pad focus and square/landscape layout selection. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/player/PlayerManager.kt` | ExoPlayer ownership, current media/source, playback position and player controls. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/player/PlayerState.kt` | Shared player UI/playback state (verify exact path before editing if the source is moved). |
| `android-tv/app/src/main/java/ir/hamrahang/tv/channel/HomeChannelPublisher.kt` | Publishes Android TV Home Channel content; currently called during app initialization and reconnect. |

## 3.5 Web reference

| File | Responsibility |
|---|---|
| `index.html` | Primary web reference for product scenarios, API behavior, and image-selection conventions. Compare behavior with Android, but do not copy web-specific implementation or alternate API hosts into Android without approval. |

---

# 4. Live — Audio & Video Behavior

## 4.1 Scope and relationship to Web TV

This section defines the Android TV behavior for Live audio and Live video. The Web TV implementation in the current `index.html` is the product-scenario reference; Android must reproduce the user-visible scenario through native Fragments, Leanback rows, D-pad navigation and the existing playback architecture.

This policy covers Live card data, grouping, artwork, card presentation, incomplete-card resolution, and the playback entry point. It does **not** define the Live player's LIVE indicator, elapsed-time/duration display, or seek-bar behavior. Those player controls are intentionally deferred until the user specifies their expected behavior.

### Core rules

- Read the top-level API field `media_type` to distinguish Live video from Live audio.
- A missing or unrecognized `media_type` is treated as audio for backward compatibility.
- Do not infer media type from the source URL, file extension, or optional source metadata.
- Do not change the existing API host or create a new Live API contract. Android uses the existing API at `https://hamrahang.ir`.
- Keep Live data in the existing Live repository and Room cache; do not create a parallel cache or new API storage model for presentation-only behavior.
- Keep Android native and D-pad friendly. Web routes and browser-specific UI are reference behavior, not implementation instructions for Android.
- Source `protocol` metadata, where available, is descriptive metadata only. It does not by itself guarantee that a particular streaming protocol is supported by the player.

## 4.2 Required Live page scenarios

### A. System-owned or ownerless Live cards

- Cards with no owner and cards whose owner ID is `0` belong to the system-owned Live sections.
- Show separate fixed sections:
  1. **Live Video**
  2. **Live Audio**
- Show the Live icon in the section artwork area.
- Do not show an empty/unknown owner title for these system sections.
- Keep the two media types in separate horizontal rows; do not mix audio and video cards in one row.

### B. Live cards belonging to other owners

- Group cards by their owner.
- Preserve the owner's display name and owner artwork.
- If an owner has both media types, show separate Video and Audio rows under that owner.
- Avoid repeating the same owner header for both rows when the layout can represent the split as one owner group.
- Do not merge different owners into one queue or one visual group.

### C. Card artwork and aspect ratio

- Live Audio cards use the existing square content-card presentation.
- Live Video cards use a landscape **16:9** presentation.
- For Live Video artwork, prefer `meta.tmv`, then fall back to `meta.tmb`.
- For Live Audio artwork, use `meta.tmb`.
- If a card has no usable thumbnail or image loading fails, show the Live fallback icon centered in the image viewport.
- The thumbnail fallback must not remove or alter the card's outer focus border, D-pad focus behavior, title, or click handling.

### D. Selecting and playing a Live card

- Selecting a Live Audio card starts an audio Live session using the existing internal playback entry point.
- Selecting a Live Video card starts a video Live session and opens the main fullscreen player directly.
- Audio queues contain only Live Audio cards from the selected owner/system section.
- Video queues contain only Live Video cards from the selected owner/system section.
- Preserve the selected card as the queue's current item; do not silently start from the first item when the selected item is available.
- Keep the existing shared player/session architecture. Do not create a second ExoPlayer solely for Live video.
- The fullscreen player must continue to support the application's existing minimize/return-to-mini-player behavior where that behavior is already supported by the shared player architecture.

### E. Incomplete Live cards and offline cache

- The latest Live response may contain summary cards without all metadata or playable sources.
- Reuse the existing Live resolve operation to complete incomplete cards; do not invent a new endpoint or response shape.
- Resolve only cards that are incomplete and do not already have a complete cached copy.
- If resolving a card fails, retain and use its complete local Room copy when one exists.
- A search failure must not make cached Live cards unusable; local search/filtering remains available.
- Do not overwrite a complete local Live record with an incomplete summary response.
- Preserve existing owner data, card ordering and unrelated cached Live records when merging results.

## 4.3 Current Android implementation — source audit

The following implementation was re-opened on the `main` branch while this section was added. This is a source audit, not a claim that the app has passed a Gradle build or TV/device test.

| Area | Current source implementation | Status |
|---|---|---|
| API model | `data/live/LiveDto.kt` maps the top-level JSON `media_type` field. A missing value is treated as audio by the domain model. | Implemented. |
| Domain model and artwork | `data/live/LiveItem.kt` exposes `isVideo`; video thumbnail priority is `tmv → tmb`, while audio uses `tmb`. | Implemented. |
| Room persistence | `data/local/entity/LiveEntity.kt` stores `mediaType`. `AppDatabase.kt` is version 38, and `DatabaseProvider.kt` adds migration 37→38 with an audio default for existing rows. | Implemented in source; migration still needs runtime verification against an existing database. |
| DTO/entity mapping | `data/live/LiveEntityMapper.kt` maps media type and preserves the existing value when an update omits it. A complete cached card is retained when a partial response arrives. | Implemented in source. |
| Latest list and resolve | `data/live/LiveRepository.kt` resolves incomplete latest cards when no complete local copy exists; a resolver failure falls back to the cached complete record. | Implemented in source. |
| Search and offline fallback | The same repository resolves incomplete search results. `LiveFragment.kt` falls back to filtering cached Live items if the remote search fails. | Implemented in source. |
| Owner/media grouping | `ui/LiveFragment.kt` groups owner ID 0 and missing owners as system-owned, then splits each owner group into Video and Audio rows. | Implemented in source. |
| Section header | `ui/LiveRowsAdapter.kt` displays fixed Live Video/Live Audio headings for system rows and owner name/artwork for other rows. | Implemented in source. |
| Card layout | `ui/LiveAdapter.kt` selects `item_live_video.xml` for video and `item_content.xml` for audio. | Implemented in source; visual sizing should be checked on TV. |
| Playback routing | `ui/LiveFragment.kt` creates type-specific Live queue items, opens the fullscreen player for video and the internal playback screen for audio. `playback/PlaybackManager.kt` uses the Live media type when preparing playback. | Implemented in source; playback and minimize behavior need device testing. |
| Player LIVE controls | `player/PlayerFragment.kt` keeps the seek bar visible; when the current queue item is Live, the duration label displays `LIVE`, stale elapsed/duration values are cleared, and stale seek progress is reset. | Implemented in source; verify on TV. |

## 4.4 Relevant Android source paths

| File | Responsibility |
|---|---|
| `android-tv/app/src/main/java/ir/hamrahang/tv/data/live/LiveDto.kt` | Deserializes Live API cards, including top-level `media_type`. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/data/live/LiveItem.kt` | Live domain model, audio/video classification and thumbnail priority. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/data/live/LiveRepository.kt` | Latest list, search, resolve, owner synchronization and offline fallback. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/data/live/LiveEntityMapper.kt` | Converts and merges Live DTOs and Room entities without replacing complete cached cards with partial responses. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/data/local/entity/LiveEntity.kt` | Persists Live metadata, owner, sources and media type. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/data/local/AppDatabase.kt` | Room schema version 38 and Live entity registration. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/data/local/DatabaseProvider.kt` | Live schema migrations, including 37→38. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/ui/LiveFragment.kt` | Loads and groups Live cards, handles search and starts type-specific Live sessions. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/ui/LiveRowsAdapter.kt` | Renders owner/system headers and horizontal rows. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/ui/LiveAdapter.kt` | Renders Live cards, selects the card layout and handles D-pad focus/clicks. |
| `android-tv/app/src/main/res/layout/item_live_video.xml` | Landscape Live Video card layout. |
| `android-tv/app/src/main/res/layout/item_content.xml` | Square Live Audio card layout. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/playback/PlaybackManager.kt` | Shared playback manager; selects the media type for Live playback. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/player/PlayerManager.kt` | Shared ExoPlayer ownership and player controls. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/ui/PlayerFragment.kt` | Main player UI. Do not change its LIVE indicator/time/seek behavior until the user provides the remaining requirements. |

## 4.5 State preservation and regression risks

- Preserve the selected Live card and the correct queue index when starting playback.
- Keep Audio and Video queues separate, including for owners that publish both types.
- Preserve the existing D-pad focus path between search controls, rows and cards.
- Do not clear or replace cached Live data merely because a resolve or search request failed.
- Keep the Room 37→38 migration non-destructive; existing Live rows must receive the backward-compatible audio default.
- Do not let a card without a thumbnail display stale artwork recycled from another card/owner.
- Do not assume source protocol metadata automatically enables HLS, DASH or another playback implementation.
- Do not interrupt an already playing item while loading or refreshing Live metadata.

## 4.6 Acceptance criteria

1. A Live item with `media_type = "video"` appears in a Video row and a Live item with `media_type = "audio"` (or no media type) appears in an Audio row.
2. Ownerless and owner-ID-0 cards appear under the fixed Live Video/Live Audio headings, never under Unknown Owner.
3. An owner with both types has separate Video and Audio rows, with the owner's identity retained.
4. Audio cards are square; video cards are landscape 16:9.
5. Video thumbnails prefer `tmv` then `tmb`; audio thumbnails use `tmb`; missing artwork shows the Live icon.
6. Selecting audio opens the internal audio playback flow; selecting video opens the main fullscreen player.
7. The selected Live item is the current queue item, and queue contents do not mix audio and video.
8. An incomplete latest/search card is resolved through the existing API contract where needed; a complete local card remains usable if resolution fails.
9. Existing Room data survives migration to version 38.
10. D-pad focus, row navigation, back navigation and the existing shared player/minimize behavior work on the target TV.
11. The app builds with the established project toolchain and is tested on an Android TV device/emulator.
12. During Live playback, the seek bar remains visible, the duration label reads `LIVE`, the current ExoPlayer timeline/seek window is preserved, and seek requests are not blocked merely because the item is Live.

### 4.8 Live duration label and seek-bar presentation

- Keep the seek bar visible during Live playback; do not hide the seek-bar container.
- Preserve the active ExoPlayer timeline and its actual seek window. When the current Live timeline exposes a seekable window, retain its duration/progress range and allow seek requests through the existing player controls.
- Replace only the finite-media duration label with the literal text `LIVE`; keep the current elapsed position visible where ExoPlayer reports one.
- Do not force every Live item to `max = 1`, reset its seek progress, or block seek requests solely because the item is Live. ExoPlayer/the active timeline remains authoritative for whether a seek can take effect.
- For finite media, preserve the existing elapsed/duration formatting and seek-bar progress behavior.
- Do not invent a finite duration when the Live timeline reports no duration.

### 4.7 Live bookmarks and More menu

- A Live station is a distinct `LIVE` content type for account actions. Do not represent Live audio as `SONG` or Live video as `VIDEO` when bookmarking.
- Preserve the public Live API ID separately from the namespaced playback queue ID (for example, `live_...`). Queue identity and account identity serve different purposes and must not be parsed interchangeably.
- Bookmark operations use the public Live ID and the `live` API type. Local favorites use the dedicated Live content-type value; account pull/push synchronization must retain Live entries.
- Do not show Add to Playlist, Add to Queue, or catalog-detail navigation for Live until those operations have an explicit Live contract. The Bookmark action remains available.
- Do not change the existing song, podcast, video, music-video, artist, album or playlist bookmark identifiers or behavior.

---

# 5. Future Policy Sections

Add new policies here as they are discussed and confirmed. Keep each topic separate and include:

1. Scope and non-negotiable rules.
2. User scenarios and expected outcomes.
3. Current implementation audit (what exists today).
4. Gaps between expected behavior and current implementation.
5. Exact repository-relative code paths and each file's responsibility.
6. Dependencies, state that must be preserved, and regression risks.
7. Acceptance criteria that can be checked after implementation.

Planned topics are placeholders only; they do not authorize new behavior:

- Authentication, account/session lifecycle and offline identity.
- Repository/cache freshness and data consistency.
- Favorites and playlist offline mutation/synchronization.
- Search and detail-page loading.
- Player source failures and playback recovery.
- Focus, navigation and screen-state preservation.
- Background jobs, retries, cancellation and app lifecycle.
- Error handling, user messages and observability.
