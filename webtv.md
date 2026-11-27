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

### 6.1 Navigation engine

The Web TV uses **`spatial_navigation.js`** as its spatial / directional focus-navigation engine.

- The file is written in **ES5-compatible JavaScript**. The current source was checked for modern syntax such as `let`, `const`, arrow functions, classes, template literals, and `async/await`; none was found.
- The implementation is based on Luke Chang's JavaScript Spatial Navigation project and includes project-side compatibility work for older TV browsers.
- Its compatibility work is intended to make selector matching, focus tracking, and collection handling more resilient on older Samsung/LG TV browser engines.
- Keep this file ES5-compatible. Do not introduce newer JavaScript syntax or APIs without a verified fallback for the oldest supported TV browsers.
- This is a source-level compatibility review, not a claim that every TV model or browser has been runtime-tested.

### 6.2 Compatibility helpers inside `spatial_navigation.js`

The file contains the following important helpers and fallback layers:

| Helper | Responsibility / compatibility purpose |
|---|---|
| `nodeContains(root, el)` | Checks whether an element belongs to a root element. Uses native `contains()` when available and falls back to walking the `parentNode` chain. |
| `trimSel(str)` | Trims whitespace from selector strings without requiring newer string APIs. |
| `matchOneSelector(el, sel)` | Matches common selectors manually, including `#id tag`, `#id .class`, `.class`, `tag`, and `tag.class`; uses document-level `querySelectorAll` only as a later fallback. |
| `elementMatchesSelector(selector)` | Tries available native `matches` variants, then document `querySelectorAll`, then manual selector matching. This specifically avoids trusting a false result from some broken TV implementations. |
| `arrayFilter(arr, fn)` | Array-filter replacement implemented with a basic loop. |
| `arrayFind(arr, fn)` | Array-find replacement implemented with a basic loop. |
| `arrayForEach(arr, fn)` | Array-forEach replacement implemented with a basic loop. |
| `arrayMap(arr, fn)` | Array-map replacement implemented with a basic loop. |
| `objectKeys(obj)` | Uses `Object.keys` when available and otherwise enumerates own properties with `hasOwnProperty`. |
| `queryAll(selector, root)` | Collects matching elements; prefers `querySelectorAll` and has manual fallbacks for common simple selectors. |
| `getCurrentFocusedElement()` | Uses `document.activeElement`; if an older TV resets it to `body`, recovers the last focused element remembered by the navigation sections. |

The file also provides an `Array.isArray` polyfill when that method is missing.

**Important limitation:** the source still uses DOM event methods such as `window.addEventListener` and `window.removeEventListener`. It does not currently provide an `attachEvent` fallback. Do not describe the file as supporting every legacy browser regardless of its DOM API; test the actual target browser or add a carefully scoped fallback if a supported device lacks these methods.

### 6.3 Loading and initialization order

The application loads `spatial_navigation.js` before the inline application code that configures and uses `SpatialNavigation`.

The current startup sequence in `index.html` is:

1. Load `spatial_navigation.js`, which exposes the global `window.SpatialNavigation` object.
2. In the navigation setup code, assign the global object to the local `sn` variable.
3. Call `sn.init()` once. This registers the keyboard and focus event listeners used by the navigation engine.
4. Register the application's navigation sections with `sn.add()`.
5. Call `sn.makeFocusable()` to prepare the current matching elements.
6. Call `sn.focus('main')` to place the initial focus in the main content section.
7. When dynamic content is rendered later, call `makeFocusable()` again for the new elements and explicitly restore or move focus when that screen's flow requires it.

The engine's `init()` is guarded by an internal ready flag, so repeated calls do not register the same listeners again while it remains initialized. Section registration and focus preparation are separate operations: adding a section does not itself make all its matching elements focusable.

### 6.4 Navigation sections configured by the Web TV

| Section ID | Selector / configuration | Purpose |
|---|---|---|
| `menu` | `#menu a, #menu .focusable` | Sidebar menu. Entering restores the last focused item; moving right can enter `main`. |
| `nav` | `disabled: true` | Registered but disabled section. |
| `main` | `#main-content a, #main-content .focusable` | Main page content and cards. Entering restores the last focused item; moving left can return to `menu`. |
| `player-box` | `#player-box .btn, #player-box .focusable` | Player controls. Uses `restrict: 'self-only'` and defaults to `#play`. |
| `modal` | `#modal .btn, #modal .focusable` | Legacy settings modal section. Uses `restrict: 'self-only'` and defaults to `#default-view`. |
| `hpop` | `#hpop .focusable` | In-app popup section. Uses `restrict: 'self-only'` and a default focus element inside the popup body. |

### 6.5 Public SpatialNavigation methods used by the app

| Method | How the Web TV uses it |
|---|---|
| `init()` | Installs the engine's keydown, keyup, focus, and blur listeners during navigation setup. |
| `add(config)` | Registers the named navigation sections and their selectors / movement rules. |
| `makeFocusable(sectionId)` | Adds `tabindex="-1"` to matching elements that are not already natively focusable; can target one section or all registered sections. |
| `focus(target)` | Moves focus to a section, selector, or element. Used for initial focus and after page / panel transitions. |
| `enable(sectionId)` / `disable(sectionId)` | Activates or temporarily disables a section, such as when moving between player controls and a modal. |
| `pause()` / `resume()` | Temporarily pauses or resumes directional navigation processing. |
| `move(direction)` | Programmatic directional movement supported by the engine. |
| `remove(sectionId)` / `clear()` | Removes one or all registered sections when explicitly needed. |

### 6.6 Application-side focus helpers and remote handling

These helpers and handlers live in `index.html`; they are not part of the navigation library itself:

| Helper / handler | Responsibility |
|---|---|
| `CFOC(target, section)` | Refreshes focusability for the requested section, finds a target element, and focuses it. Includes fallback lookup for selected selectors when `querySelector` is unavailable. |
| `dpadKey(keyCode)` | Creates and dispatches a keyboard `keydown` event for the virtual D-Pad direction buttons. |
| Virtual D-Pad `onclick` handler | Converts Up / Down / Left / Right controls to key codes 38 / 40 / 37 / 39. Its OK action dispatches the SpatialNavigation enter events and then a click to the focused element. |
| `sn:enter-down` / `sn:enter-up` | Events emitted by the virtual OK control to match the navigation engine's Enter interaction events. |
| `sn:focused` | Navigation event used by application code for focus-related behavior and diagnostics where registered. |

The navigation engine itself handles physical remote / keyboard arrow keys through its global `keydown` listener. Enter handling includes the regular Enter key and the Samsung / TV key codes explicitly handled in the source.

### 6.7 When focus is refreshed after rendering

The application must refresh focusability after creating or replacing dynamic content. Current examples in `index.html` include:

- Initial application setup: `sn.makeFocusable()`, followed by `sn.focus('main')`.
- Live page: after Live rows and cards have been rendered, call `sn.makeFocusable()` and focus the first available card.
- Search results: after rendering the result list, call `sn.makeFocusable()` and focus the first result link.
- Tabs: after tab setup, call `sn.makeFocusable()` and focus the active tab.
- Library and followed-content views: refresh the main section after rendering, then focus the first available item (some flows defer this briefly with `setTimeout` so the triggering click can finish).
- Player queue: call `makeFocusable()` after queue controls are inserted.
- Popup / modal / player transitions: enable or disable the relevant section, refresh focusability where needed, and move focus into the active section or restore the previous focus when closing.

When adding a new dynamic screen or component, follow the same pattern: render its DOM first, refresh focusability for the affected section, then move focus only when the interaction flow requires it. Avoid focusing an element before it has been inserted into the document.

### 6.8 Optional jQuery integration

The navigation file receives `window.jQuery` as its optional wrapper argument. If jQuery is present, selector parsing and matching can use jQuery methods, and the file exposes a jQuery interface. The Web TV's current setup and section registration use the plain JavaScript `SpatialNavigation` global API; jQuery is not required for the documented application calls.

### 6.9 Maintenance rules

- Keep `spatial_navigation.js` ES5-compatible.
- Preserve the manual selector and collection fallbacks when changing the engine; they exist for older / inconsistent TV browser implementations.
- Do not replace the compatibility helpers with modern-only methods such as `Element.matches`, `Array.prototype.find`, or `Object.keys` without retaining the current fallbacks.
- Do not call `makeFocusable()` before dynamic elements have been inserted into the DOM.
- Avoid unnecessary global focus resets. Preserve the last focused element when returning from a modal, popup, or player panel.
- If changing a section selector or its ID, inspect every `focus()`, `enable()`, `disable()`, and `makeFocusable()` caller in `index.html`.
- After modifying this section or the library, verify the source remains ES5-compatible and test directional movement, Enter / OK, Back, section transitions, and focus restoration on representative older and modern TV browsers.

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
