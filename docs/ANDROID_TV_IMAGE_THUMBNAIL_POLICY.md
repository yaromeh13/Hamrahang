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

## Contents

1. [Network, Offline Mode & Recovery Policy](#1-network-offline-mode--recovery-policy)
2. [Image & Thumbnail Policy](#2-image--thumbnail-policy)
3. [Code Reference Index](#3-code-reference-index)
4. [Future Policy Sections](#4-future-policy-sections)

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

## 1.8 Current implementation audit — verified against source

This subsection describes the current source snapshot inspected while this document was updated. It is an audit, not a declaration that every desired behavior is already implemented.

| Area | Current source behavior | Difference / follow-up |
|---|---|---|
| Startup Ping | Splash calls `AppRepository.ping()` after the animation; the API route is `app/ping`. | A strict overall 15-second call deadline is not enforced by the current `SplashViewModel`. OkHttp has connect/read/write timeouts of 15 seconds, which are not the same as a total call timeout. |
| Startup Retry / Offline | Splash exposes manual Retry and Offline choices after an error. | The basic choice exists. Verify timeout behavior and that automatic retry is not introduced. |
| NetworkMonitor | Android connectivity callbacks observe networks with `NET_CAPABILITY_INTERNET` and emit available/lost events. | This only indicates device network capability; it does not validate the Hamrahang API. |
| Runtime timeout overlay | MainActivity currently shows `NetworkOverlay` on `NetworkEvents.Event.Timeout`. | This conflicts with the required runtime behavior; timeout events must not open the startup Retry UI while the app is running. |
| Runtime reconnect | MainActivity hides the overlay, shows a message, publishes Home Channel data, refreshes the session, and syncs playlists. | API reachability is not separately validated before treating connectivity as recovered. Recovery is incomplete and Home/playlist-centric. |
| Current screen refresh | Playlist refresh is applied only when the current fragment is HomeFragment. | Other visible screens do not have a general stale/missing-data refresh path. |
| Background data sync | Playlist synchronization exists in the reconnection path. | A coordinated recovery pass for all relevant content types is not present in the inspected MainActivity flow. |
| Image cache | ImageResolver builds URLs and uses asynchronous Glide requests with placeholder/error drawables. | No independent reconnect-driven queue for completing missing image/thumbnail cache entries was found in the inspected ImageResolver. |
| Player | PlayerManager owns a reusable ExoPlayer instance and tracks current media/state. | The reconnection path does not coordinate a same-item/same-position stream recovery. Do not couple general data/image sync to PlayerManager.prepare/stop. |
| Network permission | AndroidManifest currently declares INTERNET. | NetworkMonitor uses ConnectivityManager APIs, but ACCESS_NETWORK_STATE was not present in the inspected manifest. Verify and address before relying on callbacks. |
| Database migration | AppDatabase declares Room version 35. DatabaseProvider registers migrations through 33→34 and uses `fallbackToDestructiveMigration()`. | The inspected provider has no 34→35 migration registration. A destructive fallback can erase local cache on an unsupported upgrade path; resolve this before treating local data preservation as guaranteed. |

### Implementation notes

- `ApiClient` currently enables `retryOnConnectionFailure(true)`. Any future retry policy must be reviewed against the requirement that startup retry is manual and runtime recovery is non-blocking and controlled.
- `NetworkInterceptor` currently emits a timeout event for a SocketTimeoutException. A timeout of one request is not sufficient evidence that device connectivity or the whole API is unavailable.
- `ReloadManager` exposes register/unregister/reload callbacks. Its registration/use sites must be traced before changing it; do not assume that invoking reload is necessary for recovery.
- The web reference's API fallback behavior must not be copied if it targets a different API host. Android API traffic remains restricted to `https://hamrahang.ir`.

---

# 2. Image & Thumbnail Policy

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

## 2.3 Source owner artwork

For the owner/provider attached to a playback source:

1. `owner.meta.ico`
2. `owner.meta.tmb`
3. The source's own icon, only if the source payload provides one.
4. No image if none of the above exists.

The owner `ico` key follows the web convention: if it has no dot, its last three characters encode the extension (for example, `u11210725ipng` becomes `u11210725i.png`). A `tmb` key always receives `.jpg`.

The owner profile artwork and source/provider icon are different concepts. Do not store a source icon as the owner's profile icon. Current Android `MediaSource` does not declare a source-icon field, so that fallback is not assumed until the API payload/model confirms one.

## 2.4 Hero wide background

For the wide Hero background only:

1. `meta.art`
2. `meta.tmv`
3. No background image.

Do not use `tmb`, `cvr`, owner artwork, or generic type icons as Hero background fallbacks.

## 2.5 Resolution and display requirements

- Treat null, empty, and whitespace-only keys as missing.
- Preserve the exact priority order above.
- Resolve thumbnail keys using the existing Android convention documented here: `https://hamrahang.ir/thumb/{key}.jpg`.
- For Hero backgrounds, both `meta.art` and `meta.tmv` use the thumbnail URL builder, matching the web reference's `resolveHeroBgUrl()` behavior.
- A missing/broken image falls back to the appropriate type icon and must not break the card, list, Hero, or playback UI.
- Keep image-key selection in the shared `ImageResolver`; card loaders use the same resolver request helper and a type-specific placeholder/error.
- Hero resolution is separate because its priority differs from thumbnails.
- Playlist artwork resolution is available in `ImageResolver`, but the current Android TV tree has no Playlist domain model/card adapter; the fallback is ready for the point where Playlist UI is introduced.
- Image loading remains asynchronous and independent of playback.

### Important implementation discrepancy to resolve

The current `ImageResolver.kt` inspected for this update defines:

```kotlin
private const val THUMB_BASE = "https://cdn.hamrahang.ir/thumb/"
```

That differs from the `https://hamrahang.ir/thumb/` URL written in the original policy text above. The current resolver is the implementation source of truth for what the app actually requests. This document records the discrepancy rather than silently changing the established thumbnail rule. Confirm the intended image host before changing either the resolver or the policy URL. This image CDN host is not an API host.

## 2.6 Web reference

The reference `index.html` contains these SVG variables:

- Song icon: `svg_sng`
- Artist/user icon: `svg_usr`
- Album disk icon: `svg_dsk`
- Video icon: `svg_vdo`
- Playlist icon: `svg_pls`

The existing web thumbnail helper currently uses `tmv → tmb` for generic video-like content. The Android policy intentionally distinguishes Video (`cvr → tmb`) from MusicVideo (`tmv → tmb`), as specified above.

---

# 3. Code Reference Index

Paths below are repository-relative and were checked against the current `main` branch while this document was updated. Kotlin package declarations do not always mirror directory names exactly; use the exact paths shown here.

## 3.1 Startup and API

| File | Responsibility |
|---|---|
| `android-tv/app/src/main/java/ir/hamrahang/tv/SplashActivity.kt` | Splash animation, observing startup state, startup Retry/Offline UI, transition to MainActivity. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/SplashViewModel.kt` | Startup Ping state machine and manual retry. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/AppRepository.kt` | Startup repository wrapper for Ping. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/network/ApiService.kt` | Retrofit API routes, including `GET app/ping`. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/network/ApiClient.kt` | Retrofit base URL, OkHttp timeouts, retry configuration and interceptors. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/network/PingResponse.kt` | Startup Ping response model. |
| `android-tv/app/src/main/AndroidManifest.xml` | Declared Android permissions and activities; check network permissions here. |

## 3.2 Runtime network and recovery

| File | Responsibility |
|---|---|
| `android-tv/app/src/main/java/ir/hamrahang/tv/App.kt` | Application initialization; initializes DatabaseProvider, NetworkMonitor and other app-wide components. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/network/NetworkMonitor.kt` | Observes Android device network callbacks. Must remain a connectivity observer, not an API-health checker. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/network/NetworkEvents.kt` | Shared network event stream (timeout, available, lost). |
| `android-tv/app/src/main/java/ir/hamrahang/tv/network/NetworkInterceptor.kt` | Converts request socket timeouts into network events. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/network/NetworkOverlay.kt` | Current network overlay view wrapper; its Kotlin package is `ir.hamrahang.tv.ui.network`. Runtime use must be removed/limited according to Section 1. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/network/ReloadManager.kt` | Callback holder for reload actions; inspect registration sites before changing behavior. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/MainActivity.kt` | Collects runtime network events and currently coordinates session/playlist recovery and Home-specific refresh. |

## 3.3 Data, local cache and synchronization

| File / area | Responsibility |
|---|---|
| `android-tv/app/src/main/java/ir/hamrahang/tv/data/local/AppDatabase.kt` | Room database schema and current database version (35 in the inspected source). |
| `android-tv/app/src/main/java/ir/hamrahang/tv/data/local/DatabaseProvider.kt` | Room construction and registered schema migrations. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/data/local/DatabaseUpdater.kt` | Maps/merges received DTO data into local Room entities. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/data/auth/AuthSessionManager.kt` | Account/session restoration used by MainActivity during startup/recovery. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/data/playlist/PlaylistRepository.kt` | Playlist refresh and synchronization used by the current reconnect flow. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/data/` | Content DTOs, repositories, Room entities and DAOs; inspect the specific repository before changing a content type's offline behavior. |

## 3.4 Images and playback

| File | Responsibility |
|---|---|
| `android-tv/app/src/main/java/ir/hamrahang/tv/util/ImageResolver.kt` | Artwork-key priority, thumbnail URL construction, Hero/owner resolution, shared Glide request helper. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/player/PlayerManager.kt` | ExoPlayer ownership, current media/source, playback position and player controls. |
| `android-tv/app/src/main/java/ir/hamrahang/tv/player/PlayerState.kt` | Shared player UI/playback state (verify exact path before editing if the source is moved). |
| `android-tv/app/src/main/java/ir/hamrahang/tv/channel/HomeChannelPublisher.kt` | Publishes Android TV Home Channel content; currently called during app initialization and reconnect. |

## 3.5 Web reference

| File | Responsibility |
|---|---|
| `index.html` | Primary web reference for product scenarios, API behavior, and image-selection conventions. Compare behavior with Android, but do not copy web-specific implementation or alternate API hosts into Android without approval. |

---

# 4. Future Policy Sections

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
