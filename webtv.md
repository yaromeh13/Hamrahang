# Web TV — Reference & Development Rules

> **Compatibility notice:** This Web TV is used on both **legacy and modern TV browsers**. All changes must preserve operation on older Smart TV browsers with limited JavaScript and CSS support.

This document is the living reference for the Web TV implementation, its behavior, compatibility rules, visual structure, JavaScript helpers, and future development decisions. Update it whenever an established behavior or shared helper changes.

---

## 1. Platform and Compatibility

### 1.1 JavaScript

- The Web TV's primary JavaScript code must use **ES5** syntax.
- Use `var`, traditional functions, callbacks, and the existing XMLHttpRequest-based request patterns.
- Do not introduce `let`, `const`, arrow functions, classes, template literals, `async/await`, or `fetch()` into the legacy Web TV runtime.
- Before implementing a behavior, check whether an existing helper already provides it. Prefer extending or reusing shared helpers instead of duplicating logic.
- New helpers must remain compatible with older TV browser engines and should include a fallback when relying on a browser capability that may not exist.
- Do not assume that a feature available in desktop Chrome is available on older Samsung/LG TV browsers.

### 1.2 CSS layers

The interface has a **base CSS layer** intended to provide the essential layout and behavior across the widest range of TV browsers.

The optional enhancement stylesheet is **`tv.css`**:

- The base styles are the compatibility baseline and must remain sufficient for the core interface.
- Where the device/browser supports the required CSS features, `tv.css` may be loaded to improve presentation.
- Enhancements in `tv.css` must not be required for core navigation, content visibility, playback, or interaction.
- Avoid making the base interface dependent on modern Flexbox, Grid, CSS variables, advanced selectors, or other features that may be missing on older devices.
- When adding a visual enhancement, preserve a usable fallback in the base styles.

### 1.3 General rules

- Keep the Web TV implementation in line with the existing single-page structure in `index.html`.
- Preserve remote-control / D-pad navigation and visible focus states.
- Avoid unnecessary rewrites and broad changes to shared code.
- Do not change API behavior until the existing Web API and its JSON response have been inspected.
- All API requests must use the canonical domain: **`https://hamrahang.ir`**. Do not introduce another API host without explicit approval.

---

## 2. Application Structure and User Experience

The Web TV is a browser-based media application designed to work with both remote controls and conventional pointer/touch input.

### 2.1 Main navigation

The main navigation is built around the application's primary content areas:

- Home
- Trending
- Search
- Events
- Library

The navigation and page transitions must remain usable with directional keys and focus navigation. Preserve the existing navigation helpers and focus behavior when changing page layouts.

### 2.2 Page and content behavior

- The application changes its visible page/content area without requiring a full browser-page navigation for every interaction.
- Page rendering must preserve the existing route and focus-management conventions.
- Content rows are horizontally browsable; users move between cards with the remote control.
- Focus must remain visible and predictable after content is rendered or refreshed.
- Empty, unavailable, or failed-to-load content must not break the rest of the page.

### 2.3 Home and content rows

- Home is composed of content sections/rows.
- Each row contains cards appropriate to its content type.
- Card presentation must follow the media type: for example, video cards use a landscape artwork ratio while music-oriented cards use a square artwork ratio.
- Artwork selection must use the shared thumbnail-resolution helper rather than implementing separate image priority rules inside each row.
- Missing artwork must have a deliberate fallback and must not leave broken-image UI.

### 2.4 Live page

The Live page is built from the locally available Live records and the latest Live ID list.

- Live records are read from `SN.live`; the latest list is held in `SN.latest_live`.
- Ownerless Live channels are displayed in two fixed sections, in this order:
  1. **پخش زنده تصویری**
  2. **پخش زنده صوتی**
- These fixed sections use the Live navigation icon (SVG when available, PNG fallback).
- Channels belonging to an owner retain the owner's name and image/avatar.
- If an owner has both video and audio channels, render separate video and audio rows under that owner.
- Video Live cards use the video-card presentation; audio Live cards use the audio/music-card presentation.
- A video Live card opens the main fullscreen player directly. The user can still minimize the player.
- Audio Live keeps the existing internal-player flow; the user can choose to open the main player.
- For video artwork, prefer `meta.tmv`, then fall back to `meta.tmb`. For audio artwork, prefer `meta.tmb`.
- If a Live record has no usable thumbnail, show the Live icon as its card-art fallback.

### 2.5 Playback

- Playback behavior is shared through the existing player and queue/context helpers.
- Preserve playlist context and queue selection when opening a card.
- Do not introduce a second independent playback flow when an existing player path can be reused.
- Keep audio/video distinctions intact when resolving a Live or on-demand item.
- Player navigation and minimize/fullscreen transitions must not unexpectedly discard the active item or queue.

---

## 3. JavaScript Reference

This section is the primary inventory of shared JavaScript helpers. Add entries here when a helper is introduced or when its responsibility changes.

### 3.1 Shared helpers

| Function / helper | Responsibility |
|---|---|
| `getIcon(icon, name)` | Returns the supplied SVG icon when the SVG asset is available; otherwise returns an image element using `/assets/<name>.png`. |
| `resolveThumb(e, ART, typeHint)` | Resolves the preferred artwork key for an item. Video-like items prefer `meta.tmv` and then `meta.tmb`; other items prefer `meta.tmb`. It can also fall back to an artist thumbnail. |
| `resolveThumbUrl(e, ART, typeHint)` | Converts the artwork key returned by `resolveThumb()` into its image URL using the existing thumbnail base URL. |
| `resolveHeroBg(e)` | Resolves the artwork key used for a video/music-video hero background (`meta.art` first, then `meta.tmv`). |
| `resolveHeroBgUrl(e)` | Converts the hero artwork key into its image URL. |
| `liveMediaType(item)` | Determines whether a Live record belongs to the video or audio presentation. |
| `splitLiveMediaIds(ids)` | Splits Live IDs into separate video and audio lists using the cached Live records. |
| `appendLiveTypeHeader(title)` | Creates one of the fixed ownerless Live section headings with the Live SVG/PNG icon. |
| `CRLVCards(ids, mediaType)` | Renders Live cards for one media type, using the appropriate card ratio and artwork fallback. |
| `CRLV(callback)` | Builds the Live page, groups records by owner, and renders the ownerless and owner-specific audio/video rows. |
| `SetPlayListContext(ids, type, item)` | Sets the playlist context for the selected content collection and item. |
| `setPlayQueue(rows, id, type)` | Sets/opens the playback queue for the selected item and content type. |
| `OpenMainPlayer()` | Opens the main player interface. |
| `getByClass(className, root)` | Legacy-compatible helper for retrieving the first element with a class name. |
| `getAllByClass(className, root)` | Legacy-compatible helper for retrieving all elements with a class name. |

### 3.2 Helper development rules

- Search this inventory and the existing `index.html` before creating a helper.
- A helper should have one clear responsibility and should be reusable by more than one page/component where appropriate.
- Keep helper parameters and return values consistent with existing callers.
- Document non-obvious fallback order and media-type rules.
- Do not silently change a shared helper's behavior: inspect all callers first and update this document when the behavior changes.
- Avoid browser APIs that are not consistently supported by older TV browsers; use the project's compatibility helpers where available.

### 3.3 Adding a helper

Use this format:

| `functionName(arguments)` | One-sentence description of its responsibility, important fallbacks, and return value. |

For more complex helpers, add a short subsection describing:
- Parameters
- Return value
- Browser compatibility notes
- Important callers / dependencies

---

## 4. Data, Cache, and API

### 4.1 Local data

- `SN` is the Web TV's local data/store object used by the application.
- Live records are available through `SN.live`.
- The latest Live record IDs are available through `SN.latest_live`.
- Rendering should use locally available data first where the existing flow supports it.
- A missing or incomplete record may be resolved through the existing item-resolution flow; a failed resolution must not crash unrelated content rendering.

### 4.2 API rules

- Canonical API origin: `https://hamrahang.ir`.
- Inspect the corresponding API implementation and current Web TV request/response shape before changing an endpoint or adding a new request.
- Keep the API response compatible with the existing local data model and rendering helpers.
- Do not assume a field exists in every record; handle optional metadata safely.
- Do not add duplicate requests when the required item or image is already available locally.

### 4.3 Image metadata

Image keys are stored under an item's `meta` object. The rendering layer resolves the appropriate key through shared helpers.

| Metadata field | Intended role |
|---|---|
| `tmb` | General thumbnail / square artwork |
| `tmv` | Video thumbnail / landscape artwork |
| `cvr` | Cover artwork |
| `art` | Hero/background or artwork role where supported |
| `gal` | Gallery image collection where supported |

The existence of a metadata field must not be assumed. Always preserve the documented fallback behavior.

---

## 5. CSS and Visual Design Reference

### 5.1 Base layout

- The base stylesheet defines the minimum layout required for the application.
- Content rows use the existing horizontal scrolling/card structure.
- Keep card dimensions and artwork ratios appropriate to their media type.
- Keep text readable from a typical TV viewing distance.
- Focus and selected states must be clearly visible.

### 5.2 Optional `tv.css` enhancements

- `tv.css` is an enhancement layer, not a replacement for the compatibility baseline.
- Use it for presentation improvements that are safe on browsers supporting the required CSS.
- Keep fallbacks in the base CSS for essential geometry and interaction.
- Do not allow optional styling to hide content or make controls inaccessible on older devices.

### 5.3 Icons and artwork

- Prefer the existing SVG icon when available.
- Use the matching PNG asset as fallback.
- Center icons within their visual containers and explicitly size them where necessary.
- Use `object-fit` only as an enhancement when supported; do not make essential image layout depend on it.
- Missing artwork must render a stable placeholder rather than a broken image.

---

## 6. Remote Control and Focus Navigation

- D-pad / remote navigation is a first-class interaction model.
- Interactive cards and controls must participate in the existing focus-navigation system.
- After rendering a new page or replacing a content row, refresh focusability through the existing navigation flow.
- Focus should move predictably between navigation, rows, cards, and player controls.
- Do not remove keyboard/remote focus outlines without providing an equally visible replacement.
- Avoid click-only interactions for controls that users must also operate with a TV remote.

---

## 7. Change Checklist

Before modifying Web TV code:

1. Identify the exact page, component, helper, or API response involved.
2. Inspect its current implementation in `index.html`, base CSS, and/or `tv.css`.
3. Search for existing shared helpers and all relevant callers.
4. Check compatibility with legacy TV browsers; keep runtime JavaScript ES5.
5. If API data is involved, inspect the existing API implementation and real response shape first.
6. Keep the change small and consistent with the current architecture.
7. Preserve D-pad navigation, focus behavior, and playback continuity.
8. Verify that the base CSS remains usable without optional `tv.css` enhancements.
9. Update this document when a behavior, helper, data contract, or compatibility rule changes.
10. Verify the final files on the target branch before reporting the change as complete.

---

## 8. Open Documentation Areas

The following inventories should be expanded as the implementation is reviewed or changed. Do not document guessed behavior as an established rule.

- Complete JavaScript helper catalog and call relationships.
- Full page/route inventory and each page's entry/exit behavior.
- Detailed request/endpoint inventory and response schemas.
- Complete player state, queue, and playback transition behavior.
- Browser/device-specific CSS enhancement support and fallback matrix.
