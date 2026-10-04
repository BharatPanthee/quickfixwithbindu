# QuickFix With Bindu — full site analysis

**Audit date:** October 3, 2026 (America/New_York).  
**Primary environment:** `http://quickfixwithbindu-local.local`  
**Production references found:** `https://www.quickfixwithbindu.com`  
**Workspace:** `C:\Users\MaxEAM\Local Sites\quickfixwithbindu-local\app\public`

**Contents:** [Findings](#main-findings) · [Pages](#published-pages-and-routes) · [Custom modules](#custom-functionality-modules) · [Data integrity](#recipe-data-integrity-and-completeness) · [Verification](#functionality-verification-matrix) · [Remediation](#remediation-sequence-and-acceptance-criteria) · [Complete inventories](#complete-inventories-and-final-crawl-totals)

## Scope and evidence

This report reviews the local WordPress installation: its published content, rendered public pages, discoverable navigation links, custom recipe database, active and inactive plugins, templates, custom code snippets, search, public recipe AJAX endpoint, PWA files, and supporting assets. The appendices contain the complete inventories collected during this audit.

Evidence comes from read-only database queries, local source inspection, HTTP requests, and HTML parsing. The database was queried directly without running WordPress admin tools or AI processing jobs. No recipes, configuration, subscribers, or plugin settings were intentionally changed. Ordinary page requests can trigger WordPress's normal background behavior.

Status meanings:

- **HTTP verified:** a request was made and its response examined. HTTP 200 alone does not prove a complete or usable page.
- **Data verified:** checked against the database or filesystem.
- **Source reviewed:** behavior and risks identified in implementation; no claim of an executed end-to-end test.
- **Unverified:** requires a browser, authenticated admin session, service account, or a consequential submission.

Browser automation reported no available browsers. Therefore, no desktop/mobile screenshot review, JavaScript execution, keyboard walkthrough, screen-reader test, or real PWA installation was completed. Newsletter signup, email delivery, push notification delivery, advertising impressions, analytics event delivery, media uploads, admin saves/deletes, YouTube synchronization, and paid AI calls were not executed. These limitations are part of the results, not passes. This is an audit of the local copy; production links are separate checks and do not constitute a complete production audit. Plugin versions below are installed versions, with no assertion about current releases or known vulnerabilities.

## Main findings

The core recipe browsing system is present, but the installation has mismatched content records, incomplete recipes, and several custom-code weaknesses. The most useful work is to restore data consistency, repair the newsletter page, protect administrative operations, and make recipe rendering and discovery reliable.

| Priority | Finding | Evidence and impact | Suggested correction |
|---|---|---|---|
| High | Published recipe pages without matching recipe records | 462 published `recipes` posts versus 453 custom recipe rows; 10 published posts have no matching `wp_recipes.wp_post_id`. The full-recipe shortcode returns empty content for missing records, producing HTTP-200 pages with no recipe. | Reconcile each orphan individually, populate valid records or retire/redirect duplicates; exclude incomplete records from discovery until repaired. |
| High | Newsletter page depends on an inactive plugin | Page 1324 contains only `<!-- wp:hostinger-reach/subscription /-->`; Hostinger Reach is inactive locally. | Choose the intended newsletter provider, restore its block or replace it with a configured active-provider form, and verify signup and delivery in an authorized test. |
| High | Several custom admin operations lack nonces | Snippets 75, 126, and 605 accept synchronization, category/video changes, deletion, or categorization without a nonce. Their menus require `manage_options`, but that does not prevent cross-site request forgery against a logged-in administrator. | Require a verified nonce and an explicit capability check in every mutation handler; perform deletion with POST and validate record IDs. |
| High | API credentials are embedded in custom snippets | Active YouTube/Gemini integration snippets contain literal API keys. Values are deliberately omitted from this report. | Move keys into server configuration outside editable content, restrict their scopes, and assess rotation of the existing keys. |
| High | AI processing can leave inconsistent published content | Snippet 192 publishes a WordPress post before confirming all custom-table writes, has no transaction or uniqueness guard, and marks videos processed even when recipe insertion fails. | Validate the complete AI payload first; create drafts; check every write; use transaction/idempotency controls and publish only after a complete successful save. |
| Medium | Recipes lack ingredients or instructions | Across 453 custom rows, 24 have no ingredients and 32 have no steps. Some may be kitchen tips rather than recipes; those need a distinct content type/schema. | Classify non-recipes and complete genuine recipes before displaying them as usable recipes. |
| High locally | PHP warnings contaminate recipe image markup and the PWA manifest | Snippet 538 reads `$recipe->ID` and `$recipe->post_title` although rows contain `id`, `wp_post_id`, and `title`. The image can still resolve via WordPress's current-post fallback, but rendered examples contain PHP warning markup inside its `alt` attribute. The linked Nginx manifest starts with theme warnings before its JSON. | Correct the field/path references, preserve the intended image fallback, and verify clean HTML and valid manifest JSON. Do not rely only on suppressing errors. |
| Medium | Visibility differs between recipe modules | The homepage, category grids, and related cards require `visible=1`. Both complete indices omit that filter and include hidden short videos. 336 videos are `short/visible=0`; 116 are `video/visible=1`. | Define whether visibility hides only the home feed or all public discovery, then implement that policy consistently. |
| Medium | “Instagram Recipes” is a second local recipe index | Snippet 1157 joins the same posts/recipes/videos as All Recipes, without an Instagram source condition. | Rename the page to describe its behavior or implement explicit Instagram provenance/filtering. |
| Medium | Archive template has the wrong heading | `/recipes/` uses the Index template, whose heading is “Search Results.” | Create an appropriate recipe archive template with a clear heading, navigation, and pagination. |
| Medium | Cook Mode scaling and timers have parsing limitations | Snippet 89 independently multiplies every number in quantity strings and extracts integer minute mentions from instruction text. Mixed fractions, decimals, ranges, and units can be misinterpreted. | Store numeric quantity/range/unit data explicitly; use `timer_minutes` where available and provide a deliberate timer control. |
| Medium | Modal accessibility and failure handling are incomplete | Custom modal code has no focus trap/restoration or Escape handler; timer spans are click-only; fetch failures leave content dimmed and only log to the console. | Implement dialog semantics and keyboard handling, plus visible loading/error/retry states. Browser verification of opening, closing, and cleanup is required. |
| Medium | PWA files retain production-origin values | Manifest icons and the service worker's start/offline/precache URLs reference production. On this local HTTP hostname, secure-context browser APIs also require verification. | Generate environment-specific PWA files and validate same-origin offline caching on HTTPS. |
| Medium | Broad schema suppression | Snippet 538 globally disables AIOSEO schema and WPRM metadata, then removes Recipe JSON-LD blocks without images using string matching. | Assign one schema owner for recipes, scope filters to recipes, and validate other page types retain their intended structured data. |
| Medium | Indexes render the full collection at once | All Recipes renders 452 cards; the Instagram index also loads its entire collection. Custom cards perform per-card recipe/thumbnail lookups. | Add pagination or progressive loading, batch related data queries, and lazy-load below-fold images. |
| Medium | Frontend loads WordPress media/admin-related assets | Snippet 1334 runs `wp_enqueue_media()` on every frontend request, including visitors denied access to its form. Home HTML includes media views, uploader, sortable, and Folders assets. | Enqueue the media library only on the authorized upload page; review Folders frontend enqueues separately. |
| Low | Content and navigation inconsistencies | Home lacks an H1; All Recipes has two H1s. Stored My Portfolio navigation points to Instagram, although its post-data binding corrects the rendered link. | Use intentional page headings and correct the stored navigation URL so behavior remains correct if bindings change. |
| Low | Windows-local theme warnings | `hostinger-ai-theme/functions.php:22–23` splits `__DIR__` with `/`, generating undefined-array-key warnings on Windows. | Use normalized platform-independent paths and confirm logs remain clean. |
| Operational | Temporary local server failures during the crawl | The local server returned a group of 502 responses; Nginx logged “no live upstreams.” A subsequent homepage check returned 200 and affected routes were rechecked. The underlying restart/failure cause was not established. | Review local PHP/Nginx lifecycle and resource behavior separately; do not interpret the transient 502 group as missing recipe permalinks. |

## Platform and architecture

| Item | Observed value |
|---|---|
| WordPress | `7.1.2` in `wp-includes/version.php` |
| Active theme | Hostinger AI theme `1.2.4` |
| PHP service installed locally | PHP `8.2.29`; HTTP runtime version not independently fingerprinted |
| Home/site URLs | `http://quickfixwithbindu-local.local` |
| Front page | Static page, ID 10 |
| Permalink structure | `/%postname%/`, custom recipes rewrite under `/recipes/` |
| Search visibility option | `blog_public=1` locally |
| Public registration | Disabled (`users_can_register=0`) |
| Default comment setting | Open; no comment block in the reviewed recipe template |
| Active plugins | 21 |
| Installed plugins | 30 |
| WPCode snippets | 22 published, 8 draft, 4 trashed |
| Other custom stylesheet | One published `custom-css-js` record |
| WordPress attachments | 472 |

The active theme supplies the shell and block templates. Elementor supplies the homepage layout. Most application behavior is implemented in database-backed WPCode snippets rather than a dedicated recipe plugin. The custom `recipes` post type is registered by snippet 192; turning off WPCode or that snippet can affect recipe routes and REST availability as well as rendering.

The content flow is:

1. YouTube synchronization or the restricted manual entry form writes to `wp_cooking_videos`.
2. The AI processor extracts recipe/SEO data from video title and description, creates a `recipes` post, and writes the custom recipe, ingredient, step, and SEO records.
3. Category management or AI categorization assigns videos through `wp_video_category_map`.
4. Home/category/related cards join videos to recipes and link to WordPress recipe pages.
5. The full-page shortcode renders the detailed recipe; Quick View fetches ingredient/step data through `get_full_recipe`.
6. Featured-image synchronization copies video thumbnails into the WordPress media library.

## Published pages and routes

| Page/route | Purpose and actual implementation | Assessment |
|---|---|---|
| `/` | Elementor landing page, four category tiles, `[recipe_gallery]`, latest 30 visible videos, header search, shared navigation/footer | HTTP verified; mixed local/production category navigation; no H1 in parsed HTML. |
| `/about-me/` | Introductory text and embedded Canva portfolio with a direct Canva link | HTTP verified; embed display needs browser verification. |
| `/all-recipes/` | `[all_recipes_index]`; complete joined recipe collection ordered by video type/date/title | HTTP verified; 452 recipe cards, no visibility filter or pagination; two H1s. |
| `/instagram-recipes/` | `[all_recipes_instagram]`; joined local collection ordered by video date/title | HTTP verified; no Instagram-specific filter or feed. |
| `/ugc-creator-portfolio/` | UGC portfolio content/Canva integration | Included in complete page inventory; external embed needs browser verification. |
| `/subscribe-to-our-newsletter/` | Hostinger Reach subscription block | Inactive block provider locally; intended signup workflow needs restoration and a delivery test. |
| `/upload-recipe/` | `[cooking_video_form]`, administrator-only manual video-record entry | Public visitors should see Access Denied. Capability and nonce checks exist in source; authorized upload/save unverified. |
| `/contact-us/` | Static email, phone, office information | HTTP verified; no contact form in page content; email and phone are plain text rather than actionable links. |
| `/privacy-policy/` | Generic privacy text | HTTP verified; wording references orders/transactions but does not explicitly identify the observed analytics, advertising, newsletter, push, or Canva integrations. Content alignment review recommended; no legal compliance determination. |
| `/recipes/` | Registered recipe archive using the generic Index block template | HTTP verified; heading incorrectly says Search Results. |
| `/recipes/{slug}/` | 462 published recipe post URLs, detailed view via `[render_recipe]` | Complete per-post results appear in Appendix A; data completeness is tracked separately from HTTP status. |
| `/category/quick-starts/` | Category template uses `[recipe_grid]` and custom category mapping | HTTP verified; 69 visible mapped videos. |
| `/category/main-meals/` | Same custom category module | HTTP verified; 52 visible mapped videos. |
| `/category/healthy-bites/` | Same custom category module | HTTP verified; 90 visible mapped videos. |
| `/category/home-remedies/` | Same custom category module | HTTP verified; 5 visible mapped videos; distinguish remedies/tips from structured recipes. |
| `/?s=lentil` | Inherited WordPress search query, 10 results per page | HTTP verified; recipe links present. Pagination URLs are included when discovered. |
| `/?s=qwb-audit-no-results-xyz` | Negative search test | HTTP verified; “No results found.” |
| `/sitemap.xml`, `/sitemap.rss`, `/wp-sitemap.xml` | SEO/indexing endpoints | HTTP 200 on endpoint rechecks. Three child sitemaps contain 9 ordinary-page URLs, 462 recipe URLs, and 1 archive URL; all 10 orphan recipe URLs are included. |
| `/robots.txt`, `/llms.txt` | Static crawler files | Both refer to production URLs; `llms.txt` includes the administrator-only Upload Recipe page. |
| `/superpwa-manifest-nginx.json` | Manifest actually linked from rendered pages on the local Nginx service | HTTP 200 with JSON content type, but two PHP warnings precede the JSON, making the response invalid JSON. Its generated icon URLs are local. |
| `/superpwa-manifest.json`, `/superpwa-sw.js` | PWA files in the site root | File/source reviewed; exact HTTP responses recorded in the inventory. |

The shared menu renders Home, About Me, All Recipes, Instagram Recipes, and My Portfolio. The footer repeats the menu, links to Privacy Policy and Contact Us, includes Facebook/YouTube/Instagram/TikTok links, and displays an Amazon affiliate disclosure. Newsletter and Upload Recipe are published pages but are not in the reviewed shared menu. The homepage's category tiles link to production, while its recipe links stay local.

## Custom functionality modules

All snippets below are published with auto-insertion enabled and conditional logic disabled in the stored metadata. “Source reviewed” does not mean the admin operation or browser interaction was executed.

| Snippet ID | Module | Functionality and assessment |
|---|---|---|
| 75 | Fetch_YT_Videos | Admin YouTube channel search/details sync, paginated channel import, duplicate YouTube-ID check, duration parsing, `<50s` classified as hidden short. No scheduled hook found in this snippet. Literal API key; no form nonce; incomplete HTTP/payload/error checking and overly broad success message. Existing videos are skipped instead of updated. Source reviewed; API not called. |
| 76 | recipe_gallery_for_latest_videos | `[recipe_gallery]`; latest 30 visible videos, shared card renderer and modal. Rendered on Home. No pagination/load-more in this shortcode. |
| 89 | JS_Video_Display | Delegated Quick View fetch, ingredient checkboxes, 1x/2x/3x scaling, minute timers, full-screen cook mode and Wake Lock. Endpoint contract tested separately; browser interaction unverified. Error UI, keyboard support, and numeric parsing need improvement. |
| 126 | Manage_Category | `manage-recipes` and `manage-categories` admin pages; create/delete custom categories, replace video/category mappings. Menu capability is `manage_options`; mutation nonces absent; deletion uses GET. The editable video title is not written by the save handler. Source reviewed. |
| 158 | recipe_grid_category_videos_list | `[recipe_grid]`; detects WordPress category slug and joins the custom category tables, filtering `visible=1`. Fallback is latest 30 videos. No pagination. Four main category routes checked. |
| 189 | Modal_Window_Cook_Mode | Shared modal HTML with scaler, close button, cook-mode toggle, timer overlay, ingredient list and instruction list. Missing explicit dialog role/aria-modal, focus management, and Escape behavior. Source reviewed. |
| 192 | AI Recipes Processor | Registers public REST-enabled recipe post type; batch of 10 unprocessed videos; two Gemini calls for recipe and SEO; creates published posts and related records, handles some remote errors, invokes image synchronization. Admin batch form has a nonce. Weak payload validation, premature publication, unchecked writes, and no idempotent transaction. Extracted cuisine/course/difficulty/featured image are not included in its custom-table insert. Source reviewed; AI calls not run. |
| 193 | QuickFix Recipe AJAX Handler | Public/authenticated `get_full_recipe` POST handler; integer ID, prepared reads, JSON recipe title/ingredients/steps/meta. Valid, zero, and absent IDs tested. Tables hardcoded with `wp_`; does not check associated post publication or video visibility. Public-read scope should be intentional. |
| 195 | Render Recipe Cards | Video-to-recipe lookup, thumbnail, title/excerpt, Quick View and Full Recipe buttons, Coming Soon fallback. Dereferences recipe data before checking existence; custom recipe title replaces an escaped title without re-escaping for HTML/attribute contexts. Escape title/excerpt/output URLs consistently and batch lookups. |
| 526 | Single Recipe SEO Schema | Emits a minimal recipe block from custom data on recipe pages. No image or instructions; snippet 538's output cleaner then removes it. Redundant competing schema generation. |
| 528 | Modal Window Cook Mode CSS | Modal, recipe grid, cooking interface and responsive styling. Source reviewed; actual mobile layout/contrast unverified. |
| 529 | Full Recipe Page CSS | Recipe page/sidebar/nutrition/related-card styling. Source reviewed; mobile rendering unverified. |
| 538 | Full Recipe Page | Detailed renderer, Recipe/Video JSON-LD, description, timing, servings, ingredients, instructions, nutrition, notes, related recipes and shared modal. Empty output when matching recipe absent; wrong image fields; explicit video-tutorial markup is commented out. Global schema suppressors and regex cleaner affect the whole site. |
| 605 | AI Categorizer | Admin batch of 10 unmapped videos; asks Gemini for one or more existing custom category names and saves matching categories. Hardcoded tables/key, no nonce, no reliable structured response or HTTP failure handling. No synchronization to WordPress term relationships. Source reviewed; not run. |
| 627 | search_result_custom_image | Frontend thumbnail HTML filter; joins video data but overwrites its fallback with the WordPress thumbnail. Name suggests search-only, but it runs on frontend thumbnail output generally. Source and search HTML reviewed. |
| 686 | Recipe Bulk Editor | Admin search and four recipes per page; edits WordPress title/content plus custom description/notes/nutrition/ingredient rows. Nonce present. Does not update custom recipe title, instructions, SEO metadata, or timing; card titles can diverge from post titles. “Internal Notes” edited here are displayed publicly as Chef's Notes. Save unverified. |
| 720 | set_recipe_featured_image_from_video | `save_post` hook for recipes; skips autosave/revisions/already-set thumbnails; downloads a linked video thumbnail and sets attachment/marker. Hardcoded tables; errors silently return. Source/data reviewed; no downloads triggered intentionally. |
| 721 | action_fix_images | Adds nonce-protected batch repair button to AI Processor and handler on `admin_init`. Lacks explicit capability check in handler. Counts attempted repairs as updated even if download failed; potentially large synchronous job. Source reviewed; not run. |
| 1133 | Recipe Archive Index | Complete visual joined index. Includes hidden videos, excludes unjoined posts, performs individual thumbnail lookups, lacks pagination, uses a hardcoded local fallback logo URL, and adds another H1 under the page H1. |
| 1157 | All Recipes Instagram | Another complete joined visual index with different ordering. No Instagram provenance filter; same visibility/pagination issues and hardcoded fallback. |
| 1333 | Impact Site Verification | Verification metadata snippet; presence/source reviewed. External affiliate account ownership or verification state not checked. |
| 1334 | Upload_Recipe | Administrator-only frontend video metadata entry; capability check, nonce, required ID/title, duplicate-ID detection, media chooser and database insert. Does not directly create a complete recipe. Accepts free-form duration while sync uses ISO-8601; validate duration/type/date/URL/visibility and restrict media enqueues to this page. Save/upload unverified. |

Draft/trashed snippets are preserved as historical alternatives, not assumed to run. These include earlier video display/processors, template forcing, a category navigator, a recipe-page backup, and a draft comments-disabling snippet.

## Recipe data integrity and completeness

| Dataset | Rows |
|---|---:|
| Published WordPress recipe posts | 462 |
| `wp_cooking_videos` | 452 |
| `wp_recipes` | 453 |
| `wp_recipe_ingredients` | 4,442 |
| `wp_recipe_steps` | 3,066 |
| `wp_recipe_step_images` | 2,120 |
| `wp_recipe_seo` | 59 |
| `wp_video_categories` | 5 |
| `wp_video_category_map` | 802 |

The recipe shortcode uses the custom recipe ID for ingredient/step reads and `wp_post_id` for route linkage. WordPress recipe titles/content and custom titles/descriptions can differ; the bulk editor does not keep all fields synchronized. AIOSEO has its own table in addition to WordPress post meta and the custom SEO table. The AI processor writes SEO post meta and custom SEO rows, but explicit writes to AIOSEO's own storage were not found in its source; plugin hooks may also participate. Audit the effective rendered metadata rather than assuming these stores remain synchronized.

**Verified relationships:** no recipe row references a nonexistent video; no duplicate YouTube IDs; no duplicate video/category mapping pairs; no orphan step-image references. All 452 video records are marked AI-processed. Five video IDs have multiple custom recipes: `258`, `422`, `423`, `517`, `522`. One custom row (`id=191`, `wp_post_id=386`) has no corresponding published recipe post in the inventory; its actual post status/type needs reconciliation, not an assumption that it should be republished.

**Completeness across all 453 custom rows:**

| Field/relationship | Missing, empty, or zero |
|---|---:|
| Ingredient rows | 24 recipes |
| Instruction rows | 32 recipes |
| Difficulty | 85 |
| Cuisine | 166 |
| Course | 97 |
| Servings | 55 |
| Prep time | 60 |
| Cook time | 89 |
| Calories | 335 |
| Custom featured-image URL | 451 |
| Ingredient quantity | 300 ingredient rows |
| Total time differs from prep + cook | 88 recipes |

Zero cook/prep times and quantity-free ingredients can be legitimate. Custom image URLs are also often unnecessary when WordPress thumbnails exist. These are review candidates, not automatically errors. Nine published recipe posts lack WordPress featured-image metadata; these are among the orphan posts. Conversely, a correct thumbnail field does not prove the detailed template displays it.

`wp_recipe_step_images` holds 2,120 images, but neither the reviewed full-page renderer nor the Quick View endpoint consumes that table. Likewise, 429 stored steps have a positive `timer_minutes`, while the browser timer implementation parses instruction text instead of using that field. Existing structured data is therefore not fully used in the visitor interface.

Custom category coverage (multi-category assignments mean totals overlap):

| Category | All mapped videos | Visible mapped videos |
|---|---:|---:|
| Quick Starts | 270 | 69 |
| Main Meals | 161 | 52 |
| Healthy Bites | 348 | 90 |
| Home Remedies | 23 | 5 |
| Uncategorized | 0 | 0 |

The matching WordPress category terms have stored counts of zero. The category shortcode bypasses ordinary WordPress post/term relationships and queries custom mappings, so a zero native count does not prove those visitor category pages are empty. Native taxonomy tooling, sitemap behavior, REST filtering, and future widgets may nevertheless disagree with the custom module.

## Functionality verification matrix

| Workflow | Verified | Still needs execution |
|---|---|---|
| Published page routing | HTTP and content checks; inventory appended | Browser layout and navigation interaction |
| Recipe index → recipe | Link extraction and target HTTP/content checks | Browser click behavior and usability |
| Category browsing | Four local routes, custom data coverage, grid source | Actual clicks on hardcoded production links and mobile display |
| Search | Positive and negative queries; extracted result/pagination links | Browser input, all search terms, keyboard navigation |
| Quick View data | Valid ID returns title, ingredients, steps/meta; invalid and missing IDs return JSON errors, all HTTP 200 | Every modal interaction and edge case |
| Scaling | Source reviewed; stored range examples identified | Mixed fractions, Unicode fractions, ranges, repeated numbers, checkmark retention |
| Timers | Source reviewed; stored timer coverage counted | Accurate countdown, decimals/ranges, stop/restart, background tab behavior |
| Cook Mode/Wake Lock | Source and styles reviewed | Secure-context support, visibility changes, lock release, resizing, device rotation |
| Upload Recipe | Access-control/nonce implementation reviewed; public route checked | Authenticated media selection/upload and record save |
| Recipe editor/category manager | Source/data reviewed | Authenticated search/save/delete with disposable test records |
| YouTube synchronization | Source and existing data reviewed | API quota/errors, pagination, import/update, short/video policy |
| AI extraction/categorization | Source and stored results reviewed | Authorized API calls, payload validation, failure recovery and cost behavior |
| Newsletter | Block/provider mismatch identified; configured alternatives inventoried | Form restoration, consent/validation, duplicate handling, confirmation/unsubscribe/delivery |
| Social/portfolio | URLs and embed dependencies inventoried | Browser embed visibility, account permission/connection, content refresh |
| SEO | Titles/meta/canonical/JSON-LD extracted from pages; sitemap files inspected | External rich-result/Search Console validation and production indexing |
| PWA | Manifest/service-worker references and source reviewed | HTTPS installability, offline navigation, cache updates and fallback behavior |
| Analytics/advertising/push | Plugin inventory and rendered dependency presence | Event delivery, consent interaction, ad display, notification lifecycle |

## SEO, accessibility, performance, and operational notes

### SEO and discovery

The recipe renderer emits a Recipe JSON-LD block with ingredients/instructions and optional nutrition/video metadata. It defaults absent servings to `2` and absent times to `PT0M`; missing real values should not be silently represented as verified facts. `VideoObject.uploadDate` uses the WordPress post date rather than the stored YouTube upload date. Blank cuisine/course may stay blank because null coalescing does not replace an empty string. Home Remedies/kitchen tips should use a schema matching the content rather than recipe metadata with empty ingredients and steps.

The schema cleaner is a fragile string/regex operation: it removes any exact-format Recipe block without an `image` key and globally disables other schema generators. Consolidate this into one intentional generator and scope it to the appropriate content type. Duplicate title groups and orphan pages are listed in the appendices. Do not merge them solely because titles match; review the actual recipe/video identity first.

The static `robots.txt` points at production sitemap URLs. `llms.txt` is a generated-looking static content snapshot with production URLs, including Upload Recipe's Access Denied text. Regenerate these for the intended environment and ensure the restricted upload page has the intended indexing policy. The local `blog_public=1` flag is a migration observation, not evidence that this private hostname is indexed publicly.

### Accessibility and usability

The custom modal needs dialog semantics, an accessible title relationship, focus trapping/restoration, Escape support, keyboard-operable timer controls, and announced loading/error states. Scaling regenerates the ingredient list, so checked ingredients can lose state. The scaling regex also multiplies numbers that describe package size or dimensions; mixed-fraction strings are not parsed as one quantity. Timer parsing can read the trailing integer in a decimal or range rather than the intended duration. Header search has an empty stored label; examine the effective accessible name in a browser. Responsive CSS exists, but no claim is made about actual mobile fit, color contrast, touch-target size, zoom behavior, or accessibility conformance. An accessibility plugin does not replace these checks.

Contact details can become `mailto:`/`tel:` links. Align the Instagram page's label with its actual source. The page rendering pipeline should show a useful incomplete/not-found state rather than an empty page. If “Launch Cook Mode” is intended to start full-screen cooking immediately, note that the current click handler opens/reset the normal modal; the separate Cook Mode toggle then activates the full-screen state.

### Performance and maintainability

The entire collection is rendered twice through the two index pages. All Recipes returned 1,012,955 bytes of HTML and Instagram Recipes returned 1,012,449 bytes; each parsed response contains 454 image elements including shared branding. These sizes exclude downloaded images/scripts/styles. Home/category/related cards perform recipe and thumbnail reads per card. All public pages enqueue the media library because of the upload snippet; Home includes numerous uploader/media/admin-related assets and social-feed assets despite the custom gallery being independent of those feed plugins. Scope enqueues, consolidate overlapping plugins, paginate collections, and reuse batched queries.

HTTP elapsed times in the appendices were measured on this local Windows installation while multiple requests were running. They are not production latency, Lighthouse, Core Web Vitals, or isolated performance benchmarks. No synthetic performance score is supplied.

The full custom application depends on snippet execution order and shared functions across snippets 76/158/189/192/193/195/538. A dedicated version-controlled plugin with schema migrations, capability/nonce checks, validation, explicit dependencies, and meaningful integration checks would make this easier to maintain. Several snippets hardcode the `wp_` prefix, so changing the table prefix would break them. They also use synchronous large admin batches without durable progress/error tracking.

### Security and operational review

The manual entry form contains capability and nonce checks. AI batch processing, bulk recipe editing, and featured-image repair include nonces; featured-image repair also needs an explicit handler capability check. Category management, video-category saves, YouTube sync, and AI categorization lack nonces. These are source-level observations; no exploit or destructive request was attempted.

The public recipe endpoint has prepared SQL and integer IDs, but no publication/visibility gate. Its responses are intended for visitors, so a nonce is not a substitute for deciding which records are public. Use post status and the intended visibility policy to limit output. Client rendering inserts ingredient/note/instruction values with `innerHTML`; ensure stored data is appropriately sanitized and rendered as text where HTML is unnecessary. The card renderer's custom title path is also not consistently escaped. These are potential output-safety paths, not confirmed successful exploits.

The local PHP configuration has errors enabled for development; the theme's Windows path warning appears in logs. Avoid treating development error output settings as a confirmed production issue. No vulnerability scan, malware assessment, backup recovery drill, credential validity test, or account-permission audit was performed.

## Remediation sequence and acceptance criteria

1. **Restore visitor-critical content.** Repair the 10 orphan recipe posts, reconcile duplicate mappings, classify tips separately, complete genuine recipes, and restore the newsletter block/provider. Acceptance: each intended published recipe has ingredients/instructions or a deliberate alternative format; the newsletter offers a visible working form and an authorized delivery test succeeds.
2. **Protect custom operations and secrets.** Add capability/nonce checks, replace mutation-by-GET, remove literal credentials, and validate AI/manual-entry data. Acceptance: missing/invalid authorization fails without writes; repeated imports do not duplicate records; failed batches remain recoverable and do not publish incomplete posts.
3. **Align rendering and discovery.** Fix recipe image field usage/fallbacks, synchronize titles, define visibility, correct archive/menu/headings, implement Instagram provenance or rename the page, and consolidate schema. Acceptance: data, visible cards, detailed pages, search, categories, and sitemap agree with the chosen publication rules.
4. **Validate browser workflows.** Test Quick View open/close/loading/errors, keyboard focus, 1x/2x/3x edge cases, timers, cook mode, mobile layout and secure-context Wake Lock. Acceptance: each control works with keyboard and touch, failure states are actionable, quantities remain correct, and closing cleans up timers/locks.
5. **Reduce overhead and verify integrations.** Scope media/social assets, paginate/batch collection queries, regenerate environment-specific PWA files, and test configured analytics/ad/newsletter/push integrations. Acceptance: unused frontend assets disappear, offline behavior works on HTTPS, and authorized service tests verify delivery without duplicate events.

## Complete inventories and final crawl totals

The following appendices record HTTP results, missing content, asset checks, external checks, and items requiring further verification. Recipe archive pagination was also checked through its final page, in addition to every individual published recipe URL.

<!-- GENERATED AUDIT INVENTORIES -->

### Final coverage totals

| Check | Result |
| --- | --- |
| Published WordPress pages/recipes inventoried | 471 |
| Published ordinary pages | 9 |
| Published recipe URLs | 462 |
| Published targets with successful HTTP responses | 471 |
| Total distinct local URLs requested, including auxiliary routes | 540 |
| Local HTTP result distribution | {200: 539, 404: 1} |
| Local HTML responses containing PHP warning markup | 529 |
| Distinct rendered anchor targets | 533 |
| Local asset URLs probed with HEAD | 538 |
| External URLs probed | 27 |
| Stored YouTube source URLs checked through oEmbed | 452 |

The deliberately missing `/qwb-audit-missing-page/` returning 404 is an expected negative test. Status 0 means a network error/timeout, not a confirmed broken link. External HTTP 200 confirms retrieval only; social login/consent pages can still return 200. YouTube oEmbed probes check metadata/embeddability, not playback or media quality.

#### Non-200 local responses

| URL | Status | Interpretation |
| --- | --- | --- |
| [/qwb-audit-missing-page/](http://quickfixwithbindu-local.local/qwb-audit-missing-page/) | 404 | Expected synthetic 404 |

#### External response findings

| URL | Status | Interpretation |
| --- | --- | --- |
| [https://purple-bison-335489.hostingersite.com/creating-a-comprehensive-recipe-website/](https://purple-bison-335489.hostingersite.com/creating-a-comprehensive-recipe-website/) | 404 | Returned 404; confirm intended destination |
| [https://purple-bison-335489.hostingersite.com/creating-the-perfect-recipe-website/](https://purple-bison-335489.hostingersite.com/creating-the-perfect-recipe-website/) | 404 | Returned 404; confirm intended destination |
| [https://www.youtube.com/@QuickFixwithBindu](https://www.youtube.com/@QuickFixwithBindu) | 404 | Returned 404; confirm intended destination |

The two `purple-bison-335489.hostingersite.com` URLs are stored legacy references discovered in Elementor data; they were not present in the parsed live homepage anchors. The YouTube channel URL is in the shared footer and therefore merits review. A separate web retrieval returned content for the [production homepage](https://www.quickfixwithbindu.com/) and [Healthy Bites](https://www.quickfixwithbindu.com/category/healthy-bites/); these are limited production-reference checks, not a production certification.

### Appendix A — every published page and recipe

Recipe completeness uses the custom recipe row. Warnings are evaluated independently of HTTP status.

| Post ID | Type | Title / URL | HTTP | Ingredients | Steps | Notes |
| --- | --- | --- | --- | --- | --- | --- |
| 568 | page | [About Me](http://quickfixwithbindu-local.local/about-me/) | 200 | — | — | PHP warnings |
| 1134 | page | [All Recipes](http://quickfixwithbindu-local.local/all-recipes/) | 200 | — | — | PHP warnings |
| 701 | page | [Contact Us](http://quickfixwithbindu-local.local/contact-us/) | 200 | — | — | PHP warnings |
| 1155 | page | [Instagram Recipes](http://quickfixwithbindu-local.local/instagram-recipes/) | 200 | — | — | PHP warnings |
| 700 | page | [Privacy Policy](http://quickfixwithbindu-local.local/privacy-policy/) | 200 | — | — | PHP warnings |
| 10 | page | [Quick Fix with Bindu](http://quickfixwithbindu-local.local/) | 200 | — | — | PHP warnings |
| 1324 | page | [Subscribe to Our Newsletter](http://quickfixwithbindu-local.local/subscribe-to-our-newsletter/) | 200 | — | — | PHP warnings |
| 1252 | page | [UGC Creator Portfolio](http://quickfixwithbindu-local.local/ugc-creator-portfolio/) | 200 | — | — | PHP warnings |
| 1342 | page | [Upload Recipe](http://quickfixwithbindu-local.local/upload-recipe/) | 200 | — | — | PHP warnings |
| 682 | recipes | [10-Min Spicy C-Momo \| Best Way to Eat Frozen Momos!](http://quickfixwithbindu-local.local/recipes/10-min-spicy-c-momo-best-way-to-eat-frozen-momos/) | 200 | 10 | 7 | PHP warnings |
| 490 | recipes | [10-Min Veggie Stir-Fry Rice with Mushrooms & Coriander](http://quickfixwithbindu-local.local/recipes/10-min-veggie-stir-fry-rice-with-mushrooms-coriander/) | 200 | 13 | 15 | PHP warnings |
| 251 | recipes | [10-Minute Air Fryer Bread Pizza](http://quickfixwithbindu-local.local/recipes/10-minute-air-fryer-bread-pizza/) | 200 | 5 | 6 | PHP warnings |
| 692 | recipes | [10-Minute Cheesy Egg Pizza \| Quick Whole Wheat Bread Omelet Hack](http://quickfixwithbindu-local.local/recipes/10-minute-cheesy-egg-pizza-quick-whole-wheat-bread-omelet-hack/) | 200 | 8 | 8 | PHP warnings |
| 714 | recipes | [10-Minute Crispy Garlic Brussels Sprouts with Sesame and Peanuts Crunch](http://quickfixwithbindu-local.local/recipes/10-minute-crispy-garlic-brussels-sprouts-with-sesame-and-peanuts-crunch/) | 200 | 7 | 7 | PHP warnings |
| 687 | recipes | [10-Minute Egg Fried Rice (No Egg Smell!) \| My Family Didn't Know It has Egg on it!](http://quickfixwithbindu-local.local/recipes/10-minute-egg-fried-rice-no-egg-smell-my-family-didnt-know-it-has-egg-on-it/) | 200 | 11 | 6 | PHP warnings |
| 397 | recipes | [100% Whole Wheat Artisan Pizza \| Fresh Mozzarella & Basil with Bakery Style Crust](http://quickfixwithbindu-local.local/recipes/100-whole-wheat-artisan-pizza-fresh-mozzarella-basil-with-bakery-style-crust/) | 200 | 10 | 7 | PHP warnings |
| 311 | recipes | [15-Min High Protein Red Lentil & Broccoli Soup](http://quickfixwithbindu-local.local/recipes/15-min-high-protein-red-lentil-broccoli-soup/) | 200 | 11 | 4 | PHP warnings |
| 479 | recipes | [15-Minute Air Fryer Lasagna-Style Pasta](http://quickfixwithbindu-local.local/recipes/15-minute-air-fryer-lasagna-style-pasta/) | 200 | 4 | 5 | PHP warnings |
| 1247 | recipes | [15-Minute Karahi Cauliflower](http://quickfixwithbindu-local.local/recipes/15-minute-karahi-cauliflower/) | 200 | 10 | 5 | PHP warnings |
| 499 | recipes | [15-Minute Vegan Pasta Bowl with Tofu & Asparagus](http://quickfixwithbindu-local.local/recipes/15-minute-vegan-pasta-bowl-with-tofu-asparagus/) | 200 | 12 | 7 | PHP warnings |
| 230 | recipes | [2-Ingredient Broccoli Omelet](http://quickfixwithbindu-local.local/recipes/2-ingredient-broccoli-omelet/) | 200 | 5 | 10 | PHP warnings |
| 326 | recipes | [2-Ingredient Pink Tortillas With No Oil Tofu Filling \| Easy Vegan Wrap](http://quickfixwithbindu-local.local/recipes/2-ingredient-pink-tortillas-with-no-oil-tofu-filling-easy-vegan-wrap/) | 200 | 12 | 8 | PHP warnings |
| 558 | recipes | [2-Ingredient Sprouted Oat and Beetroot Flatbreads](http://quickfixwithbindu-local.local/recipes/2-ingredient-sprouted-oat-and-beetroot-flatbreads/) | 200 | — | — | Missing custom recipe record; No detailed recipe markup; PHP warnings |
| 559 | recipes | [2-Ingredient Sprouted Oat and Beetroot Flatbreads](http://quickfixwithbindu-local.local/recipes/2-ingredient-sprouted-oat-and-beetroot-flatbreads-2/) | 200 | — | — | Missing custom recipe record; No detailed recipe markup; PHP warnings |
| 555 | recipes | [2-INGREDIENT SPROUTED OATS & BEETROOT FLATBREADS (Gluten-Free)](http://quickfixwithbindu-local.local/recipes/2-ingredient-sprouted-oats-beetroot-flatbreads-gluten-free/) | 200 | — | — | Missing custom recipe record; No detailed recipe markup; PHP warnings |
| 557 | recipes | [2-INGREDIENT SPROUTED OATS & BEETROOT FLATBREADS (Gluten-Free)](http://quickfixwithbindu-local.local/recipes/2-ingredient-sprouted-oats-beetroot-flatbreads-gluten-free-2/) | 200 | — | — | Missing custom recipe record; No detailed recipe markup; PHP warnings |
| 560 | recipes | [2-INGREDIENT SPROUTED OATS & BEETROOT FLATBREADS (Gluten-Free)](http://quickfixwithbindu-local.local/recipes/2-ingredient-sprouted-oats-beetroot-flatbreads-gluten-free-3/) | 200 | — | — | Missing custom recipe record; No detailed recipe markup; PHP warnings |
| 561 | recipes | [2-INGREDIENT SPROUTED OATS & BEETROOT FLATBREADS (Gluten-Free)](http://quickfixwithbindu-local.local/recipes/2-ingredient-sprouted-oats-beetroot-flatbreads-gluten-free-4/) | 200 | — | — | Missing custom recipe record; No detailed recipe markup; PHP warnings |
| 562 | recipes | [2-INGREDIENT SPROUTED OATS & BEETROOT FLATBREADS (Gluten-Free)](http://quickfixwithbindu-local.local/recipes/2-ingredient-sprouted-oats-beetroot-flatbreads-gluten-free-5/) | 200 | — | — | Missing custom recipe record; No detailed recipe markup; PHP warnings |
| 697 | recipes | [2-Ingredient Wrap Recipe \| Crispy Peanut-Mint Wraps](http://quickfixwithbindu-local.local/recipes/2-ingredient-wrap-recipe-crispy-peanut-mint-wraps/) | 200 | 6 | 7 | PHP warnings |
| 293 | recipes | [3 Ingredient Creamy Corn Soup Without Cream](http://quickfixwithbindu-local.local/recipes/3-ingredient-creamy-corn-soup-without-cream/) | 200 | 9 | 9 | PHP warnings |
| 218 | recipes | [3 Ingredient Easy Creamy Tomato Soup in Minutes (EP 6 Winter Soup) No Heavy Cream](http://quickfixwithbindu-local.local/recipes/3-ingredient-easy-creamy-tomato-soup-in-minutes-ep-6-winter-soup-no-heavy-cream/) | 200 | 8 | 5 | PHP warnings |
| 1177 | recipes | [3 Ingredient Peanut Butter Protein Bites](http://quickfixwithbindu-local.local/recipes/3-ingredient-peanut-butter-protein-bites/) | 200 | — | — | Missing custom recipe record; No detailed recipe markup; PHP warnings |
| 1179 | recipes | [3 Ingredient Peanut Butter Protein Bites](http://quickfixwithbindu-local.local/recipes/3-ingredient-peanut-butter-protein-bites-2/) | 200 | 6 | 6 | PHP warnings |
| 403 | recipes | [3 Ingredients, 5 Minutes Dates Recipe \| Sweet and Salty](http://quickfixwithbindu-local.local/recipes/3-ingredients-5-minutes-dates-recipe-sweet-and-salty/) | 200 | 6 | 5 | PHP warnings |
| 351 | recipes | [3-Ingredient Almond Flour Cookies](http://quickfixwithbindu-local.local/recipes/3-ingredient-almond-flour-cookies/) | 200 | 3 | 4 | PHP warnings |
| 274 | recipes | [3-Ingredient Bite-Sized Almond Flour Cookies \| Gluten-Free & Vegan](http://quickfixwithbindu-local.local/recipes/3-ingredient-bite-sized-almond-flour-cookies-gluten-free-vegan/) | 200 | 6 | 7 | PHP warnings |
| 693 | recipes | [3-Ingredient Easy Shortbread Cookies \| Perfect Every Time](http://quickfixwithbindu-local.local/recipes/3-ingredient-easy-shortbread-cookies-perfect-every-time/) | 200 | 5 | 10 | PHP warnings |
| 705 | recipes | [3-Ingredient Flourless Shortbread Cookies](http://quickfixwithbindu-local.local/recipes/3-ingredient-flourless-shortbread-cookies/) | 200 | 4 | 7 | PHP warnings |
| 347 | recipes | [3-Ingredient Pomegranate Pops \| Bite-Sized Pomegranate & Pistachio Clusters](http://quickfixwithbindu-local.local/recipes/3-ingredient-pomegranate-pops-bite-sized-pomegranate-pistachio-clusters/) | 200 | 5 | 5 | PHP warnings |
| 322 | recipes | [5-Min Blueberry Banana Waffle](http://quickfixwithbindu-local.local/recipes/5-min-blueberry-banana-waffle/) | 200 | 7 | 8 | PHP warnings |
| 423 | recipes | [5-Min Healthy Fruit Yogurt Salad](http://quickfixwithbindu-local.local/recipes/5-min-healthy-fruit-yogurt-salad/) | 200 | 6 | 5 | PHP warnings |
| 446 | recipes | [5-Min Spicy Mashed Potatoes](http://quickfixwithbindu-local.local/recipes/5-min-spicy-mashed-potatoes/) | 200 | 9 | 4 | PHP warnings |
| 264 | recipes | [5-Min Spicy Yogurt Dip \| Chili Cucumber Raita](http://quickfixwithbindu-local.local/recipes/5-min-spicy-yogurt-dip-chili-cucumber-raita/) | 200 | 5 | 2 | PHP warnings |
| 406 | recipes | [5-Min Zucchini Stir-Fry](http://quickfixwithbindu-local.local/recipes/5-min-zucchini-stir-fry/) | 200 | 7 | 6 | PHP warnings |
| 336 | recipes | [5-Minute Broccoli Salad](http://quickfixwithbindu-local.local/recipes/5-minute-broccoli-salad/) | 200 | 8 | 6 | PHP warnings |
| 238 | recipes | [5-Minute Cheesy Egg & Mushroom Sandwich](http://quickfixwithbindu-local.local/recipes/5-minute-cheesy-egg-mushroom-sandwich/) | 200 | 10 | 4 | PHP warnings |
| 325 | recipes | [5-Minute Garlic Charred Broccoli (Zero-Waste)](http://quickfixwithbindu-local.local/recipes/5-minute-garlic-charred-broccoli-zero-waste/) | 200 | 5 | 5 | PHP warnings |
| 309 | recipes | [5-Minute Sugar-Free Banana Pancakes](http://quickfixwithbindu-local.local/recipes/5-minute-sugar-free-banana-pancakes/) | 200 | 9 | 6 | PHP warnings |
| 683 | recipes | [50 High-Protein Nepali Momos \| Tofu & Paneer with Spicy Schezwan Chutney & Flash Freeze Hack](http://quickfixwithbindu-local.local/recipes/50-high-protein-nepali-momos-tofu-paneer-with-spicy-schezwan-chutney-flash-freeze-hack/) | 200 | 12 | 7 | PHP warnings |
| 345 | recipes | [A Quiet Meal Made With Asparagus and Simple Veggies](http://quickfixwithbindu-local.local/recipes/a-quiet-meal-made-with-asparagus-and-simple-veggies/) | 200 | 14 | 8 | PHP warnings |
| 424 | recipes | [Air Fry Crispy Quinoa Bites](http://quickfixwithbindu-local.local/recipes/air-fry-crispy-quinoa-bites/) | 200 | 0 | 0 | No ingredients; No instructions; PHP warnings |
| 428 | recipes | [Air Fryer Banana Blueberry Oat Bake](http://quickfixwithbindu-local.local/recipes/air-fryer-banana-blueberry-oat-bake/) | 200 | 11 | 6 | PHP warnings |
| 381 | recipes | [Air Fryer Corn Ribs with Garlic Butter Glaze](http://quickfixwithbindu-local.local/recipes/air-fryer-corn-ribs-with-garlic-butter-glaze/) | 200 | 9 | 5 | PHP warnings |
| 514 | recipes | [Air Fryer Egg Noodle Pizza in 10 Minutes \| Ramen Pizza \| Cheesy Maggi](http://quickfixwithbindu-local.local/recipes/air-fryer-egg-noodle-pizza-in-10-minutes-ramen-pizza-cheesy-maggi/) | 200 | 11 | 10 | PHP warnings |
| 488 | recipes | [Air Fryer Falafel & Grape Chutney](http://quickfixwithbindu-local.local/recipes/air-fryer-falafel-grape-chutney/) | 200 | 16 | 11 | PHP warnings |
| 1170 | recipes | [Air Fryer Plantain Chips with Zesty Yogurt Dip](http://quickfixwithbindu-local.local/recipes/air-fryer-plantain-chips-with-zesty-yogurt-dip/) | 200 | 8 | 8 | PHP warnings |
| 352 | recipes | [Air-Fried Chickpea Quinoa Salad](http://quickfixwithbindu-local.local/recipes/air-fried-chickpea-quinoa-salad/) | 200 | 16 | 6 | PHP warnings |
| 500 | recipes | [Air-Fried Crumble Tofu with Creamy Yogurt Dip](http://quickfixwithbindu-local.local/recipes/air-fried-crumble-tofu-with-creamy-yogurt-dip/) | 200 | 17 | 9 | PHP warnings |
| 524 | recipes | [Air-Fried Paneer Kofta with Walnut Gravy](http://quickfixwithbindu-local.local/recipes/air-fried-paneer-kofta-with-walnut-gravy/) | 200 | 25 | 11 | PHP warnings |
| 239 | recipes | [Air-Fried Potato Rolls](http://quickfixwithbindu-local.local/recipes/air-fried-potato-rolls/) | 200 | 6 | 4 | PHP warnings |
| 412 | recipes | [Airfried Cheesy Cauliflower](http://quickfixwithbindu-local.local/recipes/airfried-cheesy-cauliflower/) | 200 | 21 | 10 | PHP warnings |
| 219 | recipes | [Airfried Chickpea Quinoa Salad](http://quickfixwithbindu-local.local/recipes/airfried-chickpea-quinoa-salad/) | 200 | 0 | 0 | No ingredients; No instructions; PHP warnings |
| 393 | recipes | [Almond Flour Biscotti](http://quickfixwithbindu-local.local/recipes/almond-flour-biscotti/) | 200 | 6 | 6 | PHP warnings |
| 258 | recipes | [Apple Oats Waffle](http://quickfixwithbindu-local.local/recipes/apple-oats-waffle/) | 200 | 6 | 5 | PHP warnings |
| 455 | recipes | [Asparagus and Green Beans Curry Soup](http://quickfixwithbindu-local.local/recipes/asparagus-and-green-beans-curry-soup/) | 200 | 11 | 0 | No instructions; PHP warnings |
| 335 | recipes | [Asparagus Cherry Pop Stir-Fry](http://quickfixwithbindu-local.local/recipes/asparagus-cherry-pop-stir-fry/) | 200 | 10 | 8 | PHP warnings |
| 364 | recipes | [Authentic Himalayan Black Eyed Peas Soup \| Bamboo Shoot & Beans (Bodi Tama)](http://quickfixwithbindu-local.local/recipes/authentic-himalayan-black-eyed-peas-soup-bamboo-shoot-beans-bodi-tama/) | 200 | 12 | 6 | PHP warnings |
| 299 | recipes | [Avocado Dosa with Guacamole and Mint Chutney](http://quickfixwithbindu-local.local/recipes/avocado-dosa-with-guacamole-and-mint-chutney/) | 200 | 12 | 5 | PHP warnings |
| 379 | recipes | [Avocado Egg Bagel with Runny Yolk](http://quickfixwithbindu-local.local/recipes/avocado-egg-bagel-with-runny-yolk/) | 200 | 5 | 6 | PHP warnings |
| 205 | recipes | [Avocado Oats Egg Pancake Fail](http://quickfixwithbindu-local.local/recipes/avocado-oats-egg-pancake-fail/) | 200 | 3 | 0 | No instructions; PHP warnings |
| 516 | recipes | [Avocado Peanut Salad](http://quickfixwithbindu-local.local/recipes/avocado-peanut-salad/) | 200 | 11 | 7 | PHP warnings |
| 438 | recipes | [Banana Blueberry Oatmeal Muffin Bowl in Airfryer - No Flour, No Sugar](http://quickfixwithbindu-local.local/recipes/banana-blueberry-oatmeal-muffin-bowl-in-airfryer-no-flour-no-sugar/) | 200 | 10 | 5 | PHP warnings |
| 200 | recipes | [Banana Chia Cinnamon Smoothie](http://quickfixwithbindu-local.local/recipes/banana-chia-cinnamon-smoothie/) | 200 | 6 | 4 | PHP warnings |
| 405 | recipes | [Banana Quinoa Power Pancakes](http://quickfixwithbindu-local.local/recipes/banana-quinoa-power-pancakes/) | 200 | 8 | 6 | PHP warnings |
| 454 | recipes | [Banana Quinoa Power Pancakes](http://quickfixwithbindu-local.local/recipes/banana-quinoa-power-pancakes-2/) | 200 | 8 | 6 | PHP warnings |
| 441 | recipes | [BBQ Cottage Cheese (Paneer) Wrap](http://quickfixwithbindu-local.local/recipes/bbq-cottage-cheese-paneer-wrap/) | 200 | 8 | 6 | PHP warnings |
| 315 | recipes | [BBQ-Style Air Fryer Paneer](http://quickfixwithbindu-local.local/recipes/bbq-style-air-fryer-paneer/) | 200 | 8 | 6 | PHP warnings |
| 409 | recipes | [BBQ-Style Air Fryer Paneer](http://quickfixwithbindu-local.local/recipes/bbq-style-air-fryer-paneer-2/) | 200 | 8 | 5 | PHP warnings |
| 220 | recipes | [BBQ-Style Paneer Chili \| Air Fryer Twist](http://quickfixwithbindu-local.local/recipes/bbq-style-paneer-chili-air-fryer-twist/) | 200 | 13 | 7 | PHP warnings |
| 244 | recipes | [Beetroot Chia Drink for Digestion](http://quickfixwithbindu-local.local/recipes/beetroot-chia-drink-for-digestion/) | 200 | 6 | 4 | PHP warnings |
| 511 | recipes | [Beginner Cooking That Actually Tastes Good: Three Super Simple Veggie Recipes](http://quickfixwithbindu-local.local/recipes/beginner-cooking-that-actually-tastes-good-three-super-simple-veggie-recipes/) | 200 | 19 | 6 | PHP warnings |
| 513 | recipes | [Belgian Waffle Recipe](http://quickfixwithbindu-local.local/recipes/belgian-waffle-recipe/) | 200 | 0 | 0 | No ingredients; No instructions; PHP warnings |
| 240 | recipes | [Better Than Store-Bought Seed Crackers](http://quickfixwithbindu-local.local/recipes/better-than-store-bought-seed-crackers/) | 200 | 8 | 10 | PHP warnings |
| 675 | recipes | [Better Than Toast \| A Balanced Breakfast with Protein, Fiber & Carbs \| Gluten-Free Broccoli Waffles](http://quickfixwithbindu-local.local/recipes/better-than-toast-a-balanced-breakfast-with-protein-fiber-carbs-gluten-free-broccoli-waffles/) | 200 | 10 | 8 | PHP warnings |
| 494 | recipes | [Bindu’s Cheesy Quinoa Stuffed Peppers with fresh dill (Air Fryer Version)](http://quickfixwithbindu-local.local/recipes/bindus-cheesy-quinoa-stuffed-peppers-with-fresh-dill-air-fryer-version/) | 200 | 11 | 16 | PHP warnings |
| 285 | recipes | [Blueberry Banana Bread Waffle Hack](http://quickfixwithbindu-local.local/recipes/blueberry-banana-bread-waffle-hack/) | 200 | 7 | 8 | PHP warnings |
| 491 | recipes | [Blueberry Banana Power Smoothie](http://quickfixwithbindu-local.local/recipes/blueberry-banana-power-smoothie/) | 200 | 9 | 4 | PHP warnings |
| 267 | recipes | [Blueberry Chia Smoothie](http://quickfixwithbindu-local.local/recipes/blueberry-chia-smoothie/) | 200 | 5 | 4 | PHP warnings |
| 363 | recipes | [Buckwheat Pancake (फापरको रोटी \| कुट्टू का चीला)](http://quickfixwithbindu-local.local/recipes/buckwheat-pancake-%e0%a4%ab%e0%a4%be%e0%a4%aa%e0%a4%b0%e0%a4%95%e0%a5%8b-%e0%a4%b0%e0%a5%8b%e0%a4%9f%e0%a5%80-%e0%a4%95%e0%a5%81%e0%a4%9f%e0%a5%8d%e0%a4%9f%e0%a5%82-%e0%a4%95%e0%a4%be-%e0%a4%9a/) | 200 | 10 | 7 | PHP warnings |
| 328 | recipes | [Budget Friendly Fluffy Quinoa Rice With Squash Soup](http://quickfixwithbindu-local.local/recipes/budget-friendly-fluffy-quinoa-rice-with-squash-soup/) | 200 | 16 | 12 | PHP warnings |
| 365 | recipes | [Budget Friendly One Pot Chickpea Rice & Cooling Yogurt Dip](http://quickfixwithbindu-local.local/recipes/budget-friendly-one-pot-chickpea-rice-cooling-yogurt-dip/) | 200 | 15 | 8 | PHP warnings |
| 306 | recipes | [Butterfly Pasta](http://quickfixwithbindu-local.local/recipes/butterfly-pasta/) | 200 | 7 | 4 | PHP warnings |
| 369 | recipes | [Cabbage Omelette](http://quickfixwithbindu-local.local/recipes/cabbage-omelette/) | 200 | 6 | 8 | PHP warnings |
| 286 | recipes | [Cheesy Avocado Egg Tortilla Wrap](http://quickfixwithbindu-local.local/recipes/cheesy-avocado-egg-tortilla-wrap/) | 200 | 10 | 7 | PHP warnings |
| 374 | recipes | [Cheesy Egg Quesadilla](http://quickfixwithbindu-local.local/recipes/cheesy-egg-quesadilla/) | 200 | 8 | 5 | PHP warnings |
| 704 | recipes | [Cheesy Egg Roll \| Japanese Style Omelette Recipe](http://quickfixwithbindu-local.local/recipes/cheesy-egg-roll-japanese-style-omelette-recipe/) | 200 | 6 | 8 | PHP warnings |
| 420 | recipes | [Cheesy Jalapeño Potato Bites](http://quickfixwithbindu-local.local/recipes/cheesy-jalapeno-potato-bites/) | 200 | 10 | 5 | PHP warnings |
| 460 | recipes | [Cheesy Multigrain Egg Sandwich](http://quickfixwithbindu-local.local/recipes/cheesy-multigrain-egg-sandwich/) | 200 | 9 | 6 | PHP warnings |
| 298 | recipes | [Cheesy Paneer Potatoes in Airfryer](http://quickfixwithbindu-local.local/recipes/cheesy-paneer-potatoes-in-airfryer/) | 200 | 11 | 5 | PHP warnings |
| 355 | recipes | [Cheesy Pocket Wrap with Mushroom & Tofu](http://quickfixwithbindu-local.local/recipes/cheesy-pocket-wrap-with-mushroom-tofu/) | 200 | 11 | 8 | PHP warnings |
| 475 | recipes | [Cheesy Potato Bites](http://quickfixwithbindu-local.local/recipes/cheesy-potato-bites/) | 200 | 10 | 5 | PHP warnings |
| 375 | recipes | [Cheesy Potato Waffle Snack (No Oil, No Batter)](http://quickfixwithbindu-local.local/recipes/cheesy-potato-waffle-snack-no-oil-no-batter/) | 200 | 5 | 6 | PHP warnings |
| 474 | recipes | [Cheesy Protein-Packed Potatoes in Airfryer](http://quickfixwithbindu-local.local/recipes/cheesy-protein-packed-potatoes-in-airfryer/) | 200 | 11 | 8 | PHP warnings |
| 718 | recipes | [Cheesy Spinach Omelette (Tamagoyaki ) \| High Protein 5-Min Breakfast](http://quickfixwithbindu-local.local/recipes/cheesy-spinach-omelette-tamagoyaki-high-protein-5-min-breakfast/) | 200 | 6 | 7 | PHP warnings |
| 504 | recipes | [Cheesy Squash Blossom Cups](http://quickfixwithbindu-local.local/recipes/cheesy-squash-blossom-cups/) | 200 | 10 | 7 | PHP warnings |
| 435 | recipes | [Cheesy Stuffed Pattypan Squash Cups](http://quickfixwithbindu-local.local/recipes/cheesy-stuffed-pattypan-squash-cups/) | 200 | 10 | 7 | PHP warnings |
| 411 | recipes | [Cheesy Zucchini Onion Fritters](http://quickfixwithbindu-local.local/recipes/cheesy-zucchini-onion-fritters/) | 200 | 10 | 6 | PHP warnings |
| 431 | recipes | [Chia Banana Post workout Smoothie](http://quickfixwithbindu-local.local/recipes/chia-banana-post-workout-smoothie/) | 200 | 6 | 4 | PHP warnings |
| 257 | recipes | [Chia Microgreens](http://quickfixwithbindu-local.local/recipes/chia-microgreens/) | 200 | 4 | 7 | PHP warnings |
| 1128 | recipes | [Chickpea Avocado Almond Crackers & Creamy Cilantro Dip](http://quickfixwithbindu-local.local/recipes/chickpea-avocado-almond-crackers-creamy-cilantro-dip/) | 200 | 17 | 6 | PHP warnings |
| 519 | recipes | [Chickpea Eggs Paradise \| Protein-Packed Curry Bowl](http://quickfixwithbindu-local.local/recipes/chickpea-eggs-paradise-protein-packed-curry-bowl/) | 200 | 11 | 10 | PHP warnings |
| 206 | recipes | [Chickpea Rice with Yogurt Dip](http://quickfixwithbindu-local.local/recipes/chickpea-rice-with-yogurt-dip/) | 200 | 15 | 8 | PHP warnings |
| 388 | recipes | [Chickpea Tofu Dill Salad](http://quickfixwithbindu-local.local/recipes/chickpea-tofu-dill-salad/) | 200 | 12 | 5 | PHP warnings |
| 343 | recipes | [Chickpea Veggie Flatbread](http://quickfixwithbindu-local.local/recipes/chickpea-veggie-flatbread/) | 200 | 11 | 12 | PHP warnings |
| 493 | recipes | [Chickpea-Cauliflower Falafels with Dill (No Onion Garlic)](http://quickfixwithbindu-local.local/recipes/chickpea-cauliflower-falafels-with-dill-no-onion-garlic/) | 200 | 12 | 9 | PHP warnings |
| 414 | recipes | [Chukauni (Nepali Yogurt Potato Salad)](http://quickfixwithbindu-local.local/recipes/chukauni-nepali-yogurt-potato-salad/) | 200 | 12 | 10 | PHP warnings |
| 698 | recipes | [Classic Shortbread Bites](http://quickfixwithbindu-local.local/recipes/classic-shortbread-bites/) | 200 | 5 | 7 | PHP warnings |
| 699 | recipes | [Classic Shortbread Bites](http://quickfixwithbindu-local.local/recipes/classic-shortbread-bites-2/) | 200 | 5 | 7 | PHP warnings |
| 518 | recipes | [Colorful Whole Moong Power Bowl](http://quickfixwithbindu-local.local/recipes/colorful-whole-moong-power-bowl/) | 200 | 13 | 6 | PHP warnings |
| 716 | recipes | [Cook Your Eggs This Way & Never Go Back! \| Spinach Cheese Tamagoyaki](http://quickfixwithbindu-local.local/recipes/cook-your-eggs-this-way-never-go-back-spinach-cheese-tamagoyaki/) | 200 | 6 | 7 | PHP warnings |
| 1101 | recipes | [Cook your eggs with beans and you’ll NEVER go back! The Japanese Tamagoyaki secret](http://quickfixwithbindu-local.local/recipes/cook-your-eggs-with-beans-and-youll-never-go-back-the-japanese-tamagoyaki-secret/) | 200 | 4 | 7 | PHP warnings |
| 1105 | recipes | [Cook your eggs with beans and you’ll NEVER go back! 🍳 The Japanese Tamagoyaki secret](http://quickfixwithbindu-local.local/recipes/cook-your-eggs-with-beans-and-youll-never-go-back-%f0%9f%8d%b3-the-japanese-tamagoyaki-secret/) | 200 | 4 | 5 | PHP warnings |
| 443 | recipes | [Cottage Cheese (Paneer) Wrap \| High-Protein & Flavor Packed \| BBQ Style](http://quickfixwithbindu-local.local/recipes/cottage-cheese-paneer-wrap-high-protein-flavor-packed-bbq-style/) | 200 | 8 | 6 | PHP warnings |
| 422 | recipes | [Cozy Zucchini Lentil Soup](http://quickfixwithbindu-local.local/recipes/cozy-zucchini-lentil-soup/) | 200 | 9 | 8 | PHP warnings |
| 450 | recipes | [Creamy Banana Oats Smoothie (No Sugar Added!)](http://quickfixwithbindu-local.local/recipes/creamy-banana-oats-smoothie-no-sugar-added/) | 200 | 12 | 5 | PHP warnings |
| 396 | recipes | [Creamy Dill Potato Balls](http://quickfixwithbindu-local.local/recipes/creamy-dill-potato-balls/) | 200 | 0 | 0 | No ingredients; No instructions; PHP warnings |
| 470 | recipes | [Creamy Egg Curry with Potatoes](http://quickfixwithbindu-local.local/recipes/creamy-egg-curry-with-potatoes/) | 200 | 14 | 19 | PHP warnings |
| 1275 | recipes | [Creamy Homemade Cashew Milk](http://quickfixwithbindu-local.local/recipes/creamy-homemade-cashew-milk/) | 200 | 7 | 6 | PHP warnings |
| 214 | recipes | [Creamy Oats Avocado Pancake](http://quickfixwithbindu-local.local/recipes/creamy-oats-avocado-pancake/) | 200 | 7 | 6 | PHP warnings |
| 480 | recipes | [Creamy Potato Dill Balls \| Easy Snack with Surprise Filling \| Air Fryer Snack Everyone Loves!](http://quickfixwithbindu-local.local/recipes/creamy-potato-dill-balls-easy-snack-with-surprise-filling-air-fryer-snack-everyone-loves/) | 200 | 14 | 9 | PHP warnings |
| 324 | recipes | [Creamy Tomato Soup](http://quickfixwithbindu-local.local/recipes/creamy-tomato-soup/) | 200 | 8 | 5 | PHP warnings |
| 512 | recipes | [Crispy Air Fryer Okra in 10 Minutes](http://quickfixwithbindu-local.local/recipes/crispy-air-fryer-okra-in-10-minutes/) | 200 | 8 | 6 | PHP warnings |
| 712 | recipes | [Crispy Air Fryer Okra \| 10-Minute Masala Magic \| Healthy & Oil-Free Snack](http://quickfixwithbindu-local.local/recipes/crispy-air-fryer-okra-10-minute-masala-magic-healthy-oil-free-snack/) | 200 | 0 | 0 | No ingredients; No instructions; PHP warnings |
| 416 | recipes | [Crispy Air-Fried Asparagus Bites](http://quickfixwithbindu-local.local/recipes/crispy-air-fried-asparagus-bites/) | 200 | 8 | 5 | PHP warnings |
| 429 | recipes | [Crispy Air-Fried Chickpea & Lettuce Salad](http://quickfixwithbindu-local.local/recipes/crispy-air-fried-chickpea-lettuce-salad/) | 200 | 14 | 6 | PHP warnings |
| 300 | recipes | [Crispy Cabbage Flatbread](http://quickfixwithbindu-local.local/recipes/crispy-cabbage-flatbread/) | 200 | 10 | 6 | PHP warnings |
| 288 | recipes | [Crispy Cauliflower Bites with a Cornflakes Crunch](http://quickfixwithbindu-local.local/recipes/crispy-cauliflower-bites-with-a-cornflakes-crunch/) | 200 | 11 | 7 | PHP warnings |
| 312 | recipes | [Crispy Cheesy Zucchini Cabbage Fritters](http://quickfixwithbindu-local.local/recipes/crispy-cheesy-zucchini-cabbage-fritters/) | 200 | 12 | 6 | PHP warnings |
| 275 | recipes | [Crispy Chickpea-Cauliflower Falafels with Dill](http://quickfixwithbindu-local.local/recipes/crispy-chickpea-cauliflower-falafels-with-dill/) | 200 | 12 | 9 | PHP warnings |
| 217 | recipes | [Crispy Eggplant Fritters](http://quickfixwithbindu-local.local/recipes/crispy-eggplant-fritters/) | 200 | 8 | 6 | PHP warnings |
| 273 | recipes | [Crispy Eggplant Fritters](http://quickfixwithbindu-local.local/recipes/crispy-eggplant-fritters-2/) | 200 | 8 | 6 | PHP warnings |
| 1231 | recipes | [Crispy High-Protein Red Lentil Bites (Air Fryer)](http://quickfixwithbindu-local.local/recipes/crispy-high-protein-red-lentil-bites-air-fryer/) | 200 | 20 | 6 | PHP warnings |
| 1306 | recipes | [Crispy Lentil & Tofu Bites](http://quickfixwithbindu-local.local/recipes/crispy-lentil-tofu-bites/) | 200 | 15 | 5 | PHP warnings |
| 427 | recipes | [Crispy Onion Dill Fritters (Onion Dill Pakoras)](http://quickfixwithbindu-local.local/recipes/crispy-onion-dill-fritters-onion-dill-pakoras/) | 200 | 12 | 6 | PHP warnings |
| 492 | recipes | [Crispy Onion Dill Fritters (Onion Dill Pakoras)](http://quickfixwithbindu-local.local/recipes/crispy-onion-dill-fritters-onion-dill-pakoras-2/) | 200 | 12 | 6 | PHP warnings |
| 501 | recipes | [Crispy Poori Taco with Chickpea Filling \| Indian Street Food Twist](http://quickfixwithbindu-local.local/recipes/crispy-poori-taco-with-chickpea-filling-indian-street-food-twist/) | 200 | 16 | 6 | PHP warnings |
| 294 | recipes | [Crispy Potato Egg Fritters](http://quickfixwithbindu-local.local/recipes/crispy-potato-egg-fritters/) | 200 | 8 | 5 | PHP warnings |
| 248 | recipes | [Crispy Potato Waffle](http://quickfixwithbindu-local.local/recipes/crispy-potato-waffle/) | 200 | 6 | 5 | PHP warnings |
| 342 | recipes | [Crispy Probiotic Dosa \| Simple Fermented Delight](http://quickfixwithbindu-local.local/recipes/crispy-probiotic-dosa-simple-fermented-delight/) | 200 | 7 | 6 | PHP warnings |
| 344 | recipes | [Crispy Quinoa Eggplant Bites](http://quickfixwithbindu-local.local/recipes/crispy-quinoa-eggplant-bites/) | 200 | 20 | 8 | PHP warnings |
| 498 | recipes | [Crispy Quinoa Eggplant Bites + Tangy Dip](http://quickfixwithbindu-local.local/recipes/crispy-quinoa-eggplant-bites-tangy-dip/) | 200 | 19 | 8 | PHP warnings |
| 484 | recipes | [Crispy Quinoa-Crusted Cauliflower Wings with Mint-Peanut Yogurt Dip](http://quickfixwithbindu-local.local/recipes/crispy-quinoa-crusted-cauliflower-wings-with-mint-peanut-yogurt-dip/) | 200 | 18 | 10 | PHP warnings |
| 1226 | recipes | [Crispy Red Lentil Soy Chunk Vegan Nuggets](http://quickfixwithbindu-local.local/recipes/crispy-red-lentil-soy-chunk-vegan-nuggets/) | 200 | 14 | 24 | PHP warnings |
| 301 | recipes | [Crispy Saucy Pan-Fried Dumplings with Soy-Sesame Glaze \| Tofu-Paneer Momo](http://quickfixwithbindu-local.local/recipes/crispy-saucy-pan-fried-dumplings-with-soy-sesame-glaze-tofu-paneer-momo/) | 200 | 22 | 6 | PHP warnings |
| 225 | recipes | [Crispy Smashed Broccoli & Potato Bake](http://quickfixwithbindu-local.local/recipes/crispy-smashed-broccoli-potato-bake/) | 200 | 9 | 8 | PHP warnings |
| 303 | recipes | [Crispy Smashed Parmesan Potato & Broccoli Tray Bake](http://quickfixwithbindu-local.local/recipes/crispy-smashed-parmesan-potato-broccoli-tray-bake/) | 200 | 9 | 8 | PHP warnings |
| 407 | recipes | [Crispy Triangle Tortilla Egg Wrap](http://quickfixwithbindu-local.local/recipes/crispy-triangle-tortilla-egg-wrap/) | 200 | 10 | 6 | PHP warnings |
| 232 | recipes | [Crispy Vegan Flatbread](http://quickfixwithbindu-local.local/recipes/crispy-vegan-flatbread/) | 200 | 11 | 9 | PHP warnings |
| 223 | recipes | [Crispy Vegan Low-Carb Cabbage Flatbread](http://quickfixwithbindu-local.local/recipes/crispy-vegan-low-carb-cabbage-flatbread/) | 200 | 10 | 6 | PHP warnings |
| 291 | recipes | [Crispy Zucchini Egg Fritters](http://quickfixwithbindu-local.local/recipes/crispy-zucchini-egg-fritters/) | 200 | 9 | 8 | PHP warnings |
| 469 | recipes | [Crunchy Chickpea Lettuce Boats with Creamy Bell Pepper Yogurt Dip](http://quickfixwithbindu-local.local/recipes/crunchy-chickpea-lettuce-boats-with-creamy-bell-pepper-yogurt-dip/) | 200 | 17 | 10 | PHP warnings |
| 305 | recipes | [Crunchy Crispy Cauliflower Bites](http://quickfixwithbindu-local.local/recipes/crunchy-crispy-cauliflower-bites/) | 200 | 11 | 7 | PHP warnings |
| 408 | recipes | [Deep Fry vs Air Fry – Huge Difference!](http://quickfixwithbindu-local.local/recipes/deep-fry-vs-air-fry-huge-difference/) | 200 | 0 | 0 | No ingredients; No instructions; PHP warnings |
| 287 | recipes | [Easiest Way Of Cooking Broccoli \| Zero Waste Garlic Stir Fry](http://quickfixwithbindu-local.local/recipes/easiest-way-of-cooking-broccoli-zero-waste-garlic-stir-fry/) | 200 | 5 | 4 | PHP warnings |
| 245 | recipes | [Easy and Quick Homemade Seed Crackers](http://quickfixwithbindu-local.local/recipes/easy-and-quick-homemade-seed-crackers/) | 200 | 8 | 10 | PHP warnings |
| 199 | recipes | [Easy Beginner’s Recipe with Pattypan Squash—No Splatter, Just Flavor](http://quickfixwithbindu-local.local/recipes/easy-beginners-recipe-with-pattypan-squash-no-splatter-just-flavor/) | 200 | 7 | 5 | PHP warnings |
| 421 | recipes | [Easy Breakfast Waffle with Banana & Oats](http://quickfixwithbindu-local.local/recipes/easy-breakfast-waffle-with-banana-oats/) | 200 | 9 | 4 | PHP warnings |
| 235 | recipes | [Easy Broccoli Egg Omelet](http://quickfixwithbindu-local.local/recipes/easy-broccoli-egg-omelet/) | 200 | 6 | 6 | PHP warnings |
| 313 | recipes | [Easy Brownie Mix Cake](http://quickfixwithbindu-local.local/recipes/easy-brownie-mix-cake/) | 200 | 4 | 5 | PHP warnings |
| 556 | recipes | [Easy Eggless Whole Wheat Chocolate Chunk Cookies (Batch of 15) #wholewheat #egglessbaking](http://quickfixwithbindu-local.local/recipes/easy-eggless-whole-wheat-chocolate-chunk-cookies-batch-of-15-wholewheat-egglessbaking/) | 200 | — | — | Missing custom recipe record; No detailed recipe markup; PHP warnings |
| 202 | recipes | [Easy Flourless Lentil & Oat Flatbreads (No Egg, No Gluten)](http://quickfixwithbindu-local.local/recipes/easy-flourless-lentil-oat-flatbreads-no-egg-no-gluten/) | 200 | 18 | 6 | PHP warnings |
| 503 | recipes | [Easy Lentil Soup, Bok Choy Stir Fry, Rice & Tomato Chutney Meal](http://quickfixwithbindu-local.local/recipes/easy-lentil-soup-bok-choy-stir-fry-rice-tomato-chutney-meal/) | 200 | 16 | 13 | PHP warnings |
| 358 | recipes | [Easy Lentil Spinach Soup](http://quickfixwithbindu-local.local/recipes/easy-lentil-spinach-soup/) | 200 | 11 | 8 | PHP warnings |
| 337 | recipes | [Easy Motichoor Laddu](http://quickfixwithbindu-local.local/recipes/easy-motichoor-laddu/) | 200 | 11 | 5 | PHP warnings |
| 415 | recipes | [Easy Mushroom Peas Fried Rice](http://quickfixwithbindu-local.local/recipes/easy-mushroom-peas-fried-rice/) | 200 | 10 | 6 | PHP warnings |
| 399 | recipes | [Easy No-Bake Energy Balls \| Date & Pecan Snack (No Refined Sugar)](http://quickfixwithbindu-local.local/recipes/easy-no-bake-energy-balls-date-pecan-snack-no-refined-sugar/) | 200 | 5 | 7 | PHP warnings |
| 440 | recipes | [Egg & Cheese Sandwich with a Twist of Dill](http://quickfixwithbindu-local.local/recipes/egg-cheese-sandwich-with-a-twist-of-dill/) | 200 | 9 | 7 | PHP warnings |
| 253 | recipes | [Egg and Sweet Corn Pizza](http://quickfixwithbindu-local.local/recipes/egg-and-sweet-corn-pizza/) | 200 | 3 | 0 | No instructions; PHP warnings |
| 410 | recipes | [Egg Bread Toast in a Round Well](http://quickfixwithbindu-local.local/recipes/egg-bread-toast-in-a-round-well/) | 200 | 6 | 5 | PHP warnings |
| 404 | recipes | [Egg Curry Recipe](http://quickfixwithbindu-local.local/recipes/egg-curry-recipe/) | 200 | 14 | 19 | PHP warnings |
| 451 | recipes | [Egg Mushroom Fried Rice](http://quickfixwithbindu-local.local/recipes/egg-mushroom-fried-rice/) | 200 | 13 | 5 | PHP warnings |
| 471 | recipes | [Egg Rice Fusion Skillet – Quick Biryani Twist](http://quickfixwithbindu-local.local/recipes/egg-rice-fusion-skillet-quick-biryani-twist/) | 200 | 13 | 10 | PHP warnings |
| 1093 | recipes | [Eggless Orange Cranberry Shortbread Cookies \| Tiny, Buttery & Crisp](http://quickfixwithbindu-local.local/recipes/eggless-orange-cranberry-shortbread-cookies-tiny-buttery-crisp/) | 200 | 8 | 4 | PHP warnings |
| 207 | recipes | [Fermented Dosa That Tastes Like South Indian Street Food](http://quickfixwithbindu-local.local/recipes/fermented-dosa-that-tastes-like-south-indian-street-food/) | 200 | 7 | 7 | PHP warnings |
| 229 | recipes | [Fermented Honey Lemon Ginger Syrup \| Natural Cough Remedy & Immune Booster](http://quickfixwithbindu-local.local/recipes/fermented-honey-lemon-ginger-syrup-natural-cough-remedy-immune-booster/) | 200 | 3 | 7 | PHP warnings |
| 445 | recipes | [Flavor-Packed Nepali Potatoes \| Tangy, Spicy & Vegan in 15!](http://quickfixwithbindu-local.local/recipes/flavor-packed-nepali-potatoes-tangy-spicy-vegan-in-15/) | 200 | 13 | 4 | PHP warnings |
| 371 | recipes | [Flax Plantain Flatbread](http://quickfixwithbindu-local.local/recipes/flax-plantain-flatbread/) | 200 | 11 | 10 | PHP warnings |
| 1118 | recipes | [Flourless Crackers? I Didn’t Expect Them to Turn Out This Good](http://quickfixwithbindu-local.local/recipes/flourless-crackers-i-didnt-expect-them-to-turn-out-this-good/) | 200 | 10 | 8 | PHP warnings |
| 1291 | recipes | [Flourless High Protein Lentil Tofu Bake](http://quickfixwithbindu-local.local/recipes/flourless-high-protein-lentil-tofu-bake/) | 200 | 20 | 6 | PHP warnings |
| 317 | recipes | [Flourless High-Protein Moong Bean Flatbreads (Wraps)](http://quickfixwithbindu-local.local/recipes/flourless-high-protein-moong-bean-flatbreads-wraps/) | 200 | 6 | 7 | PHP warnings |
| 234 | recipes | [Flourless Low-Carb Omelet Flatbread](http://quickfixwithbindu-local.local/recipes/flourless-low-carb-omelet-flatbread/) | 200 | 4 | 6 | PHP warnings |
| 1301 | recipes | [Flourless Mung Bean & Oat Crackers](http://quickfixwithbindu-local.local/recipes/flourless-mung-bean-oat-crackers/) | 200 | 13 | 6 | PHP warnings |
| 1269 | recipes | [Flourless Mung Bean Chia Crackers](http://quickfixwithbindu-local.local/recipes/flourless-mung-bean-chia-crackers/) | 200 | 11 | 12 | PHP warnings |
| 1267 | recipes | [Flourless Mung Bean High-Protein Crackers](http://quickfixwithbindu-local.local/recipes/flourless-mung-bean-high-protein-crackers/) | 200 | 11 | 12 | PHP warnings |
| 1209 | recipes | [Flourless Red Lentil & Oats Pancakes with Yogurt Dip](http://quickfixwithbindu-local.local/recipes/flourless-red-lentil-oats-pancakes-with-yogurt-dip/) | 200 | 15 | 6 | PHP warnings |
| 1239 | recipes | [Flourless Red Lentil Seed Crackers](http://quickfixwithbindu-local.local/recipes/flourless-red-lentil-seed-crackers/) | 200 | 13 | 9 | PHP warnings |
| 1222 | recipes | [Flourless Red Lentil Soy Protein Crackers](http://quickfixwithbindu-local.local/recipes/flourless-red-lentil-soy-protein-crackers/) | 200 | 17 | 6 | PHP warnings |
| 1228 | recipes | [Flourless Red Lentil Soy Protein Crackers](http://quickfixwithbindu-local.local/recipes/flourless-red-lentil-soy-protein-crackers-2/) | 200 | 17 | 12 | PHP warnings |
| 370 | recipes | [Flourless, Oil-Free, Egg-Free Kidney-Friendly Oat Cookies](http://quickfixwithbindu-local.local/recipes/flourless-oil-free-egg-free-kidney-friendly-oat-cookies/) | 200 | 5 | 6 | PHP warnings |
| 367 | recipes | [Fluffiest Banana Pancakes](http://quickfixwithbindu-local.local/recipes/fluffiest-banana-pancakes/) | 200 | 9 | 6 | PHP warnings |
| 1164 | recipes | [Forget Bread And Try This 30g Lentil Quinoa Protein Bake 🍞](http://quickfixwithbindu-local.local/recipes/forget-bread-and-try-this-30g-lentil-quinoa-protein-bake-%f0%9f%8d%9e/) | 200 | 0 | 0 | No ingredients; No instructions; PHP warnings |
| 481 | recipes | [Fresh & Zesty Chickpea Salad](http://quickfixwithbindu-local.local/recipes/fresh-zesty-chickpea-salad/) | 200 | 12 | 12 | PHP warnings |
| 387 | recipes | [Fresh Romaine Lettuce Salad with Zesty Honey-Lemon Dressing](http://quickfixwithbindu-local.local/recipes/fresh-romaine-lettuce-salad-with-zesty-honey-lemon-dressing/) | 200 | 10 | 4 | PHP warnings |
| 378 | recipes | [Fruit Salad](http://quickfixwithbindu-local.local/recipes/fruit-salad/) | 200 | 7 | 6 | PHP warnings |
| 302 | recipes | [Fusion Taco Style Puri with Chickpea Curry](http://quickfixwithbindu-local.local/recipes/fusion-taco-style-puri-with-chickpea-curry/) | 200 | 16 | 6 | PHP warnings |
| 1289 | recipes | [Garlic Butter Green Beans Stir Fry](http://quickfixwithbindu-local.local/recipes/garlic-butter-green-beans-stir-fry/) | 200 | 7 | 8 | PHP warnings |
| 270 | recipes | [Garlic Green Beans](http://quickfixwithbindu-local.local/recipes/garlic-green-beans/) | 200 | 7 | 8 | PHP warnings |
| 459 | recipes | [Garlicky Greens in 5 Minutes! 🔥 Just 2 Ingredients & Big Flavor!](http://quickfixwithbindu-local.local/recipes/garlicky-greens-in-5-minutes-%f0%9f%94%a5-just-2-ingredients-big-flavor/) | 200 | 5 | 6 | PHP warnings |
| 304 | recipes | [Gentle Pink Beet Rice Crackers for Sensitive Stomach](http://quickfixwithbindu-local.local/recipes/gentle-pink-beet-rice-crackers-for-sensitive-stomach/) | 200 | 7 | 8 | PHP warnings |
| 297 | recipes | [Golden Onion Omelet](http://quickfixwithbindu-local.local/recipes/golden-onion-omelet/) | 200 | 8 | 7 | PHP warnings |
| 477 | recipes | [Greek Yogurt Salad with Sesame Chili Oil](http://quickfixwithbindu-local.local/recipes/greek-yogurt-salad-with-sesame-chili-oil/) | 200 | 8 | 4 | PHP warnings |
| 510 | recipes | [Grilled Cheese and Creamy Tomato Soup](http://quickfixwithbindu-local.local/recipes/grilled-cheese-and-creamy-tomato-soup/) | 200 | 11 | 8 | PHP warnings |
| 402 | recipes | [Grow Fresh Chia Microgreens in 7 Days with Just Paper Towels!](http://quickfixwithbindu-local.local/recipes/grow-fresh-chia-microgreens-in-7-days-with-just-paper-towels/) | 200 | 4 | 7 | PHP warnings |
| 210 | recipes | [Guilt Free Crunchy Chickpeas in Air Fryer](http://quickfixwithbindu-local.local/recipes/guilt-free-crunchy-chickpeas-in-air-fryer/) | 200 | 7 | 4 | PHP warnings |
| 385 | recipes | [Happy Canada Day Quick Celebrations](http://quickfixwithbindu-local.local/recipes/happy-canada-day-quick-celebrations/) | 200 | 0 | 0 | No ingredients; No instructions; PHP warnings |
| 221 | recipes | [Healthy & Filling Chickpea Rice with Yogurt Dip](http://quickfixwithbindu-local.local/recipes/healthy-filling-chickpea-rice-with-yogurt-dip/) | 200 | 15 | 8 | PHP warnings |
| 216 | recipes | [Healthy & Quick Egg Breakfast With Air Fryer](http://quickfixwithbindu-local.local/recipes/healthy-quick-egg-breakfast-with-air-fryer/) | 200 | 7 | 7 | PHP warnings |
| 437 | recipes | [Healthy Bitter Melon Potato Curry \| Great for Blood Pressure \| Karela Aloo Sabji](http://quickfixwithbindu-local.local/recipes/healthy-bitter-melon-potato-curry-great-for-blood-pressure-karela-aloo-sabji/) | 200 | 10 | 11 | PHP warnings |
| 478 | recipes | [Healthy Bitter Melon Potato Curry \| Karela Aloo Sabji](http://quickfixwithbindu-local.local/recipes/healthy-bitter-melon-potato-curry-karela-aloo-sabji/) | 200 | 10 | 15 | PHP warnings |
| 350 | recipes | [Healthy Chocolate-Dipped Breakfast Cookies](http://quickfixwithbindu-local.local/recipes/healthy-chocolate-dipped-breakfast-cookies/) | 200 | 13 | 6 | PHP warnings |
| 395 | recipes | [Healthy Christmas Bites \| Easy Low Carb Recipes](http://quickfixwithbindu-local.local/recipes/healthy-christmas-bites-easy-low-carb-recipes/) | 200 | 0 | 0 | No ingredients; No instructions; PHP warnings |
| 1145 | recipes | [Healthy Flourless Peanut Butter Cookies 🍪 (Soft & Chewy!)](http://quickfixwithbindu-local.local/recipes/healthy-flourless-peanut-butter-cookies-%f0%9f%8d%aa-soft-chewy/) | 200 | 7 | 7 | PHP warnings |
| 496 | recipes | [Healthy Methi-Tofu Stuffed Bun](http://quickfixwithbindu-local.local/recipes/healthy-methi-tofu-stuffed-bun/) | 200 | 9 | 11 | PHP warnings |
| 319 | recipes | [Healthy Omelet Lettuce Wrap with Mint-Peanut Yogurt Spread](http://quickfixwithbindu-local.local/recipes/healthy-omelet-lettuce-wrap-with-mint-peanut-yogurt-spread/) | 200 | 12 | 9 | PHP warnings |
| 1192 | recipes | [High Protein Avocado Chickpea Crackers](http://quickfixwithbindu-local.local/recipes/high-protein-avocado-chickpea-crackers/) | 200 | 15 | 7 | PHP warnings |
| 1194 | recipes | [High Protein Flourless Mung Crackers](http://quickfixwithbindu-local.local/recipes/flourless-mung-bean-protein-crackers/) | 200 | 12 | 6 | PHP warnings |
| 1308 | recipes | [High Protein Flourless Red Lentil Crackers](http://quickfixwithbindu-local.local/recipes/high-protein-flourless-red-lentil-crackers/) | 200 | 16 | 9 | PHP warnings |
| 1310 | recipes | [High Protein Flourless Red Lentil Crackers](http://quickfixwithbindu-local.local/recipes/high-protein-flourless-red-lentil-crackers-2/) | 200 | 16 | 9 | PHP warnings |
| 1220 | recipes | [High Protein Lentil Quinoa Tofu Bake](http://quickfixwithbindu-local.local/recipes/high-protein-lentil-quinoa-tofu-bake/) | 200 | 26 | 9 | PHP warnings |
| 706 | recipes | [High Protein Low Carb Cheesy Egg Roll \| Japanese-Style Omelette #lowcarb](http://quickfixwithbindu-local.local/recipes/high-protein-low-carb-cheesy-egg-roll-japanese-style-omelette-lowcarb/) | 200 | 6 | 6 | PHP warnings |
| 520 | recipes | [High Protein Moong Beans Egg Sandwich](http://quickfixwithbindu-local.local/recipes/high-protein-moong-beans-egg-sandwich/) | 200 | 11 | 8 | PHP warnings |
| 1233 | recipes | [High Protein Mung Bean Crackers](http://quickfixwithbindu-local.local/recipes/high-protein-mung-bean-crackers/) | 200 | 12 | 10 | PHP warnings |
| 1303 | recipes | [High Protein Powerhouse Crackers](http://quickfixwithbindu-local.local/recipes/flourless-mung-bean-protein-crackers-2/) | 200 | 12 | 6 | PHP warnings |
| 1237 | recipes | [High Protein Red Lentil Seed Crackers](http://quickfixwithbindu-local.local/recipes/high-protein-red-lentil-seed-crackers/) | 200 | 13 | 9 | PHP warnings |
| 1151 | recipes | [High Protein Salad: Warm Roasted Chickpea Salad with Tangy Mint Peanut Dressing \| Healthy Recipe](http://quickfixwithbindu-local.local/recipes/high-protein-salad-warm-roasted-chickpea-salad-with-tangy-mint-peanut-dressing-healthy-recipe/) | 200 | 18 | 9 | PHP warnings |
| 1211 | recipes | [High Protein Sweet Potato & Mung Bean Crackers](http://quickfixwithbindu-local.local/recipes/high-protein-sweet-potato-mung-bean-crackers/) | 200 | 14 | 10 | PHP warnings |
| 1166 | recipes | [High Protein Tofu Cauliflower Steak](http://quickfixwithbindu-local.local/recipes/high-protein-tofu-cauliflower-steak/) | 200 | 9 | 4 | PHP warnings |
| 1271 | recipes | [High Protein Tofu Spinach Pockets \| Easy Vegetarian Wraps](http://quickfixwithbindu-local.local/recipes/high-protein-tofu-spinach-pockets-easy-vegetarian-wraps/) | 200 | 16 | 7 | PHP warnings |
| 1183 | recipes | [High Protein Vegan Lentil Fritters (Flourless, Gluten-Free)](http://quickfixwithbindu-local.local/recipes/high-protein-vegan-lentil-fritters-flourless-gluten-free/) | 200 | 13 | 10 | PHP warnings |
| 709 | recipes | [High-Protein Almond Cookies in 30 Minutes (Just 3 Ingredients)](http://quickfixwithbindu-local.local/recipes/high-protein-almond-cookies-in-30-minutes-just-3-ingredients/) | 200 | 4 | 7 | PHP warnings |
| 1313 | recipes | [High-Protein Chickpea Avocado Crackers](http://quickfixwithbindu-local.local/recipes/high-protein-chickpea-avocado-crackers/) | 200 | 17 | 6 | PHP warnings |
| 1279 | recipes | [High-Protein Chickpea Crackers](http://quickfixwithbindu-local.local/recipes/high-protein-chickpea-crackers/) | 200 | 10 | 8 | PHP warnings |
| 1315 | recipes | [High-Protein Chickpea Crackers](http://quickfixwithbindu-local.local/recipes/high-protein-chickpea-crackers-2/) | 200 | 0 | 0 | No ingredients; No instructions; PHP warnings |
| 502 | recipes | [High-Protein Chickpea Tofu Salad](http://quickfixwithbindu-local.local/recipes/high-protein-chickpea-tofu-salad/) | 200 | 12 | 5 | PHP warnings |
| 1297 | recipes | [High-Protein Crispy Smoky Tofu Pockets with Avocado Dip](http://quickfixwithbindu-local.local/recipes/high-protein-crispy-smoky-tofu-pockets-with-avocado-dip/) | 200 | 17 | 10 | PHP warnings |
| 1162 | recipes | [High-Protein Flourless Crackers \| No Flour, No Eggs, Just Crunch!](http://quickfixwithbindu-local.local/recipes/high-protein-flourless-crackers-no-flour-no-eggs-just-crunch/) | 200 | 13 | 8 | PHP warnings |
| 1317 | recipes | [High-Protein Red Lentil Crackers \| Sweet Potato, Seeds & Spices](http://quickfixwithbindu-local.local/recipes/high-protein-red-lentil-crackers-sweet-potato-seeds-spices/) | 200 | 10 | 6 | PHP warnings |
| 1319 | recipes | [High-Protein Red Lentil Sweet Potato Crackers](http://quickfixwithbindu-local.local/recipes/high-protein-red-lentil-sweet-potato-crackers/) | 200 | 10 | 8 | PHP warnings |
| 1218 | recipes | [High-Protein Red Lentil Tomato Crackers](http://quickfixwithbindu-local.local/recipes/high-protein-red-lentil-tomato-crackers/) | 200 | 11 | 11 | PHP warnings |
| 696 | recipes | [High-Protein Roasted Chickpea & Green Salad](http://quickfixwithbindu-local.local/recipes/high-protein-roasted-chickpea-green-salad/) | 200 | 14 | 3 | PHP warnings |
| 1175 | recipes | [High-Protein Seed Crackers (Flourless)](http://quickfixwithbindu-local.local/recipes/high-protein-seed-crackers-flourless/) | 200 | 9 | 9 | PHP warnings |
| 1277 | recipes | [High-Protein Smashed Tofu & Watermelon Salad](http://quickfixwithbindu-local.local/recipes/high-protein-smashed-tofu-watermelon-salad/) | 200 | 23 | 8 | PHP warnings |
| 259 | recipes | [High-Protein White Beans Soup](http://quickfixwithbindu-local.local/recipes/high-protein-white-beans-soup/) | 200 | 15 | 5 | PHP warnings |
| 711 | recipes | [High-Protein Yellow Pea & Spinach Stew (Nutritious & Budget-Friendly)](http://quickfixwithbindu-local.local/recipes/high-protein-yellow-pea-spinach-stew-nutritious-budget-friendly/) | 200 | 17 | 8 | PHP warnings |
| 563 | recipes | [High-Protein, Zero-Waste Flatbread in Just 2 Ingredients!](http://quickfixwithbindu-local.local/recipes/high-protein-zero-waste-flatbread-in-just-2-ingredients/) | 200 | — | — | Missing custom recipe record; No detailed recipe markup; PHP warnings |
| 564 | recipes | [High-Protein, Zero-Waste Flatbread in Just 2 Ingredients!](http://quickfixwithbindu-local.local/recipes/high-protein-zero-waste-flatbread-in-just-2-ingredients-2/) | 200 | 10 | 5 | PHP warnings |
| 509 | recipes | [Homemade Veg Momo with Tofu-Paneer \| Dumplings with Spicy Chutney](http://quickfixwithbindu-local.local/recipes/homemade-veg-momo-with-tofu-paneer-dumplings-with-spicy-chutney/) | 200 | 23 | 8 | PHP warnings |
| 276 | recipes | [Homemade Whole Wheat Pizza with Fresh Mozzarella & Basil](http://quickfixwithbindu-local.local/recipes/homemade-whole-wheat-pizza-with-fresh-mozzarella-basil/) | 200 | 10 | 8 | PHP warnings |
| 241 | recipes | [Honey Cinnamon Milk (Grandma's Secret Cold Remedy)](http://quickfixwithbindu-local.local/recipes/honey-cinnamon-milk-grandmas-secret-cold-remedy/) | 200 | 4 | 5 | PHP warnings |
| 292 | recipes | [Honey Ginger Rosemary Drink for Cold & Cough](http://quickfixwithbindu-local.local/recipes/honey-ginger-rosemary-drink-for-cold-cough/) | 200 | 5 | 7 | PHP warnings |
| 390 | recipes | [How to Eat Rambutan](http://quickfixwithbindu-local.local/recipes/how-to-eat-rambutan/) | 200 | 1 | 4 | PHP warnings |
| 1114 | recipes | [I Never Thought Potatoes & Eggs Could Taste This Good in One Pan!](http://quickfixwithbindu-local.local/recipes/i-never-thought-potatoes-eggs-could-taste-this-good-in-one-pan/) | 200 | 13 | 7 | PHP warnings |
| 1116 | recipes | [I Never Thought Potatoes & Eggs Could Taste This Good in One Pan!](http://quickfixwithbindu-local.local/recipes/i-never-thought-potatoes-eggs-could-taste-this-good-in-one-pan-2/) | 200 | 13 | 7 | PHP warnings |
| 681 | recipes | [I Stopped Buying Pickled Jalapeños… and Made Them Better at Home](http://quickfixwithbindu-local.local/recipes/i-stopped-buying-pickled-jalapenos-and-made-them-better-at-home/) | 200 | 7 | 7 | PHP warnings |
| 1130 | recipes | [If You Don't Eat Flour, Make This High Protein Lentil & Tofu Bake (30g Protein lunch)](http://quickfixwithbindu-local.local/recipes/if-you-dont-eat-flour-make-this-high-protein-lentil-tofu-bake-30g-protein-lunch/) | 200 | 19 | 6 | PHP warnings |
| 1095 | recipes | [If You Have Almond Flour, Make These Easy Gluten-Free Crackers!](http://quickfixwithbindu-local.local/recipes/if-you-have-almond-flour-make-these-easy-gluten-free-crackers/) | 200 | 10 | 8 | PHP warnings |
| 1097 | recipes | [If You Have Almond Flour, Make These Easy Gluten-Free Crackers!](http://quickfixwithbindu-local.local/recipes/if-you-have-almond-flour-make-these-easy-gluten-free-crackers-2/) | 200 | 10 | 8 | PHP warnings |
| 1143 | recipes | [If You Have Overripe Bananas, Don’t Throw Them Out! Make This Eggless Banana Muffins](http://quickfixwithbindu-local.local/recipes/if-you-have-overripe-bananas-dont-throw-them-out-make-this-eggless-banana-muffins/) | 200 | 13 | 9 | PHP warnings |
| 1110 | recipes | [If You Have Strawberries 🍓 Make This Strawberry Milk Instead of Buying It from Store](http://quickfixwithbindu-local.local/recipes/if-you-have-strawberries-%f0%9f%8d%93-make-this-strawberry-milk-instead-of-buying-it-from-store/) | 200 | 5 | 7 | PHP warnings |
| 1112 | recipes | [If You Have Strawberries 🍓 Make This Strawberry Milk Instead of Buying It from Store](http://quickfixwithbindu-local.local/recipes/if-you-have-strawberries-%f0%9f%8d%93-make-this-strawberry-milk-instead-of-buying-it-from-store-2/) | 200 | 0 | 0 | No ingredients; No instructions; PHP warnings |
| 1087 | recipes | [If you love Oranges and Cranberries, make this!  Eggless Better than Bakery Shortbread Bites](http://quickfixwithbindu-local.local/recipes/if-you-love-oranges-and-cranberries-make-this-eggless-better-than-bakery-shortbread-bites/) | 200 | 9 | 7 | PHP warnings |
| 310 | recipes | [Instant No Flour Protein Pancakes \| Lentil Oats Recipe](http://quickfixwithbindu-local.local/recipes/instant-no-flour-protein-pancakes-lentil-oats-recipe/) | 200 | 14 | 8 | PHP warnings |
| 321 | recipes | [Iron-Rich Taro Leaf Curry with Paneer](http://quickfixwithbindu-local.local/recipes/iron-rich-taro-leaf-curry-with-paneer/) | 200 | 10 | 9 | PHP warnings |
| 389 | recipes | [Irresistible Vegan Meatball](http://quickfixwithbindu-local.local/recipes/irresistible-vegan-meatball/) | 200 | 0 | 0 | No ingredients; No instructions; PHP warnings |
| 262 | recipes | [Italian-Style Chestnuts at Home](http://quickfixwithbindu-local.local/recipes/italian-style-chestnuts-at-home/) | 200 | 5 | 6 | PHP warnings |
| 465 | recipes | [Japanese Layered Omelet \| Cheesy & Colorful Breakfast!](http://quickfixwithbindu-local.local/recipes/japanese-layered-omelet-cheesy-colorful-breakfast/) | 200 | 8 | 16 | PHP warnings |
| 201 | recipes | [Just 2 Ingredients Oats and Spinach Flatbread & Stuffed Buns](http://quickfixwithbindu-local.local/recipes/just-2-ingredients-oats-and-spinach-flatbread-stuffed-buns/) | 200 | 8 | 7 | PHP warnings |
| 507 | recipes | [Karela Aloo Sabji (Bitter Melon and Potato Stir-fry)](http://quickfixwithbindu-local.local/recipes/karela-aloo-sabji-bitter-melon-and-potato-stir-fry/) | 200 | 10 | 11 | PHP warnings |
| 1245 | recipes | [Keto Cheesy Almond Flour Crackers](http://quickfixwithbindu-local.local/recipes/keto-cheesy-almond-flour-crackers/) | 200 | 10 | 8 | PHP warnings |
| 269 | recipes | [Keto Friendly Flourless High Protein Flatbread \| Sprouted Moong Beans & Dill](http://quickfixwithbindu-local.local/recipes/keto-friendly-flourless-high-protein-flatbread-sprouted-moong-beans-dill/) | 200 | 13 | 8 | PHP warnings |
| 243 | recipes | [Keto-Friendly Avocado Egg Boat](http://quickfixwithbindu-local.local/recipes/keto-friendly-avocado-egg-boat/) | 200 | 6 | 7 | PHP warnings |
| 458 | recipes | [Kettle-Style Air-Fried Potato Rolls](http://quickfixwithbindu-local.local/recipes/kettle-style-air-fried-potato-rolls/) | 200 | 5 | 6 | PHP warnings |
| 1293 | recipes | [Kidney-Friendly Applesauce Oat Cookies (Flourless, Oil-Free)](http://quickfixwithbindu-local.local/recipes/kidney-friendly-applesauce-oat-cookies-flourless-oil-free/) | 200 | 5 | 6 | PHP warnings |
| 417 | recipes | [Leftover Quinoa Veggie Stir-Fry](http://quickfixwithbindu-local.local/recipes/leftover-quinoa-veggie-stir-fry/) | 200 | 13 | 5 | PHP warnings |
| 425 | recipes | [Leftover Rice Crackers \| Gluten-Free & Light](http://quickfixwithbindu-local.local/recipes/leftover-rice-crackers-gluten-free-light/) | 200 | 5 | 5 | PHP warnings |
| 1124 | recipes | [Looks Like Cake.. But It’s My Favorite Flourless High-Protein SAVORY Lunch!](http://quickfixwithbindu-local.local/recipes/looks-like-cake-but-its-my-favorite-flourless-high-protein-savory-lunch/) | 200 | 14 | 6 | PHP warnings |
| 376 | recipes | [Love-at-First-Bite \| 10-Minute Valentine’s Breakfast \| Veggie Heart Omelets](http://quickfixwithbindu-local.local/recipes/love-at-first-bite-10-minute-valentines-breakfast-veggie-heart-omelets/) | 200 | 6 | 5 | PHP warnings |
| 278 | recipes | [Low Carb Cauliflower Omelet \| High Protein Healthy Breakfast](http://quickfixwithbindu-local.local/recipes/low-carb-cauliflower-omelet-high-protein-healthy-breakfast/) | 200 | 8 | 13 | PHP warnings |
| 384 | recipes | [Low Carb Tomato Omelet That Looks Like Flatbread](http://quickfixwithbindu-local.local/recipes/low-carb-tomato-omelet-that-looks-like-flatbread/) | 200 | 7 | 10 | PHP warnings |
| 707 | recipes | [Low Sugar Eggless Flower Cookies\| Easy Two-Tone Floral Shortbread](http://quickfixwithbindu-local.local/recipes/low-sugar-eggless-flower-cookies-easy-two-tone-floral-shortbread/) | 200 | 10 | 9 | PHP warnings |
| 708 | recipes | [Low Sugar Eggless Flower Cookies\| Easy Two-Tone Floral Shortbread](http://quickfixwithbindu-local.local/recipes/low-sugar-eggless-flower-cookies-easy-two-tone-floral-shortbread-2/) | 200 | 10 | 9 | PHP warnings |
| 226 | recipes | [Low-Carb Crispy Cabbage Flatbread](http://quickfixwithbindu-local.local/recipes/low-carb-crispy-cabbage-flatbread/) | 200 | 10 | 6 | PHP warnings |
| 228 | recipes | [Low-Carb No-Flip Mushroom Omelet \| Tomato Jalapeño Egg Breakfast](http://quickfixwithbindu-local.local/recipes/low-carb-no-flip-mushroom-omelet-tomato-jalapeno-egg-breakfast/) | 200 | 8 | 7 | PHP warnings |
| 279 | recipes | [Low-carb omelet that looks like a flatbread](http://quickfixwithbindu-local.local/recipes/low-carb-omelet-that-looks-like-a-flatbread/) | 200 | 4 | 6 | PHP warnings |
| 439 | recipes | [Maggi Gets a Protein Makeover! Tofu, Egg & Masala Magic in 1 Bowl](http://quickfixwithbindu-local.local/recipes/maggi-gets-a-protein-makeover-tofu-egg-masala-magic-in-1-bowl/) | 200 | 13 | 8 | PHP warnings |
| 1181 | recipes | [Mango Banana Orange Protein Bowl](http://quickfixwithbindu-local.local/recipes/mango-banana-orange-protein-bowl/) | 200 | 10 | 4 | PHP warnings |
| 349 | recipes | [Mango Mojito in Minutes 🥭 \| Summer’s Best Drink!](http://quickfixwithbindu-local.local/recipes/mango-mojito-in-minutes-%f0%9f%a5%ad-summers-best-drink/) | 200 | 7 | 5 | PHP warnings |
| 1190 | recipes | [Mango Orange Banana Protein Ice Cream](http://quickfixwithbindu-local.local/recipes/mango-orange-banana-protein-ice-cream/) | 200 | 11 | 5 | PHP warnings |
| 1241 | recipes | [Marry Me Chickpeas with Spinach](http://quickfixwithbindu-local.local/recipes/marry-me-chickpeas-with-spinach/) | 200 | 4 | 1 | PHP warnings |
| 483 | recipes | [Masala Maggi Korean Style \| 10-Minute Spicy Fusion Noodles](http://quickfixwithbindu-local.local/recipes/masala-maggi-korean-style-10-minute-spicy-fusion-noodles/) | 200 | 12 | 4 | PHP warnings |
| 515 | recipes | [Mom's Birthday Feast for Dad](http://quickfixwithbindu-local.local/recipes/moms-birthday-feast-for-dad/) | 200 | 4 | 0 | No instructions; PHP warnings |
| 260 | recipes | [Morning Detox Drink for Toned Body, Glowing Skin & Better Metabolism \| Honey Lemon Ginger Chia](http://quickfixwithbindu-local.local/recipes/morning-detox-drink-for-toned-body-glowing-skin-better-metabolism-honey-lemon-ginger-chia/) | 200 | 5 | 6 | PHP warnings |
| 1172 | recipes | [Mung Bean & Chickpea Flour High Protein Crackers](http://quickfixwithbindu-local.local/recipes/mung-bean-chickpea-flour-high-protein-crackers/) | 200 | 12 | 9 | PHP warnings |
| 1214 | recipes | [Mung Bean & Tofu Tamagoyaki with Hemp Yogurt Dip](http://quickfixwithbindu-local.local/recipes/mung-bean-tofu-tamagoyaki-with-hemp-yogurt-dip/) | 200 | 16 | 7 | PHP warnings |
| 1224 | recipes | [Mung Bean Tofu Tamagoyaki Rolls with Tomato Basil Gravy](http://quickfixwithbindu-local.local/recipes/mung-bean-tofu-tamagoyaki-rolls-with-tomato-basil-gravy/) | 200 | 20 | 19 | PHP warnings |
| 316 | recipes | [Mushroom and Asparagus Stir-Fry](http://quickfixwithbindu-local.local/recipes/mushroom-and-asparagus-stir-fry/) | 200 | 11 | 11 | PHP warnings |
| 467 | recipes | [Mushroom and Asparagus Stir-Fry & Quinoa Salad Combo](http://quickfixwithbindu-local.local/recipes/mushroom-and-asparagus-stir-fry-quinoa-salad-combo/) | 200 | 13 | 4 | PHP warnings |
| 486 | recipes | [Mushroom Stir Fry Gravy](http://quickfixwithbindu-local.local/recipes/mushroom-stir-fry-gravy/) | 200 | 15 | 13 | PHP warnings |
| 426 | recipes | [My Daughter Says My Veg Momos 🥟 Beat Any 5-Star Hotel !](http://quickfixwithbindu-local.local/recipes/my-daughter-says-my-veg-momos-%f0%9f%a5%9f-beat-any-5-star-hotel/) | 200 | 0 | 0 | No ingredients; No instructions; PHP warnings |
| 418 | recipes | [My Fluffy Belgian Waffle Recipe](http://quickfixwithbindu-local.local/recipes/my-fluffy-belgian-waffle-recipe/) | 200 | 8 | 5 | PHP warnings |
| 508 | recipes | [My Secret Tomato Pickle Recipe for Rice & Roti](http://quickfixwithbindu-local.local/recipes/my-secret-tomato-pickle-recipe-for-rice-roti/) | 200 | 13 | 11 | PHP warnings |
| 246 | recipes | [Natural Cough & Immunity Shot \| Orange, Lemon, Ginger & Turmeric](http://quickfixwithbindu-local.local/recipes/natural-cough-immunity-shot-orange-lemon-ginger-turmeric/) | 200 | 7 | 5 | PHP warnings |
| 215 | recipes | [Natural Cough And Cold Remedy](http://quickfixwithbindu-local.local/recipes/natural-cough-and-cold-remedy/) | 200 | 10 | 10 | PHP warnings |
| 204 | recipes | [Natural Cough and Cold Syrup with Just 3 Ingredients \| Fermented Ginger-Lemon-Honey Drink](http://quickfixwithbindu-local.local/recipes/natural-cough-and-cold-syrup-with-just-3-ingredients-fermented-ginger-lemon-honey-drink/) | 200 | 3 | 6 | PHP warnings |
| 211 | recipes | [Natural Cough Syrup Passed Down for Generations](http://quickfixwithbindu-local.local/recipes/natural-cough-syrup-passed-down-for-generations/) | 200 | 3 | 3 | PHP warnings |
| 212 | recipes | [Natural Cough Syrup with 3 Ingredients \| Honey, Ginger & Lime](http://quickfixwithbindu-local.local/recipes/natural-cough-syrup-with-3-ingredients-honey-ginger-lime/) | 200 | 3 | 3 | PHP warnings |
| 197 | recipes | [Natural Cough Syrup: Honey, Ginger & Lemon Remedy](http://quickfixwithbindu-local.local/recipes/natural-cough-syrup-honey-ginger-lemon-remedy/) | 200 | 3 | 5 | PHP warnings |
| 277 | recipes | [Natural Detox Drink: Ginger Turmeric Immunity Cubes & Shots](http://quickfixwithbindu-local.local/recipes/natural-detox-drink-ginger-turmeric-immunity-cubes-shots/) | 200 | 7 | 7 | PHP warnings |
| 320 | recipes | [Natural Honey Lemon Ginger Detox Drink](http://quickfixwithbindu-local.local/recipes/natural-honey-lemon-ginger-detox-drink/) | 200 | 4 | 4 | PHP warnings |
| 366 | recipes | [Nepali-Style Potato Salad (Chukauni)](http://quickfixwithbindu-local.local/recipes/nepali-style-potato-salad-chukauni/) | 200 | 12 | 10 | PHP warnings |
| 295 | recipes | [Never Imagined Quinoa and Banana Could Make This Soft, Wholesome Loaf!](http://quickfixwithbindu-local.local/recipes/never-imagined-quinoa-and-banana-could-make-this-soft-wholesome-loaf/) | 200 | 14 | 6 | PHP warnings |
| 715 | recipes | [No Flour Flatbread \| Potato Chickpea Healthy Bread \| Gluten-Free Veggie Pancake](http://quickfixwithbindu-local.local/recipes/no-flour-flatbread-potato-chickpea-healthy-bread-gluten-free-veggie-pancake/) | 200 | 9 | 4 | PHP warnings |
| 284 | recipes | [No Flour Oats Avocado Pancake 🥑 \| Soft, Creamy & Gluten-Free](http://quickfixwithbindu-local.local/recipes/no-flour-oats-avocado-pancake-%f0%9f%a5%91-soft-creamy-gluten-free/) | 200 | 7 | 3 | PHP warnings |
| 308 | recipes | [No Flour Protein Pancake \| Lentils Oats Recipe](http://quickfixwithbindu-local.local/recipes/no-flour-protein-pancake-lentils-oats-recipe/) | 200 | 14 | 9 | PHP warnings |
| 677 | recipes | [No Flour! No Egg! Oats Zucchini Flatbread You’ll Make Every Week](http://quickfixwithbindu-local.local/recipes/no-flour-no-egg-oats-zucchini-flatbread-youll-make-every-week/) | 200 | 7 | 4 | PHP warnings |
| 380 | recipes | [No Flour, No Eggs! High-Protein Gluten-Free Lentil Flatbread](http://quickfixwithbindu-local.local/recipes/no-flour-no-eggs-high-protein-gluten-free-lentil-flatbread/) | 200 | 18 | 12 | PHP warnings |
| 332 | recipes | [No Sugar Fluffiest Banana Pancakes That Taste Like Dessert](http://quickfixwithbindu-local.local/recipes/no-sugar-fluffiest-banana-pancakes-that-taste-like-dessert/) | 200 | 9 | 6 | PHP warnings |
| 497 | recipes | [No-Flip Corn Pizza in One Pan!](http://quickfixwithbindu-local.local/recipes/no-flip-corn-pizza-in-one-pan/) | 200 | 9 | 8 | PHP warnings |
| 255 | recipes | [No-Flip Spinach Cheese Omelet](http://quickfixwithbindu-local.local/recipes/no-flip-spinach-cheese-omelet/) | 200 | 8 | 9 | PHP warnings |
| 263 | recipes | [No-Flip Spinach Cheese Omelet](http://quickfixwithbindu-local.local/recipes/no-flip-spinach-cheese-omelet-2/) | 200 | 8 | 6 | PHP warnings |
| 359 | recipes | [No-Flip Spinach Cheese Omelet](http://quickfixwithbindu-local.local/recipes/no-flip-spinach-cheese-omelet-3/) | 200 | 7 | 7 | PHP warnings |
| 330 | recipes | [Oats & Sorghum Avocado Flatbread](http://quickfixwithbindu-local.local/recipes/oats-sorghum-avocado-flatbread/) | 200 | 9 | 7 | PHP warnings |
| 327 | recipes | [Oats & Sorghum Flatbread](http://quickfixwithbindu-local.local/recipes/oats-sorghum-flatbread/) | 200 | 9 | 6 | PHP warnings |
| 237 | recipes | [One Pan Low-GI Oats & Sorghum Avocado Flatbread](http://quickfixwithbindu-local.local/recipes/one-pan-low-gi-oats-sorghum-avocado-flatbread/) | 200 | 9 | 7 | PHP warnings |
| 717 | recipes | [Orange Cranberry Shortbread Cookies – Bite-Sized, Eggless](http://quickfixwithbindu-local.local/recipes/orange-cranberry-shortbread-cookies-bite-sized-eggless/) | 200 | 9 | 10 | PHP warnings |
| 360 | recipes | [Pattypan Stir-Fry](http://quickfixwithbindu-local.local/recipes/pattypan-stir-fry/) | 200 | 7 | 5 | PHP warnings |
| 391 | recipes | [Peanut Chocolate Date Bars](http://quickfixwithbindu-local.local/recipes/peanut-chocolate-date-bars/) | 200 | 6 | 6 | PHP warnings |
| 338 | recipes | [Perfect Stovetop Popcorn](http://quickfixwithbindu-local.local/recipes/perfect-stovetop-popcorn/) | 200 | 3 | 7 | PHP warnings |
| 688 | recipes | [Pickled Red Onions & Jalapenos at Home , Not Buying Them Anymore](http://quickfixwithbindu-local.local/recipes/pickled-red-onions-jalapenos-at-home-not-buying-them-anymore/) | 200 | 13 | 7 | PHP warnings |
| 348 | recipes | [Plant-Based High-Fiber Meal Under $5 \| Leftover Quinoa Stir-Fry](http://quickfixwithbindu-local.local/recipes/plant-based-high-fiber-meal-under-5-leftover-quinoa-stir-fry/) | 200 | 13 | 5 | PHP warnings |
| 249 | recipes | [Pomegranate Chocolate Bites](http://quickfixwithbindu-local.local/recipes/pomegranate-chocolate-bites/) | 200 | 4 | 6 | PHP warnings |
| 341 | recipes | [Protein Pockets](http://quickfixwithbindu-local.local/recipes/protein-pockets/) | 200 | 0 | 0 | No ingredients; No instructions; PHP warnings |
| 281 | recipes | [Protein-Packed Black Eyed Beans Soup](http://quickfixwithbindu-local.local/recipes/protein-packed-black-eyed-beans-soup/) | 200 | 14 | 6 | PHP warnings |
| 198 | recipes | [Protein-Packed Breakfast Cookies](http://quickfixwithbindu-local.local/recipes/protein-packed-breakfast-cookies/) | 200 | 11 | 4 | PHP warnings |
| 361 | recipes | [Protein-Packed Creamy Pasta \| No Straining, No Fuss](http://quickfixwithbindu-local.local/recipes/protein-packed-creamy-pasta-no-straining-no-fuss/) | 200 | 10 | 4 | PHP warnings |
| 517 | recipes | [Protein-Packed Quinoa Tofu Veggie Bowl \| Healthy, Easy & Delicious!](http://quickfixwithbindu-local.local/recipes/protein-packed-quinoa-tofu-veggie-bowl-healthy-easy-delicious/) | 200 | 14 | 8 | PHP warnings |
| 430 | recipes | [Protein-Packed Veggie Burger](http://quickfixwithbindu-local.local/recipes/protein-packed-veggie-burger/) | 200 | 14 | 0 | No instructions; PHP warnings |
| 272 | recipes | [Protein-Rich Black Eyed Beans Soup](http://quickfixwithbindu-local.local/recipes/protein-rich-black-eyed-beans-soup/) | 200 | 15 | 7 | PHP warnings |
| 196 | recipes | [Protein-Rich Black-Eyed Peas Soup](http://quickfixwithbindu-local.local/recipes/protein-rich-black-eyed-peas-soup/) | 200 | 14 | 11 | PHP warnings |
| 413 | recipes | [Pure Semolina Momo](http://quickfixwithbindu-local.local/recipes/pure-semolina-momo/) | 200 | 0 | 0 | No ingredients; No instructions; PHP warnings |
| 432 | recipes | [Quick & Easy Bok Choy Stir Fry Recipe](http://quickfixwithbindu-local.local/recipes/quick-easy-bok-choy-stir-fry-recipe/) | 200 | 7 | 8 | PHP warnings |
| 487 | recipes | [Quick & Easy Vegan Mushroom Pasta](http://quickfixwithbindu-local.local/recipes/quick-easy-vegan-mushroom-pasta/) | 200 | 8 | 7 | PHP warnings |
| 436 | recipes | [Quick & Healthy Dilly Chickpeas](http://quickfixwithbindu-local.local/recipes/quick-healthy-dilly-chickpeas/) | 200 | 9 | 4 | PHP warnings |
| 254 | recipes | [Quick & Healthy Overnight Oats Breakfast](http://quickfixwithbindu-local.local/recipes/quick-healthy-overnight-oats-breakfast/) | 200 | 4 | 3 | PHP warnings |
| 340 | recipes | [Quick & Tasty Breakfast Idea You Can Make in Minutes!](http://quickfixwithbindu-local.local/recipes/quick-tasty-breakfast-idea-you-can-make-in-minutes/) | 200 | 7 | 0 | No instructions; PHP warnings |
| 346 | recipes | [Quick and Easy High Protein Plant-Based Soup](http://quickfixwithbindu-local.local/recipes/quick-and-easy-high-protein-plant-based-soup/) | 200 | 0 | 0 | No ingredients; No instructions; PHP warnings |
| 522 | recipes | [Quick and Easy Lentil Soup, Bok Choy Stir Fry, Rice & Tomato Chutney](http://quickfixwithbindu-local.local/recipes/quick-and-easy-lentil-soup-bok-choy-stir-fry-rice-tomato-chutney/) | 200 | 23 | 13 | PHP warnings |
| 505 | recipes | [Quick Cauliflower Stir Fry](http://quickfixwithbindu-local.local/recipes/quick-cauliflower-stir-fry/) | 200 | 10 | 8 | PHP warnings |
| 495 | recipes | [Quick Cucumber Yogurt Salad](http://quickfixwithbindu-local.local/recipes/quick-cucumber-yogurt-salad/) | 200 | 6 | 5 | PHP warnings |
| 447 | recipes | [Quick Egg & Mushroom Sandwich](http://quickfixwithbindu-local.local/recipes/quick-egg-mushroom-sandwich/) | 200 | 10 | 5 | PHP warnings |
| 372 | recipes | [Quick Fruit Salad \| Summer Fruit Bowl with Chia seeds & Honey](http://quickfixwithbindu-local.local/recipes/quick-fruit-salad-summer-fruit-bowl-with-chia-seeds-honey/) | 200 | 7 | 4 | PHP warnings |
| 334 | recipes | [Quick High-Protein Soup with Zucchini & Lentils](http://quickfixwithbindu-local.local/recipes/quick-high-protein-soup-with-zucchini-lentils/) | 200 | 10 | 3 | PHP warnings |
| 227 | recipes | [QUICK LOW CARB CABBAGE OMELET \| EASY HIGH PROTEIN, LOW CARB BREAKFAST](http://quickfixwithbindu-local.local/recipes/quick-low-carb-cabbage-omelet-easy-high-protein-low-carb-breakfast/) | 200 | 6 | 5 | PHP warnings |
| 448 | recipes | [Quick Mushroom and Egg Frittata](http://quickfixwithbindu-local.local/recipes/quick-mushroom-and-egg-frittata/) | 200 | 7 | 9 | PHP warnings |
| 461 | recipes | [Quick Mushroom and Egg Frittata](http://quickfixwithbindu-local.local/recipes/quick-mushroom-and-egg-frittata-2/) | 200 | 8 | 9 | PHP warnings |
| 392 | recipes | [Quick Omelet Wrap with Veggies & Cheese](http://quickfixwithbindu-local.local/recipes/quick-omelet-wrap-with-veggies-cheese/) | 200 | 9 | 10 | PHP warnings |
| 268 | recipes | [Quick Quinoa Veggie Stir-Fry](http://quickfixwithbindu-local.local/recipes/quick-quinoa-veggie-stir-fry/) | 200 | 13 | 5 | PHP warnings |
| 368 | recipes | [Quick Red Lentil Spinach Soup](http://quickfixwithbindu-local.local/recipes/quick-red-lentil-spinach-soup/) | 200 | 11 | 8 | PHP warnings |
| 353 | recipes | [Quick Stuffed Cabbage Rolls with Spicy Nutty Sauce](http://quickfixwithbindu-local.local/recipes/quick-stuffed-cabbage-rolls-with-spicy-nutty-sauce/) | 200 | 23 | 13 | PHP warnings |
| 452 | recipes | [Quick Veg Stir Fry](http://quickfixwithbindu-local.local/recipes/quick-veg-stir-fry/) | 200 | 14 | 4 | PHP warnings |
| 290 | recipes | [Quick Zucchini Edamame Soup with Tofu](http://quickfixwithbindu-local.local/recipes/quick-zucchini-edamame-soup-with-tofu/) | 200 | 13 | 9 | PHP warnings |
| 382 | recipes | [Quinoa & Oats Quick Easy and Healthy Pancakes](http://quickfixwithbindu-local.local/recipes/quinoa-oats-quick-easy-and-healthy-pancakes/) | 200 | 7 | 6 | PHP warnings |
| 242 | recipes | [Quinoa and Banana Turns Into the Softest Healthy Loaf](http://quickfixwithbindu-local.local/recipes/quinoa-and-banana-turns-into-the-softest-healthy-loaf/) | 200 | 12 | 6 | PHP warnings |
| 209 | recipes | [Quinoa Crepe Wraps with Spiced Potato Filling](http://quickfixwithbindu-local.local/recipes/quinoa-crepe-wraps-with-spiced-potato-filling/) | 200 | 14 | 19 | PHP warnings |
| 1207 | recipes | [Quinoa Kidney Bean Protein Patties](http://quickfixwithbindu-local.local/recipes/quinoa-kidney-bean-protein-patties/) | 200 | 23 | 6 | PHP warnings |
| 485 | recipes | [Quinoa Meets Cornmeal Dosa Style Flatbread](http://quickfixwithbindu-local.local/recipes/quinoa-meets-cornmeal-dosa-style-flatbread/) | 200 | 15 | 7 | PHP warnings |
| 476 | recipes | [Quinoa Oats Pancakes](http://quickfixwithbindu-local.local/recipes/quinoa-oats-pancakes/) | 200 | 5 | 3 | PHP warnings |
| 271 | recipes | [Rainy Day Tomato Zucchini Soup](http://quickfixwithbindu-local.local/recipes/rainy-day-tomato-zucchini-soup/) | 200 | 11 | 5 | PHP warnings |
| 224 | recipes | [Raspberry Chia Pudding](http://quickfixwithbindu-local.local/recipes/raspberry-chia-pudding/) | 200 | 6 | 5 | PHP warnings |
| 250 | recipes | [Raspberry Chia Pudding](http://quickfixwithbindu-local.local/recipes/raspberry-chia-pudding-2/) | 200 | 6 | 4 | PHP warnings |
| 1295 | recipes | [Red Lentil & Seed Flourless Crackers](http://quickfixwithbindu-local.local/recipes/red-lentil-seed-flourless-crackers/) | 200 | 0 | 0 | No ingredients; No instructions; PHP warnings |
| 1216 | recipes | [Red Lentil & Tomato Flourless Crackers](http://quickfixwithbindu-local.local/recipes/red-lentil-tomato-flourless-crackers/) | 200 | 11 | 11 | PHP warnings |
| 213 | recipes | [Refreshing Peach Chia Smoothie](http://quickfixwithbindu-local.local/recipes/refreshing-peach-chia-smoothie/) | 200 | 5 | 5 | PHP warnings |
| 307 | recipes | [Regrowing Chia Microgreens in 7 Days](http://quickfixwithbindu-local.local/recipes/regrowing-chia-microgreens-in-7-days/) | 200 | 4 | 7 | PHP warnings |
| 339 | recipes | [Restaurant Style Garlic Green Beans](http://quickfixwithbindu-local.local/recipes/restaurant-style-garlic-green-beans/) | 200 | 7 | 8 | PHP warnings |
| 265 | recipes | [Rice Paper Samosa](http://quickfixwithbindu-local.local/recipes/rice-paper-samosa/) | 200 | 12 | 8 | PHP warnings |
| 1243 | recipes | [Rich & Chewy Coconut Chocolate Bites (Fiber-Rich, No Added Sugar)](http://quickfixwithbindu-local.local/recipes/rich-chewy-coconut-chocolate-bites-fiber-rich-no-added-sugar/) | 200 | 8 | 7 | PHP warnings |
| 1153 | recipes | [Roasted Cauliflower Steaks with Creamy Tofu-Tomato Gravy](http://quickfixwithbindu-local.local/recipes/roasted-cauliflower-steaks-with-creamy-tofu-tomato-gravy/) | 200 | 18 | 6 | PHP warnings |
| 456 | recipes | [Roasted Mushroom Salad That Wakes You Up](http://quickfixwithbindu-local.local/recipes/roasted-mushroom-salad-that-wakes-you-up/) | 200 | 12 | 4 | PHP warnings |
| 256 | recipes | [Roasted Squash and Bell Pepper Soup](http://quickfixwithbindu-local.local/recipes/roasted-squash-and-bell-pepper-soup/) | 200 | 3 | 2 | PHP warnings |
| 289 | recipes | [Roasted Squash Soup](http://quickfixwithbindu-local.local/recipes/roasted-squash-soup/) | 200 | 12 | 6 | PHP warnings |
| 203 | recipes | [Saucy Crispy Pan Fried Momo with Soy-Sesame Glaze](http://quickfixwithbindu-local.local/recipes/saucy-crispy-pan-fried-momo-with-soy-sesame-glaze/) | 200 | 23 | 6 | PHP warnings |
| 462 | recipes | [Savory Buckwheat Pancake](http://quickfixwithbindu-local.local/recipes/savory-buckwheat-pancake/) | 200 | 10 | 7 | PHP warnings |
| 506 | recipes | [Savory Cornmeal Waffles](http://quickfixwithbindu-local.local/recipes/savory-cornmeal-waffles/) | 200 | 9 | 6 | PHP warnings |
| 208 | recipes | [Seed Crackers That Are Better Than Store Bought \| Naturally Gluten-Free, High Fiber & Healthy Fats](http://quickfixwithbindu-local.local/recipes/seed-crackers-that-are-better-than-store-bought-naturally-gluten-free-high-fiber-healthy-fats/) | 200 | 8 | 10 | PHP warnings |
| 525 | recipes | [Semolina Momos with Veggie Tofu Filling and Spicy Jhol Chutney](http://quickfixwithbindu-local.local/recipes/semolina-momos-with-veggie-tofu-filling-and-spicy-jhol-chutney/) | 200 | 24 | 26 | PHP warnings |
| 713 | recipes | [She Took the First Bite… and This Happened 🧡 #waffle #belgianwaffle @ShreyaPanthee #mukbang](http://quickfixwithbindu-local.local/recipes/she-took-the-first-bite-and-this-happened-%f0%9f%a7%a1-waffle-belgianwaffle-shreyapanthee-mukbang/) | 200 | 8 | 5 | PHP warnings |
| 354 | recipes | [Signature Spicy Mushroom Salad](http://quickfixwithbindu-local.local/recipes/signature-spicy-mushroom-salad/) | 200 | 11 | 9 | PHP warnings |
| 695 | recipes | [Simple Cauliflower Curry](http://quickfixwithbindu-local.local/recipes/simple-cauliflower-curry/) | 200 | 14 | 12 | PHP warnings |
| 453 | recipes | [Simple Festive Meal That Feels Like Home \| Okra Curry, Beaten Rice & Omelet](http://quickfixwithbindu-local.local/recipes/simple-festive-meal-that-feels-like-home-okra-curry-beaten-rice-omelet/) | 200 | 12 | 7 | PHP warnings |
| 252 | recipes | [Smash Chickpeas and Airfry for a Crunchy, Guilt-Free Snack](http://quickfixwithbindu-local.local/recipes/smash-chickpeas-and-airfry-for-a-crunchy-guilt-free-snack/) | 200 | 6 | 7 | PHP warnings |
| 433 | recipes | [So Good, It Never Lasts! \| Tomato Pickle That Vanishes Fast](http://quickfixwithbindu-local.local/recipes/so-good-it-never-lasts-tomato-pickle-that-vanishes-fast/) | 200 | 0 | 0 | No ingredients; No instructions; PHP warnings |
| 676 | recipes | [Soft & Chewy Eggless Whole Wheat Chocolate Chunk Cookies](http://quickfixwithbindu-local.local/recipes/soft-chewy-eggless-whole-wheat-chocolate-chunk-cookies/) | 200 | 9 | 10 | PHP warnings |
| 1103 | recipes | [Soft Whole Wheat Bread with Flax \| Less Yeast & Slow Rise](http://quickfixwithbindu-local.local/recipes/soft-whole-wheat-bread-with-flax-less-yeast-slow-rise/) | 200 | 13 | 9 | PHP warnings |
| 331 | recipes | [Spice Lovers Pickle 🌶️ Sweet, Tangy & Fiery](http://quickfixwithbindu-local.local/recipes/spice-lovers-pickle-%f0%9f%8c%b6%ef%b8%8f-sweet-tangy-fiery/) | 200 | 6 | 0 | No instructions; PHP warnings |
| 398 | recipes | [Spice Lovers Pickle 🌶️ Sweet, Tangy & Fiery](http://quickfixwithbindu-local.local/recipes/spice-lovers-pickle-%f0%9f%8c%b6%ef%b8%8f-sweet-tangy-fiery-2/) | 200 | 18 | 9 | PHP warnings |
| 1287 | recipes | [Spicy Frozen Tofu Sandwich](http://quickfixwithbindu-local.local/recipes/spicy-frozen-tofu-sandwich/) | 200 | 10 | 12 | PHP warnings |
| 356 | recipes | [Spicy Mushroom Maggi in Minutes!](http://quickfixwithbindu-local.local/recipes/spicy-mushroom-maggi-in-minutes/) | 200 | 9 | 9 | PHP warnings |
| 401 | recipes | [Spicy Mushroom Noodles in Minutes!](http://quickfixwithbindu-local.local/recipes/spicy-mushroom-noodles-in-minutes/) | 200 | 9 | 9 | PHP warnings |
| 1284 | recipes | [Spicy Sesame Tofu Wraps with Greek Yogurt Dip](http://quickfixwithbindu-local.local/recipes/spicy-sesame-tofu-wraps-with-greek-yogurt-dip/) | 200 | 22 | 5 | PHP warnings |
| 489 | recipes | [Spicy Vegetarian Chili](http://quickfixwithbindu-local.local/recipes/spicy-vegetarian-chili/) | 200 | 17 | 10 | PHP warnings |
| 1107 | recipes | [Stop Buying Store-Bought Cookies! Make These Healthy Protein-Packed Cookies Instead](http://quickfixwithbindu-local.local/recipes/stop-buying-store-bought-cookies-make-these-healthy-protein-packed-cookies-instead/) | 200 | 10 | 4 | PHP warnings |
| 1147 | recipes | [Stop Chopping Mushrooms! This 10-Minute Garlic Curry Is a Weeknight Game-Changer](http://quickfixwithbindu-local.local/recipes/stop-chopping-mushrooms-this-10-minute-garlic-curry-is-a-weeknight-game-changer/) | 200 | 12 | 7 | PHP warnings |
| 1149 | recipes | [Stop Eating Flour ! Make This 30g Protein "Bloat-Free" Bake Instead](http://quickfixwithbindu-local.local/recipes/stop-eating-flour-make-this-30g-protein-bloat-free-bake-instead/) | 200 | 26 | 11 | PHP warnings |
| 1122 | recipes | [Stop Frying Potatoes! Just Add Eggs and veggies... The Result Will Surprise You!](http://quickfixwithbindu-local.local/recipes/stop-frying-potatoes-just-add-eggs-and-veggies-the-result-will-surprise-you/) | 200 | 13 | 7 | PHP warnings |
| 1120 | recipes | [Stop making boring eggs! Try this Asparagus Roll](http://quickfixwithbindu-local.local/recipes/stop-making-boring-eggs-try-this-asparagus-roll/) | 200 | 5 | 0 | No instructions; PHP warnings |
| 1126 | recipes | [Stop Making Boring Lentils! Try This 30g Protein Savory Bake 🍄#noflourcake #savorycake #noeggs](http://quickfixwithbindu-local.local/recipes/stop-making-boring-lentils-try-this-30g-protein-savory-bake-%f0%9f%8d%84noflourcake-savorycake-noeggs/) | 200 | 14 | 6 | PHP warnings |
| 1089 | recipes | [Strawberry Chia Pudding \| Quick & Healthy Breakfast, No Refined Sugar, 5-Min Prep](http://quickfixwithbindu-local.local/recipes/strawberry-chia-pudding-quick-healthy-breakfast-no-refined-sugar-5-min-prep/) | 200 | 5 | 5 | PHP warnings |
| 394 | recipes | [Strawberry Mojito](http://quickfixwithbindu-local.local/recipes/strawberry-mojito/) | 200 | 6 | 5 | PHP warnings |
| 333 | recipes | [Super Easy Oyster Mushroom Stir Fry](http://quickfixwithbindu-local.local/recipes/super-easy-oyster-mushroom-stir-fry/) | 200 | 12 | 7 | PHP warnings |
| 419 | recipes | [Sweet Potato Falafel-Style Bites](http://quickfixwithbindu-local.local/recipes/sweet-potato-falafel-style-bites/) | 200 | 13 | 9 | PHP warnings |
| 463 | recipes | [Sweet Potato Falafel-Style Bites](http://quickfixwithbindu-local.local/recipes/sweet-potato-falafel-style-bites-2/) | 200 | 13 | 9 | PHP warnings |
| 1168 | recipes | [Sweet Potato Lentil High Protein Crackers](http://quickfixwithbindu-local.local/recipes/sweet-potato-lentil-high-protein-crackers/) | 200 | 10 | 9 | PHP warnings |
| 1299 | recipes | [Sweet Potato Red Lentil Crackers](http://quickfixwithbindu-local.local/recipes/sweet-potato-red-lentil-crackers/) | 200 | 10 | 8 | PHP warnings |
| 1099 | recipes | [Tea Strainer Cleaning Hack 😍 No Scrubbing Needed!](http://quickfixwithbindu-local.local/recipes/tea-strainer-cleaning-hack-%f0%9f%98%8d-no-scrubbing-needed/) | 200 | 0 | 1 | No ingredients; PHP warnings |
| 457 | recipes | [The Creamiest Veggie Sandwich \| With Cottage Cheese (Paneer) & Avocado](http://quickfixwithbindu-local.local/recipes/the-creamiest-veggie-sandwich-with-cottage-cheese-paneer-avocado/) | 200 | 14 | 5 | PHP warnings |
| 684 | recipes | [The Perfect Whole Wheat Breakfast Loaf](http://quickfixwithbindu-local.local/recipes/the-perfect-whole-wheat-breakfast-loaf/) | 200 | 7 | 4 | PHP warnings |
| 674 | recipes | [The Rustic Zucchini Flatbread my mom made to get us to eat our veggies](http://quickfixwithbindu-local.local/recipes/the-rustic-zucchini-flatbread-my-mom-made-to-get-us-to-eat-our-veggies-shorts/) | 200 | 8 | 7 | PHP warnings |
| 314 | recipes | [The Secret to 2-Ingredient Pink Tortillas!](http://quickfixwithbindu-local.local/recipes/the-secret-to-2-ingredient-pink-tortillas/) | 200 | 4 | 6 | PHP warnings |
| 685 | recipes | [The Secret to the Perfect Momo Fold 🥟](http://quickfixwithbindu-local.local/recipes/the-secret-to-the-perfect-momo-fold-%f0%9f%a5%9f/) | 200 | 0 | 0 | No ingredients; No instructions; PHP warnings |
| 266 | recipes | [The Ultimate Veggie Sandwich](http://quickfixwithbindu-local.local/recipes/the-ultimate-veggie-sandwich/) | 200 | 15 | 5 | PHP warnings |
| 323 | recipes | [The VIRAL Crispy Rice Paper Omelet \| 5-Minute Breakfast](http://quickfixwithbindu-local.local/recipes/the-viral-crispy-rice-paper-omelet-5-minute-breakfast/) | 200 | 6 | 8 | PHP warnings |
| 468 | recipes | [This Isn’t Just a Veg Cutlet… It Turns Into a Melty Pocket Wrap! Easy 2-in-1 Snack Hack](http://quickfixwithbindu-local.local/recipes/this-isnt-just-a-veg-cutlet-it-turns-into-a-melty-pocket-wrap-easy-2-in-1-snack-hack/) | 200 | 10 | 13 | PHP warnings |
| 639 | recipes | [Tofu Lentils Rice With Spinach \| Healthy Protein-Packed Rice Recipe \| Easy One-Pot Dinner](http://quickfixwithbindu-local.local/recipes/tofu-lentils-rice-with-spinach-healthy-protein-packed-rice-recipe-easy-one-pot-dinner/) | 200 | 18 | 15 | PHP warnings |
| 434 | recipes | [Tofu Stir Fry with Asparagus & Mushrooms](http://quickfixwithbindu-local.local/recipes/tofu-stir-fry-with-asparagus-mushrooms/) | 200 | 14 | 11 | PHP warnings |
| 473 | recipes | [Tofu Veg Stuffed Buns](http://quickfixwithbindu-local.local/recipes/tofu-veg-stuffed-buns/) | 200 | 13 | 0 | No instructions; PHP warnings |
| 383 | recipes | [Tomato Mint Chutney](http://quickfixwithbindu-local.local/recipes/tomato-mint-chutney/) | 200 | 0 | 0 | No ingredients; No instructions; PHP warnings |
| 694 | recipes | [Try It Once, You’ll Make It Every Week \| 2 Ingredient High-Protein Mung Beans Flatbread #vegan](http://quickfixwithbindu-local.local/recipes/try-it-once-youll-make-it-every-week-2-ingredient-high-protein-mung-beans-flatbread-vegan/) | 200 | 3 | 8 | PHP warnings |
| 373 | recipes | [Turmeric And Ginger Concentrate \| The Flu Stops Here](http://quickfixwithbindu-local.local/recipes/turmeric-and-ginger-concentrate-the-flu-stops-here/) | 200 | 5 | 7 | PHP warnings |
| 357 | recipes | [Ultra Creamy Macaroni Pasta with Tomato Sauce & Veggies](http://quickfixwithbindu-local.local/recipes/ultra-creamy-macaroni-pasta-with-tomato-sauce-veggies/) | 200 | 20 | 7 | PHP warnings |
| 296 | recipes | [Ultra Crispy Air-Fryer Plantain Chips](http://quickfixwithbindu-local.local/recipes/ultra-crispy-air-fryer-plantain-chips/) | 200 | 8 | 7 | PHP warnings |
| 482 | recipes | [Unique Oats & Spinach Recipe for Weight Loss \| Healthy Flatbreads & Stuffed Buns](http://quickfixwithbindu-local.local/recipes/unique-oats-spinach-recipe-for-weight-loss-healthy-flatbreads-stuffed-buns/) | 200 | 9 | 9 | PHP warnings |
| 449 | recipes | [Unique style Egg Toast \| Classic Breakfast with a Fun Twist!](http://quickfixwithbindu-local.local/recipes/unique-style-egg-toast-classic-breakfast-with-a-fun-twist/) | 200 | 6 | 5 | PHP warnings |
| 247 | recipes | [Veg Dumplings with Spicy Chutney \| Homemade Momo](http://quickfixwithbindu-local.local/recipes/veg-dumplings-with-spicy-chutney-homemade-momo/) | 200 | 23 | 8 | PHP warnings |
| 280 | recipes | [Veg Momos (Nepali-style steamed dumplings) with Spicy Chutney](http://quickfixwithbindu-local.local/recipes/veg-momos-nepali-style-steamed-dumplings-with-spicy-chutney/) | 200 | 23 | 8 | PHP warnings |
| 1186 | recipes | [Vegan Cabbage Flatbread (Flourless, High Protein)](http://quickfixwithbindu-local.local/recipes/vegan-cabbage-flatbread-flourless-high-protein/) | 200 | 13 | 11 | PHP warnings |
| 1235 | recipes | [Vegan Karahi Tofu Rice Bowl](http://quickfixwithbindu-local.local/recipes/vegan-karahi-tofu-rice-bowl/) | 200 | 21 | 4 | PHP warnings |
| 1282 | recipes | [Vegan Kidney Bean and Soy Chunks Curry](http://quickfixwithbindu-local.local/recipes/vegan-kidney-bean-and-soy-chunks-curry/) | 200 | 18 | 12 | PHP warnings |
| 466 | recipes | [Vegan Mushroom Pasta Under $5! Quick & Healthy Dinner](http://quickfixwithbindu-local.local/recipes/vegan-mushroom-pasta-under-5-quick-healthy-dinner/) | 200 | 8 | 7 | PHP warnings |
| 1197 | recipes | [Vegan Quinoa Kidney Bean Fiber Balls](http://quickfixwithbindu-local.local/recipes/vegan-quinoa-kidney-bean-fiber-balls/) | 200 | 19 | 8 | PHP warnings |
| 236 | recipes | [Viral Egg Flower Breakfast](http://quickfixwithbindu-local.local/recipes/viral-egg-flower-breakfast/) | 200 | 6 | 7 | PHP warnings |
| 400 | recipes | [Viral Egg Toast](http://quickfixwithbindu-local.local/recipes/viral-egg-toast/) | 200 | 9 | 8 | PHP warnings |
| 231 | recipes | [Viral Potato Mushroom Buttons 🍄](http://quickfixwithbindu-local.local/recipes/viral-potato-mushroom-buttons-%f0%9f%8d%84/) | 200 | 9 | 8 | PHP warnings |
| 444 | recipes | [Viral Soy Chunks Peanut Salad 🥜🌶](http://quickfixwithbindu-local.local/recipes/viral-soy-chunks-peanut-salad-%f0%9f%a5%9c%f0%9f%8c%b6/) | 200 | 15 | 6 | PHP warnings |
| 442 | recipes | [Viral Soy Chunks Peanut Salad 🥜🌶 \| High Protein, No-Fry Recipe](http://quickfixwithbindu-local.local/recipes/viral-soy-chunks-peanut-salad-%f0%9f%a5%9c%f0%9f%8c%b6-high-protein-no-fry-recipe/) | 200 | 13 | 9 | PHP warnings |
| 222 | recipes | [Warm Asparagus Winter Salad with Honey-Lemon Seed Dressing](http://quickfixwithbindu-local.local/recipes/warm-asparagus-winter-salad-with-honey-lemon-seed-dressing/) | 200 | 14 | 8 | PHP warnings |
| 318 | recipes | [Watermelon Mint Lime Juice](http://quickfixwithbindu-local.local/recipes/watermelon-mint-lime-juice/) | 200 | 5 | 3 | PHP warnings |
| 377 | recipes | [Ways to Incorporate Turmeric for Health Benefits](http://quickfixwithbindu-local.local/recipes/ways-to-incorporate-turmeric-for-health-benefits/) | 200 | 5 | 4 | PHP warnings |
| 1091 | recipes | [What’s inside this omelet?](http://quickfixwithbindu-local.local/recipes/whats-inside-this-omelet/) | 200 | 0 | 0 | No ingredients; No instructions; PHP warnings |
| 521 | recipes | [White Bean & Garlic Rice Recipe](http://quickfixwithbindu-local.local/recipes/white-bean-garlic-rice-recipe/) | 200 | 19 | 10 | PHP warnings |
| 362 | recipes | [Whole Wheat Garlic Cheese Buns](http://quickfixwithbindu-local.local/recipes/whole-wheat-garlic-cheese-buns/) | 200 | 9 | 12 | PHP warnings |
| 523 | recipes | [Whole Wheat Pizza Dough for Three Recipes (Pizzas & Garlic Buns)](http://quickfixwithbindu-local.local/recipes/whole-wheat-pizza-dough-for-three-recipes-pizzas-garlic-buns/) | 200 | 19 | 20 | PHP warnings |
| 1273 | recipes | [Whole Wheat Tofu Paneer Mushroom Momos](http://quickfixwithbindu-local.local/recipes/whole-wheat-tofu-paneer-mushroom-momos/) | 200 | 12 | 7 | PHP warnings |
| 233 | recipes | [Winter Immunity Bars](http://quickfixwithbindu-local.local/recipes/winter-immunity-bars/) | 200 | 13 | 9 | PHP warnings |
| 261 | recipes | [You’ve NEVER Seen an Egg Sandwich Like This!](http://quickfixwithbindu-local.local/recipes/youve-never-seen-an-egg-sandwich-like-this/) | 200 | 0 | 0 | No ingredients; No instructions; PHP warnings |
| 282 | recipes | [Zero Waste Broccoli Stir Fry](http://quickfixwithbindu-local.local/recipes/zero-waste-broccoli-stir-fry/) | 200 | 5 | 5 | PHP warnings |
| 283 | recipes | [Zero Waste Broccoli Stir Fry](http://quickfixwithbindu-local.local/recipes/zero-waste-broccoli-stir-fry-2/) | 200 | 5 | 4 | PHP warnings |
| 329 | recipes | [Zero-Oil Beet Oats Flatbread \| Low-GI Healthy Flatbread](http://quickfixwithbindu-local.local/recipes/zero-oil-beet-oats-flatbread-low-gi-healthy-flatbread/) | 200 | 8 | 12 | PHP warnings |
| 472 | recipes | [Zero-Waste Garlic Broccoli Stir Fry](http://quickfixwithbindu-local.local/recipes/zero-waste-garlic-broccoli-stir-fry/) | 200 | 5 | 5 | PHP warnings |
| 464 | recipes | [Zucchini Quinoa Bites with Secret Tangy Dip](http://quickfixwithbindu-local.local/recipes/zucchini-quinoa-bites-with-secret-tangy-dip/) | 200 | 17 | 6 | PHP warnings |

#### Additional local routes requested

| URL | HTTP | Page heading / notes |
| --- | --- | --- |
| [/?s=lentil](http://quickfixwithbindu-local.local/?s=lentil) | 200 | Search Results |
| [/?s=qwb-audit-no-results-xyz](http://quickfixwithbindu-local.local/?s=qwb-audit-no-results-xyz) | 200 | Search Results |
| [/category/healthy-bites/](http://quickfixwithbindu-local.local/category/healthy-bites/) | 200 | Healthy Bites |
| [/category/home-remedies/](http://quickfixwithbindu-local.local/category/home-remedies/) | 200 | Home Remedies |
| [/category/main-meals/](http://quickfixwithbindu-local.local/category/main-meals/) | 200 | Main Meals |
| [/category/quick-starts/](http://quickfixwithbindu-local.local/category/quick-starts/) | 200 | Quick Starts |
| [/llms.txt](http://quickfixwithbindu-local.local/llms.txt) | 200 | text/plain |
| [/page-sitemap.xml](http://quickfixwithbindu-local.local/page-sitemap.xml) | 200 |  |
| [/page/2/?s=lentil](http://quickfixwithbindu-local.local/page/2/?s=lentil) | 200 | Search Results |
| [/page/3/?s=lentil](http://quickfixwithbindu-local.local/page/3/?s=lentil) | 200 | Search Results |
| [/page/4/?s=lentil](http://quickfixwithbindu-local.local/page/4/?s=lentil) | 200 | Search Results |
| [/page/5/?s=lentil](http://quickfixwithbindu-local.local/page/5/?s=lentil) | 200 | Search Results |
| [/post-archive-sitemap.xml](http://quickfixwithbindu-local.local/post-archive-sitemap.xml) | 200 |  |
| [/qwb-audit-missing-page/](http://quickfixwithbindu-local.local/qwb-audit-missing-page/) | 404 | HTTP Error 404: Not Found |
| [/recipes-sitemap.xml](http://quickfixwithbindu-local.local/recipes-sitemap.xml) | 200 |  |
| [/recipes/](http://quickfixwithbindu-local.local/recipes/) | 200 | Search Results |
| [/recipes/page/10/](http://quickfixwithbindu-local.local/recipes/page/10/) | 200 | Search Results |
| [/recipes/page/11/](http://quickfixwithbindu-local.local/recipes/page/11/) | 200 | Search Results |
| [/recipes/page/12/](http://quickfixwithbindu-local.local/recipes/page/12/) | 200 | Search Results |
| [/recipes/page/13/](http://quickfixwithbindu-local.local/recipes/page/13/) | 200 | Search Results |
| [/recipes/page/14/](http://quickfixwithbindu-local.local/recipes/page/14/) | 200 | Search Results |
| [/recipes/page/15/](http://quickfixwithbindu-local.local/recipes/page/15/) | 200 | Search Results |
| [/recipes/page/16/](http://quickfixwithbindu-local.local/recipes/page/16/) | 200 | Search Results |
| [/recipes/page/17/](http://quickfixwithbindu-local.local/recipes/page/17/) | 200 | Search Results |
| [/recipes/page/18/](http://quickfixwithbindu-local.local/recipes/page/18/) | 200 | Search Results |
| [/recipes/page/19/](http://quickfixwithbindu-local.local/recipes/page/19/) | 200 | Search Results |
| [/recipes/page/2/](http://quickfixwithbindu-local.local/recipes/page/2/) | 200 | Search Results |
| [/recipes/page/20/](http://quickfixwithbindu-local.local/recipes/page/20/) | 200 | Search Results |
| [/recipes/page/21/](http://quickfixwithbindu-local.local/recipes/page/21/) | 200 | Search Results |
| [/recipes/page/22/](http://quickfixwithbindu-local.local/recipes/page/22/) | 200 | Search Results |
| [/recipes/page/23/](http://quickfixwithbindu-local.local/recipes/page/23/) | 200 | Search Results |
| [/recipes/page/24/](http://quickfixwithbindu-local.local/recipes/page/24/) | 200 | Search Results |
| [/recipes/page/25/](http://quickfixwithbindu-local.local/recipes/page/25/) | 200 | Search Results |
| [/recipes/page/26/](http://quickfixwithbindu-local.local/recipes/page/26/) | 200 | Search Results |
| [/recipes/page/27/](http://quickfixwithbindu-local.local/recipes/page/27/) | 200 | Search Results |
| [/recipes/page/28/](http://quickfixwithbindu-local.local/recipes/page/28/) | 200 | Search Results |
| [/recipes/page/29/](http://quickfixwithbindu-local.local/recipes/page/29/) | 200 | Search Results |
| [/recipes/page/3/](http://quickfixwithbindu-local.local/recipes/page/3/) | 200 | Search Results |
| [/recipes/page/30/](http://quickfixwithbindu-local.local/recipes/page/30/) | 200 | Search Results |
| [/recipes/page/31/](http://quickfixwithbindu-local.local/recipes/page/31/) | 200 | Search Results |
| [/recipes/page/32/](http://quickfixwithbindu-local.local/recipes/page/32/) | 200 | Search Results |
| [/recipes/page/33/](http://quickfixwithbindu-local.local/recipes/page/33/) | 200 | Search Results |
| [/recipes/page/34/](http://quickfixwithbindu-local.local/recipes/page/34/) | 200 | Search Results |
| [/recipes/page/35/](http://quickfixwithbindu-local.local/recipes/page/35/) | 200 | Search Results |
| [/recipes/page/36/](http://quickfixwithbindu-local.local/recipes/page/36/) | 200 | Search Results |
| [/recipes/page/37/](http://quickfixwithbindu-local.local/recipes/page/37/) | 200 | Search Results |
| [/recipes/page/38/](http://quickfixwithbindu-local.local/recipes/page/38/) | 200 | Search Results |
| [/recipes/page/39/](http://quickfixwithbindu-local.local/recipes/page/39/) | 200 | Search Results |
| [/recipes/page/4/](http://quickfixwithbindu-local.local/recipes/page/4/) | 200 | Search Results |
| [/recipes/page/40/](http://quickfixwithbindu-local.local/recipes/page/40/) | 200 | Search Results |
| [/recipes/page/41/](http://quickfixwithbindu-local.local/recipes/page/41/) | 200 | Search Results |
| [/recipes/page/42/](http://quickfixwithbindu-local.local/recipes/page/42/) | 200 | Search Results |
| [/recipes/page/43/](http://quickfixwithbindu-local.local/recipes/page/43/) | 200 | Search Results |
| [/recipes/page/44/](http://quickfixwithbindu-local.local/recipes/page/44/) | 200 | Search Results |
| [/recipes/page/45/](http://quickfixwithbindu-local.local/recipes/page/45/) | 200 | Search Results |
| [/recipes/page/46/](http://quickfixwithbindu-local.local/recipes/page/46/) | 200 | Search Results |
| [/recipes/page/47/](http://quickfixwithbindu-local.local/recipes/page/47/) | 200 | Search Results |
| [/recipes/page/5/](http://quickfixwithbindu-local.local/recipes/page/5/) | 200 | Search Results |
| [/recipes/page/6/](http://quickfixwithbindu-local.local/recipes/page/6/) | 200 | Search Results |
| [/recipes/page/7/](http://quickfixwithbindu-local.local/recipes/page/7/) | 200 | Search Results |
| [/recipes/page/8/](http://quickfixwithbindu-local.local/recipes/page/8/) | 200 | Search Results |
| [/recipes/page/9/](http://quickfixwithbindu-local.local/recipes/page/9/) | 200 | Search Results |
| [/robots.txt](http://quickfixwithbindu-local.local/robots.txt) | 200 | text/plain |
| [/sitemap.rss](http://quickfixwithbindu-local.local/sitemap.rss) | 200 | text/xml; charset=UTF-8 |
| [/sitemap.xml](http://quickfixwithbindu-local.local/sitemap.xml) | 200 | text/xml; charset=UTF-8 |
| [/superpwa-manifest-nginx.json](http://quickfixwithbindu-local.local/superpwa-manifest-nginx.json) | 200 | application/json |
| [/superpwa-manifest.json](http://quickfixwithbindu-local.local/superpwa-manifest.json) | 200 | application/json |
| [/superpwa-sw.js](http://quickfixwithbindu-local.local/superpwa-sw.js) | 200 | application/x-javascript |
| [/wp-sitemap.xml](http://quickfixwithbindu-local.local/wp-sitemap.xml) | 200 | text/xml; charset=UTF-8 |

### Appendix B — complete rendered anchor-link inventory

Targets are deduplicated after resolving relative URLs and removing fragments. Production links retain their original destination; local-equivalent checks do not replace a live production response. Counts are distinct source pages, not repeated link occurrences. One example source is shown.

| Target | HTTP/result | Source pages | Example source |
| --- | --- | --- | --- |
| [/](http://quickfixwithbindu-local.local/) | 200 | 528 | [/](http://quickfixwithbindu-local.local/) |
| [/?s=lentil](http://quickfixwithbindu-local.local/?s=lentil) | 200 | 2 | [/?s=lentil](http://quickfixwithbindu-local.local/?s=lentil) |
| [/?s=qwb-audit-no-results-xyz](http://quickfixwithbindu-local.local/?s=qwb-audit-no-results-xyz) | 200 | 1 | [/?s=qwb-audit-no-results-xyz](http://quickfixwithbindu-local.local/?s=qwb-audit-no-results-xyz) |
| [/about-me/](http://quickfixwithbindu-local.local/about-me/) | 200 | 528 | [/](http://quickfixwithbindu-local.local/) |
| [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) | 200 | 528 | [/](http://quickfixwithbindu-local.local/) |
| [/contact-us/](http://quickfixwithbindu-local.local/contact-us/) | 200 | 528 | [/](http://quickfixwithbindu-local.local/) |
| [/instagram-recipes/](http://quickfixwithbindu-local.local/instagram-recipes/) | 200 | 528 | [/](http://quickfixwithbindu-local.local/) |
| [/page/2/?s=lentil](http://quickfixwithbindu-local.local/page/2/?s=lentil) | 200 | 3 | [/?s=lentil](http://quickfixwithbindu-local.local/?s=lentil) |
| [/page/3/?s=lentil](http://quickfixwithbindu-local.local/page/3/?s=lentil) | 200 | 3 | [/page/2/?s=lentil](http://quickfixwithbindu-local.local/page/2/?s=lentil) |
| [/page/4/?s=lentil](http://quickfixwithbindu-local.local/page/4/?s=lentil) | 200 | 3 | [/page/3/?s=lentil](http://quickfixwithbindu-local.local/page/3/?s=lentil) |
| [/page/5/?s=lentil](http://quickfixwithbindu-local.local/page/5/?s=lentil) | 200 | 2 | [/page/4/?s=lentil](http://quickfixwithbindu-local.local/page/4/?s=lentil) |
| [/privacy-policy/](http://quickfixwithbindu-local.local/privacy-policy/) | 200 | 528 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/](http://quickfixwithbindu-local.local/recipes/) | 200 | 2 | [/recipes/](http://quickfixwithbindu-local.local/recipes/) |
| [/recipes/10-min-spicy-c-momo-best-way-to-eat-frozen-momos/](http://quickfixwithbindu-local.local/recipes/10-min-spicy-c-momo-best-way-to-eat-frozen-momos/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/10-min-veggie-stir-fry-rice-with-mushrooms-coriander/](http://quickfixwithbindu-local.local/recipes/10-min-veggie-stir-fry-rice-with-mushrooms-coriander/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/10-minute-air-fryer-bread-pizza/](http://quickfixwithbindu-local.local/recipes/10-minute-air-fryer-bread-pizza/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/10-minute-cheesy-egg-pizza-quick-whole-wheat-bread-omelet-hack/](http://quickfixwithbindu-local.local/recipes/10-minute-cheesy-egg-pizza-quick-whole-wheat-bread-omelet-hack/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/10-minute-crispy-garlic-brussels-sprouts-with-sesame-and-peanuts-crunch/](http://quickfixwithbindu-local.local/recipes/10-minute-crispy-garlic-brussels-sprouts-with-sesame-and-peanuts-crunch/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/10-minute-egg-fried-rice-no-egg-smell-my-family-didnt-know-it-has-egg-on-it/](http://quickfixwithbindu-local.local/recipes/10-minute-egg-fried-rice-no-egg-smell-my-family-didnt-know-it-has-egg-on-it/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/100-whole-wheat-artisan-pizza-fresh-mozzarella-basil-with-bakery-style-crust/](http://quickfixwithbindu-local.local/recipes/100-whole-wheat-artisan-pizza-fresh-mozzarella-basil-with-bakery-style-crust/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/15-min-high-protein-red-lentil-broccoli-soup/](http://quickfixwithbindu-local.local/recipes/15-min-high-protein-red-lentil-broccoli-soup/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/15-minute-air-fryer-lasagna-style-pasta/](http://quickfixwithbindu-local.local/recipes/15-minute-air-fryer-lasagna-style-pasta/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/15-minute-karahi-cauliflower/](http://quickfixwithbindu-local.local/recipes/15-minute-karahi-cauliflower/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/15-minute-vegan-pasta-bowl-with-tofu-asparagus/](http://quickfixwithbindu-local.local/recipes/15-minute-vegan-pasta-bowl-with-tofu-asparagus/) | 200 | 6 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/2-ingredient-broccoli-omelet/](http://quickfixwithbindu-local.local/recipes/2-ingredient-broccoli-omelet/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/2-ingredient-pink-tortillas-with-no-oil-tofu-filling-easy-vegan-wrap/](http://quickfixwithbindu-local.local/recipes/2-ingredient-pink-tortillas-with-no-oil-tofu-filling-easy-vegan-wrap/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/2-ingredient-sprouted-oat-and-beetroot-flatbreads-2/](http://quickfixwithbindu-local.local/recipes/2-ingredient-sprouted-oat-and-beetroot-flatbreads-2/) | 200 | 1 | [/recipes/page/13/](http://quickfixwithbindu-local.local/recipes/page/13/) |
| [/recipes/2-ingredient-sprouted-oat-and-beetroot-flatbreads/](http://quickfixwithbindu-local.local/recipes/2-ingredient-sprouted-oat-and-beetroot-flatbreads/) | 200 | 1 | [/recipes/page/13/](http://quickfixwithbindu-local.local/recipes/page/13/) |
| [/recipes/2-ingredient-sprouted-oats-beetroot-flatbreads-gluten-free-2/](http://quickfixwithbindu-local.local/recipes/2-ingredient-sprouted-oats-beetroot-flatbreads-gluten-free-2/) | 200 | 1 | [/recipes/page/14/](http://quickfixwithbindu-local.local/recipes/page/14/) |
| [/recipes/2-ingredient-sprouted-oats-beetroot-flatbreads-gluten-free-3/](http://quickfixwithbindu-local.local/recipes/2-ingredient-sprouted-oats-beetroot-flatbreads-gluten-free-3/) | 200 | 1 | [/recipes/page/13/](http://quickfixwithbindu-local.local/recipes/page/13/) |
| [/recipes/2-ingredient-sprouted-oats-beetroot-flatbreads-gluten-free-4/](http://quickfixwithbindu-local.local/recipes/2-ingredient-sprouted-oats-beetroot-flatbreads-gluten-free-4/) | 200 | 1 | [/recipes/page/13/](http://quickfixwithbindu-local.local/recipes/page/13/) |
| [/recipes/2-ingredient-sprouted-oats-beetroot-flatbreads-gluten-free-5/](http://quickfixwithbindu-local.local/recipes/2-ingredient-sprouted-oats-beetroot-flatbreads-gluten-free-5/) | 200 | 1 | [/recipes/page/13/](http://quickfixwithbindu-local.local/recipes/page/13/) |
| [/recipes/2-ingredient-sprouted-oats-beetroot-flatbreads-gluten-free/](http://quickfixwithbindu-local.local/recipes/2-ingredient-sprouted-oats-beetroot-flatbreads-gluten-free/) | 200 | 1 | [/recipes/page/14/](http://quickfixwithbindu-local.local/recipes/page/14/) |
| [/recipes/2-ingredient-wrap-recipe-crispy-peanut-mint-wraps/](http://quickfixwithbindu-local.local/recipes/2-ingredient-wrap-recipe-crispy-peanut-mint-wraps/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/3-ingredient-almond-flour-cookies/](http://quickfixwithbindu-local.local/recipes/3-ingredient-almond-flour-cookies/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/3-ingredient-bite-sized-almond-flour-cookies-gluten-free-vegan/](http://quickfixwithbindu-local.local/recipes/3-ingredient-bite-sized-almond-flour-cookies-gluten-free-vegan/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/3-ingredient-creamy-corn-soup-without-cream/](http://quickfixwithbindu-local.local/recipes/3-ingredient-creamy-corn-soup-without-cream/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/3-ingredient-easy-creamy-tomato-soup-in-minutes-ep-6-winter-soup-no-heavy-cream/](http://quickfixwithbindu-local.local/recipes/3-ingredient-easy-creamy-tomato-soup-in-minutes-ep-6-winter-soup-no-heavy-cream/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/3-ingredient-easy-shortbread-cookies-perfect-every-time/](http://quickfixwithbindu-local.local/recipes/3-ingredient-easy-shortbread-cookies-perfect-every-time/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/3-ingredient-flourless-shortbread-cookies/](http://quickfixwithbindu-local.local/recipes/3-ingredient-flourless-shortbread-cookies/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/3-ingredient-peanut-butter-protein-bites-2/](http://quickfixwithbindu-local.local/recipes/3-ingredient-peanut-butter-protein-bites-2/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/3-ingredient-peanut-butter-protein-bites/](http://quickfixwithbindu-local.local/recipes/3-ingredient-peanut-butter-protein-bites/) | 200 | 1 | [/recipes/page/6/](http://quickfixwithbindu-local.local/recipes/page/6/) |
| [/recipes/3-ingredient-pomegranate-pops-bite-sized-pomegranate-pistachio-clusters/](http://quickfixwithbindu-local.local/recipes/3-ingredient-pomegranate-pops-bite-sized-pomegranate-pistachio-clusters/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/3-ingredients-5-minutes-dates-recipe-sweet-and-salty/](http://quickfixwithbindu-local.local/recipes/3-ingredients-5-minutes-dates-recipe-sweet-and-salty/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/5-min-blueberry-banana-waffle/](http://quickfixwithbindu-local.local/recipes/5-min-blueberry-banana-waffle/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/5-min-healthy-fruit-yogurt-salad/](http://quickfixwithbindu-local.local/recipes/5-min-healthy-fruit-yogurt-salad/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/5-min-spicy-mashed-potatoes/](http://quickfixwithbindu-local.local/recipes/5-min-spicy-mashed-potatoes/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/5-min-spicy-yogurt-dip-chili-cucumber-raita/](http://quickfixwithbindu-local.local/recipes/5-min-spicy-yogurt-dip-chili-cucumber-raita/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/5-min-zucchini-stir-fry/](http://quickfixwithbindu-local.local/recipes/5-min-zucchini-stir-fry/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/5-minute-broccoli-salad/](http://quickfixwithbindu-local.local/recipes/5-minute-broccoli-salad/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/5-minute-cheesy-egg-mushroom-sandwich/](http://quickfixwithbindu-local.local/recipes/5-minute-cheesy-egg-mushroom-sandwich/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/5-minute-garlic-charred-broccoli-zero-waste/](http://quickfixwithbindu-local.local/recipes/5-minute-garlic-charred-broccoli-zero-waste/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/5-minute-sugar-free-banana-pancakes/](http://quickfixwithbindu-local.local/recipes/5-minute-sugar-free-banana-pancakes/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/50-high-protein-nepali-momos-tofu-paneer-with-spicy-schezwan-chutney-flash-freeze-hack/](http://quickfixwithbindu-local.local/recipes/50-high-protein-nepali-momos-tofu-paneer-with-spicy-schezwan-chutney-flash-freeze-hack/) | 200 | 83 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/a-quiet-meal-made-with-asparagus-and-simple-veggies/](http://quickfixwithbindu-local.local/recipes/a-quiet-meal-made-with-asparagus-and-simple-veggies/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/air-fried-chickpea-quinoa-salad/](http://quickfixwithbindu-local.local/recipes/air-fried-chickpea-quinoa-salad/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/air-fried-crumble-tofu-with-creamy-yogurt-dip/](http://quickfixwithbindu-local.local/recipes/air-fried-crumble-tofu-with-creamy-yogurt-dip/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/air-fried-paneer-kofta-with-walnut-gravy/](http://quickfixwithbindu-local.local/recipes/air-fried-paneer-kofta-with-walnut-gravy/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/air-fried-potato-rolls/](http://quickfixwithbindu-local.local/recipes/air-fried-potato-rolls/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/air-fry-crispy-quinoa-bites/](http://quickfixwithbindu-local.local/recipes/air-fry-crispy-quinoa-bites/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/air-fryer-banana-blueberry-oat-bake/](http://quickfixwithbindu-local.local/recipes/air-fryer-banana-blueberry-oat-bake/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/air-fryer-corn-ribs-with-garlic-butter-glaze/](http://quickfixwithbindu-local.local/recipes/air-fryer-corn-ribs-with-garlic-butter-glaze/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/air-fryer-egg-noodle-pizza-in-10-minutes-ramen-pizza-cheesy-maggi/](http://quickfixwithbindu-local.local/recipes/air-fryer-egg-noodle-pizza-in-10-minutes-ramen-pizza-cheesy-maggi/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/air-fryer-falafel-grape-chutney/](http://quickfixwithbindu-local.local/recipes/air-fryer-falafel-grape-chutney/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/air-fryer-plantain-chips-with-zesty-yogurt-dip/](http://quickfixwithbindu-local.local/recipes/air-fryer-plantain-chips-with-zesty-yogurt-dip/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/airfried-cheesy-cauliflower/](http://quickfixwithbindu-local.local/recipes/airfried-cheesy-cauliflower/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/airfried-chickpea-quinoa-salad/](http://quickfixwithbindu-local.local/recipes/airfried-chickpea-quinoa-salad/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/almond-flour-biscotti/](http://quickfixwithbindu-local.local/recipes/almond-flour-biscotti/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/apple-oats-waffle/](http://quickfixwithbindu-local.local/recipes/apple-oats-waffle/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/asparagus-and-green-beans-curry-soup/](http://quickfixwithbindu-local.local/recipes/asparagus-and-green-beans-curry-soup/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/asparagus-cherry-pop-stir-fry/](http://quickfixwithbindu-local.local/recipes/asparagus-cherry-pop-stir-fry/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/authentic-himalayan-black-eyed-peas-soup-bamboo-shoot-beans-bodi-tama/](http://quickfixwithbindu-local.local/recipes/authentic-himalayan-black-eyed-peas-soup-bamboo-shoot-beans-bodi-tama/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/avocado-dosa-with-guacamole-and-mint-chutney/](http://quickfixwithbindu-local.local/recipes/avocado-dosa-with-guacamole-and-mint-chutney/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/avocado-egg-bagel-with-runny-yolk/](http://quickfixwithbindu-local.local/recipes/avocado-egg-bagel-with-runny-yolk/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/avocado-oats-egg-pancake-fail/](http://quickfixwithbindu-local.local/recipes/avocado-oats-egg-pancake-fail/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/avocado-peanut-salad/](http://quickfixwithbindu-local.local/recipes/avocado-peanut-salad/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/banana-blueberry-oatmeal-muffin-bowl-in-airfryer-no-flour-no-sugar/](http://quickfixwithbindu-local.local/recipes/banana-blueberry-oatmeal-muffin-bowl-in-airfryer-no-flour-no-sugar/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/banana-chia-cinnamon-smoothie/](http://quickfixwithbindu-local.local/recipes/banana-chia-cinnamon-smoothie/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/banana-quinoa-power-pancakes-2/](http://quickfixwithbindu-local.local/recipes/banana-quinoa-power-pancakes-2/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/banana-quinoa-power-pancakes/](http://quickfixwithbindu-local.local/recipes/banana-quinoa-power-pancakes/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/bbq-cottage-cheese-paneer-wrap/](http://quickfixwithbindu-local.local/recipes/bbq-cottage-cheese-paneer-wrap/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/bbq-style-air-fryer-paneer-2/](http://quickfixwithbindu-local.local/recipes/bbq-style-air-fryer-paneer-2/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/bbq-style-air-fryer-paneer/](http://quickfixwithbindu-local.local/recipes/bbq-style-air-fryer-paneer/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/bbq-style-paneer-chili-air-fryer-twist/](http://quickfixwithbindu-local.local/recipes/bbq-style-paneer-chili-air-fryer-twist/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/beetroot-chia-drink-for-digestion/](http://quickfixwithbindu-local.local/recipes/beetroot-chia-drink-for-digestion/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/beginner-cooking-that-actually-tastes-good-three-super-simple-veggie-recipes/](http://quickfixwithbindu-local.local/recipes/beginner-cooking-that-actually-tastes-good-three-super-simple-veggie-recipes/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/belgian-waffle-recipe/](http://quickfixwithbindu-local.local/recipes/belgian-waffle-recipe/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/better-than-store-bought-seed-crackers/](http://quickfixwithbindu-local.local/recipes/better-than-store-bought-seed-crackers/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/better-than-toast-a-balanced-breakfast-with-protein-fiber-carbs-gluten-free-broccoli-waffles/](http://quickfixwithbindu-local.local/recipes/better-than-toast-a-balanced-breakfast-with-protein-fiber-carbs-gluten-free-broccoli-waffles/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/bindus-cheesy-quinoa-stuffed-peppers-with-fresh-dill-air-fryer-version/](http://quickfixwithbindu-local.local/recipes/bindus-cheesy-quinoa-stuffed-peppers-with-fresh-dill-air-fryer-version/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/blueberry-banana-bread-waffle-hack/](http://quickfixwithbindu-local.local/recipes/blueberry-banana-bread-waffle-hack/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/blueberry-banana-power-smoothie/](http://quickfixwithbindu-local.local/recipes/blueberry-banana-power-smoothie/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/blueberry-chia-smoothie/](http://quickfixwithbindu-local.local/recipes/blueberry-chia-smoothie/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/buckwheat-pancake-%e0%a4%ab%e0%a4%be%e0%a4%aa%e0%a4%b0%e0%a4%95%e0%a5%8b-%e0%a4%b0%e0%a5%8b%e0%a4%9f%e0%a5%80-%e0%a4%95%e0%a5%81%e0%a4%9f%e0%a5%8d%e0%a4%9f%e0%a5%82-%e0%a4%95%e0%a4%be-%e0%a4%9a/](http://quickfixwithbindu-local.local/recipes/buckwheat-pancake-%e0%a4%ab%e0%a4%be%e0%a4%aa%e0%a4%b0%e0%a4%95%e0%a5%8b-%e0%a4%b0%e0%a5%8b%e0%a4%9f%e0%a5%80-%e0%a4%95%e0%a5%81%e0%a4%9f%e0%a5%8d%e0%a4%9f%e0%a5%82-%e0%a4%95%e0%a4%be-%e0%a4%9a/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/budget-friendly-fluffy-quinoa-rice-with-squash-soup/](http://quickfixwithbindu-local.local/recipes/budget-friendly-fluffy-quinoa-rice-with-squash-soup/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/budget-friendly-one-pot-chickpea-rice-cooling-yogurt-dip/](http://quickfixwithbindu-local.local/recipes/budget-friendly-one-pot-chickpea-rice-cooling-yogurt-dip/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/butterfly-pasta/](http://quickfixwithbindu-local.local/recipes/butterfly-pasta/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/cabbage-omelette/](http://quickfixwithbindu-local.local/recipes/cabbage-omelette/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/cheesy-avocado-egg-tortilla-wrap/](http://quickfixwithbindu-local.local/recipes/cheesy-avocado-egg-tortilla-wrap/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/cheesy-egg-quesadilla/](http://quickfixwithbindu-local.local/recipes/cheesy-egg-quesadilla/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/cheesy-egg-roll-japanese-style-omelette-recipe/](http://quickfixwithbindu-local.local/recipes/cheesy-egg-roll-japanese-style-omelette-recipe/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/cheesy-jalapeno-potato-bites/](http://quickfixwithbindu-local.local/recipes/cheesy-jalapeno-potato-bites/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/cheesy-multigrain-egg-sandwich/](http://quickfixwithbindu-local.local/recipes/cheesy-multigrain-egg-sandwich/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/cheesy-paneer-potatoes-in-airfryer/](http://quickfixwithbindu-local.local/recipes/cheesy-paneer-potatoes-in-airfryer/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/cheesy-pocket-wrap-with-mushroom-tofu/](http://quickfixwithbindu-local.local/recipes/cheesy-pocket-wrap-with-mushroom-tofu/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/cheesy-potato-bites/](http://quickfixwithbindu-local.local/recipes/cheesy-potato-bites/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/cheesy-potato-waffle-snack-no-oil-no-batter/](http://quickfixwithbindu-local.local/recipes/cheesy-potato-waffle-snack-no-oil-no-batter/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/cheesy-protein-packed-potatoes-in-airfryer/](http://quickfixwithbindu-local.local/recipes/cheesy-protein-packed-potatoes-in-airfryer/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/cheesy-spinach-omelette-tamagoyaki-high-protein-5-min-breakfast/](http://quickfixwithbindu-local.local/recipes/cheesy-spinach-omelette-tamagoyaki-high-protein-5-min-breakfast/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/cheesy-squash-blossom-cups/](http://quickfixwithbindu-local.local/recipes/cheesy-squash-blossom-cups/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/cheesy-stuffed-pattypan-squash-cups/](http://quickfixwithbindu-local.local/recipes/cheesy-stuffed-pattypan-squash-cups/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/cheesy-zucchini-onion-fritters/](http://quickfixwithbindu-local.local/recipes/cheesy-zucchini-onion-fritters/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/chia-banana-post-workout-smoothie/](http://quickfixwithbindu-local.local/recipes/chia-banana-post-workout-smoothie/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/chia-microgreens/](http://quickfixwithbindu-local.local/recipes/chia-microgreens/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/chickpea-avocado-almond-crackers-creamy-cilantro-dip/](http://quickfixwithbindu-local.local/recipes/chickpea-avocado-almond-crackers-creamy-cilantro-dip/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/chickpea-cauliflower-falafels-with-dill-no-onion-garlic/](http://quickfixwithbindu-local.local/recipes/chickpea-cauliflower-falafels-with-dill-no-onion-garlic/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/chickpea-eggs-paradise-protein-packed-curry-bowl/](http://quickfixwithbindu-local.local/recipes/chickpea-eggs-paradise-protein-packed-curry-bowl/) | 200 | 6 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/chickpea-rice-with-yogurt-dip/](http://quickfixwithbindu-local.local/recipes/chickpea-rice-with-yogurt-dip/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/chickpea-tofu-dill-salad/](http://quickfixwithbindu-local.local/recipes/chickpea-tofu-dill-salad/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/chickpea-veggie-flatbread/](http://quickfixwithbindu-local.local/recipes/chickpea-veggie-flatbread/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/chukauni-nepali-yogurt-potato-salad/](http://quickfixwithbindu-local.local/recipes/chukauni-nepali-yogurt-potato-salad/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/classic-shortbread-bites-2/](http://quickfixwithbindu-local.local/recipes/classic-shortbread-bites-2/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/classic-shortbread-bites/](http://quickfixwithbindu-local.local/recipes/classic-shortbread-bites/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/colorful-whole-moong-power-bowl/](http://quickfixwithbindu-local.local/recipes/colorful-whole-moong-power-bowl/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/cook-your-eggs-this-way-never-go-back-spinach-cheese-tamagoyaki/](http://quickfixwithbindu-local.local/recipes/cook-your-eggs-this-way-never-go-back-spinach-cheese-tamagoyaki/) | 200 | 275 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/cook-your-eggs-with-beans-and-youll-never-go-back-%f0%9f%8d%b3-the-japanese-tamagoyaki-secret/](http://quickfixwithbindu-local.local/recipes/cook-your-eggs-with-beans-and-youll-never-go-back-%f0%9f%8d%b3-the-japanese-tamagoyaki-secret/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/cook-your-eggs-with-beans-and-youll-never-go-back-the-japanese-tamagoyaki-secret/](http://quickfixwithbindu-local.local/recipes/cook-your-eggs-with-beans-and-youll-never-go-back-the-japanese-tamagoyaki-secret/) | 200 | 353 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/cottage-cheese-paneer-wrap-high-protein-flavor-packed-bbq-style/](http://quickfixwithbindu-local.local/recipes/cottage-cheese-paneer-wrap-high-protein-flavor-packed-bbq-style/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/cozy-zucchini-lentil-soup/](http://quickfixwithbindu-local.local/recipes/cozy-zucchini-lentil-soup/) | 200 | 7 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/creamy-banana-oats-smoothie-no-sugar-added/](http://quickfixwithbindu-local.local/recipes/creamy-banana-oats-smoothie-no-sugar-added/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/creamy-dill-potato-balls/](http://quickfixwithbindu-local.local/recipes/creamy-dill-potato-balls/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/creamy-egg-curry-with-potatoes/](http://quickfixwithbindu-local.local/recipes/creamy-egg-curry-with-potatoes/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/creamy-homemade-cashew-milk/](http://quickfixwithbindu-local.local/recipes/creamy-homemade-cashew-milk/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/creamy-oats-avocado-pancake/](http://quickfixwithbindu-local.local/recipes/creamy-oats-avocado-pancake/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/creamy-potato-dill-balls-easy-snack-with-surprise-filling-air-fryer-snack-everyone-loves/](http://quickfixwithbindu-local.local/recipes/creamy-potato-dill-balls-easy-snack-with-surprise-filling-air-fryer-snack-everyone-loves/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/creamy-tomato-soup/](http://quickfixwithbindu-local.local/recipes/creamy-tomato-soup/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-air-fried-asparagus-bites/](http://quickfixwithbindu-local.local/recipes/crispy-air-fried-asparagus-bites/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-air-fried-chickpea-lettuce-salad/](http://quickfixwithbindu-local.local/recipes/crispy-air-fried-chickpea-lettuce-salad/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-air-fryer-okra-10-minute-masala-magic-healthy-oil-free-snack/](http://quickfixwithbindu-local.local/recipes/crispy-air-fryer-okra-10-minute-masala-magic-healthy-oil-free-snack/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-air-fryer-okra-in-10-minutes/](http://quickfixwithbindu-local.local/recipes/crispy-air-fryer-okra-in-10-minutes/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-cabbage-flatbread/](http://quickfixwithbindu-local.local/recipes/crispy-cabbage-flatbread/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-cauliflower-bites-with-a-cornflakes-crunch/](http://quickfixwithbindu-local.local/recipes/crispy-cauliflower-bites-with-a-cornflakes-crunch/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-cheesy-zucchini-cabbage-fritters/](http://quickfixwithbindu-local.local/recipes/crispy-cheesy-zucchini-cabbage-fritters/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-chickpea-cauliflower-falafels-with-dill/](http://quickfixwithbindu-local.local/recipes/crispy-chickpea-cauliflower-falafels-with-dill/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-eggplant-fritters-2/](http://quickfixwithbindu-local.local/recipes/crispy-eggplant-fritters-2/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-eggplant-fritters/](http://quickfixwithbindu-local.local/recipes/crispy-eggplant-fritters/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-high-protein-red-lentil-bites-air-fryer/](http://quickfixwithbindu-local.local/recipes/crispy-high-protein-red-lentil-bites-air-fryer/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-lentil-tofu-bites/](http://quickfixwithbindu-local.local/recipes/crispy-lentil-tofu-bites/) | 200 | 4 | [/?s=lentil](http://quickfixwithbindu-local.local/?s=lentil) |
| [/recipes/crispy-onion-dill-fritters-onion-dill-pakoras-2/](http://quickfixwithbindu-local.local/recipes/crispy-onion-dill-fritters-onion-dill-pakoras-2/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-onion-dill-fritters-onion-dill-pakoras/](http://quickfixwithbindu-local.local/recipes/crispy-onion-dill-fritters-onion-dill-pakoras/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-poori-taco-with-chickpea-filling-indian-street-food-twist/](http://quickfixwithbindu-local.local/recipes/crispy-poori-taco-with-chickpea-filling-indian-street-food-twist/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-potato-egg-fritters/](http://quickfixwithbindu-local.local/recipes/crispy-potato-egg-fritters/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-potato-waffle/](http://quickfixwithbindu-local.local/recipes/crispy-potato-waffle/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-probiotic-dosa-simple-fermented-delight/](http://quickfixwithbindu-local.local/recipes/crispy-probiotic-dosa-simple-fermented-delight/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-quinoa-crusted-cauliflower-wings-with-mint-peanut-yogurt-dip/](http://quickfixwithbindu-local.local/recipes/crispy-quinoa-crusted-cauliflower-wings-with-mint-peanut-yogurt-dip/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-quinoa-eggplant-bites-tangy-dip/](http://quickfixwithbindu-local.local/recipes/crispy-quinoa-eggplant-bites-tangy-dip/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-quinoa-eggplant-bites/](http://quickfixwithbindu-local.local/recipes/crispy-quinoa-eggplant-bites/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-red-lentil-soy-chunk-vegan-nuggets/](http://quickfixwithbindu-local.local/recipes/crispy-red-lentil-soy-chunk-vegan-nuggets/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-saucy-pan-fried-dumplings-with-soy-sesame-glaze-tofu-paneer-momo/](http://quickfixwithbindu-local.local/recipes/crispy-saucy-pan-fried-dumplings-with-soy-sesame-glaze-tofu-paneer-momo/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-smashed-broccoli-potato-bake/](http://quickfixwithbindu-local.local/recipes/crispy-smashed-broccoli-potato-bake/) | 200 | 13 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-smashed-parmesan-potato-broccoli-tray-bake/](http://quickfixwithbindu-local.local/recipes/crispy-smashed-parmesan-potato-broccoli-tray-bake/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-triangle-tortilla-egg-wrap/](http://quickfixwithbindu-local.local/recipes/crispy-triangle-tortilla-egg-wrap/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-vegan-flatbread/](http://quickfixwithbindu-local.local/recipes/crispy-vegan-flatbread/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-vegan-low-carb-cabbage-flatbread/](http://quickfixwithbindu-local.local/recipes/crispy-vegan-low-carb-cabbage-flatbread/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crispy-zucchini-egg-fritters/](http://quickfixwithbindu-local.local/recipes/crispy-zucchini-egg-fritters/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crunchy-chickpea-lettuce-boats-with-creamy-bell-pepper-yogurt-dip/](http://quickfixwithbindu-local.local/recipes/crunchy-chickpea-lettuce-boats-with-creamy-bell-pepper-yogurt-dip/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/crunchy-crispy-cauliflower-bites/](http://quickfixwithbindu-local.local/recipes/crunchy-crispy-cauliflower-bites/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/deep-fry-vs-air-fry-huge-difference/](http://quickfixwithbindu-local.local/recipes/deep-fry-vs-air-fry-huge-difference/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/easiest-way-of-cooking-broccoli-zero-waste-garlic-stir-fry/](http://quickfixwithbindu-local.local/recipes/easiest-way-of-cooking-broccoli-zero-waste-garlic-stir-fry/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/easy-and-quick-homemade-seed-crackers/](http://quickfixwithbindu-local.local/recipes/easy-and-quick-homemade-seed-crackers/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/easy-beginners-recipe-with-pattypan-squash-no-splatter-just-flavor/](http://quickfixwithbindu-local.local/recipes/easy-beginners-recipe-with-pattypan-squash-no-splatter-just-flavor/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/easy-breakfast-waffle-with-banana-oats/](http://quickfixwithbindu-local.local/recipes/easy-breakfast-waffle-with-banana-oats/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/easy-broccoli-egg-omelet/](http://quickfixwithbindu-local.local/recipes/easy-broccoli-egg-omelet/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/easy-brownie-mix-cake/](http://quickfixwithbindu-local.local/recipes/easy-brownie-mix-cake/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/easy-eggless-whole-wheat-chocolate-chunk-cookies-batch-of-15-wholewheat-egglessbaking/](http://quickfixwithbindu-local.local/recipes/easy-eggless-whole-wheat-chocolate-chunk-cookies-batch-of-15-wholewheat-egglessbaking/) | 200 | 1 | [/recipes/page/14/](http://quickfixwithbindu-local.local/recipes/page/14/) |
| [/recipes/easy-flourless-lentil-oat-flatbreads-no-egg-no-gluten/](http://quickfixwithbindu-local.local/recipes/easy-flourless-lentil-oat-flatbreads-no-egg-no-gluten/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/easy-lentil-soup-bok-choy-stir-fry-rice-tomato-chutney-meal/](http://quickfixwithbindu-local.local/recipes/easy-lentil-soup-bok-choy-stir-fry-rice-tomato-chutney-meal/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/easy-lentil-spinach-soup/](http://quickfixwithbindu-local.local/recipes/easy-lentil-spinach-soup/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/easy-motichoor-laddu/](http://quickfixwithbindu-local.local/recipes/easy-motichoor-laddu/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/easy-mushroom-peas-fried-rice/](http://quickfixwithbindu-local.local/recipes/easy-mushroom-peas-fried-rice/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/easy-no-bake-energy-balls-date-pecan-snack-no-refined-sugar/](http://quickfixwithbindu-local.local/recipes/easy-no-bake-energy-balls-date-pecan-snack-no-refined-sugar/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/egg-and-sweet-corn-pizza/](http://quickfixwithbindu-local.local/recipes/egg-and-sweet-corn-pizza/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/egg-bread-toast-in-a-round-well/](http://quickfixwithbindu-local.local/recipes/egg-bread-toast-in-a-round-well/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/egg-cheese-sandwich-with-a-twist-of-dill/](http://quickfixwithbindu-local.local/recipes/egg-cheese-sandwich-with-a-twist-of-dill/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/egg-curry-recipe/](http://quickfixwithbindu-local.local/recipes/egg-curry-recipe/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/egg-mushroom-fried-rice/](http://quickfixwithbindu-local.local/recipes/egg-mushroom-fried-rice/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/egg-rice-fusion-skillet-quick-biryani-twist/](http://quickfixwithbindu-local.local/recipes/egg-rice-fusion-skillet-quick-biryani-twist/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/eggless-orange-cranberry-shortbread-cookies-tiny-buttery-crisp/](http://quickfixwithbindu-local.local/recipes/eggless-orange-cranberry-shortbread-cookies-tiny-buttery-crisp/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/fermented-dosa-that-tastes-like-south-indian-street-food/](http://quickfixwithbindu-local.local/recipes/fermented-dosa-that-tastes-like-south-indian-street-food/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/fermented-honey-lemon-ginger-syrup-natural-cough-remedy-immune-booster/](http://quickfixwithbindu-local.local/recipes/fermented-honey-lemon-ginger-syrup-natural-cough-remedy-immune-booster/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/flavor-packed-nepali-potatoes-tangy-spicy-vegan-in-15/](http://quickfixwithbindu-local.local/recipes/flavor-packed-nepali-potatoes-tangy-spicy-vegan-in-15/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/flax-plantain-flatbread/](http://quickfixwithbindu-local.local/recipes/flax-plantain-flatbread/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/flourless-crackers-i-didnt-expect-them-to-turn-out-this-good/](http://quickfixwithbindu-local.local/recipes/flourless-crackers-i-didnt-expect-them-to-turn-out-this-good/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/flourless-high-protein-lentil-tofu-bake/](http://quickfixwithbindu-local.local/recipes/flourless-high-protein-lentil-tofu-bake/) | 200 | 4 | [/?s=lentil](http://quickfixwithbindu-local.local/?s=lentil) |
| [/recipes/flourless-high-protein-moong-bean-flatbreads-wraps/](http://quickfixwithbindu-local.local/recipes/flourless-high-protein-moong-bean-flatbreads-wraps/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/flourless-low-carb-omelet-flatbread/](http://quickfixwithbindu-local.local/recipes/flourless-low-carb-omelet-flatbread/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/flourless-mung-bean-chia-crackers/](http://quickfixwithbindu-local.local/recipes/flourless-mung-bean-chia-crackers/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/flourless-mung-bean-high-protein-crackers/](http://quickfixwithbindu-local.local/recipes/flourless-mung-bean-high-protein-crackers/) | 200 | 97 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/flourless-mung-bean-oat-crackers/](http://quickfixwithbindu-local.local/recipes/flourless-mung-bean-oat-crackers/) | 200 | 97 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/flourless-mung-bean-protein-crackers-2/](http://quickfixwithbindu-local.local/recipes/flourless-mung-bean-protein-crackers-2/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/flourless-mung-bean-protein-crackers/](http://quickfixwithbindu-local.local/recipes/flourless-mung-bean-protein-crackers/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/flourless-oil-free-egg-free-kidney-friendly-oat-cookies/](http://quickfixwithbindu-local.local/recipes/flourless-oil-free-egg-free-kidney-friendly-oat-cookies/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/flourless-red-lentil-oats-pancakes-with-yogurt-dip/](http://quickfixwithbindu-local.local/recipes/flourless-red-lentil-oats-pancakes-with-yogurt-dip/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/flourless-red-lentil-seed-crackers/](http://quickfixwithbindu-local.local/recipes/flourless-red-lentil-seed-crackers/) | 200 | 98 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/flourless-red-lentil-soy-protein-crackers-2/](http://quickfixwithbindu-local.local/recipes/flourless-red-lentil-soy-protein-crackers-2/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/flourless-red-lentil-soy-protein-crackers/](http://quickfixwithbindu-local.local/recipes/flourless-red-lentil-soy-protein-crackers/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/fluffiest-banana-pancakes/](http://quickfixwithbindu-local.local/recipes/fluffiest-banana-pancakes/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/forget-bread-and-try-this-30g-lentil-quinoa-protein-bake-%f0%9f%8d%9e/](http://quickfixwithbindu-local.local/recipes/forget-bread-and-try-this-30g-lentil-quinoa-protein-bake-%f0%9f%8d%9e/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/fresh-romaine-lettuce-salad-with-zesty-honey-lemon-dressing/](http://quickfixwithbindu-local.local/recipes/fresh-romaine-lettuce-salad-with-zesty-honey-lemon-dressing/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/fresh-zesty-chickpea-salad/](http://quickfixwithbindu-local.local/recipes/fresh-zesty-chickpea-salad/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/fruit-salad/](http://quickfixwithbindu-local.local/recipes/fruit-salad/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/fusion-taco-style-puri-with-chickpea-curry/](http://quickfixwithbindu-local.local/recipes/fusion-taco-style-puri-with-chickpea-curry/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/garlic-butter-green-beans-stir-fry/](http://quickfixwithbindu-local.local/recipes/garlic-butter-green-beans-stir-fry/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/garlic-green-beans/](http://quickfixwithbindu-local.local/recipes/garlic-green-beans/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/garlicky-greens-in-5-minutes-%f0%9f%94%a5-just-2-ingredients-big-flavor/](http://quickfixwithbindu-local.local/recipes/garlicky-greens-in-5-minutes-%f0%9f%94%a5-just-2-ingredients-big-flavor/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/gentle-pink-beet-rice-crackers-for-sensitive-stomach/](http://quickfixwithbindu-local.local/recipes/gentle-pink-beet-rice-crackers-for-sensitive-stomach/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/golden-onion-omelet/](http://quickfixwithbindu-local.local/recipes/golden-onion-omelet/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/greek-yogurt-salad-with-sesame-chili-oil/](http://quickfixwithbindu-local.local/recipes/greek-yogurt-salad-with-sesame-chili-oil/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/grilled-cheese-and-creamy-tomato-soup/](http://quickfixwithbindu-local.local/recipes/grilled-cheese-and-creamy-tomato-soup/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/grow-fresh-chia-microgreens-in-7-days-with-just-paper-towels/](http://quickfixwithbindu-local.local/recipes/grow-fresh-chia-microgreens-in-7-days-with-just-paper-towels/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/guilt-free-crunchy-chickpeas-in-air-fryer/](http://quickfixwithbindu-local.local/recipes/guilt-free-crunchy-chickpeas-in-air-fryer/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/happy-canada-day-quick-celebrations/](http://quickfixwithbindu-local.local/recipes/happy-canada-day-quick-celebrations/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/healthy-bitter-melon-potato-curry-great-for-blood-pressure-karela-aloo-sabji/](http://quickfixwithbindu-local.local/recipes/healthy-bitter-melon-potato-curry-great-for-blood-pressure-karela-aloo-sabji/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/healthy-bitter-melon-potato-curry-karela-aloo-sabji/](http://quickfixwithbindu-local.local/recipes/healthy-bitter-melon-potato-curry-karela-aloo-sabji/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/healthy-chocolate-dipped-breakfast-cookies/](http://quickfixwithbindu-local.local/recipes/healthy-chocolate-dipped-breakfast-cookies/) | 200 | 5 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/healthy-christmas-bites-easy-low-carb-recipes/](http://quickfixwithbindu-local.local/recipes/healthy-christmas-bites-easy-low-carb-recipes/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/healthy-filling-chickpea-rice-with-yogurt-dip/](http://quickfixwithbindu-local.local/recipes/healthy-filling-chickpea-rice-with-yogurt-dip/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/healthy-flourless-peanut-butter-cookies-%f0%9f%8d%aa-soft-chewy/](http://quickfixwithbindu-local.local/recipes/healthy-flourless-peanut-butter-cookies-%f0%9f%8d%aa-soft-chewy/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/healthy-methi-tofu-stuffed-bun/](http://quickfixwithbindu-local.local/recipes/healthy-methi-tofu-stuffed-bun/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/healthy-omelet-lettuce-wrap-with-mint-peanut-yogurt-spread/](http://quickfixwithbindu-local.local/recipes/healthy-omelet-lettuce-wrap-with-mint-peanut-yogurt-spread/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/healthy-quick-egg-breakfast-with-air-fryer/](http://quickfixwithbindu-local.local/recipes/healthy-quick-egg-breakfast-with-air-fryer/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/high-protein-almond-cookies-in-30-minutes-just-3-ingredients/](http://quickfixwithbindu-local.local/recipes/high-protein-almond-cookies-in-30-minutes-just-3-ingredients/) | 200 | 5 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/high-protein-avocado-chickpea-crackers/](http://quickfixwithbindu-local.local/recipes/high-protein-avocado-chickpea-crackers/) | 200 | 276 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/high-protein-chickpea-avocado-crackers/](http://quickfixwithbindu-local.local/recipes/high-protein-chickpea-avocado-crackers/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/high-protein-chickpea-crackers-2/](http://quickfixwithbindu-local.local/recipes/high-protein-chickpea-crackers-2/) | 200 | 368 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/high-protein-chickpea-crackers/](http://quickfixwithbindu-local.local/recipes/high-protein-chickpea-crackers/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/high-protein-chickpea-tofu-salad/](http://quickfixwithbindu-local.local/recipes/high-protein-chickpea-tofu-salad/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/high-protein-crispy-smoky-tofu-pockets-with-avocado-dip/](http://quickfixwithbindu-local.local/recipes/high-protein-crispy-smoky-tofu-pockets-with-avocado-dip/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/high-protein-flourless-crackers-no-flour-no-eggs-just-crunch/](http://quickfixwithbindu-local.local/recipes/high-protein-flourless-crackers-no-flour-no-eggs-just-crunch/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/high-protein-flourless-red-lentil-crackers-2/](http://quickfixwithbindu-local.local/recipes/high-protein-flourless-red-lentil-crackers-2/) | 200 | 369 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/high-protein-flourless-red-lentil-crackers/](http://quickfixwithbindu-local.local/recipes/high-protein-flourless-red-lentil-crackers/) | 200 | 4 | [/?s=lentil](http://quickfixwithbindu-local.local/?s=lentil) |
| [/recipes/high-protein-lentil-quinoa-tofu-bake/](http://quickfixwithbindu-local.local/recipes/high-protein-lentil-quinoa-tofu-bake/) | 200 | 84 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/high-protein-low-carb-cheesy-egg-roll-japanese-style-omelette-lowcarb/](http://quickfixwithbindu-local.local/recipes/high-protein-low-carb-cheesy-egg-roll-japanese-style-omelette-lowcarb/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/high-protein-moong-beans-egg-sandwich/](http://quickfixwithbindu-local.local/recipes/high-protein-moong-beans-egg-sandwich/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/high-protein-mung-bean-crackers/](http://quickfixwithbindu-local.local/recipes/high-protein-mung-bean-crackers/) | 200 | 279 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/high-protein-red-lentil-crackers-sweet-potato-seeds-spices/](http://quickfixwithbindu-local.local/recipes/high-protein-red-lentil-crackers-sweet-potato-seeds-spices/) | 200 | 369 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/high-protein-red-lentil-seed-crackers/](http://quickfixwithbindu-local.local/recipes/high-protein-red-lentil-seed-crackers/) | 200 | 4 | [/?s=lentil](http://quickfixwithbindu-local.local/?s=lentil) |
| [/recipes/high-protein-red-lentil-sweet-potato-crackers/](http://quickfixwithbindu-local.local/recipes/high-protein-red-lentil-sweet-potato-crackers/) | 200 | 4 | [/?s=lentil](http://quickfixwithbindu-local.local/?s=lentil) |
| [/recipes/high-protein-red-lentil-tomato-crackers/](http://quickfixwithbindu-local.local/recipes/high-protein-red-lentil-tomato-crackers/) | 200 | 7 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/high-protein-roasted-chickpea-green-salad/](http://quickfixwithbindu-local.local/recipes/high-protein-roasted-chickpea-green-salad/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/high-protein-salad-warm-roasted-chickpea-salad-with-tangy-mint-peanut-dressing-healthy-recipe/](http://quickfixwithbindu-local.local/recipes/high-protein-salad-warm-roasted-chickpea-salad-with-tangy-mint-peanut-dressing-healthy-recipe/) | 200 | 83 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/high-protein-seed-crackers-flourless/](http://quickfixwithbindu-local.local/recipes/high-protein-seed-crackers-flourless/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/high-protein-smashed-tofu-watermelon-salad/](http://quickfixwithbindu-local.local/recipes/high-protein-smashed-tofu-watermelon-salad/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/high-protein-sweet-potato-mung-bean-crackers/](http://quickfixwithbindu-local.local/recipes/high-protein-sweet-potato-mung-bean-crackers/) | 200 | 276 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/high-protein-tofu-cauliflower-steak/](http://quickfixwithbindu-local.local/recipes/high-protein-tofu-cauliflower-steak/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/high-protein-tofu-spinach-pockets-easy-vegetarian-wraps/](http://quickfixwithbindu-local.local/recipes/high-protein-tofu-spinach-pockets-easy-vegetarian-wraps/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/high-protein-vegan-lentil-fritters-flourless-gluten-free/](http://quickfixwithbindu-local.local/recipes/high-protein-vegan-lentil-fritters-flourless-gluten-free/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/high-protein-white-beans-soup/](http://quickfixwithbindu-local.local/recipes/high-protein-white-beans-soup/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/high-protein-yellow-pea-spinach-stew-nutritious-budget-friendly/](http://quickfixwithbindu-local.local/recipes/high-protein-yellow-pea-spinach-stew-nutritious-budget-friendly/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/high-protein-zero-waste-flatbread-in-just-2-ingredients-2/](http://quickfixwithbindu-local.local/recipes/high-protein-zero-waste-flatbread-in-just-2-ingredients-2/) | 200 | 6 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/high-protein-zero-waste-flatbread-in-just-2-ingredients/](http://quickfixwithbindu-local.local/recipes/high-protein-zero-waste-flatbread-in-just-2-ingredients/) | 200 | 1 | [/recipes/page/13/](http://quickfixwithbindu-local.local/recipes/page/13/) |
| [/recipes/homemade-veg-momo-with-tofu-paneer-dumplings-with-spicy-chutney/](http://quickfixwithbindu-local.local/recipes/homemade-veg-momo-with-tofu-paneer-dumplings-with-spicy-chutney/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/homemade-whole-wheat-pizza-with-fresh-mozzarella-basil/](http://quickfixwithbindu-local.local/recipes/homemade-whole-wheat-pizza-with-fresh-mozzarella-basil/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/honey-cinnamon-milk-grandmas-secret-cold-remedy/](http://quickfixwithbindu-local.local/recipes/honey-cinnamon-milk-grandmas-secret-cold-remedy/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/honey-ginger-rosemary-drink-for-cold-cough/](http://quickfixwithbindu-local.local/recipes/honey-ginger-rosemary-drink-for-cold-cough/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/how-to-eat-rambutan/](http://quickfixwithbindu-local.local/recipes/how-to-eat-rambutan/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/i-never-thought-potatoes-eggs-could-taste-this-good-in-one-pan-2/](http://quickfixwithbindu-local.local/recipes/i-never-thought-potatoes-eggs-could-taste-this-good-in-one-pan-2/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/i-never-thought-potatoes-eggs-could-taste-this-good-in-one-pan/](http://quickfixwithbindu-local.local/recipes/i-never-thought-potatoes-eggs-could-taste-this-good-in-one-pan/) | 200 | 353 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/i-stopped-buying-pickled-jalapenos-and-made-them-better-at-home/](http://quickfixwithbindu-local.local/recipes/i-stopped-buying-pickled-jalapenos-and-made-them-better-at-home/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/if-you-dont-eat-flour-make-this-high-protein-lentil-tofu-bake-30g-protein-lunch/](http://quickfixwithbindu-local.local/recipes/if-you-dont-eat-flour-make-this-high-protein-lentil-tofu-bake-30g-protein-lunch/) | 200 | 84 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/if-you-have-almond-flour-make-these-easy-gluten-free-crackers-2/](http://quickfixwithbindu-local.local/recipes/if-you-have-almond-flour-make-these-easy-gluten-free-crackers-2/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/if-you-have-almond-flour-make-these-easy-gluten-free-crackers/](http://quickfixwithbindu-local.local/recipes/if-you-have-almond-flour-make-these-easy-gluten-free-crackers/) | 200 | 5 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/if-you-have-overripe-bananas-dont-throw-them-out-make-this-eggless-banana-muffins/](http://quickfixwithbindu-local.local/recipes/if-you-have-overripe-bananas-dont-throw-them-out-make-this-eggless-banana-muffins/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/if-you-have-strawberries-%f0%9f%8d%93-make-this-strawberry-milk-instead-of-buying-it-from-store-2/](http://quickfixwithbindu-local.local/recipes/if-you-have-strawberries-%f0%9f%8d%93-make-this-strawberry-milk-instead-of-buying-it-from-store-2/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/if-you-have-strawberries-%f0%9f%8d%93-make-this-strawberry-milk-instead-of-buying-it-from-store/](http://quickfixwithbindu-local.local/recipes/if-you-have-strawberries-%f0%9f%8d%93-make-this-strawberry-milk-instead-of-buying-it-from-store/) | 200 | 5 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/if-you-love-oranges-and-cranberries-make-this-eggless-better-than-bakery-shortbread-bites/](http://quickfixwithbindu-local.local/recipes/if-you-love-oranges-and-cranberries-make-this-eggless-better-than-bakery-shortbread-bites/) | 200 | 5 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/instant-no-flour-protein-pancakes-lentil-oats-recipe/](http://quickfixwithbindu-local.local/recipes/instant-no-flour-protein-pancakes-lentil-oats-recipe/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/iron-rich-taro-leaf-curry-with-paneer/](http://quickfixwithbindu-local.local/recipes/iron-rich-taro-leaf-curry-with-paneer/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/irresistible-vegan-meatball/](http://quickfixwithbindu-local.local/recipes/irresistible-vegan-meatball/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/italian-style-chestnuts-at-home/](http://quickfixwithbindu-local.local/recipes/italian-style-chestnuts-at-home/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/japanese-layered-omelet-cheesy-colorful-breakfast/](http://quickfixwithbindu-local.local/recipes/japanese-layered-omelet-cheesy-colorful-breakfast/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/just-2-ingredients-oats-and-spinach-flatbread-stuffed-buns/](http://quickfixwithbindu-local.local/recipes/just-2-ingredients-oats-and-spinach-flatbread-stuffed-buns/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/karela-aloo-sabji-bitter-melon-and-potato-stir-fry/](http://quickfixwithbindu-local.local/recipes/karela-aloo-sabji-bitter-melon-and-potato-stir-fry/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/keto-cheesy-almond-flour-crackers/](http://quickfixwithbindu-local.local/recipes/keto-cheesy-almond-flour-crackers/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/keto-friendly-avocado-egg-boat/](http://quickfixwithbindu-local.local/recipes/keto-friendly-avocado-egg-boat/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/keto-friendly-flourless-high-protein-flatbread-sprouted-moong-beans-dill/](http://quickfixwithbindu-local.local/recipes/keto-friendly-flourless-high-protein-flatbread-sprouted-moong-beans-dill/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/kettle-style-air-fried-potato-rolls/](http://quickfixwithbindu-local.local/recipes/kettle-style-air-fried-potato-rolls/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/kidney-friendly-applesauce-oat-cookies-flourless-oil-free/](http://quickfixwithbindu-local.local/recipes/kidney-friendly-applesauce-oat-cookies-flourless-oil-free/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/leftover-quinoa-veggie-stir-fry/](http://quickfixwithbindu-local.local/recipes/leftover-quinoa-veggie-stir-fry/) | 200 | 6 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/leftover-rice-crackers-gluten-free-light/](http://quickfixwithbindu-local.local/recipes/leftover-rice-crackers-gluten-free-light/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/looks-like-cake-but-its-my-favorite-flourless-high-protein-savory-lunch/](http://quickfixwithbindu-local.local/recipes/looks-like-cake-but-its-my-favorite-flourless-high-protein-savory-lunch/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/love-at-first-bite-10-minute-valentines-breakfast-veggie-heart-omelets/](http://quickfixwithbindu-local.local/recipes/love-at-first-bite-10-minute-valentines-breakfast-veggie-heart-omelets/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/low-carb-cauliflower-omelet-high-protein-healthy-breakfast/](http://quickfixwithbindu-local.local/recipes/low-carb-cauliflower-omelet-high-protein-healthy-breakfast/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/low-carb-crispy-cabbage-flatbread/](http://quickfixwithbindu-local.local/recipes/low-carb-crispy-cabbage-flatbread/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/low-carb-no-flip-mushroom-omelet-tomato-jalapeno-egg-breakfast/](http://quickfixwithbindu-local.local/recipes/low-carb-no-flip-mushroom-omelet-tomato-jalapeno-egg-breakfast/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/low-carb-omelet-that-looks-like-a-flatbread/](http://quickfixwithbindu-local.local/recipes/low-carb-omelet-that-looks-like-a-flatbread/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/low-carb-tomato-omelet-that-looks-like-flatbread/](http://quickfixwithbindu-local.local/recipes/low-carb-tomato-omelet-that-looks-like-flatbread/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/low-sugar-eggless-flower-cookies-easy-two-tone-floral-shortbread-2/](http://quickfixwithbindu-local.local/recipes/low-sugar-eggless-flower-cookies-easy-two-tone-floral-shortbread-2/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/low-sugar-eggless-flower-cookies-easy-two-tone-floral-shortbread/](http://quickfixwithbindu-local.local/recipes/low-sugar-eggless-flower-cookies-easy-two-tone-floral-shortbread/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/maggi-gets-a-protein-makeover-tofu-egg-masala-magic-in-1-bowl/](http://quickfixwithbindu-local.local/recipes/maggi-gets-a-protein-makeover-tofu-egg-masala-magic-in-1-bowl/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/mango-banana-orange-protein-bowl/](http://quickfixwithbindu-local.local/recipes/mango-banana-orange-protein-bowl/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/mango-mojito-in-minutes-%f0%9f%a5%ad-summers-best-drink/](http://quickfixwithbindu-local.local/recipes/mango-mojito-in-minutes-%f0%9f%a5%ad-summers-best-drink/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/mango-orange-banana-protein-ice-cream/](http://quickfixwithbindu-local.local/recipes/mango-orange-banana-protein-ice-cream/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/marry-me-chickpeas-with-spinach/](http://quickfixwithbindu-local.local/recipes/marry-me-chickpeas-with-spinach/) | 200 | 176 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/masala-maggi-korean-style-10-minute-spicy-fusion-noodles/](http://quickfixwithbindu-local.local/recipes/masala-maggi-korean-style-10-minute-spicy-fusion-noodles/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/moms-birthday-feast-for-dad/](http://quickfixwithbindu-local.local/recipes/moms-birthday-feast-for-dad/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/morning-detox-drink-for-toned-body-glowing-skin-better-metabolism-honey-lemon-ginger-chia/](http://quickfixwithbindu-local.local/recipes/morning-detox-drink-for-toned-body-glowing-skin-better-metabolism-honey-lemon-ginger-chia/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/mung-bean-chickpea-flour-high-protein-crackers/](http://quickfixwithbindu-local.local/recipes/mung-bean-chickpea-flour-high-protein-crackers/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/mung-bean-tofu-tamagoyaki-rolls-with-tomato-basil-gravy/](http://quickfixwithbindu-local.local/recipes/mung-bean-tofu-tamagoyaki-rolls-with-tomato-basil-gravy/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/mung-bean-tofu-tamagoyaki-with-hemp-yogurt-dip/](http://quickfixwithbindu-local.local/recipes/mung-bean-tofu-tamagoyaki-with-hemp-yogurt-dip/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/mushroom-and-asparagus-stir-fry-quinoa-salad-combo/](http://quickfixwithbindu-local.local/recipes/mushroom-and-asparagus-stir-fry-quinoa-salad-combo/) | 200 | 6 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/mushroom-and-asparagus-stir-fry/](http://quickfixwithbindu-local.local/recipes/mushroom-and-asparagus-stir-fry/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/mushroom-stir-fry-gravy/](http://quickfixwithbindu-local.local/recipes/mushroom-stir-fry-gravy/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/my-daughter-says-my-veg-momos-%f0%9f%a5%9f-beat-any-5-star-hotel/](http://quickfixwithbindu-local.local/recipes/my-daughter-says-my-veg-momos-%f0%9f%a5%9f-beat-any-5-star-hotel/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/my-fluffy-belgian-waffle-recipe/](http://quickfixwithbindu-local.local/recipes/my-fluffy-belgian-waffle-recipe/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/my-secret-tomato-pickle-recipe-for-rice-roti/](http://quickfixwithbindu-local.local/recipes/my-secret-tomato-pickle-recipe-for-rice-roti/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/natural-cough-and-cold-remedy/](http://quickfixwithbindu-local.local/recipes/natural-cough-and-cold-remedy/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/natural-cough-and-cold-syrup-with-just-3-ingredients-fermented-ginger-lemon-honey-drink/](http://quickfixwithbindu-local.local/recipes/natural-cough-and-cold-syrup-with-just-3-ingredients-fermented-ginger-lemon-honey-drink/) | 200 | 14 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/natural-cough-immunity-shot-orange-lemon-ginger-turmeric/](http://quickfixwithbindu-local.local/recipes/natural-cough-immunity-shot-orange-lemon-ginger-turmeric/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/natural-cough-syrup-honey-ginger-lemon-remedy/](http://quickfixwithbindu-local.local/recipes/natural-cough-syrup-honey-ginger-lemon-remedy/) | 200 | 14 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/natural-cough-syrup-passed-down-for-generations/](http://quickfixwithbindu-local.local/recipes/natural-cough-syrup-passed-down-for-generations/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/natural-cough-syrup-with-3-ingredients-honey-ginger-lime/](http://quickfixwithbindu-local.local/recipes/natural-cough-syrup-with-3-ingredients-honey-ginger-lime/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/natural-detox-drink-ginger-turmeric-immunity-cubes-shots/](http://quickfixwithbindu-local.local/recipes/natural-detox-drink-ginger-turmeric-immunity-cubes-shots/) | 200 | 16 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/natural-honey-lemon-ginger-detox-drink/](http://quickfixwithbindu-local.local/recipes/natural-honey-lemon-ginger-detox-drink/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/nepali-style-potato-salad-chukauni/](http://quickfixwithbindu-local.local/recipes/nepali-style-potato-salad-chukauni/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/never-imagined-quinoa-and-banana-could-make-this-soft-wholesome-loaf/](http://quickfixwithbindu-local.local/recipes/never-imagined-quinoa-and-banana-could-make-this-soft-wholesome-loaf/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/no-flip-corn-pizza-in-one-pan/](http://quickfixwithbindu-local.local/recipes/no-flip-corn-pizza-in-one-pan/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/no-flip-spinach-cheese-omelet-2/](http://quickfixwithbindu-local.local/recipes/no-flip-spinach-cheese-omelet-2/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/no-flip-spinach-cheese-omelet-3/](http://quickfixwithbindu-local.local/recipes/no-flip-spinach-cheese-omelet-3/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/no-flip-spinach-cheese-omelet/](http://quickfixwithbindu-local.local/recipes/no-flip-spinach-cheese-omelet/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/no-flour-flatbread-potato-chickpea-healthy-bread-gluten-free-veggie-pancake/](http://quickfixwithbindu-local.local/recipes/no-flour-flatbread-potato-chickpea-healthy-bread-gluten-free-veggie-pancake/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/no-flour-no-egg-oats-zucchini-flatbread-youll-make-every-week/](http://quickfixwithbindu-local.local/recipes/no-flour-no-egg-oats-zucchini-flatbread-youll-make-every-week/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/no-flour-no-eggs-high-protein-gluten-free-lentil-flatbread/](http://quickfixwithbindu-local.local/recipes/no-flour-no-eggs-high-protein-gluten-free-lentil-flatbread/) | 200 | 18 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/no-flour-oats-avocado-pancake-%f0%9f%a5%91-soft-creamy-gluten-free/](http://quickfixwithbindu-local.local/recipes/no-flour-oats-avocado-pancake-%f0%9f%a5%91-soft-creamy-gluten-free/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/no-flour-protein-pancake-lentils-oats-recipe/](http://quickfixwithbindu-local.local/recipes/no-flour-protein-pancake-lentils-oats-recipe/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/no-sugar-fluffiest-banana-pancakes-that-taste-like-dessert/](http://quickfixwithbindu-local.local/recipes/no-sugar-fluffiest-banana-pancakes-that-taste-like-dessert/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/oats-sorghum-avocado-flatbread/](http://quickfixwithbindu-local.local/recipes/oats-sorghum-avocado-flatbread/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/oats-sorghum-flatbread/](http://quickfixwithbindu-local.local/recipes/oats-sorghum-flatbread/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/one-pan-low-gi-oats-sorghum-avocado-flatbread/](http://quickfixwithbindu-local.local/recipes/one-pan-low-gi-oats-sorghum-avocado-flatbread/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/orange-cranberry-shortbread-cookies-bite-sized-eggless/](http://quickfixwithbindu-local.local/recipes/orange-cranberry-shortbread-cookies-bite-sized-eggless/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/page/10/](http://quickfixwithbindu-local.local/recipes/page/10/) | 200 | 3 | [/recipes/page/10/](http://quickfixwithbindu-local.local/recipes/page/10/) |
| [/recipes/page/11/](http://quickfixwithbindu-local.local/recipes/page/11/) | 200 | 3 | [/recipes/page/10/](http://quickfixwithbindu-local.local/recipes/page/10/) |
| [/recipes/page/12/](http://quickfixwithbindu-local.local/recipes/page/12/) | 200 | 3 | [/recipes/page/11/](http://quickfixwithbindu-local.local/recipes/page/11/) |
| [/recipes/page/13/](http://quickfixwithbindu-local.local/recipes/page/13/) | 200 | 3 | [/recipes/page/12/](http://quickfixwithbindu-local.local/recipes/page/12/) |
| [/recipes/page/14/](http://quickfixwithbindu-local.local/recipes/page/14/) | 200 | 3 | [/recipes/page/13/](http://quickfixwithbindu-local.local/recipes/page/13/) |
| [/recipes/page/15/](http://quickfixwithbindu-local.local/recipes/page/15/) | 200 | 3 | [/recipes/page/14/](http://quickfixwithbindu-local.local/recipes/page/14/) |
| [/recipes/page/16/](http://quickfixwithbindu-local.local/recipes/page/16/) | 200 | 3 | [/recipes/page/15/](http://quickfixwithbindu-local.local/recipes/page/15/) |
| [/recipes/page/17/](http://quickfixwithbindu-local.local/recipes/page/17/) | 200 | 3 | [/recipes/page/16/](http://quickfixwithbindu-local.local/recipes/page/16/) |
| [/recipes/page/18/](http://quickfixwithbindu-local.local/recipes/page/18/) | 200 | 3 | [/recipes/page/17/](http://quickfixwithbindu-local.local/recipes/page/17/) |
| [/recipes/page/19/](http://quickfixwithbindu-local.local/recipes/page/19/) | 200 | 3 | [/recipes/page/18/](http://quickfixwithbindu-local.local/recipes/page/18/) |
| [/recipes/page/2/](http://quickfixwithbindu-local.local/recipes/page/2/) | 200 | 3 | [/recipes/](http://quickfixwithbindu-local.local/recipes/) |
| [/recipes/page/20/](http://quickfixwithbindu-local.local/recipes/page/20/) | 200 | 3 | [/recipes/page/19/](http://quickfixwithbindu-local.local/recipes/page/19/) |
| [/recipes/page/21/](http://quickfixwithbindu-local.local/recipes/page/21/) | 200 | 3 | [/recipes/page/20/](http://quickfixwithbindu-local.local/recipes/page/20/) |
| [/recipes/page/22/](http://quickfixwithbindu-local.local/recipes/page/22/) | 200 | 3 | [/recipes/page/21/](http://quickfixwithbindu-local.local/recipes/page/21/) |
| [/recipes/page/23/](http://quickfixwithbindu-local.local/recipes/page/23/) | 200 | 3 | [/recipes/page/22/](http://quickfixwithbindu-local.local/recipes/page/22/) |
| [/recipes/page/24/](http://quickfixwithbindu-local.local/recipes/page/24/) | 200 | 3 | [/recipes/page/23/](http://quickfixwithbindu-local.local/recipes/page/23/) |
| [/recipes/page/25/](http://quickfixwithbindu-local.local/recipes/page/25/) | 200 | 3 | [/recipes/page/24/](http://quickfixwithbindu-local.local/recipes/page/24/) |
| [/recipes/page/26/](http://quickfixwithbindu-local.local/recipes/page/26/) | 200 | 3 | [/recipes/page/25/](http://quickfixwithbindu-local.local/recipes/page/25/) |
| [/recipes/page/27/](http://quickfixwithbindu-local.local/recipes/page/27/) | 200 | 3 | [/recipes/page/26/](http://quickfixwithbindu-local.local/recipes/page/26/) |
| [/recipes/page/28/](http://quickfixwithbindu-local.local/recipes/page/28/) | 200 | 3 | [/recipes/page/27/](http://quickfixwithbindu-local.local/recipes/page/27/) |
| [/recipes/page/29/](http://quickfixwithbindu-local.local/recipes/page/29/) | 200 | 3 | [/recipes/page/28/](http://quickfixwithbindu-local.local/recipes/page/28/) |
| [/recipes/page/3/](http://quickfixwithbindu-local.local/recipes/page/3/) | 200 | 3 | [/recipes/page/2/](http://quickfixwithbindu-local.local/recipes/page/2/) |
| [/recipes/page/30/](http://quickfixwithbindu-local.local/recipes/page/30/) | 200 | 3 | [/recipes/page/29/](http://quickfixwithbindu-local.local/recipes/page/29/) |
| [/recipes/page/31/](http://quickfixwithbindu-local.local/recipes/page/31/) | 200 | 3 | [/recipes/page/30/](http://quickfixwithbindu-local.local/recipes/page/30/) |
| [/recipes/page/32/](http://quickfixwithbindu-local.local/recipes/page/32/) | 200 | 3 | [/recipes/page/31/](http://quickfixwithbindu-local.local/recipes/page/31/) |
| [/recipes/page/33/](http://quickfixwithbindu-local.local/recipes/page/33/) | 200 | 3 | [/recipes/page/32/](http://quickfixwithbindu-local.local/recipes/page/32/) |
| [/recipes/page/34/](http://quickfixwithbindu-local.local/recipes/page/34/) | 200 | 3 | [/recipes/page/33/](http://quickfixwithbindu-local.local/recipes/page/33/) |
| [/recipes/page/35/](http://quickfixwithbindu-local.local/recipes/page/35/) | 200 | 3 | [/recipes/page/34/](http://quickfixwithbindu-local.local/recipes/page/34/) |
| [/recipes/page/36/](http://quickfixwithbindu-local.local/recipes/page/36/) | 200 | 3 | [/recipes/page/35/](http://quickfixwithbindu-local.local/recipes/page/35/) |
| [/recipes/page/37/](http://quickfixwithbindu-local.local/recipes/page/37/) | 200 | 3 | [/recipes/page/36/](http://quickfixwithbindu-local.local/recipes/page/36/) |
| [/recipes/page/38/](http://quickfixwithbindu-local.local/recipes/page/38/) | 200 | 3 | [/recipes/page/37/](http://quickfixwithbindu-local.local/recipes/page/37/) |
| [/recipes/page/39/](http://quickfixwithbindu-local.local/recipes/page/39/) | 200 | 3 | [/recipes/page/38/](http://quickfixwithbindu-local.local/recipes/page/38/) |
| [/recipes/page/4/](http://quickfixwithbindu-local.local/recipes/page/4/) | 200 | 3 | [/recipes/page/3/](http://quickfixwithbindu-local.local/recipes/page/3/) |
| [/recipes/page/40/](http://quickfixwithbindu-local.local/recipes/page/40/) | 200 | 3 | [/recipes/page/39/](http://quickfixwithbindu-local.local/recipes/page/39/) |
| [/recipes/page/41/](http://quickfixwithbindu-local.local/recipes/page/41/) | 200 | 3 | [/recipes/page/40/](http://quickfixwithbindu-local.local/recipes/page/40/) |
| [/recipes/page/42/](http://quickfixwithbindu-local.local/recipes/page/42/) | 200 | 3 | [/recipes/page/41/](http://quickfixwithbindu-local.local/recipes/page/41/) |
| [/recipes/page/43/](http://quickfixwithbindu-local.local/recipes/page/43/) | 200 | 3 | [/recipes/page/42/](http://quickfixwithbindu-local.local/recipes/page/42/) |
| [/recipes/page/44/](http://quickfixwithbindu-local.local/recipes/page/44/) | 200 | 3 | [/recipes/page/43/](http://quickfixwithbindu-local.local/recipes/page/43/) |
| [/recipes/page/45/](http://quickfixwithbindu-local.local/recipes/page/45/) | 200 | 3 | [/recipes/page/44/](http://quickfixwithbindu-local.local/recipes/page/44/) |
| [/recipes/page/46/](http://quickfixwithbindu-local.local/recipes/page/46/) | 200 | 3 | [/recipes/page/45/](http://quickfixwithbindu-local.local/recipes/page/45/) |
| [/recipes/page/47/](http://quickfixwithbindu-local.local/recipes/page/47/) | 200 | 2 | [/recipes/page/46/](http://quickfixwithbindu-local.local/recipes/page/46/) |
| [/recipes/page/5/](http://quickfixwithbindu-local.local/recipes/page/5/) | 200 | 3 | [/recipes/page/4/](http://quickfixwithbindu-local.local/recipes/page/4/) |
| [/recipes/page/6/](http://quickfixwithbindu-local.local/recipes/page/6/) | 200 | 3 | [/recipes/page/5/](http://quickfixwithbindu-local.local/recipes/page/5/) |
| [/recipes/page/7/](http://quickfixwithbindu-local.local/recipes/page/7/) | 200 | 3 | [/recipes/page/6/](http://quickfixwithbindu-local.local/recipes/page/6/) |
| [/recipes/page/8/](http://quickfixwithbindu-local.local/recipes/page/8/) | 200 | 3 | [/recipes/page/7/](http://quickfixwithbindu-local.local/recipes/page/7/) |
| [/recipes/page/9/](http://quickfixwithbindu-local.local/recipes/page/9/) | 200 | 3 | [/recipes/page/10/](http://quickfixwithbindu-local.local/recipes/page/10/) |
| [/recipes/pattypan-stir-fry/](http://quickfixwithbindu-local.local/recipes/pattypan-stir-fry/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/peanut-chocolate-date-bars/](http://quickfixwithbindu-local.local/recipes/peanut-chocolate-date-bars/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/perfect-stovetop-popcorn/](http://quickfixwithbindu-local.local/recipes/perfect-stovetop-popcorn/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/pickled-red-onions-jalapenos-at-home-not-buying-them-anymore/](http://quickfixwithbindu-local.local/recipes/pickled-red-onions-jalapenos-at-home-not-buying-them-anymore/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/plant-based-high-fiber-meal-under-5-leftover-quinoa-stir-fry/](http://quickfixwithbindu-local.local/recipes/plant-based-high-fiber-meal-under-5-leftover-quinoa-stir-fry/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/pomegranate-chocolate-bites/](http://quickfixwithbindu-local.local/recipes/pomegranate-chocolate-bites/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/protein-packed-black-eyed-beans-soup/](http://quickfixwithbindu-local.local/recipes/protein-packed-black-eyed-beans-soup/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/protein-packed-breakfast-cookies/](http://quickfixwithbindu-local.local/recipes/protein-packed-breakfast-cookies/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/protein-packed-creamy-pasta-no-straining-no-fuss/](http://quickfixwithbindu-local.local/recipes/protein-packed-creamy-pasta-no-straining-no-fuss/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/protein-packed-quinoa-tofu-veggie-bowl-healthy-easy-delicious/](http://quickfixwithbindu-local.local/recipes/protein-packed-quinoa-tofu-veggie-bowl-healthy-easy-delicious/) | 200 | 6 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/protein-packed-veggie-burger/](http://quickfixwithbindu-local.local/recipes/protein-packed-veggie-burger/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/protein-pockets/](http://quickfixwithbindu-local.local/recipes/protein-pockets/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/protein-rich-black-eyed-beans-soup/](http://quickfixwithbindu-local.local/recipes/protein-rich-black-eyed-beans-soup/) | 200 | 6 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/protein-rich-black-eyed-peas-soup/](http://quickfixwithbindu-local.local/recipes/protein-rich-black-eyed-peas-soup/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/pure-semolina-momo/](http://quickfixwithbindu-local.local/recipes/pure-semolina-momo/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/quick-and-easy-high-protein-plant-based-soup/](http://quickfixwithbindu-local.local/recipes/quick-and-easy-high-protein-plant-based-soup/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/quick-and-easy-lentil-soup-bok-choy-stir-fry-rice-tomato-chutney/](http://quickfixwithbindu-local.local/recipes/quick-and-easy-lentil-soup-bok-choy-stir-fry-rice-tomato-chutney/) | 200 | 6 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/quick-cauliflower-stir-fry/](http://quickfixwithbindu-local.local/recipes/quick-cauliflower-stir-fry/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/quick-cucumber-yogurt-salad/](http://quickfixwithbindu-local.local/recipes/quick-cucumber-yogurt-salad/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/quick-easy-bok-choy-stir-fry-recipe/](http://quickfixwithbindu-local.local/recipes/quick-easy-bok-choy-stir-fry-recipe/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/quick-easy-vegan-mushroom-pasta/](http://quickfixwithbindu-local.local/recipes/quick-easy-vegan-mushroom-pasta/) | 200 | 6 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/quick-egg-mushroom-sandwich/](http://quickfixwithbindu-local.local/recipes/quick-egg-mushroom-sandwich/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/quick-fruit-salad-summer-fruit-bowl-with-chia-seeds-honey/](http://quickfixwithbindu-local.local/recipes/quick-fruit-salad-summer-fruit-bowl-with-chia-seeds-honey/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/quick-healthy-dilly-chickpeas/](http://quickfixwithbindu-local.local/recipes/quick-healthy-dilly-chickpeas/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/quick-healthy-overnight-oats-breakfast/](http://quickfixwithbindu-local.local/recipes/quick-healthy-overnight-oats-breakfast/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/quick-high-protein-soup-with-zucchini-lentils/](http://quickfixwithbindu-local.local/recipes/quick-high-protein-soup-with-zucchini-lentils/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/quick-low-carb-cabbage-omelet-easy-high-protein-low-carb-breakfast/](http://quickfixwithbindu-local.local/recipes/quick-low-carb-cabbage-omelet-easy-high-protein-low-carb-breakfast/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/quick-mushroom-and-egg-frittata-2/](http://quickfixwithbindu-local.local/recipes/quick-mushroom-and-egg-frittata-2/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/quick-mushroom-and-egg-frittata/](http://quickfixwithbindu-local.local/recipes/quick-mushroom-and-egg-frittata/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/quick-omelet-wrap-with-veggies-cheese/](http://quickfixwithbindu-local.local/recipes/quick-omelet-wrap-with-veggies-cheese/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/quick-quinoa-veggie-stir-fry/](http://quickfixwithbindu-local.local/recipes/quick-quinoa-veggie-stir-fry/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/quick-red-lentil-spinach-soup/](http://quickfixwithbindu-local.local/recipes/quick-red-lentil-spinach-soup/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/quick-stuffed-cabbage-rolls-with-spicy-nutty-sauce/](http://quickfixwithbindu-local.local/recipes/quick-stuffed-cabbage-rolls-with-spicy-nutty-sauce/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/quick-tasty-breakfast-idea-you-can-make-in-minutes/](http://quickfixwithbindu-local.local/recipes/quick-tasty-breakfast-idea-you-can-make-in-minutes/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/quick-veg-stir-fry/](http://quickfixwithbindu-local.local/recipes/quick-veg-stir-fry/) | 200 | 6 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/quick-zucchini-edamame-soup-with-tofu/](http://quickfixwithbindu-local.local/recipes/quick-zucchini-edamame-soup-with-tofu/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/quinoa-and-banana-turns-into-the-softest-healthy-loaf/](http://quickfixwithbindu-local.local/recipes/quinoa-and-banana-turns-into-the-softest-healthy-loaf/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/quinoa-crepe-wraps-with-spiced-potato-filling/](http://quickfixwithbindu-local.local/recipes/quinoa-crepe-wraps-with-spiced-potato-filling/) | 200 | 6 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/quinoa-kidney-bean-protein-patties/](http://quickfixwithbindu-local.local/recipes/quinoa-kidney-bean-protein-patties/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/quinoa-meets-cornmeal-dosa-style-flatbread/](http://quickfixwithbindu-local.local/recipes/quinoa-meets-cornmeal-dosa-style-flatbread/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/quinoa-oats-pancakes/](http://quickfixwithbindu-local.local/recipes/quinoa-oats-pancakes/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/quinoa-oats-quick-easy-and-healthy-pancakes/](http://quickfixwithbindu-local.local/recipes/quinoa-oats-quick-easy-and-healthy-pancakes/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/rainy-day-tomato-zucchini-soup/](http://quickfixwithbindu-local.local/recipes/rainy-day-tomato-zucchini-soup/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/raspberry-chia-pudding-2/](http://quickfixwithbindu-local.local/recipes/raspberry-chia-pudding-2/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/raspberry-chia-pudding/](http://quickfixwithbindu-local.local/recipes/raspberry-chia-pudding/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/red-lentil-seed-flourless-crackers/](http://quickfixwithbindu-local.local/recipes/red-lentil-seed-flourless-crackers/) | 200 | 98 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/red-lentil-tomato-flourless-crackers/](http://quickfixwithbindu-local.local/recipes/red-lentil-tomato-flourless-crackers/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/refreshing-peach-chia-smoothie/](http://quickfixwithbindu-local.local/recipes/refreshing-peach-chia-smoothie/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/regrowing-chia-microgreens-in-7-days/](http://quickfixwithbindu-local.local/recipes/regrowing-chia-microgreens-in-7-days/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/restaurant-style-garlic-green-beans/](http://quickfixwithbindu-local.local/recipes/restaurant-style-garlic-green-beans/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/rice-paper-samosa/](http://quickfixwithbindu-local.local/recipes/rice-paper-samosa/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/rich-chewy-coconut-chocolate-bites-fiber-rich-no-added-sugar/](http://quickfixwithbindu-local.local/recipes/rich-chewy-coconut-chocolate-bites-fiber-rich-no-added-sugar/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/roasted-cauliflower-steaks-with-creamy-tofu-tomato-gravy/](http://quickfixwithbindu-local.local/recipes/roasted-cauliflower-steaks-with-creamy-tofu-tomato-gravy/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/roasted-mushroom-salad-that-wakes-you-up/](http://quickfixwithbindu-local.local/recipes/roasted-mushroom-salad-that-wakes-you-up/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/roasted-squash-and-bell-pepper-soup/](http://quickfixwithbindu-local.local/recipes/roasted-squash-and-bell-pepper-soup/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/roasted-squash-soup/](http://quickfixwithbindu-local.local/recipes/roasted-squash-soup/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/saucy-crispy-pan-fried-momo-with-soy-sesame-glaze/](http://quickfixwithbindu-local.local/recipes/saucy-crispy-pan-fried-momo-with-soy-sesame-glaze/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/savory-buckwheat-pancake/](http://quickfixwithbindu-local.local/recipes/savory-buckwheat-pancake/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/savory-cornmeal-waffles/](http://quickfixwithbindu-local.local/recipes/savory-cornmeal-waffles/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/seed-crackers-that-are-better-than-store-bought-naturally-gluten-free-high-fiber-healthy-fats/](http://quickfixwithbindu-local.local/recipes/seed-crackers-that-are-better-than-store-bought-naturally-gluten-free-high-fiber-healthy-fats/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/semolina-momos-with-veggie-tofu-filling-and-spicy-jhol-chutney/](http://quickfixwithbindu-local.local/recipes/semolina-momos-with-veggie-tofu-filling-and-spicy-jhol-chutney/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/she-took-the-first-bite-and-this-happened-%f0%9f%a7%a1-waffle-belgianwaffle-shreyapanthee-mukbang/](http://quickfixwithbindu-local.local/recipes/she-took-the-first-bite-and-this-happened-%f0%9f%a7%a1-waffle-belgianwaffle-shreyapanthee-mukbang/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/signature-spicy-mushroom-salad/](http://quickfixwithbindu-local.local/recipes/signature-spicy-mushroom-salad/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/simple-cauliflower-curry/](http://quickfixwithbindu-local.local/recipes/simple-cauliflower-curry/) | 200 | 83 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/simple-festive-meal-that-feels-like-home-okra-curry-beaten-rice-omelet/](http://quickfixwithbindu-local.local/recipes/simple-festive-meal-that-feels-like-home-okra-curry-beaten-rice-omelet/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/smash-chickpeas-and-airfry-for-a-crunchy-guilt-free-snack/](http://quickfixwithbindu-local.local/recipes/smash-chickpeas-and-airfry-for-a-crunchy-guilt-free-snack/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/so-good-it-never-lasts-tomato-pickle-that-vanishes-fast/](http://quickfixwithbindu-local.local/recipes/so-good-it-never-lasts-tomato-pickle-that-vanishes-fast/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/soft-chewy-eggless-whole-wheat-chocolate-chunk-cookies/](http://quickfixwithbindu-local.local/recipes/soft-chewy-eggless-whole-wheat-chocolate-chunk-cookies/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/soft-whole-wheat-bread-with-flax-less-yeast-slow-rise/](http://quickfixwithbindu-local.local/recipes/soft-whole-wheat-bread-with-flax-less-yeast-slow-rise/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/spice-lovers-pickle-%f0%9f%8c%b6%ef%b8%8f-sweet-tangy-fiery-2/](http://quickfixwithbindu-local.local/recipes/spice-lovers-pickle-%f0%9f%8c%b6%ef%b8%8f-sweet-tangy-fiery-2/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/spice-lovers-pickle-%f0%9f%8c%b6%ef%b8%8f-sweet-tangy-fiery/](http://quickfixwithbindu-local.local/recipes/spice-lovers-pickle-%f0%9f%8c%b6%ef%b8%8f-sweet-tangy-fiery/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/spicy-frozen-tofu-sandwich/](http://quickfixwithbindu-local.local/recipes/spicy-frozen-tofu-sandwich/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/spicy-mushroom-maggi-in-minutes/](http://quickfixwithbindu-local.local/recipes/spicy-mushroom-maggi-in-minutes/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/spicy-mushroom-noodles-in-minutes/](http://quickfixwithbindu-local.local/recipes/spicy-mushroom-noodles-in-minutes/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/spicy-sesame-tofu-wraps-with-greek-yogurt-dip/](http://quickfixwithbindu-local.local/recipes/spicy-sesame-tofu-wraps-with-greek-yogurt-dip/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/spicy-vegetarian-chili/](http://quickfixwithbindu-local.local/recipes/spicy-vegetarian-chili/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/stop-buying-store-bought-cookies-make-these-healthy-protein-packed-cookies-instead/](http://quickfixwithbindu-local.local/recipes/stop-buying-store-bought-cookies-make-these-healthy-protein-packed-cookies-instead/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/stop-chopping-mushrooms-this-10-minute-garlic-curry-is-a-weeknight-game-changer/](http://quickfixwithbindu-local.local/recipes/stop-chopping-mushrooms-this-10-minute-garlic-curry-is-a-weeknight-game-changer/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/stop-eating-flour-make-this-30g-protein-bloat-free-bake-instead/](http://quickfixwithbindu-local.local/recipes/stop-eating-flour-make-this-30g-protein-bloat-free-bake-instead/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/stop-frying-potatoes-just-add-eggs-and-veggies-the-result-will-surprise-you/](http://quickfixwithbindu-local.local/recipes/stop-frying-potatoes-just-add-eggs-and-veggies-the-result-will-surprise-you/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/stop-making-boring-eggs-try-this-asparagus-roll/](http://quickfixwithbindu-local.local/recipes/stop-making-boring-eggs-try-this-asparagus-roll/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/stop-making-boring-lentils-try-this-30g-protein-savory-bake-%f0%9f%8d%84noflourcake-savorycake-noeggs/](http://quickfixwithbindu-local.local/recipes/stop-making-boring-lentils-try-this-30g-protein-savory-bake-%f0%9f%8d%84noflourcake-savorycake-noeggs/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/strawberry-chia-pudding-quick-healthy-breakfast-no-refined-sugar-5-min-prep/](http://quickfixwithbindu-local.local/recipes/strawberry-chia-pudding-quick-healthy-breakfast-no-refined-sugar-5-min-prep/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/strawberry-mojito/](http://quickfixwithbindu-local.local/recipes/strawberry-mojito/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/super-easy-oyster-mushroom-stir-fry/](http://quickfixwithbindu-local.local/recipes/super-easy-oyster-mushroom-stir-fry/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/sweet-potato-falafel-style-bites-2/](http://quickfixwithbindu-local.local/recipes/sweet-potato-falafel-style-bites-2/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/sweet-potato-falafel-style-bites/](http://quickfixwithbindu-local.local/recipes/sweet-potato-falafel-style-bites/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/sweet-potato-lentil-high-protein-crackers/](http://quickfixwithbindu-local.local/recipes/sweet-potato-lentil-high-protein-crackers/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/sweet-potato-red-lentil-crackers/](http://quickfixwithbindu-local.local/recipes/sweet-potato-red-lentil-crackers/) | 200 | 4 | [/?s=lentil](http://quickfixwithbindu-local.local/?s=lentil) |
| [/recipes/tea-strainer-cleaning-hack-%f0%9f%98%8d-no-scrubbing-needed/](http://quickfixwithbindu-local.local/recipes/tea-strainer-cleaning-hack-%f0%9f%98%8d-no-scrubbing-needed/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/the-creamiest-veggie-sandwich-with-cottage-cheese-paneer-avocado/](http://quickfixwithbindu-local.local/recipes/the-creamiest-veggie-sandwich-with-cottage-cheese-paneer-avocado/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/the-perfect-whole-wheat-breakfast-loaf/](http://quickfixwithbindu-local.local/recipes/the-perfect-whole-wheat-breakfast-loaf/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/the-rustic-zucchini-flatbread-my-mom-made-to-get-us-to-eat-our-veggies-shorts/](http://quickfixwithbindu-local.local/recipes/the-rustic-zucchini-flatbread-my-mom-made-to-get-us-to-eat-our-veggies-shorts/) | 200 | 15 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/the-secret-to-2-ingredient-pink-tortillas/](http://quickfixwithbindu-local.local/recipes/the-secret-to-2-ingredient-pink-tortillas/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/the-secret-to-the-perfect-momo-fold-%f0%9f%a5%9f/](http://quickfixwithbindu-local.local/recipes/the-secret-to-the-perfect-momo-fold-%f0%9f%a5%9f/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/the-ultimate-veggie-sandwich/](http://quickfixwithbindu-local.local/recipes/the-ultimate-veggie-sandwich/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/the-viral-crispy-rice-paper-omelet-5-minute-breakfast/](http://quickfixwithbindu-local.local/recipes/the-viral-crispy-rice-paper-omelet-5-minute-breakfast/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/this-isnt-just-a-veg-cutlet-it-turns-into-a-melty-pocket-wrap-easy-2-in-1-snack-hack/](http://quickfixwithbindu-local.local/recipes/this-isnt-just-a-veg-cutlet-it-turns-into-a-melty-pocket-wrap-easy-2-in-1-snack-hack/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/tofu-lentils-rice-with-spinach-healthy-protein-packed-rice-recipe-easy-one-pot-dinner/](http://quickfixwithbindu-local.local/recipes/tofu-lentils-rice-with-spinach-healthy-protein-packed-rice-recipe-easy-one-pot-dinner/) | 200 | 7 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/tofu-stir-fry-with-asparagus-mushrooms/](http://quickfixwithbindu-local.local/recipes/tofu-stir-fry-with-asparagus-mushrooms/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/tofu-veg-stuffed-buns/](http://quickfixwithbindu-local.local/recipes/tofu-veg-stuffed-buns/) | 200 | 6 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/tomato-mint-chutney/](http://quickfixwithbindu-local.local/recipes/tomato-mint-chutney/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/try-it-once-youll-make-it-every-week-2-ingredient-high-protein-mung-beans-flatbread-vegan/](http://quickfixwithbindu-local.local/recipes/try-it-once-youll-make-it-every-week-2-ingredient-high-protein-mung-beans-flatbread-vegan/) | 200 | 5 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/turmeric-and-ginger-concentrate-the-flu-stops-here/](http://quickfixwithbindu-local.local/recipes/turmeric-and-ginger-concentrate-the-flu-stops-here/) | 200 | 14 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/ultra-creamy-macaroni-pasta-with-tomato-sauce-veggies/](http://quickfixwithbindu-local.local/recipes/ultra-creamy-macaroni-pasta-with-tomato-sauce-veggies/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/ultra-crispy-air-fryer-plantain-chips/](http://quickfixwithbindu-local.local/recipes/ultra-crispy-air-fryer-plantain-chips/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/unique-oats-spinach-recipe-for-weight-loss-healthy-flatbreads-stuffed-buns/](http://quickfixwithbindu-local.local/recipes/unique-oats-spinach-recipe-for-weight-loss-healthy-flatbreads-stuffed-buns/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/unique-style-egg-toast-classic-breakfast-with-a-fun-twist/](http://quickfixwithbindu-local.local/recipes/unique-style-egg-toast-classic-breakfast-with-a-fun-twist/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/veg-dumplings-with-spicy-chutney-homemade-momo/](http://quickfixwithbindu-local.local/recipes/veg-dumplings-with-spicy-chutney-homemade-momo/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/veg-momos-nepali-style-steamed-dumplings-with-spicy-chutney/](http://quickfixwithbindu-local.local/recipes/veg-momos-nepali-style-steamed-dumplings-with-spicy-chutney/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/vegan-cabbage-flatbread-flourless-high-protein/](http://quickfixwithbindu-local.local/recipes/vegan-cabbage-flatbread-flourless-high-protein/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/vegan-karahi-tofu-rice-bowl/](http://quickfixwithbindu-local.local/recipes/vegan-karahi-tofu-rice-bowl/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/vegan-kidney-bean-and-soy-chunks-curry/](http://quickfixwithbindu-local.local/recipes/vegan-kidney-bean-and-soy-chunks-curry/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/vegan-mushroom-pasta-under-5-quick-healthy-dinner/](http://quickfixwithbindu-local.local/recipes/vegan-mushroom-pasta-under-5-quick-healthy-dinner/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/vegan-quinoa-kidney-bean-fiber-balls/](http://quickfixwithbindu-local.local/recipes/vegan-quinoa-kidney-bean-fiber-balls/) | 200 | 4 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/viral-egg-flower-breakfast/](http://quickfixwithbindu-local.local/recipes/viral-egg-flower-breakfast/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/viral-egg-toast/](http://quickfixwithbindu-local.local/recipes/viral-egg-toast/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/viral-potato-mushroom-buttons-%f0%9f%8d%84/](http://quickfixwithbindu-local.local/recipes/viral-potato-mushroom-buttons-%f0%9f%8d%84/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/viral-soy-chunks-peanut-salad-%f0%9f%a5%9c%f0%9f%8c%b6-high-protein-no-fry-recipe/](http://quickfixwithbindu-local.local/recipes/viral-soy-chunks-peanut-salad-%f0%9f%a5%9c%f0%9f%8c%b6-high-protein-no-fry-recipe/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/viral-soy-chunks-peanut-salad-%f0%9f%a5%9c%f0%9f%8c%b6/](http://quickfixwithbindu-local.local/recipes/viral-soy-chunks-peanut-salad-%f0%9f%a5%9c%f0%9f%8c%b6/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/warm-asparagus-winter-salad-with-honey-lemon-seed-dressing/](http://quickfixwithbindu-local.local/recipes/warm-asparagus-winter-salad-with-honey-lemon-seed-dressing/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/watermelon-mint-lime-juice/](http://quickfixwithbindu-local.local/recipes/watermelon-mint-lime-juice/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/ways-to-incorporate-turmeric-for-health-benefits/](http://quickfixwithbindu-local.local/recipes/ways-to-incorporate-turmeric-for-health-benefits/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/whats-inside-this-omelet/](http://quickfixwithbindu-local.local/recipes/whats-inside-this-omelet/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/white-bean-garlic-rice-recipe/](http://quickfixwithbindu-local.local/recipes/white-bean-garlic-rice-recipe/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/whole-wheat-garlic-cheese-buns/](http://quickfixwithbindu-local.local/recipes/whole-wheat-garlic-cheese-buns/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/whole-wheat-pizza-dough-for-three-recipes-pizzas-garlic-buns/](http://quickfixwithbindu-local.local/recipes/whole-wheat-pizza-dough-for-three-recipes-pizzas-garlic-buns/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/whole-wheat-tofu-paneer-mushroom-momos/](http://quickfixwithbindu-local.local/recipes/whole-wheat-tofu-paneer-mushroom-momos/) | 200 | 176 | [/](http://quickfixwithbindu-local.local/) |
| [/recipes/winter-immunity-bars/](http://quickfixwithbindu-local.local/recipes/winter-immunity-bars/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/youve-never-seen-an-egg-sandwich-like-this/](http://quickfixwithbindu-local.local/recipes/youve-never-seen-an-egg-sandwich-like-this/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/zero-oil-beet-oats-flatbread-low-gi-healthy-flatbread/](http://quickfixwithbindu-local.local/recipes/zero-oil-beet-oats-flatbread-low-gi-healthy-flatbread/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/zero-waste-broccoli-stir-fry-2/](http://quickfixwithbindu-local.local/recipes/zero-waste-broccoli-stir-fry-2/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/zero-waste-broccoli-stir-fry/](http://quickfixwithbindu-local.local/recipes/zero-waste-broccoli-stir-fry/) | 200 | 3 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/zero-waste-garlic-broccoli-stir-fry/](http://quickfixwithbindu-local.local/recipes/zero-waste-garlic-broccoli-stir-fry/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/recipes/zucchini-quinoa-bites-with-secret-tangy-dip/](http://quickfixwithbindu-local.local/recipes/zucchini-quinoa-bites-with-secret-tangy-dip/) | 200 | 5 | [/all-recipes/](http://quickfixwithbindu-local.local/all-recipes/) |
| [/subscribe-to-our-newsletter/](http://quickfixwithbindu-local.local/subscribe-to-our-newsletter/) | 200 | 1 | [/subscribe-to-our-newsletter/](http://quickfixwithbindu-local.local/subscribe-to-our-newsletter/) |
| [/ugc-creator-portfolio/](http://quickfixwithbindu-local.local/ugc-creator-portfolio/) | 200 | 528 | [/](http://quickfixwithbindu-local.local/) |
| [/upload-recipe/](http://quickfixwithbindu-local.local/upload-recipe/) | 200 | 1 | [/upload-recipe/](http://quickfixwithbindu-local.local/upload-recipe/) |
| [https://www.canva.com/design/DAHRnwlQn-E/KURL9TVWV5dIcOMvDuF-MA/view?utm_content=DAHRnwlQn-E&utm_campaign=designshare&utm_medium=embeds&utm_source=link](https://www.canva.com/design/DAHRnwlQn-E/KURL9TVWV5dIcOMvDuF-MA/view?utm_content=DAHRnwlQn-E&utm_campaign=designshare&utm_medium=embeds&utm_source=link) | 200 | 2 | [/about-me/](http://quickfixwithbindu-local.local/about-me/) |
| [https://www.facebook.com/bindu.pathak.18/reels/](https://www.facebook.com/bindu.pathak.18/reels/) | 200 | 528 | [/](http://quickfixwithbindu-local.local/) |
| [https://www.instagram.com/quickfixwithbindu/reels/](https://www.instagram.com/quickfixwithbindu/reels/) | 200 | 528 | [/](http://quickfixwithbindu-local.local/) |
| [https://www.quickfixwithbindu.com/category/healthy-bites/](https://www.quickfixwithbindu.com/category/healthy-bites/) | 200 | 1 | [/](http://quickfixwithbindu-local.local/) |
| [https://www.quickfixwithbindu.com/category/home-remedies/](https://www.quickfixwithbindu.com/category/home-remedies/) | 200 | 1 | [/](http://quickfixwithbindu-local.local/) |
| [https://www.quickfixwithbindu.com/category/main-meals/](https://www.quickfixwithbindu.com/category/main-meals/) | 200 | 1 | [/](http://quickfixwithbindu-local.local/) |
| [https://www.quickfixwithbindu.com/category/quick-starts/](https://www.quickfixwithbindu.com/category/quick-starts/) | 200 | 1 | [/](http://quickfixwithbindu-local.local/) |
| [https://www.tiktok.com/@quickfixwithbindu?lang=en](https://www.tiktok.com/@quickfixwithbindu?lang=en) | 200 | 528 | [/](http://quickfixwithbindu-local.local/) |
| [https://www.youtube.com/@QuickFixwithBindu](https://www.youtube.com/@QuickFixwithBindu) | 404 | 528 | [/](http://quickfixwithbindu-local.local/) |

### Appendix C — supporting asset and external dependency inventory

Local asset checks use HEAD, not image decoding or JavaScript execution. A correct HTTP status does not prove visual quality, valid JavaScript, or complete plugin behavior. Referenced production assets mapped to the local copy are checked only as local assets here. CSS-internal URLs, every responsive srcset variant, and URLs generated only after JavaScript runs were not exhaustively crawled.

| Local resource | HTTP | Content type |
| --- | --- | --- |
| [/superpwa-manifest-nginx.json](http://quickfixwithbindu-local.local/superpwa-manifest-nginx.json) | 200 | application/json |
| [/wp-content/plugins/custom-facebook-feed/assets/css/cff-style.min.css?ver=4.10.0](http://quickfixwithbindu-local.local/wp-content/plugins/custom-facebook-feed/assets/css/cff-style.min.css?ver=4.10.0) | 200 | text/css |
| [/wp-content/plugins/custom-facebook-feed/assets/js/cff-scripts.min.js?ver=4.10.0](http://quickfixwithbindu-local.local/wp-content/plugins/custom-facebook-feed/assets/js/cff-scripts.min.js?ver=4.10.0) | 200 | application/x-javascript |
| [/wp-content/plugins/custom-facebook-feed/vendor/smashballoon/framework/Packages/Blocks/css/sb-elementor.css?ver=1.0.0](http://quickfixwithbindu-local.local/wp-content/plugins/custom-facebook-feed/vendor/smashballoon/framework/Packages/Blocks/css/sb-elementor.css?ver=1.0.0) | 200 | text/css |
| [/wp-content/plugins/custom-twitter-feeds/css/ctf-styles.min.css?ver=2.9.0](http://quickfixwithbindu-local.local/wp-content/plugins/custom-twitter-feeds/css/ctf-styles.min.css?ver=2.9.0) | 200 | text/css |
| [/wp-content/plugins/custom-twitter-feeds/js/ctf-scripts.min.js?ver=2.9.0&ver=2.9.0](http://quickfixwithbindu-local.local/wp-content/plugins/custom-twitter-feeds/js/ctf-scripts.min.js?ver=2.9.0&ver=2.9.0) | 200 | application/x-javascript |
| [/wp-content/plugins/elementor/assets/css/frontend.min.css?ver=4.1.4](http://quickfixwithbindu-local.local/wp-content/plugins/elementor/assets/css/frontend.min.css?ver=4.1.4) | 200 | text/css |
| [/wp-content/plugins/elementor/assets/css/widget-heading.min.css?ver=4.1.4](http://quickfixwithbindu-local.local/wp-content/plugins/elementor/assets/css/widget-heading.min.css?ver=4.1.4) | 200 | text/css |
| [/wp-content/plugins/elementor/assets/css/widget-image.min.css?ver=4.1.4](http://quickfixwithbindu-local.local/wp-content/plugins/elementor/assets/css/widget-image.min.css?ver=4.1.4) | 200 | text/css |
| [/wp-content/plugins/elementor/assets/js/frontend-modules.min.js?ver=4.1.4](http://quickfixwithbindu-local.local/wp-content/plugins/elementor/assets/js/frontend-modules.min.js?ver=4.1.4) | 200 | application/x-javascript |
| [/wp-content/plugins/elementor/assets/js/frontend.min.js?ver=4.1.4](http://quickfixwithbindu-local.local/wp-content/plugins/elementor/assets/js/frontend.min.js?ver=4.1.4) | 200 | application/x-javascript |
| [/wp-content/plugins/elementor/assets/js/webpack.runtime.min.js?ver=4.1.4](http://quickfixwithbindu-local.local/wp-content/plugins/elementor/assets/js/webpack.runtime.min.js?ver=4.1.4) | 200 | application/x-javascript |
| [/wp-content/plugins/folders/assets/css/folder-icon.min.css?ver=3.2.0](http://quickfixwithbindu-local.local/wp-content/plugins/folders/assets/css/folder-icon.min.css?ver=3.2.0) | 200 | text/css |
| [/wp-content/plugins/folders/assets/css/folders.min.css?ver=3.2.0](http://quickfixwithbindu-local.local/wp-content/plugins/folders/assets/css/folders.min.css?ver=3.2.0) | 200 | text/css |
| [/wp-content/plugins/folders/assets/css/jstree.min.css?ver=3.2.0](http://quickfixwithbindu-local.local/wp-content/plugins/folders/assets/css/jstree.min.css?ver=3.2.0) | 200 | text/css |
| [/wp-content/plugins/folders/assets/css/overlayscrollbars.min.css?ver=3.2.0](http://quickfixwithbindu-local.local/wp-content/plugins/folders/assets/css/overlayscrollbars.min.css?ver=3.2.0) | 200 | text/css |
| [/wp-content/plugins/folders/assets/css/page-post-media.min.css?ver=3.2.0](http://quickfixwithbindu-local.local/wp-content/plugins/folders/assets/css/page-post-media.min.css?ver=3.2.0) | 200 | text/css |
| [/wp-content/plugins/folders/assets/js/jquery.overlayscrollbars.min.js?ver=3.2.0](http://quickfixwithbindu-local.local/wp-content/plugins/folders/assets/js/jquery.overlayscrollbars.min.js?ver=3.2.0) | 200 | application/x-javascript |
| [/wp-content/plugins/folders/assets/js/jquery.ui.touch-punch.min.js?ver=3.2.0](http://quickfixwithbindu-local.local/wp-content/plugins/folders/assets/js/jquery.ui.touch-punch.min.js?ver=3.2.0) | 200 | application/x-javascript |
| [/wp-content/plugins/folders/assets/js/jstree.min.js?ver=3.2.0](http://quickfixwithbindu-local.local/wp-content/plugins/folders/assets/js/jstree.min.js?ver=3.2.0) | 200 | application/x-javascript |
| [/wp-content/plugins/folders/assets/js/page-post-media.min.js?ver=3.2.0](http://quickfixwithbindu-local.local/wp-content/plugins/folders/assets/js/page-post-media.min.js?ver=3.2.0) | 200 | application/x-javascript |
| [/wp-content/plugins/folders/assets/js/replace-file-name.js?ver=3.2.0](http://quickfixwithbindu-local.local/wp-content/plugins/folders/assets/js/replace-file-name.js?ver=3.2.0) | 200 | application/x-javascript |
| [/wp-content/plugins/instagram-feed/css/sbi-styles.min.css?ver=6.11.4](http://quickfixwithbindu-local.local/wp-content/plugins/instagram-feed/css/sbi-styles.min.css?ver=6.11.4) | 200 | text/css |
| [/wp-content/plugins/mailin/css/mailin-front.css?ver=7.1.2](http://quickfixwithbindu-local.local/wp-content/plugins/mailin/css/mailin-front.css?ver=7.1.2) | 200 | text/css |
| [/wp-content/plugins/mailin/js/mailin-front.js?ver=1790710436](http://quickfixwithbindu-local.local/wp-content/plugins/mailin/js/mailin-front.js?ver=1790710436) | 200 | application/x-javascript |
| [/wp-content/plugins/reviews-feed/assets/css/sbr-styles.min.css?ver=2.6.0](http://quickfixwithbindu-local.local/wp-content/plugins/reviews-feed/assets/css/sbr-styles.min.css?ver=2.6.0) | 200 | text/css |
| [/wp-content/plugins/super-progressive-web-apps/public/js/register-sw.js?ver=2.2.45](http://quickfixwithbindu-local.local/wp-content/plugins/super-progressive-web-apps/public/js/register-sw.js?ver=2.2.45) | 200 | application/x-javascript |
| [/wp-content/plugins/wp-recipe-maker/dist/public-modern-split.js?ver=10.8.3](http://quickfixwithbindu-local.local/wp-content/plugins/wp-recipe-maker/dist/public-modern-split.js?ver=10.8.3) | 200 | application/x-javascript |
| [/wp-content/plugins/wp-recipe-maker/dist/public-modern.css?ver=10.8.3](http://quickfixwithbindu-local.local/wp-content/plugins/wp-recipe-maker/dist/public-modern.css?ver=10.8.3) | 200 | text/css |
| [/wp-content/themes/hostinger-ai-theme/assets/css/style.min.css?ver=1.2.4](http://quickfixwithbindu-local.local/wp-content/themes/hostinger-ai-theme/assets/css/style.min.css?ver=1.2.4) | 200 | text/css |
| [/wp-content/themes/hostinger-ai-theme/assets/js/front-scripts.min.js?ver=1.2.4](http://quickfixwithbindu-local.local/wp-content/themes/hostinger-ai-theme/assets/js/front-scripts.min.js?ver=1.2.4) | 200 | application/x-javascript |
| [/wp-content/uploads/2026/02/cropped-Gemini_Generated_Image_ld8tpqld8tpqld8t-180x180.png](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/cropped-Gemini_Generated_Image_ld8tpqld8tpqld8t-180x180.png) | 200 | image/png |
| [/wp-content/uploads/2026/02/cropped-Gemini_Generated_Image_ld8tpqld8tpqld8t-192x192.png](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/cropped-Gemini_Generated_Image_ld8tpqld8tpqld8t-192x192.png) | 200 | image/png |
| [/wp-content/uploads/2026/02/cropped-Gemini_Generated_Image_ld8tpqld8tpqld8t-32x32.png](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/cropped-Gemini_Generated_Image_ld8tpqld8tpqld8t-32x32.png) | 200 | image/png |
| [/wp-content/uploads/2026/02/healthy_bites-300x300.png](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/healthy_bites-300x300.png) | 200 | image/png |
| [/wp-content/uploads/2026/02/home_remedies-300x300.png](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/home_remedies-300x300.png) | 200 | image/png |
| [/wp-content/uploads/2026/02/hqdefault-1.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-1.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-10.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-10.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-100.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-100.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-101.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-101.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-102.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-102.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-103.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-103.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-104.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-104.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-105.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-105.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-106.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-106.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-107.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-107.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-108.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-108.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-109.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-109.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-11.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-11.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-110.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-110.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-111.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-111.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-112.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-112.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-113.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-113.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-114.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-114.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-115.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-115.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-116.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-116.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-117.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-117.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-118.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-118.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-119.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-119.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-12.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-12.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-120.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-120.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-121.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-121.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-122.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-122.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-123.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-123.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-124.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-124.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-125.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-125.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-126.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-126.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-127.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-127.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-128.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-128.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-129.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-129.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-13.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-13.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-130.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-130.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-131.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-131.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-132.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-132.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-133.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-133.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-134.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-134.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-135.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-135.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-136.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-136.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-137.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-137.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-138.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-138.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-139.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-139.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-14.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-14.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-140.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-140.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-141.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-141.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-142.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-142.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-143.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-143.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-144.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-144.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-145.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-145.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-146.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-146.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-148.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-148.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-149.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-149.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-15.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-15.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-150.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-150.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-151.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-151.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-152.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-152.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-153.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-153.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-154.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-154.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-155.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-155.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-156.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-156.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-157.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-157.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-158.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-158.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-159.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-159.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-16.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-16.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-160.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-160.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-161.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-161.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-162.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-162.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-163.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-163.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-164.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-164.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-165.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-165.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-166.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-166.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-167.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-167.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-168.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-168.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-169.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-169.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-17.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-17.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-170.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-170.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-171.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-171.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-172.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-172.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-173.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-173.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-174.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-174.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-175.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-175.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-176.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-176.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-177.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-177.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-178.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-178.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-179.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-179.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-18.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-18.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-180.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-180.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-181.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-181.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-182.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-182.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-183.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-183.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-184.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-184.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-185.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-185.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-186.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-186.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-187.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-187.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-188.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-188.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-189.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-189.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-19.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-19.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-190.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-190.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-191.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-191.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-192.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-192.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-193.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-193.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-194.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-194.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-195.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-195.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-196.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-196.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-197.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-197.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-198.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-198.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-199.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-199.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-2.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-2.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-20.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-20.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-200.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-200.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-201.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-201.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-202.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-202.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-203.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-203.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-204.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-204.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-205.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-205.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-206.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-206.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-207.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-207.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-208.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-208.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-209.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-209.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-21.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-21.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-210.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-210.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-211.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-211.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-212.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-212.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-213.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-213.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-214.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-214.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-215.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-215.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-216.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-216.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-217.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-217.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-218.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-218.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-219.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-219.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-22.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-22.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-220.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-220.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-221.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-221.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-222.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-222.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-223.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-223.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-224.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-224.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-225.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-225.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-226.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-226.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-227.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-227.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-228.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-228.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-229.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-229.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-23.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-23.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-230.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-230.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-231.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-231.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-232.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-232.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-233.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-233.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-234.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-234.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-235.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-235.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-236.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-236.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-237.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-237.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-238.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-238.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-239.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-239.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-24.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-24.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-240.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-240.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-241.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-241.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-242.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-242.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-243.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-243.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-244.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-244.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-245.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-245.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-246.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-246.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-247.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-247.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-248.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-248.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-249.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-249.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-25.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-25.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-250.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-250.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-251.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-251.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-252.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-252.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-253.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-253.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-254.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-254.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-255.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-255.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-256.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-256.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-257.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-257.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-258.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-258.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-259.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-259.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-26.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-26.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-260.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-260.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-261.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-261.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-262.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-262.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-263.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-263.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-264.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-264.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-265.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-265.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-266.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-266.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-267.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-267.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-268.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-268.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-269.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-269.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-27.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-27.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-270.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-270.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-271.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-271.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-272.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-272.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-273.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-273.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-274.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-274.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-275.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-275.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-276.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-276.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-277.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-277.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-278.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-278.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-279.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-279.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-28.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-28.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-280.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-280.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-281.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-281.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-282.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-282.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-283.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-283.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-284.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-284.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-285.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-285.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-286.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-286.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-287.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-287.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-288.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-288.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-289.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-289.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-29.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-29.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-290.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-290.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-291.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-291.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-292.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-292.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-293.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-293.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-294.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-294.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-295.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-295.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-296.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-296.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-297.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-297.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-298.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-298.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-299.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-299.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-3.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-3.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-30.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-30.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-300.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-300.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-301.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-301.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-302.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-302.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-303.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-303.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-304.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-304.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-305.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-305.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-306.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-306.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-307.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-307.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-308.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-308.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-309.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-309.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-31.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-31.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-310.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-310.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-311.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-311.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-312.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-312.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-313.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-313.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-314.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-314.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-315.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-315.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-316.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-316.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-317.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-317.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-318.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-318.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-319.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-319.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-32.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-32.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-320.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-320.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-321.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-321.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-322.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-322.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-323.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-323.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-324.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-324.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-325.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-325.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-326.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-326.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-327.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-327.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-328.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-328.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-329.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-329.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-33.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-33.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-330.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-330.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-331.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-331.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-332.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-332.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-333.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-333.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-334.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-334.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-335.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-335.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-336.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-336.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-337.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-337.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-34.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-34.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-35.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-35.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-36.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-36.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-37.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-37.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-38.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-38.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-39.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-39.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-4.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-4.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-40.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-40.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-41.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-41.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-42.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-42.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-43.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-43.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-44.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-44.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-45.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-45.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-46.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-46.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-47.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-47.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-48.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-48.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-49.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-49.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-5.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-5.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-50.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-50.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-51.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-51.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-52.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-52.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-53.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-53.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-54.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-54.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-55.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-55.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-56.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-56.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-57.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-57.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-58.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-58.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-59.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-59.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-6.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-6.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-60.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-60.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-61.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-61.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-62.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-62.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-63.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-63.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-64.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-64.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-65.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-65.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-66.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-66.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-67.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-67.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-68.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-68.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-69.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-69.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-7.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-7.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-70.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-70.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-71.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-71.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-72.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-72.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-73.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-73.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-74.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-74.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-75.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-75.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-76.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-76.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-77.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-77.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-78.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-78.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-79.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-79.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-8.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-8.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-80.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-80.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-81.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-81.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-82.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-82.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-83.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-83.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-84.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-84.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-85.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-85.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-86.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-86.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-87.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-87.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-88.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-88.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-89.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-89.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-9.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-9.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-90.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-90.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-91.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-91.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-92.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-92.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-93.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-93.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-94.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-94.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-95.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-95.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-96.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-96.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-97.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-97.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-98.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-98.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault-99.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault-99.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/hqdefault.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/hqdefault.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/02/logo_image-transparent.png](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/logo_image-transparent.png) | 200 | image/png |
| [/wp-content/uploads/2026/02/logo_image_mobile.png](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/logo_image_mobile.png) | 200 | image/png |
| [/wp-content/uploads/2026/02/main_meals-300x300.png](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/main_meals-300x300.png) | 200 | image/png |
| [/wp-content/uploads/2026/02/quick_starts-300x300.png](http://quickfixwithbindu-local.local/wp-content/uploads/2026/02/quick_starts-300x300.png) | 200 | image/png |
| [/wp-content/uploads/2026/03/hqdefault-1.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/03/hqdefault-1.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/03/hqdefault-10.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/03/hqdefault-10.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/03/hqdefault-11.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/03/hqdefault-11.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/03/hqdefault-12.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/03/hqdefault-12.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/03/hqdefault-13.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/03/hqdefault-13.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/03/hqdefault-14.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/03/hqdefault-14.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/03/hqdefault-15.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/03/hqdefault-15.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/03/hqdefault-16.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/03/hqdefault-16.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/03/hqdefault-17.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/03/hqdefault-17.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/03/hqdefault-18.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/03/hqdefault-18.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/03/hqdefault-19.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/03/hqdefault-19.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/03/hqdefault-2.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/03/hqdefault-2.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/03/hqdefault-20.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/03/hqdefault-20.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/03/hqdefault-21.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/03/hqdefault-21.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/03/hqdefault-22.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/03/hqdefault-22.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/03/hqdefault-23.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/03/hqdefault-23.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/03/hqdefault-24.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/03/hqdefault-24.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/03/hqdefault-25.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/03/hqdefault-25.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/03/hqdefault-26.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/03/hqdefault-26.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/03/hqdefault-3.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/03/hqdefault-3.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/03/hqdefault-4.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/03/hqdefault-4.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/03/hqdefault-5.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/03/hqdefault-5.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/03/hqdefault-6.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/03/hqdefault-6.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/03/hqdefault-7.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/03/hqdefault-7.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/03/hqdefault-8.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/03/hqdefault-8.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/03/hqdefault-9.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/03/hqdefault-9.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/03/hqdefault.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/03/hqdefault.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/04/hqdefault-1.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/04/hqdefault-1.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/04/hqdefault-10.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/04/hqdefault-10.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/04/hqdefault-11.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/04/hqdefault-11.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/04/hqdefault-12.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/04/hqdefault-12.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/04/hqdefault-13.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/04/hqdefault-13.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/04/hqdefault-14.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/04/hqdefault-14.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/04/hqdefault-15.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/04/hqdefault-15.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/04/hqdefault-16.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/04/hqdefault-16.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/04/hqdefault-17.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/04/hqdefault-17.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/04/hqdefault-18.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/04/hqdefault-18.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/04/hqdefault-19.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/04/hqdefault-19.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/04/hqdefault-2.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/04/hqdefault-2.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/04/hqdefault-20.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/04/hqdefault-20.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/04/hqdefault-21.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/04/hqdefault-21.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/04/hqdefault-22.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/04/hqdefault-22.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/04/hqdefault-23.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/04/hqdefault-23.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/04/hqdefault-24.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/04/hqdefault-24.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/04/hqdefault-25.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/04/hqdefault-25.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/04/hqdefault-3.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/04/hqdefault-3.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/04/hqdefault-4.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/04/hqdefault-4.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/04/hqdefault-5.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/04/hqdefault-5.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/04/hqdefault-6.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/04/hqdefault-6.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/04/hqdefault-7.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/04/hqdefault-7.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/04/hqdefault-8.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/04/hqdefault-8.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/04/hqdefault-9.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/04/hqdefault-9.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/04/hqdefault.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/04/hqdefault.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/05/hqdefault-1.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/05/hqdefault-1.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/05/hqdefault-10.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/05/hqdefault-10.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/05/hqdefault-11.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/05/hqdefault-11.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/05/hqdefault-12.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/05/hqdefault-12.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/05/hqdefault-13.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/05/hqdefault-13.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/05/hqdefault-2.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/05/hqdefault-2.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/05/hqdefault-3.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/05/hqdefault-3.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/05/hqdefault-4.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/05/hqdefault-4.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/05/hqdefault-5.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/05/hqdefault-5.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/05/hqdefault-6.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/05/hqdefault-6.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/05/hqdefault-7.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/05/hqdefault-7.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/05/hqdefault-8.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/05/hqdefault-8.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/05/hqdefault-9.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/05/hqdefault-9.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/05/hqdefault.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/05/hqdefault.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/06/hqdefault-1.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/06/hqdefault-1.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/06/hqdefault-2.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/06/hqdefault-2.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/06/hqdefault-3.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/06/hqdefault-3.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/06/hqdefault-4.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/06/hqdefault-4.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/06/hqdefault-5.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/06/hqdefault-5.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/06/hqdefault-6.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/06/hqdefault-6.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/06/hqdefault-7.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/06/hqdefault-7.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/06/hqdefault.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/06/hqdefault.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/07/hqdefault-1.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/07/hqdefault-1.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/07/hqdefault-10.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/07/hqdefault-10.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/07/hqdefault-11.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/07/hqdefault-11.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/07/hqdefault-2.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/07/hqdefault-2.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/07/hqdefault-3.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/07/hqdefault-3.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/07/hqdefault-4.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/07/hqdefault-4.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/07/hqdefault-5.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/07/hqdefault-5.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/07/hqdefault-6.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/07/hqdefault-6.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/07/hqdefault-7.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/07/hqdefault-7.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/07/hqdefault-8.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/07/hqdefault-8.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/07/hqdefault-9.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/07/hqdefault-9.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/07/hqdefault.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/07/hqdefault.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/08/hqdefault-1.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/08/hqdefault-1.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/08/hqdefault-10.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/08/hqdefault-10.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/08/hqdefault-11.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/08/hqdefault-11.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/08/hqdefault-12.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/08/hqdefault-12.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/08/hqdefault-2.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/08/hqdefault-2.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/08/hqdefault-3.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/08/hqdefault-3.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/08/hqdefault-4.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/08/hqdefault-4.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/08/hqdefault-5.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/08/hqdefault-5.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/08/hqdefault-6.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/08/hqdefault-6.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/08/hqdefault-7.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/08/hqdefault-7.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/08/hqdefault-8.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/08/hqdefault-8.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/08/hqdefault-9.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/08/hqdefault-9.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/08/hqdefault.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/08/hqdefault.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/09/hqdefault-1.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/09/hqdefault-1.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/09/hqdefault-10.webp](http://quickfixwithbindu-local.local/wp-content/uploads/2026/09/hqdefault-10.webp) | 200 | image/webp |
| [/wp-content/uploads/2026/09/hqdefault-11.webp](http://quickfixwithbindu-local.local/wp-content/uploads/2026/09/hqdefault-11.webp) | 200 | image/webp |
| [/wp-content/uploads/2026/09/hqdefault-12.webp](http://quickfixwithbindu-local.local/wp-content/uploads/2026/09/hqdefault-12.webp) | 200 | image/webp |
| [/wp-content/uploads/2026/09/hqdefault-13.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/09/hqdefault-13.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/09/hqdefault-14-1024x576.webp?ver=1790702789](http://quickfixwithbindu-local.local/wp-content/uploads/2026/09/hqdefault-14-1024x576.webp?ver=1790702789) | 200 | image/webp |
| [/wp-content/uploads/2026/09/hqdefault-14-768x432.webp?ver=1790702789](http://quickfixwithbindu-local.local/wp-content/uploads/2026/09/hqdefault-14-768x432.webp?ver=1790702789) | 200 | image/webp |
| [/wp-content/uploads/2026/09/hqdefault-15.webp](http://quickfixwithbindu-local.local/wp-content/uploads/2026/09/hqdefault-15.webp) | 200 | image/webp |
| [/wp-content/uploads/2026/09/hqdefault-2.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/09/hqdefault-2.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/09/hqdefault-3.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/09/hqdefault-3.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/09/hqdefault-4-1024x576.png?ver=1790703069](http://quickfixwithbindu-local.local/wp-content/uploads/2026/09/hqdefault-4-1024x576.png?ver=1790703069) | 200 | image/png |
| [/wp-content/uploads/2026/09/hqdefault-4-768x432.png?ver=1790703069](http://quickfixwithbindu-local.local/wp-content/uploads/2026/09/hqdefault-4-768x432.png?ver=1790703069) | 200 | image/png |
| [/wp-content/uploads/2026/09/hqdefault-5.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/09/hqdefault-5.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/09/hqdefault-6.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/09/hqdefault-6.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/2026/09/hqdefault-7.webp](http://quickfixwithbindu-local.local/wp-content/uploads/2026/09/hqdefault-7.webp) | 200 | image/webp |
| [/wp-content/uploads/2026/09/hqdefault-8.webp](http://quickfixwithbindu-local.local/wp-content/uploads/2026/09/hqdefault-8.webp) | 200 | image/webp |
| [/wp-content/uploads/2026/09/hqdefault-9.webp](http://quickfixwithbindu-local.local/wp-content/uploads/2026/09/hqdefault-9.webp) | 200 | image/webp |
| [/wp-content/uploads/2026/09/hqdefault.jpg](http://quickfixwithbindu-local.local/wp-content/uploads/2026/09/hqdefault.jpg) | 200 | image/jpeg |
| [/wp-content/uploads/elementor/css/post-10.css?ver=1790899370](http://quickfixwithbindu-local.local/wp-content/uploads/elementor/css/post-10.css?ver=1790899370) | 200 | text/css |
| [/wp-content/uploads/elementor/css/post-1134.css?ver=1790899372](http://quickfixwithbindu-local.local/wp-content/uploads/elementor/css/post-1134.css?ver=1790899372) | 200 | text/css |
| [/wp-content/uploads/elementor/css/post-1155.css?ver=1790899371](http://quickfixwithbindu-local.local/wp-content/uploads/elementor/css/post-1155.css?ver=1790899371) | 200 | text/css |
| [/wp-content/uploads/elementor/css/post-1342.css?ver=1790899371](http://quickfixwithbindu-local.local/wp-content/uploads/elementor/css/post-1342.css?ver=1790899371) | 200 | text/css |
| [/wp-content/uploads/elementor/css/post-5.css?ver=1790899371](http://quickfixwithbindu-local.local/wp-content/uploads/elementor/css/post-5.css?ver=1790899371) | 200 | text/css |
| [/wp-includes/blocks/navigation/style.min.css?ver=7.1.2](http://quickfixwithbindu-local.local/wp-includes/blocks/navigation/style.min.css?ver=7.1.2) | 200 | text/css |
| [/wp-includes/css/buttons.min.css?ver=7.1.2](http://quickfixwithbindu-local.local/wp-includes/css/buttons.min.css?ver=7.1.2) | 200 | text/css |
| [/wp-includes/css/dashicons.min.css?ver=7.1.2](http://quickfixwithbindu-local.local/wp-includes/css/dashicons.min.css?ver=7.1.2) | 200 | text/css |
| [/wp-includes/css/media-views.min.css?ver=7.1.2](http://quickfixwithbindu-local.local/wp-includes/css/media-views.min.css?ver=7.1.2) | 200 | text/css |
| [/wp-includes/js/api-request.min.js?ver=7.1.2](http://quickfixwithbindu-local.local/wp-includes/js/api-request.min.js?ver=7.1.2) | 200 | application/x-javascript |
| [/wp-includes/js/backbone.min.js?ver=1.6.1](http://quickfixwithbindu-local.local/wp-includes/js/backbone.min.js?ver=1.6.1) | 200 | application/x-javascript |
| [/wp-includes/js/clipboard.min.js?ver=2.0.11](http://quickfixwithbindu-local.local/wp-includes/js/clipboard.min.js?ver=2.0.11) | 200 | application/x-javascript |
| [/wp-includes/js/dist/a11y.min.js?ver=31c6cec5a4ff7aff483d](http://quickfixwithbindu-local.local/wp-includes/js/dist/a11y.min.js?ver=31c6cec5a4ff7aff483d) | 200 | application/x-javascript |
| [/wp-includes/js/dist/dom-ready.min.js?ver=3fe927cab37bf38d6a23](http://quickfixwithbindu-local.local/wp-includes/js/dist/dom-ready.min.js?ver=3fe927cab37bf38d6a23) | 200 | application/x-javascript |
| [/wp-includes/js/dist/hooks.min.js?ver=f0f188028580e8dc1255](http://quickfixwithbindu-local.local/wp-includes/js/dist/hooks.min.js?ver=f0f188028580e8dc1255) | 200 | application/x-javascript |
| [/wp-includes/js/dist/i18n.min.js?ver=1dfe7db3940c23ea9216](http://quickfixwithbindu-local.local/wp-includes/js/dist/i18n.min.js?ver=1dfe7db3940c23ea9216) | 200 | application/x-javascript |
| [/wp-includes/js/dist/script-modules/block-library/navigation/view.min.js?ver=1bf28ded04f9f188bdcb](http://quickfixwithbindu-local.local/wp-includes/js/dist/script-modules/block-library/navigation/view.min.js?ver=1bf28ded04f9f188bdcb) | 200 | application/x-javascript |
| [/wp-includes/js/imgareaselect/imgareaselect.css?ver=0.9.8](http://quickfixwithbindu-local.local/wp-includes/js/imgareaselect/imgareaselect.css?ver=0.9.8) | 200 | text/css |
| [/wp-includes/js/jquery/jquery-migrate.min.js?ver=3.4.1](http://quickfixwithbindu-local.local/wp-includes/js/jquery/jquery-migrate.min.js?ver=3.4.1) | 200 | application/x-javascript |
| [/wp-includes/js/jquery/jquery.min.js?ver=3.7.1](http://quickfixwithbindu-local.local/wp-includes/js/jquery/jquery.min.js?ver=3.7.1) | 200 | application/x-javascript |
| [/wp-includes/js/jquery/ui/core.min.js?ver=1.14.2](http://quickfixwithbindu-local.local/wp-includes/js/jquery/ui/core.min.js?ver=1.14.2) | 200 | application/x-javascript |
| [/wp-includes/js/jquery/ui/draggable.min.js?ver=1.14.2](http://quickfixwithbindu-local.local/wp-includes/js/jquery/ui/draggable.min.js?ver=1.14.2) | 200 | application/x-javascript |
| [/wp-includes/js/jquery/ui/droppable.min.js?ver=1.14.2](http://quickfixwithbindu-local.local/wp-includes/js/jquery/ui/droppable.min.js?ver=1.14.2) | 200 | application/x-javascript |
| [/wp-includes/js/jquery/ui/mouse.min.js?ver=1.14.2](http://quickfixwithbindu-local.local/wp-includes/js/jquery/ui/mouse.min.js?ver=1.14.2) | 200 | application/x-javascript |
| [/wp-includes/js/jquery/ui/resizable.min.js?ver=1.14.2](http://quickfixwithbindu-local.local/wp-includes/js/jquery/ui/resizable.min.js?ver=1.14.2) | 200 | application/x-javascript |
| [/wp-includes/js/jquery/ui/sortable.min.js?ver=1.14.2](http://quickfixwithbindu-local.local/wp-includes/js/jquery/ui/sortable.min.js?ver=1.14.2) | 200 | application/x-javascript |
| [/wp-includes/js/media-audiovideo.min.js?ver=7.1.2](http://quickfixwithbindu-local.local/wp-includes/js/media-audiovideo.min.js?ver=7.1.2) | 200 | application/x-javascript |
| [/wp-includes/js/media-editor.min.js?ver=7.1.2](http://quickfixwithbindu-local.local/wp-includes/js/media-editor.min.js?ver=7.1.2) | 200 | application/x-javascript |
| [/wp-includes/js/media-models.min.js?ver=7.1.2](http://quickfixwithbindu-local.local/wp-includes/js/media-models.min.js?ver=7.1.2) | 200 | application/x-javascript |
| [/wp-includes/js/media-views.min.js?ver=7.1.2](http://quickfixwithbindu-local.local/wp-includes/js/media-views.min.js?ver=7.1.2) | 200 | application/x-javascript |
| [/wp-includes/js/mediaelement/mediaelement-and-player.min.js?ver=4.2.17](http://quickfixwithbindu-local.local/wp-includes/js/mediaelement/mediaelement-and-player.min.js?ver=4.2.17) | 200 | application/x-javascript |
| [/wp-includes/js/mediaelement/mediaelement-migrate.min.js?ver=7.1.2](http://quickfixwithbindu-local.local/wp-includes/js/mediaelement/mediaelement-migrate.min.js?ver=7.1.2) | 200 | application/x-javascript |
| [/wp-includes/js/mediaelement/mediaelementplayer-legacy.min.css?ver=4.2.17](http://quickfixwithbindu-local.local/wp-includes/js/mediaelement/mediaelementplayer-legacy.min.css?ver=4.2.17) | 200 | text/css |
| [/wp-includes/js/mediaelement/wp-mediaelement.min.css?ver=7.1.2](http://quickfixwithbindu-local.local/wp-includes/js/mediaelement/wp-mediaelement.min.css?ver=7.1.2) | 200 | text/css |
| [/wp-includes/js/mediaelement/wp-mediaelement.min.js?ver=7.1.2](http://quickfixwithbindu-local.local/wp-includes/js/mediaelement/wp-mediaelement.min.js?ver=7.1.2) | 200 | application/x-javascript |
| [/wp-includes/js/plupload/moxie.min.js?ver=1.3.5.1](http://quickfixwithbindu-local.local/wp-includes/js/plupload/moxie.min.js?ver=1.3.5.1) | 200 | application/x-javascript |
| [/wp-includes/js/plupload/plupload.min.js?ver=2.1.9](http://quickfixwithbindu-local.local/wp-includes/js/plupload/plupload.min.js?ver=2.1.9) | 200 | application/x-javascript |
| [/wp-includes/js/plupload/wp-plupload.min.js?ver=7.1.2](http://quickfixwithbindu-local.local/wp-includes/js/plupload/wp-plupload.min.js?ver=7.1.2) | 200 | application/x-javascript |
| [/wp-includes/js/shortcode.min.js?ver=7.1.2](http://quickfixwithbindu-local.local/wp-includes/js/shortcode.min.js?ver=7.1.2) | 200 | application/x-javascript |
| [/wp-includes/js/underscore.min.js?ver=1.13.8](http://quickfixwithbindu-local.local/wp-includes/js/underscore.min.js?ver=1.13.8) | 200 | application/x-javascript |
| [/wp-includes/js/utils.min.js?ver=7.1.2](http://quickfixwithbindu-local.local/wp-includes/js/utils.min.js?ver=7.1.2) | 200 | application/x-javascript |
| [/wp-includes/js/wp-backbone.min.js?ver=7.1.2](http://quickfixwithbindu-local.local/wp-includes/js/wp-backbone.min.js?ver=7.1.2) | 200 | application/x-javascript |
| [/wp-includes/js/wp-util.min.js?ver=7.1.2](http://quickfixwithbindu-local.local/wp-includes/js/wp-util.min.js?ver=7.1.2) | 200 | application/x-javascript |

#### External links/assets, including stored references

| URL | HTTP | Content type / error |
| --- | --- | --- |
| [https://cdn.brevo.com/js/sdk-loader.js](https://cdn.brevo.com/js/sdk-loader.js) | 200 | application/javascript |
| [https://cdn.by.wonderpush.com/sdk/1.1/wonderpush-loader.min.js](https://cdn.by.wonderpush.com/sdk/1.1/wonderpush-loader.min.js) | 200 | application/javascript; charset=utf-8 |
| [https://fonts.googleapis.com/css?family=Roboto+Slab:100,100italic,200,200italic,300,300italic,400,400italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic&display=swap](https://fonts.googleapis.com/css?family=Roboto+Slab:100,100italic,200,200italic,300,300italic,400,400italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic&display=swap) | 200 | text/css; charset=utf-8 |
| [https://fonts.googleapis.com/css?family=Roboto:100,100italic,200,200italic,300,300italic,400,400italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic&display=swap](https://fonts.googleapis.com/css?family=Roboto:100,100italic,200,200italic,300,300italic,400,400italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic&display=swap) | 200 | text/css; charset=utf-8 |
| [https://images.unsplash.com/photo-1692275294193-656d9dca69ae?q=85&w=584&h=438&fit=crop](https://images.unsplash.com/photo-1692275294193-656d9dca69ae?q=85&w=584&h=438&fit=crop) | 200 | image/jpeg |
| [https://images.unsplash.com/photo-1701743803710-3391bd2d28e2?q=85&w=584&h=438&fit=crop](https://images.unsplash.com/photo-1701743803710-3391bd2d28e2?q=85&w=584&h=438&fit=crop) | 200 | image/jpeg |
| [https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css?ver=7.1.2](https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css?ver=7.1.2) | 200 | text/css; charset=utf-8 |
| [https://purple-bison-335489.hostingersite.com/creating-a-comprehensive-recipe-website/](https://purple-bison-335489.hostingersite.com/creating-a-comprehensive-recipe-website/) | 404 | HTTP Error 404: Not Found |
| [https://purple-bison-335489.hostingersite.com/creating-the-perfect-recipe-website/](https://purple-bison-335489.hostingersite.com/creating-the-perfect-recipe-website/) | 404 | HTTP Error 404: Not Found |
| [https://scripts.journeymv.com/tags/2f334773-b2be-495b-9948-0faeba981123.js?ver=7.1.2](https://scripts.journeymv.com/tags/2f334773-b2be-495b-9948-0faeba981123.js?ver=7.1.2) | 200 | application/javascript; charset=utf-8 |
| [https://www.canva.com/design/DAHRnwlQn-E/KURL9TVWV5dIcOMvDuF-MA/view?embed](https://www.canva.com/design/DAHRnwlQn-E/KURL9TVWV5dIcOMvDuF-MA/view?embed) | 200 | text/html;charset=utf-8 |
| [https://www.canva.com/design/DAHRnwlQn-E/KURL9TVWV5dIcOMvDuF-MA/view?utm_content=DAHRnwlQn-E&utm_campaign=designshare&utm_medium=embeds&utm_source=link](https://www.canva.com/design/DAHRnwlQn-E/KURL9TVWV5dIcOMvDuF-MA/view?utm_content=DAHRnwlQn-E&utm_campaign=designshare&utm_medium=embeds&utm_source=link) | 200 | text/html;charset=utf-8 |
| [https://www.facebook.com/bindu.pathak.18/reels/](https://www.facebook.com/bindu.pathak.18/reels/) | 200 | text/html; charset="utf-8" |
| [https://www.instagram.com/quickfixwithbindu/reels/](https://www.instagram.com/quickfixwithbindu/reels/) | 200 | text/html; charset="utf-8" |
| [https://www.quickfixwithbindu.com/](https://www.quickfixwithbindu.com/) | 200 | text/html; charset=UTF-8 |
| [https://www.quickfixwithbindu.com/category/healthy-bites/](https://www.quickfixwithbindu.com/category/healthy-bites/) | 200 | text/html; charset=UTF-8 |
| [https://www.quickfixwithbindu.com/category/home-remedies/](https://www.quickfixwithbindu.com/category/home-remedies/) | 200 | text/html; charset=UTF-8 |
| [https://www.quickfixwithbindu.com/category/main-meals/](https://www.quickfixwithbindu.com/category/main-meals/) | 200 | text/html; charset=UTF-8 |
| [https://www.quickfixwithbindu.com/category/quick-starts/](https://www.quickfixwithbindu.com/category/quick-starts/) | 200 | text/html; charset=UTF-8 |
| [https://www.quickfixwithbindu.com/wp-content/uploads/2026/02/IMG_9419-1-scaled.jpg](https://www.quickfixwithbindu.com/wp-content/uploads/2026/02/IMG_9419-1-scaled.jpg) | 200 | image/jpeg |
| [https://www.quickfixwithbindu.com/wp-content/uploads/2026/02/cropped-Gemini_Generated_Image_3v5yuj3v5yuj3v5y-300x300.png](https://www.quickfixwithbindu.com/wp-content/uploads/2026/02/cropped-Gemini_Generated_Image_3v5yuj3v5yuj3v5y-300x300.png) | 200 | image/png |
| [https://www.quickfixwithbindu.com/wp-content/uploads/2026/02/healthy_bites.png](https://www.quickfixwithbindu.com/wp-content/uploads/2026/02/healthy_bites.png) | 200 | image/png |
| [https://www.quickfixwithbindu.com/wp-content/uploads/2026/02/home_remedies.png](https://www.quickfixwithbindu.com/wp-content/uploads/2026/02/home_remedies.png) | 200 | image/png |
| [https://www.quickfixwithbindu.com/wp-content/uploads/2026/02/main_meals.png](https://www.quickfixwithbindu.com/wp-content/uploads/2026/02/main_meals.png) | 200 | image/png |
| [https://www.quickfixwithbindu.com/wp-content/uploads/2026/02/quick_starts.png](https://www.quickfixwithbindu.com/wp-content/uploads/2026/02/quick_starts.png) | 200 | image/png |
| [https://www.tiktok.com/@quickfixwithbindu?lang=en](https://www.tiktok.com/@quickfixwithbindu?lang=en) | 200 | text/html; charset=utf-8 |
| [https://www.youtube.com/@QuickFixwithBindu](https://www.youtube.com/@QuickFixwithBindu) | 404 | HTTP Error 404: Not Found |

### Appendix D — installed plugin/module inventory

| Plugin | Version | State | Role / verification limit |
| --- | --- | --- | --- |
| All in One SEO | 5.0.2 | Active | Metadata, sitemap and SEO administration |
| Broken Link Checker by AIOSEO | 1.3.0.1 | Active | Link checking service; account/job execution unverified |
| Simple Custom CSS and JS | 3.53 | Active | Additional stylesheet/script injection |
| Smash Balloon Facebook Feed | 4.10.0 | Active | Facebook feed integration; credentials/content delivery unverified |
| Smash Balloon X (Twitter) Feed | 2.9.0 | Active | X/Twitter feed integration; delivery unverified |
| Elementor | 4.1.4 | Active | Homepage/page layout |
| Feeds for TikTok (TikTok feed, video, and gallery plugin) | 1.5.2 | Active | TikTok feed integration; delivery unverified |
| Feeds for YouTube | 2.6.6 | Installed, inactive | YouTube feed plugin; custom YouTube importer is separate |
| FileBird Lite | 6.5.8 | Installed, inactive | Media-folder management alternative |
| Folders | 3.2.0 | Active | Media/content organization; frontend assets observed |
| Google Analytics for WordPress by MonsterInsights | 11.1.3 | Installed, inactive | MonsterInsights analytics alternative |
| Site Kit by Google | 1.187.0 | Active | Google service integration; event delivery unverified |
| Hostinger Tools | 3.0.66 | Installed, inactive | Hostinger hosting utilities |
| Hostinger AI | 3.0.39 | Installed, inactive | Hostinger AI assistance, separate from custom Gemini snippets |
| Hostinger Easy Onboarding | 2.1.22 | Installed, inactive | Hostinger onboarding |
| Hostinger Reach | 1.8.5 | Installed, inactive | Subscription block provider used by newsletter page; currently inactive |
| Image Optimization - Optimize Images and Convert to WebP or AVIF | 1.7.7 | Active | Image conversion/optimization; jobs unverified |
| WPCode Lite | 2.3.9 | Active | WPCode, runs custom recipe application snippets |
| Smash Balloon Instagram Feed | 6.11.4 | Active | Instagram feed integration; custom Instagram index is separate |
| LiteSpeed Cache | 7.9.1 | Installed, inactive | Caching/optimization; inactive |
| Brevo - Email, SMS, Web Push, Chat, and more. | 3.3.5 | Active | Brevo email/push integration; SDKs observed, delivery unverified |
| MailPoet | 5.40.0 | Installed, inactive | Newsletter alternative; inactive |
| Mediavine Control Panel | 2.10.11 | Active | Advertising integration; Journey script observed |
| Noptin | 4.3.10 | Active | Noptin newsletter form and automation support; delivery unverified |
| OptinMonster | 2.16.24 | Active | Opt-in/campaign integration; account/campaign execution unverified |
| Ally - Web Accessibility & Usability | 4.1.1 | Active | Accessibility interface support; conformance unverified |
| Reviews Feed | 2.6.0 | Active | Review feed integration; delivery unverified |
| Super Progressive Web Apps | 2.2.45 | Active | Manifest/service worker/PWA registration |
| WP Recipe Maker | 10.8.3 | Active | Separate recipe-card tooling; no published wprm recipe posts in inventory and metadata suppressed by custom code |
| WPForms Lite | 2.0.2.1 | Active | Forms builder; no published wpforms definitions in inventory |

#### Stored template and historical snippet inventory

| ID | Type | Status | Name |
| --- | --- | --- | --- |
| 5 | elementor_library | publish | Default Kit |
| 18 | wp_navigation | publish | AI menu |
| 19 | wp_template_part | publish | Header |
| 21 | wp_template_part | publish | Footer |
| 61 | custom-css-js | publish |  |
| 122 | wp_template | publish | Pages |
| 176 | wp_template | publish | Category Archives |
| 544 | wp_template | publish | Single Posts |
| 625 | wp_template | publish | Search Results |
| 636 | wp_template | publish | Index |
| 1322 | noptin-form | publish | Newsletter Subscription Form |

| Snippet ID | Status | Name |
| --- | --- | --- |
| 58 | draft | Display a message after the 1st paragraph of posts |
| 59 | draft | Completely Disable Comments |
| 60 | draft | Youtube Videos |
| 61 | publish |  |
| 74 | draft | Fetch_videos |
| 75 | publish | Fetch_YT_Videos |
| 76 | publish | recipe_gallery_for_latest_videos |
| 78 | trash | Video_Display_CSS |
| 89 | publish | JS_Video_Display |
| 90 | trash | Video_Modal_Display |
| 126 | publish | Manage_Category |
| 158 | publish | recipe_grid_category_videos_list |
| 159 | trash | category_recipe_css |
| 189 | publish | Modal_Window_Cook_Mode |
| 190 | trash | AI_Recipe_Video_Processor |
| 191 | draft | AI_Videos_Processor_debug |
| 192 | publish | AI Recipes Processor |
| 193 | publish | QuickFix Recipe AJAX Handler |
| 195 | publish | Render Recipe Cards |
| 526 | publish | Single Recipe SEO Schema |
| 528 | publish | Modal Window Cook Mode CSS |
| 529 | publish | Full Recipe Page CSS |
| 537 | draft | force quickfix recipe template |
| 538 | publish | Full Recipe Page |
| 605 | publish | AI Categorizer |
| 608 | draft | quickfix_category_nav |
| 627 | publish | search_result_custom_image |
| 686 | publish | Recipe Bulk Editor |
| 720 | publish | set_recipe_featured_image_from_video |
| 721 | publish | action_fix_images |
| 1133 | publish | Recipe Archive Index |
| 1157 | publish | All Recipes Instagram |
| 1312 | draft | Full Recipe Page - backup |
| 1333 | publish | Impact Site Verification |
| 1334 | publish | Upload_Recipe |

### Appendix E — content reconciliation lists

#### Published posts without matching custom recipes

| Post ID | Recipe page |
| --- | --- |
| 555 | [2-INGREDIENT SPROUTED OATS & BEETROOT FLATBREADS (Gluten-Free)](http://quickfixwithbindu-local.local/recipes/2-ingredient-sprouted-oats-beetroot-flatbreads-gluten-free/) |
| 556 | [Easy Eggless Whole Wheat Chocolate Chunk Cookies (Batch of 15) #wholewheat #egglessbaking](http://quickfixwithbindu-local.local/recipes/easy-eggless-whole-wheat-chocolate-chunk-cookies-batch-of-15-wholewheat-egglessbaking/) |
| 557 | [2-INGREDIENT SPROUTED OATS & BEETROOT FLATBREADS (Gluten-Free)](http://quickfixwithbindu-local.local/recipes/2-ingredient-sprouted-oats-beetroot-flatbreads-gluten-free-2/) |
| 558 | [2-Ingredient Sprouted Oat and Beetroot Flatbreads](http://quickfixwithbindu-local.local/recipes/2-ingredient-sprouted-oat-and-beetroot-flatbreads/) |
| 559 | [2-Ingredient Sprouted Oat and Beetroot Flatbreads](http://quickfixwithbindu-local.local/recipes/2-ingredient-sprouted-oat-and-beetroot-flatbreads-2/) |
| 560 | [2-INGREDIENT SPROUTED OATS & BEETROOT FLATBREADS (Gluten-Free)](http://quickfixwithbindu-local.local/recipes/2-ingredient-sprouted-oats-beetroot-flatbreads-gluten-free-3/) |
| 561 | [2-INGREDIENT SPROUTED OATS & BEETROOT FLATBREADS (Gluten-Free)](http://quickfixwithbindu-local.local/recipes/2-ingredient-sprouted-oats-beetroot-flatbreads-gluten-free-4/) |
| 562 | [2-INGREDIENT SPROUTED OATS & BEETROOT FLATBREADS (Gluten-Free)](http://quickfixwithbindu-local.local/recipes/2-ingredient-sprouted-oats-beetroot-flatbreads-gluten-free-5/) |
| 563 | [High-Protein, Zero-Waste Flatbread in Just 2 Ingredients!](http://quickfixwithbindu-local.local/recipes/high-protein-zero-waste-flatbread-in-just-2-ingredients/) |
| 1177 | [3 Ingredient Peanut Butter Protein Bites](http://quickfixwithbindu-local.local/recipes/3-ingredient-peanut-butter-protein-bites/) |

#### Custom recipes with missing ingredients or instructions

| Custom ID | Post ID / page | Ingredients | Steps |
| --- | --- | --- | --- |
| 10 | [Avocado Oats Egg Pancake Fail](http://quickfixwithbindu-local.local/recipes/avocado-oats-egg-pancake-fail/) | 3 | 0 |
| 24 | [Airfried Chickpea Quinoa Salad](http://quickfixwithbindu-local.local/recipes/airfried-chickpea-quinoa-salad/) | 0 | 0 |
| 58 | [Egg and Sweet Corn Pizza](http://quickfixwithbindu-local.local/recipes/egg-and-sweet-corn-pizza/) | 3 | 0 |
| 66 | [You’ve NEVER Seen an Egg Sandwich Like This!](http://quickfixwithbindu-local.local/recipes/youve-never-seen-an-egg-sandwich-like-this/) | 0 | 0 |
| 136 | [Spice Lovers Pickle 🌶️ Sweet, Tangy & Fiery](http://quickfixwithbindu-local.local/recipes/spice-lovers-pickle-%f0%9f%8c%b6%ef%b8%8f-sweet-tangy-fiery/) | 6 | 0 |
| 145 | [Quick & Tasty Breakfast Idea You Can Make in Minutes!](http://quickfixwithbindu-local.local/recipes/quick-tasty-breakfast-idea-you-can-make-in-minutes/) | 7 | 0 |
| 146 | [Protein Pockets](http://quickfixwithbindu-local.local/recipes/protein-pockets/) | 0 | 0 |
| 151 | [Quick and Easy High Protein Plant-Based Soup](http://quickfixwithbindu-local.local/recipes/quick-and-easy-high-protein-plant-based-soup/) | 0 | 0 |
| 188 | [Tomato Mint Chutney](http://quickfixwithbindu-local.local/recipes/tomato-mint-chutney/) | 0 | 0 |
| 190 | [Happy Canada Day Quick Celebrations](http://quickfixwithbindu-local.local/recipes/happy-canada-day-quick-celebrations/) | 0 | 0 |
| 191 | 386 (no published post) | 0 | 0 |
| 194 | [Irresistible Vegan Meatball](http://quickfixwithbindu-local.local/recipes/irresistible-vegan-meatball/) | 0 | 0 |
| 200 | [Healthy Christmas Bites \| Easy Low Carb Recipes](http://quickfixwithbindu-local.local/recipes/healthy-christmas-bites-easy-low-carb-recipes/) | 0 | 0 |
| 201 | [Creamy Dill Potato Balls](http://quickfixwithbindu-local.local/recipes/creamy-dill-potato-balls/) | 0 | 0 |
| 213 | [Deep Fry vs Air Fry – Huge Difference!](http://quickfixwithbindu-local.local/recipes/deep-fry-vs-air-fry-huge-difference/) | 0 | 0 |
| 218 | [Pure Semolina Momo](http://quickfixwithbindu-local.local/recipes/pure-semolina-momo/) | 0 | 0 |
| 229 | [Air Fry Crispy Quinoa Bites](http://quickfixwithbindu-local.local/recipes/air-fry-crispy-quinoa-bites/) | 0 | 0 |
| 231 | [My Daughter Says My Veg Momos 🥟 Beat Any 5-Star Hotel !](http://quickfixwithbindu-local.local/recipes/my-daughter-says-my-veg-momos-%f0%9f%a5%9f-beat-any-5-star-hotel/) | 0 | 0 |
| 235 | [Protein-Packed Veggie Burger](http://quickfixwithbindu-local.local/recipes/protein-packed-veggie-burger/) | 14 | 0 |
| 238 | [So Good, It Never Lasts! \| Tomato Pickle That Vanishes Fast](http://quickfixwithbindu-local.local/recipes/so-good-it-never-lasts-tomato-pickle-that-vanishes-fast/) | 0 | 0 |
| 260 | [Asparagus and Green Beans Curry Soup](http://quickfixwithbindu-local.local/recipes/asparagus-and-green-beans-curry-soup/) | 11 | 0 |
| 278 | [Tofu Veg Stuffed Buns](http://quickfixwithbindu-local.local/recipes/tofu-veg-stuffed-buns/) | 13 | 0 |
| 318 | [Belgian Waffle Recipe](http://quickfixwithbindu-local.local/recipes/belgian-waffle-recipe/) | 0 | 0 |
| 320 | [Mom's Birthday Feast for Dad](http://quickfixwithbindu-local.local/recipes/moms-birthday-feast-for-dad/) | 4 | 0 |
| 343 | [The Secret to the Perfect Momo Fold 🥟](http://quickfixwithbindu-local.local/recipes/the-secret-to-the-perfect-momo-fold-%f0%9f%a5%9f/) | 0 | 0 |
| 361 | [Crispy Air Fryer Okra \| 10-Minute Masala Magic \| Healthy & Oil-Free Snack](http://quickfixwithbindu-local.local/recipes/crispy-air-fryer-okra-10-minute-masala-magic-healthy-oil-free-snack/) | 0 | 0 |
| 370 | [What’s inside this omelet?](http://quickfixwithbindu-local.local/recipes/whats-inside-this-omelet/) | 0 | 0 |
| 374 | [Tea Strainer Cleaning Hack 😍 No Scrubbing Needed!](http://quickfixwithbindu-local.local/recipes/tea-strainer-cleaning-hack-%f0%9f%98%8d-no-scrubbing-needed/) | 0 | 1 |
| 380 | [If You Have Strawberries 🍓 Make This Strawberry Milk Instead of Buying It from Store](http://quickfixwithbindu-local.local/recipes/if-you-have-strawberries-%f0%9f%8d%93-make-this-strawberry-milk-instead-of-buying-it-from-store-2/) | 0 | 0 |
| 384 | [Stop making boring eggs! Try this Asparagus Roll](http://quickfixwithbindu-local.local/recipes/stop-making-boring-eggs-try-this-asparagus-roll/) | 5 | 0 |
| 397 | [Forget Bread And Try This 30g Lentil Quinoa Protein Bake 🍞](http://quickfixwithbindu-local.local/recipes/forget-bread-and-try-this-30g-lentil-quinoa-protein-bake-%f0%9f%8d%9e/) | 0 | 0 |
| 445 | [Red Lentil & Seed Flourless Crackers](http://quickfixwithbindu-local.local/recipes/red-lentil-seed-flourless-crackers/) | 0 | 0 |
| 454 | [High-Protein Chickpea Crackers](http://quickfixwithbindu-local.local/recipes/high-protein-chickpea-crackers-2/) | 0 | 0 |

#### Videos with multiple custom recipe records

| Video ID | Custom recipe IDs | WordPress post IDs |
| --- | --- | --- |
| 258 | 87, 88 | 282, 283 |
| 422 | 246, 248 | 441, 443 |
| 423 | 247, 249 | 442, 444 |
| 517 | 352, 353 | 698, 699 |
| 522 | 357, 358 | 707, 708 |

#### Videos without custom recipes

| Video ID | Title | Visible |
| --- | --- | --- |
| 365 | New Glass Tumbler Set Unboxing !!! #unboxing #unboxingshorts #unboxingkitchenset #kitchengadgets | 0 |
| 390 | Canada Day 2025 Ends with Fireworks at Spencers Smith Park !!! | 0 |
| 501 | Easy Eggless Whole Wheat Chocolate Chunk Cookies (Batch of 15) #wholewheat #egglessbaking | 0 |
| 583 | High Protein: I never thought Homemade sweet potato & mung bean crackers would turn out THIS good | 0 |

#### Exact duplicate published titles

| Title | Post IDs |
| --- | --- |
| 2-INGREDIENT SPROUTED OATS & BEETROOT FLATBREADS (Gluten-Free) | 555, 557, 560, 561, 562 |
| 2-Ingredient Sprouted Oat and Beetroot Flatbreads | 558, 559 |
| 3 Ingredient Peanut Butter Protein Bites | 1177, 1179 |
| BBQ-Style Air Fryer Paneer | 315, 409 |
| Banana Quinoa Power Pancakes | 405, 454 |
| Classic Shortbread Bites | 698, 699 |
| Crispy Eggplant Fritters | 217, 273 |
| Crispy Onion Dill Fritters (Onion Dill Pakoras) | 427, 492 |
| Flourless Red Lentil Soy Protein Crackers | 1222, 1228 |
| High Protein Flourless Red Lentil Crackers | 1308, 1310 |
| High-Protein Chickpea Crackers | 1279, 1315 |
| High-Protein, Zero-Waste Flatbread in Just 2 Ingredients! | 563, 564 |
| I Never Thought Potatoes & Eggs Could Taste This Good in One Pan! | 1114, 1116 |
| If You Have Almond Flour, Make These Easy Gluten-Free Crackers! | 1095, 1097 |
| If You Have Strawberries 🍓 Make This Strawberry Milk Instead of Buying It from Store | 1110, 1112 |
| Low Sugar Eggless Flower Cookies\| Easy Two-Tone Floral Shortbread | 707, 708 |
| No-Flip Spinach Cheese Omelet | 255, 263, 359 |
| Quick Mushroom and Egg Frittata | 448, 461 |
| Raspberry Chia Pudding | 224, 250 |
| Spice Lovers Pickle 🌶️ Sweet, Tangy & Fiery | 331, 398 |
| Sweet Potato Falafel-Style Bites | 419, 463 |
| Zero Waste Broccoli Stir Fry | 282, 283 |

### Appendix F — every stored video source link

All 452 oEmbed requests returned HTTP 404. This uniform result, combined with unsuccessful direct watch-page retrieval, leaves video availability unverified; it is not evidence that all videos are removed. The channel URL also returned 404 to the HTTP checker, while a separate web tool has an older cached channel page. Confirm channel and video availability in a regular browser. Every stored YouTube ID matches the standard 11-character shape; this checks syntax only. The oEmbed result below separately tests metadata/embeddability. Non-200 results can reflect removed/private/non-embeddable videos or access restrictions and need follow-up; no video stream was downloaded or played.

| Video record | Source link | Visible | oEmbed HTTP | Notes |
| --- | --- | --- | --- | --- |
| 171 | [Natural Cough Syrup Passed Down for Generations \| Sore throat #homeremedy  #honey #ginger](https://www.youtube.com/watch?v=H6d5ZquXCLI) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 172 | [Natural Cough Syrup with 3 Ingredients \| Honey, Ginger & Lime 🍯🍋 #homeremedy #honey #ginger](https://www.youtube.com/watch?v=7KVpsHdNa9w) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 173 | [Refreshing Peach Chia Smoothie \| Perfect Morning Boost #smoothie #peach #breakfastsmoothie #asmr](https://www.youtube.com/watch?v=YSd_qpvEnX0) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 174 | [Creamy Oats Avocado Pancake\| Gluten Free, No Flour, No Kneading, just Oats, Avocado & Water](https://www.youtube.com/watch?v=oFkF2d8c9AI) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 175 | [Natural Cough And Cold Remedy \| Cold And Sore throat #homeremedy  #honey #ginger #turmeric](https://www.youtube.com/watch?v=rBB9egBKEjk) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 176 | [Healthy & Quick Egg Breakfast With Air Fryer #breakfast  #airfryer  #food  #shorts #airfryerrecipes](https://www.youtube.com/watch?v=-rqrUjWLAx8) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 177 | [Crispy Eggplant Fritters \| Vegan, Gluten-Free & Protein-Packed with Chickpea Flour (Besan) #lowcarb](https://www.youtube.com/watch?v=fave6DUUDWk) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 178 | [3 Ingredient Easy Creamy Tomato Soup in Minutes(EP 6 Winter Soup) No Heavy Cream #soupseason #vegan](https://www.youtube.com/watch?v=a_vYV-JbWJw) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 179 | [If You Have Asparagus Make This warm salad To Warmup Your Winter  #wintersalad #asparagusrecipe](https://www.youtube.com/watch?v=-DgZjX9jcBU) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 180 | [Airfried Chickpea Quinoa Salad #glutenfree #quinoa #viral  #shorts](https://www.youtube.com/watch?v=7q9JlennmcQ) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 181 | [BBQ-Style Paneer Chili \| Air Fryer Twist \| Recipe in description #shorts #paneer](https://www.youtube.com/watch?v=0Y6Ld4dzoGE) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 182 | [Healthy & Filling Chickpea Rice with Yogurt Dip — All for $5 #budgetfriendly #healthylunch #dinner](https://www.youtube.com/watch?v=rWln32x4Tf8) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 183 | [Who knew cabbage could turn into a crispy, vegan, low-carb flatbread with just few pure ingredients?](https://www.youtube.com/watch?v=MIbw-AU6_wM) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 184 | [Chia seeds are trending for a reason, and this raspberry pudding shows you why](https://www.youtube.com/watch?v=4Tf4yzro70w) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 185 | [Crispy Smashed Broccoli & Potato Bake \| Easy, Quick & Nutritious Oven Recipe #shorts #shortsfeed](https://www.youtube.com/watch?v=7J-5DzFGCoU) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 186 | [Low-Carb Crispy Cabbage Flatbread \| No Flour, No Dairy, No gluten #vegan #lowcarb #asmr #easycooking](https://www.youtube.com/watch?v=wrk8ZEKuNBs) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 187 | [QUICK LOW CARB CABBAGE OMELET \| EASY HIGH PROTEIN, LOW CARB BREAKFAST](https://www.youtube.com/watch?v=GM9MUXX6Duc) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 188 | [Low-Carb No-Flip Mushroom Omelet \| Tomato Jalapeño Egg Breakfast #easybreakfast #lowcarb #asmrsounds](https://www.youtube.com/watch?v=S4liEzVbQ9M) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 189 | [Fermented Honey Lemon Ginger Syrup \| Natural Cough Remedy & Immune Booster#homeremedy #honey #ginger](https://www.youtube.com/watch?v=NwexV4OAdHY) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 190 | [2-Ingredient Broccoli Omelet \| Keto, Low Carb Breakfast #omelette #ketorecipes #lowcarbrecipes](https://www.youtube.com/watch?v=RJdNYalxIu4) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 191 | [Boil, Mash, Shape! Viral Potato Mushroom Buttons 🍄](https://www.youtube.com/watch?v=TaxqmC4brrA) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 192 | [Crispy Vegan Flatbread \| Protein & Fiber Rich \| Gluten-Free #shortsfeed #shorts #plantains](https://www.youtube.com/watch?v=wzvWNGkx7kc) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 193 | [Protein Bar That Boosts Your Immunity In Winter #proteinbar #healthysnacks #immunitybooster](https://www.youtube.com/watch?v=W18m0Q0RGAo) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 194 | [A flourless, low-carb omelet that looks like a flatbread \| Healthy and so easy #keto #lowcarb](https://www.youtube.com/watch?v=acN2o8Wf3mw) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 195 | [Easy Broccoli Egg Omelet \| Low Carb & High Protein #shorts #shortsfeed  #lowcarbmeals  #ketorecipes](https://www.youtube.com/watch?v=R6QaDf27p6s) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 196 | [Who Knew Eggs Could Bloom Like This? #viralrecipe #egg #food #creativecooking](https://www.youtube.com/watch?v=Bhgq_7Jkk4c) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 197 | [One Pan Low-GI Oats & Sorghum Avocado Flatbread \| #glutenfree #diabeticfriendly #bloodsugarcontrol](https://www.youtube.com/watch?v=e8C7C5VTuv4) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 198 | [5-Minute Cheesy Egg & Mushroom Sandwich \| Student Breakfast Idea #sandwich #egg](https://www.youtube.com/watch?v=aNEiBcyvT94) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 199 | [Skip Store-Bought Chips – Try These Air-Fried Potato Rolls \| Easy Snack #chips #easysnacks](https://www.youtube.com/watch?v=lI4RSsRNNV4) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 200 | [Better Than Store-Bought Seed Crackers \| Naturally Gluten-Free, High Fiber & Healthy Fats #recipe](https://www.youtube.com/watch?v=2U13zQ6uyBY) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 201 | [Natural Cold Remedy You’ll Actually Love. Honey Cinnamon Milk my Grandma’s Secret Drink #homeremedy](https://www.youtube.com/watch?v=KdDE8ta_c1o) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 202 | [Quinoa and Banana Turns Into the Softest Healthy Loaf \| High Protein, Fiber  #glutenfree #protein](https://www.youtube.com/watch?v=mBj0HQjQ2HI) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 203 | [Keto-Friendly Avocado Egg Recipe \| #airfryerrecipes #keto #breakfast #viralrecipe #viral #shorts](https://www.youtube.com/watch?v=wqmlV0JrB2A) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 204 | [Soothe Your Stomach Naturally \| Beetroot Chia Drink for Digestion \| Home Remedy That Works](https://www.youtube.com/watch?v=BPhCO67Eb0Q) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 205 | [Smash Chickpeas and Airfry for a Crunchy, Guilt-Free Snack #airfryerrecipes  #zerooil  #proteinrich](https://www.youtube.com/watch?v=WMAGMStPiaA) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 206 | [Easy and Quick Homemade Seed Crackers \| Crunchy, Healthy & Gluten-Free #shorts #shortsfeed #recipe](https://www.youtube.com/watch?v=Yf1YCXF-BEY) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 207 | [Natural Cough & Immunity Shot \| Orange, Lemon, Ginger & Turmeric \| Easy Home Remedy #homeremedies](https://www.youtube.com/watch?v=dgKBYYGnkT0) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 208 | [Veg Dumplings with Spicy Chutney \| Homemade Momo #momos #dumplings #momoslover #viral](https://www.youtube.com/watch?v=AUHhyA_Yogg) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 209 | [From Potato to Crispy Waffle \| Lazy Day Comfort Food #lazyday #potatolovers #shorts #viralrecipes](https://www.youtube.com/watch?v=B-hwJOVTpw4) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 210 | [Pomegranate Chocolate Bites \| Sweet Treat  #dessert #sweet #darkchocolate #pistachio #pomegranate](https://www.youtube.com/watch?v=VTP6xTtkiEA) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 211 | [Raspberry Chia Pudding , Easy and Healthy Breakfast #easyrecipe #chiaseeds #breakfast](https://www.youtube.com/watch?v=1Vdg9jh3p-I) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 212 | [Stop Stressing About Healthy Bread: Easy Flourless Lentil & Oat Flatbreads (No Egg, No Gluten)](https://www.youtube.com/watch?v=62vnoN0ABa8) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 213 | [No Dough? No Problem, Turn Bread into Pizza in Just 10 Mins ! #breadpizza #pizza #easypizzarecipe](https://www.youtube.com/watch?v=Z_GsdiQbpSg) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 214 | [Egg and sweet corn makes this magic happen #glutenfreerecipes #healthybreakfast](https://www.youtube.com/watch?v=u000u1lPceA) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 215 | [Quick & Healthy Overnight Oats Breakfast #overnightoats #asmr #easyrecipe #nocookrecipes](https://www.youtube.com/watch?v=uCUrPCPmSKQ) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 216 | [No-Flip Spinach Cheese Omelet \| Easy, Healthy Breakfast in 10 Minutes! #quickrecipes  #omellette](https://www.youtube.com/watch?v=hcRRE8pLeNI) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 217 | [This Roasted Squash and Bell Pepper Soup Changed My Dinner Game  #vegan #souprecipe](https://www.youtube.com/watch?v=XOnGyF7hUk4) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 218 | [From Chia Seeds to Microgreens in Just 1 Week! #microgreensathome #chiaseeds](https://www.youtube.com/watch?v=inwBTOINFj0) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 219 | [If You Have An Apple And Oats Make This Easy Breakfast Waffle #glutenfree #breakfast](https://www.youtube.com/watch?v=GTdOQ7w-h2k) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 220 | [High-Protein White Beans Soup (Winter Soup Episode 2) \| #soupseason #vegetarianrecipes #vegan](https://www.youtube.com/watch?v=F3hdHhj1g3w) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 221 | [Morning Detox Drink for Toned Body, Glowing Skin & Better Metabolism \| Honey Lemon Ginger Chia](https://www.youtube.com/watch?v=Gq_cacosohY) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 222 | [Saucy Crispy Pan Fried Momo with Soy-Sesame Glaze \| #dumplings #panfriedmomo #asmrsounds #easyrecipe](https://www.youtube.com/watch?v=xlfnwVNoPAk) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 223 | [You’ve NEVER Seen an Egg Sandwich Like This! 🥪💚 #highprotein #shorts #viral](https://www.youtube.com/watch?v=N-iU6K1awBA) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 224 | [Italian-Style Chestnuts at Home \| Sweet & Savory Winter Snack #chestnut](https://www.youtube.com/watch?v=3rSYVvV2xMY) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 225 | [Start Your Day Right with This Egg and Spinach Power Omelet \| Protein-Packed breakfast #egg #recipe](https://www.youtube.com/watch?v=sLSX-KLoIEw) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 226 | [5-Min Spicy Yogurt Dip \| Cucumber Yogurt Dip \| Chili Cucumber Raita \| Tzatziki Sauce #dips #shorts](https://www.youtube.com/watch?v=mrVQt-VDH44) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 227 | [Protein-Rich Black-Eyed Peas Soup  \| Quick, Easy & Budget-Friendly #soupseason #blackeyedpeas](https://www.youtube.com/watch?v=Q9p-bdEd9A0) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 228 | [If you have Rice Paper And Potatoes Make This Crispy Snack \| No Deep Fry #samosa #vegan #glutenfree](https://www.youtube.com/watch?v=nsspO0NRgeo) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 229 | [Say Goodbye to Boring Sandwiches! 🥪 Creative Fillings for Every Mood #sandwich  #shorts   #snacks](https://www.youtube.com/watch?v=yih3K-Yv6Ew) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 230 | [Protein-Rich Vegan Meal under $2 (Winter Soup Ep 4) \| Instant, Easy & Budget-Friendly #soupseason](https://www.youtube.com/watch?v=QjcEZhIZvsM) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 231 | [Blueberry Chia Smoothie \| Artisan Chia Seeds #smoothie #blueberry #breakfastsmoothie #asmr](https://www.youtube.com/watch?v=ug63lviEOWs) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 232 | [Keto Friendly Flourless High Protein Flatbread \|Sprouted Moong Beans & Dill #proteinpacked #shorts](https://www.youtube.com/watch?v=33kFL2ts2wI) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 233 | [Natural Cough Syrup With Just 3 Ingredients \| Honey, Ginger & Lemon #homeremedy #honey #ginger #asmr](https://www.youtube.com/watch?v=DCeQ6QyQwx0) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 234 | [Stop Stressing About Breakfast: These Protein-Packed Cookies Are Perfect for Healthy Mornings](https://www.youtube.com/watch?v=A7lHryWyYQo) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 235 | [Make This Easy Beginner’s Recipe with Pattypan Squash—No Splatter, Just Flavor #begginersrecipe](https://www.youtube.com/watch?v=nWrEUsk7Xls) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 236 | [Banana Chia Cinnamon Smoothie \| Artisan Chia Seeds #smoothie #banana #breakfastsmoothie #asmr](https://www.youtube.com/watch?v=G7TjphbZvvg) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 237 | [Just 2 Ingredients Oats and Spinach and You Get the Healthiest, Yummiest Flatbread & Stuffed Buns](https://www.youtube.com/watch?v=qspo2mm_hsE) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 238 | [Natural Cough and Cold Syrup with Just 3 Ingredients \| Fermented Ginger-Lemon-Honey Drink](https://www.youtube.com/watch?v=Ved9QSmGIrY) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 239 | [Avocado Oats Egg Pancake Fail \| Still Tasted So Good #breakfast #oats #egg #easyrecipe #protein](https://www.youtube.com/watch?v=xuRS4wFZt7U) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 240 | [One-Pot Budget Meal Prep #dinner #shorts](https://www.youtube.com/watch?v=IMzLyPz8vnQ) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 241 | [Fermented Dosa That Tastes Like South Indian Street Food#dosa #shorts](https://www.youtube.com/watch?v=6zAXzsSBBFQ) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 242 | [Seed Crackers That Are Better Than Store Bought \| Naturally Gluten-Free, High Fiber & Healthy Fats](https://www.youtube.com/watch?v=kjd-tGILZTc) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 243 | [Quick Quinoa Dosa-Style Wraps \| No Fermentation, No Problem! \| #glutenfree #quinoarecipes #quinoa](https://www.youtube.com/watch?v=bRswr-ibLsM) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 244 | [Guilt Free Crunchy Chickpeas in air fryer \| #glutenfree \|#protein](https://www.youtube.com/watch?v=aKSYJOgsMoA) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 245 | [Garlic Green Beans 🍽 \| Restaurant-Style for Under $5 \| Quick & Healthy Side Dish #food #shorts #asmr](https://www.youtube.com/watch?v=MKAm0uwSj1M) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 246 | [I Made Momo Using Only Semolina 100% Suji (No All Purpose Flour) #momosrecipe #plantbased #jholmomo](https://www.youtube.com/watch?v=EtMsyiyMKaM) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 247 | [If You Have Leftover Quinoa? Try This Quick Stir-Fry! #stirfry #easyrecipe #quino #glutenfree #vegan](https://www.youtube.com/watch?v=d55RCOMDX_I) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 248 | [Rainy Day Tomato Zucchini Soup 🌧️ \| Quick & Cozy Comfort in 15 Minutes! #zucchini #soup #vegan](https://www.youtube.com/watch?v=UQt1HZQ4jhA) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 249 | [Protein-Rich Vegan Meal Under $5 (Winter Soup Ep 4)\| Quick, Easy & Budget-Friendly #soupseason#vegan](https://www.youtube.com/watch?v=hIudUGnAgnA) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 250 | [Better Than Deep Fry! Chickpea + Cauliflower = Crispy Falafel #veganfood #glutenfree #proteinrich](https://www.youtube.com/watch?v=3w4Rd1easnM) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 251 | [Never Thought Eggplant Could Taste Like This Just in 10 minutes#lowcarb #vegan #easyrecipe #eggplant](https://www.youtube.com/watch?v=n6QH2hUJgGg) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 252 | [3-Ingredient Bite-Sized Almond Flour Cookies \| Gluten-Free & Vegan](https://www.youtube.com/watch?v=l-4TKk4qVdQ) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 253 | [Homemade Whole Wheat Pizza with Fresh Mozzarella & Basil \| Bakery-Style Crust #pizza #viral #shorts](https://www.youtube.com/watch?v=rpfjBow2P1c) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 254 | [Natural Detox Drink: Make These 2-in-1 Immunity Cubes (Zero Waste) #shorts #immunity](https://www.youtube.com/watch?v=XHpMRijcY7c) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 255 | [Low Carb Cauliflower Omelet \| High Protein Healthy Breakfast](https://www.youtube.com/watch?v=ft6mXOtStXo) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 256 | [Low-carb omelet that looks like a flatbread \| Healthy and so easy #keto #lowcarb #healthyeating](https://www.youtube.com/watch?v=P8YGZWDGGqk) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 257 | [When It’s Cold Outside, Make These Steamed Momos with Spicy Chutney #momos #asmr  #dumplings](https://www.youtube.com/watch?v=tD7JkogHxVQ) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 258 | [Zero Waste Broccoli Stir Fry \| Quick & Healthy 5-Minute Recipe #broccoli #stirfry #weightloss](https://www.youtube.com/watch?v=6r_kw38tNas) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 259 | [No Flour Oats Avocado Pancake 🥑 \| Soft, Creamy & Gluten-Free](https://www.youtube.com/watch?v=Da9TpGdOdlU) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 260 | [No Batter? No Problem! Blueberry Banana Bread Waffle Hack 🍌\| Quick 5-Min Breakfast #eggbread](https://www.youtube.com/watch?v=vSZzXTJmXxU) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 261 | [Breakfast You’ll Want Every Day \|Viral Triangle Tortilla Wrap #asmr #food #egg #cheese #kidslunchbox](https://www.youtube.com/watch?v=dfExJjtpVyw) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 262 | [Easiest Way Of Cooking Broccoli \| Zero Waste #shorts #shortsfeed #stirfryrecipes](https://www.youtube.com/watch?v=VH_0HQvVzhc) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 263 | [Crispy cauliflower bites with a cornflakes crunch, sometimes frying doesn’t hurt #asmr #snacks](https://www.youtube.com/watch?v=PmWin0Iwlz0) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 264 | [Roasted Squash Soup (Winter Soup Episode 1) #squash #shorts #soupseason](https://www.youtube.com/watch?v=X_XwHTRqRxo) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 265 | [15-Minute Plant-Based Protein Packed Soup! Quick Zucchini Edamame Soup with Tofu \| Soup Series Ep#9](https://www.youtube.com/watch?v=EoMfyukiFjU) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 266 | [Never Imagined Quinoa and Banana Could Make This Soft, Wholesome Loaf!  #glutenfree  #bananabread](https://www.youtube.com/watch?v=oAYsMIiq97c) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 267 | [Crispy Zucchini Egg Fritters \| Easy High-Protein Breakfast or Snack #fritters #shorts #shortsfeed](https://www.youtube.com/watch?v=1sniNdOcW8w) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 268 | [Honey Ginger Rosemary Drink for Cold & Cough \| Nighttime Relief #homeremedy #honey #ginger #rosemary](https://www.youtube.com/watch?v=QrxHEr-dVCk) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 269 | [Stop Buying Chips! Try This Air-Fryer Hack for Ultra Crispy Plantain Chips #veganrecipes](https://www.youtube.com/watch?v=gpd5JXfW39g) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 270 | [3 Ingredient Creamy Corn Soup Without Cream \| EP 5 Winter Soup #wintersoup #quickrecipeseries #vegan](https://www.youtube.com/watch?v=LpnTQ0hehS8) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 271 | [Crispy Potato Egg Fritters in 10 Minutes! Quick Breakfast or Snack Idea 🍳🥔 #recipe #food #eggrecipes](https://www.youtube.com/watch?v=M9hWaQGBQag) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 272 | [This 1-Onion Omelet Changed My Breakfast Forever #healthyeating #easyrecipe](https://www.youtube.com/watch?v=LasAhsi8WzM) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 273 | [Cheesy Paneer Potatoes in Airfryer 🍽️ So Easy & Gluten-Free \|15 minutes snack #airfryer #paneer](https://www.youtube.com/watch?v=oCns5CaPCgA) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 274 | [Avacado in Dosa \|Turn Your Classic Dosa into a Superfood Meal #guacamole #glutenfree  #avocadorecipe](https://www.youtube.com/watch?v=X_szbGDgeN8) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 275 | [Looking For Gluten Free And Vegan Breakfast, Try This Crispy Cabbage Flatbread  #lowcarb #asmr](https://www.youtube.com/watch?v=EupB3MwVLQc) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 276 | [Crispy Saucy Pan-Fried Dumplings with Soy-Sesame Glaze \| Tofu-Paneer Momo \| #dumplings #panfriedmomo](https://www.youtube.com/watch?v=wHomqkZNy38) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 277 | [This Fusion Taco Will Blow Your Mind \| Quick Taco Style Puri #tacos #chickpeas](https://www.youtube.com/watch?v=nj9ZNRGU1hY) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 278 | [Crispy Smashed Parmesan Potato & Broccoli Tray Bake \| Easy Oven Veggie Recipe #shorts](https://www.youtube.com/watch?v=epTFdZikeh4) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 279 | [2 Ingredients Gentle Pink Rice Crackers for Sensitive Stomach  #glutenfree #oilfreesnack](https://www.youtube.com/watch?v=-QDx41z9jlQ) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 280 | [Crispy Crunchy Cauliflower Wings #asmr #snacks](https://www.youtube.com/watch?v=YEkw591v-po) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 281 | [Butterfly Pasta \| $5 Easy Dinner That Saves Time & Brings Joy to the table #quickmeals #easyrecipe](https://www.youtube.com/watch?v=qud6H3fUCWo) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 282 | [Instant No Flour Protein Pancakes \| Lentil Oats Recipe \| High Protein #glutenfree  #diabeticfriendly](https://www.youtube.com/watch?v=J2vXQxSZPhM) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 283 | [Regrowing Chia Microgreens in 7 Days #chiaseeds #microgreenswithoutsoil #microgreensathome](https://www.youtube.com/watch?v=sZObFYKZBmI) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 284 | [No Flour Protein Pancake \| Lentils Oats Recipe \| High Protein #glutenfree  #diabeticfriendly #shorts](https://www.youtube.com/watch?v=MkJzEp1x7Gs) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 285 | [Sugar Free Pancakes\| Perfect Breakfast #pancakes #easybreakfast #bananapancake #shortsfeed #shorts](https://www.youtube.com/watch?v=9VsC9l2fBO4) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 286 | [15-Min High Protein Red Lentil & Broccoli Soup \| Episode 8: The Quick Fix](https://www.youtube.com/watch?v=OG9728TK6qs) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 287 | [If you have zucchini and cabbage, make this ! #breakfast #zucchini #food](https://www.youtube.com/watch?v=0_BbcMOGVqs) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 288 | [Easy Brownie Mix Cake Anyone can make \| Quick & Moist Dessert \| Recipe in description #shorts](https://www.youtube.com/watch?v=hKJomHcJlkU) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 289 | [The Secret to 2-Ingredient Pink Tortillas!](https://www.youtube.com/watch?v=qAL_tCLapQI) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 290 | [BBQ-Style Air Fryer Paneer \| Smoky & Juicy Cottage Cheese in 10 Mins #paneer #quickrecipes #airfryer](https://www.youtube.com/watch?v=s2rMKY8iK34) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 291 | [This 10-Min Stir Fry Will Steal Your Heart \| Love for Mushroom and Asparagus #glutenfree #vegan](https://www.youtube.com/watch?v=eZOSV-ZGwgA) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 292 | [Stop Buying Tortillas!  Try These 2-Ingredient Flourless Protein Wraps (8.5g Protein) #proteinrich](https://www.youtube.com/watch?v=cfovjLJ20jY) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 293 | [Watermelon Mint Lime Juice 🍉🌿💦 #summer #watermelon #refreshing #nosugar #easysummerdrinks](https://www.youtube.com/watch?v=FKH3rX-5SbY) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 294 | [Egg with lettuce \| Healthy Omelet Wrap #glutenfree #eggrecipes #lettuce #yogurt](https://www.youtube.com/watch?v=-baU0LaW6PQ) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 295 | [Searching for a Low-Carb Meal Try This Chia Cabbage And Carrot Flatbread #lowcarb #asmr #glutenfree](https://www.youtube.com/watch?v=7COJpOz1nZg) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 296 | [Toned Body & Glowing Skin in Weeks! Natural Honey Lemon Ginger Detox Drink  #weightlossdrink](https://www.youtube.com/watch?v=n8kUXqZ4XEQ) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 297 | [Have You Tried Taro Leaves This Way? Iron-Rich Taro Leaf Curry with Paneer #taroleaves #lowiron](https://www.youtube.com/watch?v=xeaIPzasfSA) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 298 | [5- Min Blueberry Banana Waffle For Breakfast \| No Batter Needed ! #eggbreadrecipe #waffle](https://www.youtube.com/watch?v=woDOkZHXSeM) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 299 | [The VIRAL Crispy Rice Paper Omelet \| 5-Minute Breakfast](https://www.youtube.com/watch?v=8jIkMMUIQGw) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 300 | [When winter approaches, nothing warms the soul like a quick creamy tomato soup made in minutes](https://www.youtube.com/watch?v=sc5494P19rU) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 301 | [Stop Throwing Away Your Broccoli Stalks \| 5 - Minute Garlic Charred Broccoli](https://www.youtube.com/watch?v=eyggrfhrGVY) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 302 | [2-Ingredient Pink Tortillas With No Oil Tofu Filling \| Easy Vegan Wrap](https://www.youtube.com/watch?v=KCtvyQeIwDY) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 303 | [Looking for Diabetic-Friendly Meal Options? Try This Oats & Sorghum Flatbread  #glutenfree #diet](https://www.youtube.com/watch?v=jBiv_FZu2u4) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 304 | [Budget Friendly Fluffy Quinoa Rice With Squash Soup\|  Healthy Weeknight Dinner Fix](https://www.youtube.com/watch?v=q_nCyDwzAmc) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 305 | [Zero-Oil Beet Oats Flatbread \| Low-GI Healthy Flatbread](https://www.youtube.com/watch?v=9_9K8ussg0Q) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 306 | [This Creamy Flatbread Has NO Right To Be This Healthy #glutenfree  #diabeticfriendly](https://www.youtube.com/watch?v=CFkpJ5o22-I) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 307 | [Spice Lovers Pickle 🌶️ Sweet, Tangy & Fiery #spicy #pickle #cooking #homemade #hotsauce #foodies](https://www.youtube.com/watch?v=gJsrXfqqW1g) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 308 | [No Sugar Fluffiest Banana Pancakes That Taste Like Dessert #pancakes #easybreakfast #sugarfree #asmr](https://www.youtube.com/watch?v=DuphlUGBg1E) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 309 | [Super Easy Oyster Mushroom Stir Fry \| Quick Lunch or Dinner Idea #mushroom #vegetarianrecipes](https://www.youtube.com/watch?v=PTMNIsaxFpo) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 310 | [Quick High-Protein Soup with Zucchini & Lentils #highprotein #vegan #15minutesrecipe](https://www.youtube.com/watch?v=euEimKLvYKE) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 311 | [You’ll Never Eat Asparagus the Same Way Again! 🍅✨ \| Asparagus Cherry Pop Stir-Fry #quickfixwithbindu](https://www.youtube.com/watch?v=hI237NtZwtU) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 312 | [Who Said Broccoli Is Boring? 🥦 Try This 5-Minute Salad You’ll Make Every Day #broccolistirfry](https://www.youtube.com/watch?v=ZlS6qqb9-UQ) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 313 | [Easy Homemade Festive Sweet Treat Anytime \| Motichoor Laddu with Simple Ingredients #homemade #laddu](https://www.youtube.com/watch?v=toN_vLE4m8w) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 314 | [I Tried the Viral Air Fryer Popcorn Hack… FAIL 😂🍿 #popcorn #viralshorts #kitchenhacks](https://www.youtube.com/watch?v=5AlgEsVCKNc) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 315 | [Restaurant Style Garlic Green Beans Just Under $5 \| Quick & Healthy Side Dish #food #shorts #asmr](https://www.youtube.com/watch?v=AG58R85STWc) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 316 | [Quick & Tasty Breakfast Idea You Can Make in Minutes!](https://www.youtube.com/watch?v=r-AN6tErapk) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 317 | [Proteins in pocket ! #shorts #viralvideo #quickfixwithbindu](https://www.youtube.com/watch?v=clRN76jfMJg) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 318 | [If You Have Rice and Urad, Make This Crispy probiotic Dosa \| Simple Fermented Delight #glutenfree](https://www.youtube.com/watch?v=L0PMJh3jeks) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 319 | [Crispy Quinoa Eggplant Bites You’ll Want it Every Day! #glutenfree #quinoa #eathealthy  #weightloss](https://www.youtube.com/watch?v=eHoBNGln-Ww) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 320 | [A Quiet Meal Made With Asparagus and Simple Veggies #wintersalad #asparagusrecipe #easyrecipe](https://www.youtube.com/watch?v=nkc3Ac04-eQ) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 321 | [Quick and Easy High Protein plant based soup #highprotein #vegan](https://www.youtube.com/watch?v=9OK2Lzr722I) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 322 | [Budget Friendly One Pot Chickpea Rice & Cooling Yogurt Dip !  #proteinpacked  #dinner #lunch #shorts](https://www.youtube.com/watch?v=iI9jhTY4FVs) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 323 | [3-Ingredient Pomegranate Pops \| Bite-Sized Pomegranate & Pistachio Clusters](https://www.youtube.com/watch?v=7cB7fsmt458) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 324 | [Plant-Based High-Fiber Meal Under $5 \| Leftover Quinoa Stir-Fry #quinoarecipes #plantbased](https://www.youtube.com/watch?v=dBFgH-_f8HU) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 325 | [Mango Mojito in Minutes 🥭 \| Summer’s Best Drink! #mojito #mangojuice](https://www.youtube.com/watch?v=gvCSWOCG67g) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 326 | [I Stopped Buying Breakfast Cookies After Making These #healthyeating](https://www.youtube.com/watch?v=7gC9yYnXGhE) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 327 | [NO Flour. NO Oil. NO Eggs \| 3-Ingredient Almond Flour Cookies](https://www.youtube.com/watch?v=yKcazGUz40g) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 328 | [Air-Fried Goodness in Every Bite! Chickpea Quinoa Salad You’ll Crave \| #glutenfree #veganfood](https://www.youtube.com/watch?v=2KxF8wZCmM8) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 329 | [Quick Stuffed Cabbage Rolls with Spicy Nutty Sauce \| Healthy & Plant-Based  #shorts #cabbagerecipe](https://www.youtube.com/watch?v=7AUalckQXpE) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 330 | [From Simple Mushrooms to a Signature Salad Full of Flavor and Story #shorts #mushroom #spicy](https://www.youtube.com/watch?v=1AmqlFF5biI) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 331 | [If You Have Mushroom & Tofu, Make This Cheesy Pocket Wrap #vegan #asmr #easyrecipe #mushroom #tofu](https://www.youtube.com/watch?v=Wn7uEC7WZVA) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 332 | [Spicy Mushroom Maggi in Minutes!](https://www.youtube.com/watch?v=T_lDVYMNRoA) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 333 | [Ultra Creamy Macaroni Pasta with Tomato Sauce & Veggies \| #macandcheese #whitesaucepasta #dinner](https://www.youtube.com/watch?v=z9wmMUsNQJI) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 334 | [Make This Easy Lentil Spinach Soup This Winter (Winter Soup Episode 3) #lentilrecipe  #soupseason](https://www.youtube.com/watch?v=wmzjty9b6OY) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 335 | [No-Flip Spinach Cheese Omelet \| Easy, Healthy Breakfast in 10 Minutes! 🍳  #omelette #omelet](https://www.youtube.com/watch?v=m8FAoGKU4VI) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 336 | [Who Knew Stir-Fry Could Be This Easy? No Frying, No Stress, All Flavor #squash #stirfry](https://www.youtube.com/watch?v=r9lS4uKXcKg) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 337 | [Potato Salad with a Nepali Twist! 🥔🌶️ Creamy, Tangy & Spicy – A Potato Salad Like No Other!](https://www.youtube.com/watch?v=eU9-WJmq-0U) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 338 | [Protein-Packed Creamy Pasta \| No Straining, No Fuss  #pasta  #highprotein](https://www.youtube.com/watch?v=wpR2BN11NjY) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 339 | [Whole Wheat Garlic Cheese Buns \| Made From Artisan Pizza Dough ! #garlicbread #easyrecipe #shorts](https://www.youtube.com/watch?v=aqvfkd86zqk) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 340 | [From Pantry to Plate in 5 Minutes! 🕒 Buckwheat Pancake (फापरको रोटी \| कुट्टू का चीला)](https://www.youtube.com/watch?v=4lsBEMfHwd8) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 341 | [How To Make Authentic Himalayan Black Eyed Peas Soup\| Bamboo Shoot & Beans (Soup Series Ep 10)](https://www.youtube.com/watch?v=4OiaARvO9Ek) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 342 | [Just 3 Bananas and You’ll Never Make Pancakes the Old Way Again #pancakes #easybreakfast #asmr](https://www.youtube.com/watch?v=AUDyLolcjIM) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 343 | [Quick Red Lentil Spinach Soup (Winter Soup Episode 3) \| 15-Minute Recipe #lentilsoup #soupseason](https://www.youtube.com/watch?v=-d235zSu1lk) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 344 | [The Simplest Omelette That Changed My Breakfast Routine #cabbagerecipe #lowcarb #quickbreakfast](https://www.youtube.com/watch?v=NoWFd7UTcYI) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 345 | [Flourless, Oil-Free, Egg-Free Kidney-Friendly Oat Cookies \| Low Potassium & Phosphorus Snack #vegan](https://www.youtube.com/watch?v=Kw4mTOEwAZE) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 346 | [Flax Plantain Flatbread \| Simple Vegan Meal \|Crispy & Gluten-Free](https://www.youtube.com/watch?v=wxVprmlQoqE) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 347 | [Quick Fruit Salad \| Summer Fruit Bowl with Chia seeds & Honey #detox #salad #asmr](https://www.youtube.com/watch?v=azeWMmOojkc) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 348 | [Turmeric And Ginger Concentrate \| The Flu Stops Here](https://www.youtube.com/watch?v=C0GKJcQuhqY) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 349 | [Cheesy Egg Quesadilla 🧀 (The 5-Minute Breakfast Hack) #tortillawrap #breakfast](https://www.youtube.com/watch?v=qJuLuuPhty8) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 350 | [No Oil, No Batter! Just Potato & Cheese \| Quick and Easy Waffle Snack #oilfreesnack #guiltfreefood](https://www.youtube.com/watch?v=BdRUyzUnSdk) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 351 | [Love-at-First-Bite \|10-Minute valentine’s Breakfast \| Veggie Heart Omelets #food #recipe #easyrecipe](https://www.youtube.com/watch?v=DDXaNldFsg8) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 352 | [Spice Up Your Health with Turmeric! #turmeric #healthy #shorts](https://www.youtube.com/watch?v=kQgSYbBRHTA) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 353 | [Quick & Tasty Breakfast Idea You Can Make in Minutes! #breakfast #fruit #date Fruit Salad](https://www.youtube.com/watch?v=TiW9B5nPMSw) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 354 | [Avocado Egg Toast #bagel #avocado #egg #toast #protein](https://www.youtube.com/watch?v=22aVealJzyg) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 355 | [No Flour, No Eggs! High-Protein Gluten-Free Lentil Flatbread](https://www.youtube.com/watch?v=2ObzixTIHTo) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 356 | [100% Whole Wheat Artisan Pizza \| Fresh Mozzarella & Basil with Bakery Style Crust #pizza  #shorts](https://www.youtube.com/watch?v=_Vu8WNeeGMs) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 357 | [Spice Lovers Pickle 🌶️ Sweet, Tangy & Fiery #spicy #pickle #cooking #homemade #hotsauce #foodies](https://www.youtube.com/watch?v=WIGklqVx1BU) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 358 | [Air Fryer Corn Ribs with Garlic Butter Glaze 🌽🔥 \| Quick Snack in 10 Mins! #glutenfree #vegan](https://www.youtube.com/watch?v=D35v75ROPy4) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 359 | [Quinoa & Oats quick easy and healthy pancakes 🥞✨ #quickbreakfast #glutenfree #quinoa #vegetarian](https://www.youtube.com/watch?v=4cTusKg67jQ) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 360 | [Tomato Mint Chutney \| Quick, Spicy Dip for Every Meal #tomato #spicy #quick #achar  #mint #dips](https://www.youtube.com/watch?v=KBXYaZwj5ts) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 361 | [Low Carb Tomato Omelet That Looks  Like Flatbread \| No Flour, Keto-Friendly #shorts #easyrecipe](https://www.youtube.com/watch?v=Z0vyq9f4J2M) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 362 | [Happy Canada Day 2025!  from QuickFixWithBindu ❤️ Quick Celebrations from My Cozy Kitchen #canada](https://www.youtube.com/watch?v=ag0HPTLA28A) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 363 | [My recipes #quickfixwithbindu #recipe](https://www.youtube.com/watch?v=qAUXDfvuu_c) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 364 | [Fresh Romaine Lettuce Salad 🥗 with Zesty Honey-Lemon Dressing \| Diet lunch recipes #lettuce #olive](https://www.youtube.com/watch?v=HD7zXNkMSJI) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 365 | [New Glass Tumbler Set Unboxing !!! #unboxing #unboxingshorts #unboxingkitchenset #kitchengadgets](https://www.youtube.com/watch?v=gMIw1aABb4M) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 366 | [Chickpea Tofu Herby Salad #food #asmr #airfryer #chickpeasalad #tofu](https://www.youtube.com/watch?v=HVHCMrnbRVw) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 367 | [Forget Meatballs! Try This Irresistible Vegan Meatball \| Full Video with recipe coming soon #vegan](https://www.youtube.com/watch?v=793n-Kvo6Y4) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 368 | [Rambutan Taste Test 🍒 First Time Trying \| Sweet Like Lychee #tastetest #rambutan #lychee](https://www.youtube.com/watch?v=hCfnefaGtXA) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 369 | [Struggling with Low Iron? These Peanut Chocolate Date Bars Are Perfect #protein #vegan #easyrecipe](https://www.youtube.com/watch?v=uuKsC_L-fYY) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 370 | [Quick Omelet Wrap with Veggies & Cheese \| Easy Breakfast Idea! #eggwrap #eating food #easybreakfast](https://www.youtube.com/watch?v=ga7f9v1jqtI) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 371 | [No Flour No Eggs No Baking Soda \|Almond Flour Biscotti \| Just Mix & Bake](https://www.youtube.com/watch?v=KwHPGm9wW1g) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 372 | [Strawberry Mojito – Summer’s Healthier Refreshment! 🍓🌿 #summerdrink #mojito #shorts](https://www.youtube.com/watch?v=orTUGybLmrQ) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 373 | [Healthy Christmas Bites \| Easy Low Carb Recipes 🎄](https://www.youtube.com/watch?v=s4o3C7fUFUw) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 374 | [Garlic Cheese Buns, Margherita & Veggie Pizza \|1 Whole Wheat Dough, 3 Recipes #pizza #garlicbread](https://www.youtube.com/watch?v=vQkS7XynMpY) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 375 | [Creamy Dill Potato Balls – Full Recipe Out Now(Follow Related Video) #creamydillpotatoballs](https://www.youtube.com/watch?v=1ETDQGZ8EvI) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 376 | [Easy No-Bake Energy Balls \| Date & Pecan Snack (No Refined Sugar)](https://www.youtube.com/watch?v=8kWkdymPEmY) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 377 | [Viral Egg Toast](https://www.youtube.com/watch?v=GPPsPJNuvCU) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 378 | [This Is How I Make Noodles Healthier Without Losing Flavor \| Spicy Mushroom Noodles in Minutes!](https://www.youtube.com/watch?v=gZCAPkwN0mg) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 379 | [Grow Fresh Chia Microgreens in 7 Days with Just Paper Towels!](https://www.youtube.com/watch?v=KOKIiq8frrs) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 380 | [3 Ingredients, 5 Minutes Dates Recipe \| Sweet and Salty #iron #dates #peanut #nobake #protein](https://www.youtube.com/watch?v=N9S4lMw0vuA) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 381 | [Egg Curry Recipe \| अण्डा करी स्वादिलो र सजिलो परिकार \| Roasted Egg Masala \| Anda Curry \| Egg Recipes](https://www.youtube.com/watch?v=YuOaSPSIUy8) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 382 | [The Only Healthy Pancake You’ll Ever Need!: No Sugar, All Energy! 🥞💪 #oats #proteinrich #quinoa](https://www.youtube.com/watch?v=I65O5_D00Cc) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 383 | [I Didn’t Expect Cauliflower To Taste THIS GOOD \| Airfried Cheesy Cauliflower #cauliflower#vegetarian](https://www.youtube.com/watch?v=R9fT_bNzkto) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 384 | [If You Have Zucchini, Try This 5-Min Stir-Fry! 🥒✨ So Quick & Tasty! #stirfry](https://www.youtube.com/watch?v=fE6kdjeVdIg) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 385 | [You’ve Never Wrapped Eggs Like This Before #asmr #food #egg #cheese #kidslunchbox #egghack](https://www.youtube.com/watch?v=cPE-tkRW_jk) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 386 | [Deep Fry vs Air Fry – Huge Difference! 😳 #HealthySwap #airfryer #healthy #nodeepfry](https://www.youtube.com/watch?v=BamDwbN1gyM) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 387 | [Perfectly Cooked Paneer Every Single Time #airfryer #paneer #vegetables](https://www.youtube.com/watch?v=V77VXK-xwu0) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 388 | [Make this unique Classic Breakfast with a Fun Twist ! #eggrecipes #eggtoast #breakfast #viralshorts](https://www.youtube.com/watch?v=JKIXZl-F0hU) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 389 | [Cheesy Zucchini Onion Fritters \| Recipe in description #glutenfree #proteinrich](https://www.youtube.com/watch?v=ir3_qCC6EqY) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 390 | [Canada Day 2025 Ends with Fireworks at Spencers Smith Park !!!](https://www.youtube.com/watch?v=F4mJKOTJci0) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 391 | [Pure Semolina Momo — Authentic & Irresistible ! #momos #asmr  #dumplings](https://www.youtube.com/watch?v=V-8MlEuxZk8) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 392 | [This Nepali Yogurt Potato Salad Will Blow Your Mind \| The Pride of Palpa 🇳🇵 #nepalirecipes #chukauni](https://www.youtube.com/watch?v=ipB9efXTJRw) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 393 | [Easy Mushroom Peas Fried Rice \| Quick 15-Min Comfort Meal!🍚🌿 #healthycooking #friedrice #vegetables](https://www.youtube.com/watch?v=-Ux07eii3IY) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 394 | [Crispy Air-Fried Asparagus Bites \| Spiced Yogurt Coating in 10 Mins! #airfryer #shorts #viralshort](https://www.youtube.com/watch?v=w7wHlic9VgA) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 395 | [Under $5 Dinner: Leftover Quinoa Veggie Stir-Fry #stirfry #easyrecipe #quinoa #glutenfree #vegan](https://www.youtube.com/watch?v=kgrvGaCtPOs) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 396 | [My Fluffy Belgian Waffle Recipe 🧇 Soft, Golden & Homemade #waffle #belgianwaffle](https://www.youtube.com/watch?v=Fu89Oq3sNxo) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 397 | [✨Crispy Sweet Potato Bites \| Pan & Air Fryer \| Fusion Falafel 🧆 #kofta #shorts #viralshorts](https://www.youtube.com/watch?v=w8Hf4RQh8tU) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 398 | [Cheesy Jalapeño Potato Bites \| Easy Air Fryer Snack Recipe \| #potatorecipes](https://www.youtube.com/watch?v=E0xdAQedh_o) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 399 | [Easy Breakfast Waffle with Banana & Oats \| Guilt-Free #nosugar #waffles](https://www.youtube.com/watch?v=JKJ2NpZi7wI) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 400 | [Cozy Zucchini Lentil Soup 🥣 \| Quick & Healthy Dinner Recipe #shorts #easyrecipes #zucchinirecipe](https://www.youtube.com/watch?v=UXya8SkbHpo) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 401 | [5-Min Healthy Fruit Yogurt Salad \| So Easy & Creamy!  #viral #shorts #summer #salad](https://www.youtube.com/watch?v=d5hvSzA4gxE) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 402 | [Air Fry Crispy Quinoa Bites #vegan #quinoarecipes \| ⬇️ Full Recipe – Click on Related Video Below👇](https://www.youtube.com/watch?v=DIemoPeV9Fc) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 403 | [If You Have Sensitive Stomach , Try This Gentle Snack \| Leftover Rice Crackers \| Gluten-Free & Light](https://www.youtube.com/watch?v=Bwn2q8wlYMo) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 404 | [Do you have Mushroom and Egg try this amazing recipe ready in 10 minutes #eggrecipes  #frittata #egg](https://www.youtube.com/watch?v=4-9Gekts9mI) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 405 | [My Daughter Says My Veg Momos 🥟 Beat Any 5-Star Hotel ! #vegmomos #homemade  #shorts](https://www.youtube.com/watch?v=1G0k5Bb9dm0) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 406 | [The Crunchiest Onion Dill Fritters with Peanuts \| Chickpea Flour Snack You’ll Make Again #snacks](https://www.youtube.com/watch?v=jJXyjqaqQ8I) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 407 | [Air Fryer Banana Blueberry Oat Bake \| Breakfast Delight in 15 Mins \| #oats #blueberry #breakfast](https://www.youtube.com/watch?v=EKJb-vRMsFw) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 408 | [Crispy Air-Fried Chickpea & Lettuce Salad 🥗 \| Diet Dinner Recipe #asmr #asmrcooking #chickpeas](https://www.youtube.com/watch?v=wJgUKNtTZwo) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 409 | [🌱 Protein-Packed Veggie Burger 🍔💪](https://www.youtube.com/watch?v=PgqVz8C-Grc) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 410 | [Chia Banana Post workout Smoothie \| #healthysmoothie  #smoothie #banana #breakfastsmoothie #asmr](https://www.youtube.com/watch?v=8jrG3KRCdLw) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 411 | [Quick & Easy Bok Choy Stir Fry Recipe \| #vegetarianrecipe #stir fry bok choy with garlic #quickmeals](https://www.youtube.com/watch?v=A9TwkSTkWtc) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 412 | [So Good, It Never Lasts! \| Tomato Pickle That Vanishes Fast #vegan  #pickle](https://www.youtube.com/watch?v=zKqzQKVbiXs) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 413 | [Tofu Stir Fry with Asparagus & Mushrooms \| Fast & Flavorful! #VeganRecipe  #TofuRecipes #stirfry](https://www.youtube.com/watch?v=GMvPPpqq6dQ) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 414 | [From Baby Zucchini to Cheesy Cups #zucchini #squash #creativecooking #asmr](https://www.youtube.com/watch?v=jir2k_PAhl0) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 415 | [Quick & Healthy Dilly Chickpeas \| Recipe in Description #Shorts #chanamasala #glutenfree #vegan](https://www.youtube.com/watch?v=yFn7nccFFlM) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 416 | [Healthy Bitter Melon Potato Curry \| Great for Blood Pressure \| Karela Aloo Sabji #karelarecipe](https://www.youtube.com/watch?v=hoPvnr2TGgA) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 417 | [Banana Blueberry Oatmeal Muffin Bowl in Airfryer - No Flour, No Sugar#eggless  #airfryerrecipes](https://www.youtube.com/watch?v=MeDXxhbmpy0) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 418 | [Easy Bok Choy Stir Fry Lunch Menu \| #lentilsoup #rice #bokchoy #asmr #peas](https://www.youtube.com/watch?v=-LkpxA1mxbY) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 419 | [Maggi Lovers: Maggi Gets a Protein Makeover! Tofu, Egg & Masala Magic in 1 Bowl #masalamaggirecipe](https://www.youtube.com/watch?v=HsANZEbxq40) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 420 | [If You Have Chickpeas & Lettuce, Make This Crunchy Healthy Snack! @ShreyaPanthee #mediterranean](https://www.youtube.com/watch?v=8-buTY3R8m0) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 421 | [Egg & Cheese Sandwich with a Twist of Dill \|Breakfast in 10 Minutes!#breakfast #easyrecipe #sandwich](https://www.youtube.com/watch?v=V5AzV_GTOLg) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 422 | [Cottage Cheese (Paneer) Wrap \| High-Protein & Flavor Packed \| BBQ Style #lunch #dinner  #quickrecipe](https://www.youtube.com/watch?v=7jjZxgCnlds) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 423 | [Viral Soy Chunks Peanut Salad 🥜🌶 \| High Protein, No-Fry Recipe #vegan #protein](https://www.youtube.com/watch?v=AHRzhdkel44) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 424 | [Flavor-Packed Nepali Potatoes \| Tangy, Spicy & Vegan in 15! #recipe #vegan #potatosnacks #vegetarian](https://www.youtube.com/watch?v=7YViiBXgcIo) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 425 | [5-Min Spicy Mashed Potatoes \| Sizzling Garlic Aloo Mashed Potato Recipe Will Blow Your Mind #shorts](https://www.youtube.com/watch?v=Bt2ML5DT_vg) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 426 | [This quick egg & mushroom sandwich has saved me on so many busy mornings #sandwich #egg](https://www.youtube.com/watch?v=s2Rrklz_LpU) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 427 | [Unique style Egg Toast \| Classic Breakfast with a Fun Twist! #eggrecipes #eggtoast](https://www.youtube.com/watch?v=k0N9MuWnZqw) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 428 | [One sip and you’ll want this every morning! Smoothie That Keeps You Full! Mango, Banana & Oats Blend](https://www.youtube.com/watch?v=DPAMoTVM3Cc) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 429 | [A warm bowl of comfort \| Healthy and Easy Egg Mushroom Fried Rice #leftoverricerecipe #egg](https://www.youtube.com/watch?v=_b5LEJjlPFk) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 430 | [Quick Veg Stir Fry \| Easy & Healthy Recipe with Bindu](https://www.youtube.com/watch?v=WErZYZv2Dxc) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 431 | [Simple Festive Meal That Feels Like Home \| Okra Curry, Beaten Rice & Omelet](https://www.youtube.com/watch?v=ZlMHVEiiThk) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 432 | [Healthy Banana Quinoa Pancakes – Oats + Quinoa = Breakfast Magic! \| No Sugar, High Energy, Protein](https://www.youtube.com/watch?v=mPETG4Dc9Qc) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 433 | [Asparagus and Green Beans Curry Soup #easyrecipes #currysoup #vegsouprecipe #vegan](https://www.youtube.com/watch?v=RTVMbCqbqDY) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 434 | [Roasted Mushroom Salad That Wakes You Up \| Easy Dinner Idea \| Summer Salad #shorts #mushroom #spicy](https://www.youtube.com/watch?v=7j9rDSxEw5k) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 435 | [The Creamiest Veggie Sandwich \| With Cottage Cheese (Paneer ) & Avocado #sandwich #easyrecipe](https://www.youtube.com/watch?v=z8CvErvzvM4) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 436 | [$2 Snack \| Easiest Potato Snack You've Never Heard Of (Made From Scratch!)  #potatorecipe #foodhacks](https://www.youtube.com/watch?v=RVPbJtG5BeA) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 437 | [Garlicky Greens in 5 Minutes! 🔥 Just 2 Ingredients & Big Flavor! にんにく香る！激ウマ青菜炒め｜5分でパパッと2食材レシピ！](https://www.youtube.com/watch?v=Tk5NNuqHUAw) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 438 | [Cheesy Egg Sandwich That Beats Takeout! 🧀💥 Ready in Minutes! #breakfast #lunch #cheese](https://www.youtube.com/watch?v=l6ANJAX4buo) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 439 | [My Daughter Tasting my mushroom and egg recipe @ShreyaPanthee #eggrecipes  #frittata  #egg](https://www.youtube.com/watch?v=2qeaB2fRZ78) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 440 | [Savory Buckwheat Pancake \| फापरको रोटी \| कुट्टू का चीला \| Quick Healthy Meal #food #healthy pancakes](https://www.youtube.com/watch?v=lcz-cL-H3FQ) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 441 | [✨Crispy Sweet Potato Bites \| Pan & Air Fryer \| Fusion Falafel Fridays 🧆 #kofta #sweetpotato](https://www.youtube.com/watch?v=3VfPazs5k_8) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 442 | [You’ll Be Making These Zucchini Quinoa Bites Every Week \| Air Fryer Veg Snack #glutenfree #quinoa](https://www.youtube.com/watch?v=PhxSBy3dEqI) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 443 | [Japanese Layered Omelet \| Cheesy & Colorful Breakfast!  🧀🥚 #egg #viralvideo #viralshorts #shorts](https://www.youtube.com/watch?v=sgyFPU3ZiT4) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 444 | [Can You Feed Your Family for $5? This White Bean & Garlic Rice Recipe Says YES! #cheapdinner #beans](https://www.youtube.com/watch?v=Bc1nNj9ZKZk) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 445 | [Vegan Pasta Under $5! Quick & Healthy Dinner](https://www.youtube.com/watch?v=KHGn3tB6E4s) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 446 | [Easy Meal For Beginners! If you have Mushroom and Asparagus you can make it! Quinoa Salad is a bonus](https://www.youtube.com/watch?v=udxku3UN5-g) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 447 | [Egg Curry with a Twist \| Quick & Creamy Recipe 🍛 \| QuickFixWithBindu #dinner ideas indian](https://www.youtube.com/watch?v=7tDnI8hf2m8) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 448 | [This Isn’t Just a Veg Cutlet… It Turns Into a Melty Pocket Wrap! Easy 2-in-1 Snack Hack #snacks](https://www.youtube.com/watch?v=y3zmQCrFXAk) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 449 | [Egg Rice Fusion Skillet – Quick Biryani Twist #shorts #rice #viral](https://www.youtube.com/watch?v=whqvT1RibBY) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 450 | [Most people throw away broccoli stems and stalks, but I turn them into this delicious stir fry](https://www.youtube.com/watch?v=AxyBeSv-u_8) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 451 | [Tofu Veg Stuffed Buns #tofu #proteinrich #veg #healthy  #food #recipe #foodie #stuffed buns recipe](https://www.youtube.com/watch?v=OJPWEYrRCKM) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 452 | [Try this Cheesy Protein-Packed Potatoes in Airfryer  #potato #cheese #cottagecheese #snacks](https://www.youtube.com/watch?v=sSOxQmWK2yI) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 453 | [Turn Your Potatoes Into These Cheesy Bites,You’ll Be Obsessed\|#food #asmr #airfryerrecipes #airfryer](https://www.youtube.com/watch?v=-zXVQLOJTII) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 454 | [Healthy Bitter Melon Potato Curry \| तिते करेला आलु तरकारी \| करेला आलू की सब्ज़ी \| #hearthealth](https://www.youtube.com/watch?v=MxKSewrLaLw) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 455 | [Just Quinoa, Water and Oats makes this Pancakes \| Inspired by My Mom #glutenfree #quinoa #breakfast](https://www.youtube.com/watch?v=wRGJK1WESV0) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 456 | [This Salad Doesn’t Need a Recipe—Just Your Taste Buds! \| Chop, Mix, Sizzle—No Math, Just Magic ✨](https://www.youtube.com/watch?v=DNsj0CKy8gI) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 457 | [Lasagna Without the Oven? Yes, Please! \|15 minutes Air Fryer Lasagna-Style Pasta](https://www.youtube.com/watch?v=QGt5yCzqOe4) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 458 | [Creamy Potato Dill Balls \| Easy Snack with Surprise Filling \| Air Fryer Snack Everyone Loves!](https://www.youtube.com/watch?v=pQwf5skJWEo) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 459 | [Fresh & Zesty Chickpea Salad \| Quick Summer Recipe! #chickpea salad recipe #chopped salad recipes](https://www.youtube.com/watch?v=aOQwFSM2hqc) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 460 | [Unique Oats & Spinach Recipe for Weight Loss \| Healthy Flatbreads & Stuffed Buns #weightlossrecipes](https://www.youtube.com/watch?v=K4kczvcgU1Q) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 461 | [Don’t Fry That Cauliflower! Try This Crispy Quinoa-Crusted Snack Instead #vegan #airfryer #healthy](https://www.youtube.com/watch?v=pj4w_YHG3AY) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 462 | [Kitchen Experiment Gone Right! Quinoa Meets Cornmeal \| Dosa style flatbread #glutenfree  #quinoa](https://www.youtube.com/watch?v=mJvTtOl9Gi4) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 463 | [Masala Maggi Korean Style \| 10-Minute Spicy Fusion Noodles 🍜 \| #maggi  #veganfood #spicy](https://www.youtube.com/watch?v=QVaIcbYYzdg) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 464 | [Mushroom Stir Fry Gravy within 10 mins \| #MushroomRecipe #chinese  #vegetarianrecipes #gravy recipe](https://www.youtube.com/watch?v=Mfy3b23tpqI) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 465 | [Middle Eastern Vibes with a Twist: Air Fryer Falafel & Grape Chutney #glutenfree  #vegetarian #dill](https://www.youtube.com/watch?v=eQ9yB1Pp568) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 466 | [Spicy Vegetarian Chili in Minutes! \| Perfect Comfort Food ❤️🌱#proteinrich #veggies #chili #dinner](https://www.youtube.com/watch?v=ZdXgdz6LWt8) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 467 | [Under $5 Dinner for 3 \| Quick & Easy Vegan Mushroom Pasta \| Healthy, Budget-Friendly  #vegan #pasta](https://www.youtube.com/watch?v=yV9s0JjdSWY) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 468 | [Leftover Rice? Make This 10-Min Veggie Stir-Fry Rice! recipe #food #leftoverricerecipe #mushroom](https://www.youtube.com/watch?v=s3oaSM-M8Gw) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 469 | [Blueberry Banana Power Smoothie 🍌 \| No Sugar, Just Goodness! #protein #smoothie](https://www.youtube.com/watch?v=sj2BK8lTJXA) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 470 | [Onion And Dill Lovers, This One’s for You! The Perfect Crispy Onion Fritters Snack You’ll Make Again](https://www.youtube.com/watch?v=TJeRjDdce84) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 471 | [Bok Choy Stir Fry Lunch Menu \| बोक चोय साग, दाल, भात र अचार \| बोक चॉय, दाल, चावल और चटनी #lentilsoup](https://www.youtube.com/watch?v=NKUzczaXz2o) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 472 | [Secret to soft falafels with cauliflower & dill \| No onion garlic #veganfood #glutenfree #proteins](https://www.youtube.com/watch?v=fGgdfL7pBY4) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 473 | [Cheesy Quinoa Dill Stuffed Peppers in Air Fryer! #cooking videos #shorts #quinoa  #capsicum #cheese](https://www.youtube.com/watch?v=t_C-Y-_7pMQ) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 474 | [Quick Cucumber Yogurt Salad 🥒🍅 \| 5-Min Summer Side Dish! #summer #salad #yogurt](https://www.youtube.com/watch?v=VaxHRYuB6kw) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 475 | [If You Love Tofu, This Recipe Will Blow Your Mind! #vegetables #asmr #dinner #lunch](https://www.youtube.com/watch?v=_OgiPjfwIVs) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 476 | [No-Flip Corn Pizza in One Pan! Easy Cheesy Breakfast You'll Crave 🍕🌽#glutenfree #healthybreakfast](https://www.youtube.com/watch?v=30Q0H0hu_Dc) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 477 | [Quinoa and Eggplant for Weight Loss? That will Surprise Everyone #glutenfree #proteinrich  #vegan](https://www.youtube.com/watch?v=z4NTMPMWZyw) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 478 | [15-Minute Vegan Pasta Bowl with Tofu & Asparagus \| Quick & Colorful! #vegan #asparagus #quickrecipes](https://www.youtube.com/watch?v=WCJ2PGWBpYI) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 479 | [If You Have Tofu, Don’t Cut It – Crumble It and Air Fry! \| Creamy Dip \| #vegan @ShreyasiPanthee](https://www.youtube.com/watch?v=ECyaEkZ8D64) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 480 | [Crispy Poori Taco with Chickpea Filling \| Indian Street Food Twist](https://www.youtube.com/watch?v=LQESQtOodxM) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 481 | [High-Protein Chickpea Tofu Salad \| Low-Cal, High-Fiber & Weight-Loss Friendly #food #asmr #airfryer](https://www.youtube.com/watch?v=Nd-FE-OatlQ) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 482 | [Try This Quick Cauliflower Stir Fry Recipe 🥦✨ Don’t throw the leaves away! #vitamins #healthy](https://www.youtube.com/watch?v=FoqXpnt_7-I) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 483 | [From Farmers Market to Table \| Cheesy Squash Blossom Cups #zucchini #squash #creativecooking #asmr](https://www.youtube.com/watch?v=g4EK-1QE_KY) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 484 | [Chickpea Eggs Paradise \| Protein-Packed Curry Bowl \| Quick & Healthy Meal #eggcurry #chickpearecipe](https://www.youtube.com/watch?v=d8gorufia6k) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 485 | [High Protein Moong Beans Sandwich Perfect for Breakfast #highprotein #shorts #viral](https://www.youtube.com/watch?v=BOg7iabJDSU) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 486 | [Forget Boring Salads! Try This Colorful Whole Moong Power Bowl Ready in 5 Minutes #protein  #vegan](https://www.youtube.com/watch?v=rlvTiKUka5g) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 487 | [Protein-Packed Quinoa Tofu Veggie Bowl \| Healthy, Easy & Delicious! Healthy, Easy & Flavorful Meal!](https://www.youtube.com/watch?v=HiGY3UP7VK0) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 488 | [You’ll Stop Eating Other Veggies After This Avocado Peanut Salad! #vegan #quickrecipes #avacado](https://www.youtube.com/watch?v=HH5OO8tqHOo) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 489 | [Daughter’s Sweet Review of Mom’s Birthday Feast for Dad](https://www.youtube.com/watch?v=TNQwqqmiPqM) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 490 | [Air Fryer Egg Noodle Pizza in 10 Minutes \| Ramen Pizza  \| Cheesy Maggi #easyrecipe #easymeals #maggi](https://www.youtube.com/watch?v=fnrMbWVeoYk) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 491 | [Crispy Paneer Kofta in Air Fryer - No Cream, No Frying #vegetarian #paneer #kofta #airfryerrecipes](https://www.youtube.com/watch?v=N_9WBrPfyF8) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 492 | [My Daughter Tasting My Belgian Waffle Recipe 🧇 #waffle #belgianwaffle @ShreyaPanthee #mukbang](https://www.youtube.com/watch?v=pnudQ6oeOKc) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 493 | [एयर फ्रायरमा १० मिनेटमा स्वादिलो भिन्डी \|न त चिप्लो, न त चिल्लो #nodeepfry #vegetarianrecipe #recipe](https://www.youtube.com/watch?v=TCqDjZTfV6Y) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 494 | [Nothing feels better on a chilly day than golden grilled cheese dipped in creamy tomato soup](https://www.youtube.com/watch?v=KsVTmKyYMs8) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 495 | [Beginner Cooking That Actually Tastes Good](https://www.youtube.com/watch?v=we3e4q6yync) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 496 | [Homemade Veg Momo with Tofu-Paneer \| Dumplings with Spicy Chutney #momos #asmr #mukbang #dumplings](https://www.youtube.com/watch?v=ZBUCUX_F2r4) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 497 | [My Secret Tomato Pickle Recipe for Rice & Roti \| You'll Eat It All! #vegan #pickle #tomatopickle](https://www.youtube.com/watch?v=_KOnvQ_ioW0) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 498 | [Tofu Lentils Rice With Spinach \| Healthy Protein-Packed Rice Recipe \| Easy One-Pot Dinner](https://www.youtube.com/watch?v=45GVVBIcOAM) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 499 | [करेला मन नपर्नेले पनि मन पराउँछन् – एकचोटि यसरी बनाएर हेर्नुहोस्! #KarelaAlooSabji#BitterMelonRecipe](https://www.youtube.com/watch?v=VEkZkA2hDh0) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 500 | [Mushroom, Eggs, and Cornmeal Waffles \| Easy Stuffed Waffle #lowcarb #diabeticfriendly #glutenfree](https://www.youtube.com/watch?v=gavLK9iGFe8) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 501 | [Easy Eggless Whole Wheat Chocolate Chunk Cookies (Batch of 15) #wholewheat #egglessbaking](https://www.youtube.com/watch?v=TI2E4mz7efw) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 503 | [High-Protein, Zero-Waste Flatbread in Just 2 Ingredients!](https://www.youtube.com/watch?v=S1IVT7hgaSU) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 504 | [No Flour! No Egg! Oats Zucchini Flatbread You’ll Make Every Week](https://www.youtube.com/watch?v=4hS6RBYLSm8) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 505 | [Soft & Chewy Eggless Whole Wheat Chocolate Chunk Cookies \| Easy No Egg Cookie Recipe #cookies](https://www.youtube.com/watch?v=DCLcMY0bSBU) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 506 | [Better Than Toast \| A Balanced Breakfast with Protein, Fiber & Carbs \| Gluten-Free Broccoli Waffles](https://www.youtube.com/watch?v=bPF-UEjRlGk) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 507 | [The Rustic Zucchini Flatbread my mom made to get us to eat our veggies](https://www.youtube.com/watch?v=N4xEAXeAf4g) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 508 | [I Stopped Buying Pickled Jalapeños… and Made Them Better at Home](https://www.youtube.com/watch?v=T-CmNl9Z-RE) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 509 | [10-Min Spicy C-Momo \| Best Way to Eat Frozen Momos!](https://www.youtube.com/watch?v=GB4B77y1gT4) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 510 | [The Secret to the Perfect Momo Fold 🥟 #veganrecipes #comfortfood #momos](https://www.youtube.com/watch?v=L2W_wKQf4xI) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 511 | [The Perfect Whole Wheat Breakfast Loaf](https://www.youtube.com/watch?v=lXQGWxv9QhE) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 512 | [50 High-Protein Nepali Momos \| Tofu & Paneer with Spicy Schezwan Chutney & Flash Freeze Hack](https://www.youtube.com/watch?v=B6hlBEscLFQ) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 513 | [Pickled Red Onions  & Jalapenos at Home , Not Buying Them Anymore](https://www.youtube.com/watch?v=RRH9Af3PJBM) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 514 | [10-Minute Egg Fried Rice (No Egg Smell!) \| My Family Didn't Know It has Egg on it!](https://www.youtube.com/watch?v=6A0jJ60Btg8) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 515 | [10-Minute Cheesy Egg Pizza \| Quick Whole Wheat Bread Omelet Hack](https://www.youtube.com/watch?v=wKllQaeqkRw) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 516 | [3-Ingredient Easy Shortbread Cookies \| Perfect Every Time](https://www.youtube.com/watch?v=259UXrp-vl8) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 517 | [Classic Shortbread Bites](https://www.youtube.com/watch?v=K8uz9RYK6gI) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 518 | [2-Ingredient Wrap Recipe \| Crispy Peanut-Mint Wraps](https://www.youtube.com/watch?v=fnocgRIkE7c) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 519 | [High-Protein Roasted Chickpea & Green Salad](https://www.youtube.com/watch?v=byQIsbsyn7E) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 520 | [Simple Cauliflower Curry \| Mild Tomato Cauliflower Curry \| Easy Homemade Vegetable Curry #curry](https://www.youtube.com/watch?v=0U2Uwy_ur3o) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 521 | [Try It Once, You’ll Make It Every Week \| 2 Ingredient High-Protein Mung Beans Flatbread #vegan](https://www.youtube.com/watch?v=GRzirPubnDg) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 522 | [Low Sugar Eggless Flower Cookies\| Easy Two-Tone Floral Shortbread](https://www.youtube.com/watch?v=KyyZlHozsF0) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 523 | [High Protein Low Carb Cheesy Egg Roll \| Japanese-Style Omelette #lowcarb](https://www.youtube.com/watch?v=UCXRrOx8Yqo) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 524 | [3-Ingredient Flourless Shortbread Cookies \|3g Protein each](https://www.youtube.com/watch?v=ZUgBCjDItr8) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 525 | [Cheesy Egg Roll \| Japanese Style Omelette Recipe](https://www.youtube.com/watch?v=-aqmlVCYwUQ) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 526 | [High-Protein Almond Cookies in 30 Minutes (Just 3 Ingredients)](https://www.youtube.com/watch?v=0bqX4oD_NYw) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 527 | [High-Protein Yellow Pea & Spinach Stew (Nutritious & Budget-Friendly)](https://www.youtube.com/watch?v=Y_szGlHE8ZQ) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 528 | [10-Minute Crispy Garlic Brussels Sprouts with Sesame and Peanuts Crunch](https://www.youtube.com/watch?v=qKRKl43E_Eo) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 529 | [She Took the First Bite… and This Happened 🧡 #waffle #belgianwaffle @ShreyaPanthee #mukbang](https://www.youtube.com/watch?v=yxSf0013ycs) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 530 | [Crispy Air Fryer Okra \| 10-Minute Masala Magic \| Healthy & Oil-Free Snack #bhindi #veganfood](https://www.youtube.com/watch?v=ULKl_fOknQ0) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 531 | [No Flour Flatbread \| Potato Chickpea Healthy Bread \| Gluten-Free Veggie Pancake](https://www.youtube.com/watch?v=MfPJlSjG1gw) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 532 | [Cheesy Spinach Omelette (Tamagoyaki ) \| High Protein 5-Min Breakfast](https://www.youtube.com/watch?v=obcYt-0Cobo) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 533 | [Orange Cranberry Shortbread Cookies – Bite-Sized, Eggless](https://www.youtube.com/watch?v=4gvFDT_MDww) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 534 | [Cook Your Eggs This Way & Never Go Back! \| Spinach Cheese Tamagoyaki](https://www.youtube.com/watch?v=Mtof9044gRk) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 535 | [Eggless Orange Cranberry Shortbread Cookies \| Tiny, Buttery & Crisp](https://www.youtube.com/watch?v=YDRUlOnVz_A) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 536 | [What’s inside this omelet?](https://www.youtube.com/watch?v=5NWXlzbb3UQ) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 537 | [Strawberry Chia Pudding \| Quick & Healthy Breakfast, No Refined Sugar, 5-Min Prep](https://www.youtube.com/watch?v=6Sdw4uwE5CU) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 538 | [If you love Oranges and Cranberries, make this!  Eggless Better than Bakery Shortbread Bites](https://www.youtube.com/watch?v=UiUF7HRA1AU) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 539 | [If You Have Almond Flour, Make These Easy Gluten-Free Crackers!](https://www.youtube.com/watch?v=36MGKSxayyQ) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 540 | [If You Have Almond Flour, Make These Easy Gluten-Free Crackers!](https://www.youtube.com/watch?v=B2fu-q340Ps) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 541 | [Stop Buying Store-Bought Cookies! Make These Healthy Protein-Packed Cookies Instead](https://www.youtube.com/watch?v=Iv_M26MHiIc) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 542 | [Cook your eggs with beans and you’ll NEVER go back! 🍳 The Japanese Tamagoyaki secret](https://www.youtube.com/watch?v=MZIb6HO0hJs) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 543 | [Soft Whole Wheat Bread with Flax \| Less Yeast & Slow Rise](https://www.youtube.com/watch?v=dBNpddiQa9E) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 544 | [Cook your eggs with beans and you’ll NEVER go back! The Japanese Tamagoyaki secret](https://www.youtube.com/watch?v=zYvy2DlCDq0) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 545 | [Tea Strainer Cleaning Hack 😍 No Scrubbing Needed!](https://www.youtube.com/watch?v=TUa4OcAepBs) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 546 | [If You Have Strawberries 🍓 Make This Strawberry Milk Instead of Buying It from Store](https://www.youtube.com/watch?v=irKW6rysqRQ) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 547 | [If You Have Strawberries 🍓 Make This Strawberry Milk Instead of Buying It from Store](https://www.youtube.com/watch?v=jEdkkpvnDjs) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 548 | [Stop making boring eggs! Try this Asparagus Roll](https://www.youtube.com/watch?v=R8299SCs-nw) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 549 | [Flourless Crackers? I Didn’t Expect Them to Turn Out This Good](https://www.youtube.com/watch?v=Ov1VVSM8O8g) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 550 | [I Never Thought Potatoes & Eggs Could Taste This Good in One Pan!](https://www.youtube.com/watch?v=9xiLtBnPlKw) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 551 | [I Never Thought Potatoes & Eggs Could Taste This Good in One Pan!](https://www.youtube.com/watch?v=flCK5zYZymo) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 552 | [Looks Like Cake.. But It’s My Favorite Flourless High-Protein SAVORY Lunch! #noflourcake #savorycake](https://www.youtube.com/watch?v=gh77CX4xy6g) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 553 | [Stop Frying Potatoes! Just Add Eggs and veggies... The Result Will Surprise You!](https://www.youtube.com/watch?v=Vy6eOHx1qyg) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 554 | [Stop Making Boring Lentils! Try This 30g Protein Savory Bake 🍄#noflourcake #savorycake #noeggs](https://www.youtube.com/watch?v=MAvEWO6Nt50) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 555 | [Chickpea Avocado Almond Crackers & Creamy Cilantro Dip #crackers #glutenfree](https://www.youtube.com/watch?v=kuUkJ-Qc5w4) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 556 | [If You Don't Eat Flour, Make This High Protein Lentil & Tofu Bake (30g Protein lunch)](https://www.youtube.com/watch?v=sbh1XN7p0Zk) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 557 | [If You Have Overripe Bananas, Don’t Throw Them Out! Make This Eggless Banana Muffins #egglessbaking](https://www.youtube.com/watch?v=783BGZRsdNw) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 558 | [Healthy Flourless Peanut Butter Cookies 🍪 (Soft & Chewy!)](https://www.youtube.com/watch?v=mZXtBaZe1X8) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 559 | [Stop Chopping Mushrooms! This 10-Minute Garlic Curry Is a Weeknight Game-Changer](https://www.youtube.com/watch?v=puff0CqO_MI) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 560 | [Stop Eating Flour ! Make This 30g Protein "Bloat-Free" Bake Instead](https://www.youtube.com/watch?v=0JCNCHWAjqs) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 562 | [Roasted Cauliflower Steaks with Creamy Tofu-Tomato Gravy](https://www.youtube.com/watch?v=AaXmcxGpMNk) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 563 | [High Protein Salad: Warm Roasted Chickpea Salad with Tangy Mint Peanut Dressing \|  Healthy Recipe](https://www.youtube.com/watch?v=O_FfpOwn1uU) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 564 | [Forget Bread And Try This 30g Lentil Quinoa Protein Bake 🍞](https://www.youtube.com/watch?v=x_FHdfPUZv4) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 565 | [High-Protein Flourless Crackers \| No Flour, No Eggs, Just Crunch!](https://www.youtube.com/watch?v=5mYtqbKtpsU) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 566 | [High Protein Flourless Cracker Series: Sweet Potato & Lentil Power Snap](https://www.youtube.com/watch?v=p25u3M__bLk) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 567 | [22g Protein Cauliflower Steak \| Silky No-Dairy Cream](https://www.youtube.com/watch?v=MV4ZdGZ5Hio) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 568 | [Crispy Smashed Plantains \| High-Protein Zesty Dip \| Flourless Air Fried Quick Fix](https://www.youtube.com/watch?v=h1OIOhbKTro) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 569 | [High Protein Cracker Series: Mung Beans & Chickpea Flour](https://www.youtube.com/watch?v=LAdogN9ojqo) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 570 | [High-Protein Flourless Power Crackers (Just Seeds & Water)](https://www.youtube.com/watch?v=SvHEbgRGZFE) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 571 | [3-Ingredient Easiest Flourless Protein Bites #veganrecipes #eggfreebaking](https://www.youtube.com/watch?v=j38mbp_KDh0) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 572 | [Better than ice cream? High Protein Mango Orange Banana Bowl](https://www.youtube.com/watch?v=oy0QNoAApjs) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 573 | [High Protein Flourless Vegan Fritters (No Eggs, No Gluten) \| Meal Prep Friendly](https://www.youtube.com/watch?v=QBgLqMFonOw) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 574 | [High Protein Flourless Vegan Cabbage Flatbread (No Eggs, No Gluten) \| Meal Prep Friendly](https://www.youtube.com/watch?v=WKYrgoq5cU8) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 575 | [Better than ice cream? 🍦 High Protein Mango Orange Banana Ice Cream](https://www.youtube.com/watch?v=SP75KlS0gxA) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 576 | [HIGH PROTEIN CRACKERS : I NEVER THOUGHT HOMEMADE CRACKERS COULD TASTE THIS GOOD](https://www.youtube.com/watch?v=E84hN45yHEs) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 577 | [High Protein Powerhouse Crackers \| Flourless, No Oil, No Egg, Protein-Rich Snack](https://www.youtube.com/watch?v=qgOVDU9ninQ) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 578 | [High Protein Flourless Quinoa & Kidney Bean Fiber Balls (Vegan Meal Prep Recipe)](https://www.youtube.com/watch?v=9caoAOWv18I) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 579 | [High Protein Flourless Quinoa & Bean Patties](https://www.youtube.com/watch?v=GHvbQbypyis) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 580 | [High Protein Flourless Red Lentil & Oats Pancakes \| 24g Protein High Fiber Meal](https://www.youtube.com/watch?v=F8xjJRXhqvU) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 581 | [High Protein Sweet Potato & Mung Bean Crackers](https://www.youtube.com/watch?v=lBos-IskhRE) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 582 | [High Protein Flourless Bake \| Lentil Quinoa Tofu \| Gluten Free & Bloat Free Recipe](https://www.youtube.com/watch?v=56-X4AwvhxE) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 583 | [High Protein: I never thought Homemade sweet potato & mung bean crackers would turn out THIS good](https://www.youtube.com/watch?v=_nKeFG8RfE0) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 584 | [The Eggless Roll With More Protein Than 5 Eggs \| High Protein Vegan Meal](https://www.youtube.com/watch?v=UiXG8_auM-Y) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 585 | [High Protein Flourless Crackers Made With Red Lentils and Tomato](https://www.youtube.com/watch?v=hYviM1afIAI) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 586 | [High Protein Plant Based Flourless Crackers Made With Red Lentils and Tomato](https://www.youtube.com/watch?v=Kv7kwpox5wQ) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 587 | [High Protein High Fiber Vegan Crackers \| 10g Protein Snack (No Flour!)](https://www.youtube.com/watch?v=aaytwNlQvws) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 588 | [High Protein Vegan Tamagoyaki Rolls \| Mung Bean Tofu Rolls with Creamy Tomato Basil Gravy](https://www.youtube.com/watch?v=9EDCaMlq4QU) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 589 | [High Protein Vegan Nuggets \| Crispy Red Lentil & Soy Chunk Nuggets (Air Fryer Recipe)](https://www.youtube.com/watch?v=_D4PqF-j0sw) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 590 | [Stop Buying Protein Crackers! Make These High Protein Lentils And Soy Crackers Instead](https://www.youtube.com/watch?v=hLOpM4L68KQ) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 591 | [High Protein Mung Bean Crackers \| Crispy, Flourless & Surprisingly Easy!](https://www.youtube.com/watch?v=YS7stL6HOHw) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 592 | [Crispy High-Protein Red Lentil Bites (Air Fryer)](https://www.youtube.com/watch?v=4bM3PAcey7U) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 593 | [High Protein Vegan Karahi Tofu Rice Bowl](https://www.youtube.com/watch?v=_gPMa1rY1bg) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 594 | [High Protein Flourless Red Lentil Crackers \| No Flour, No Eggs, Crispy & Healthy Snack](https://www.youtube.com/watch?v=CVKmpeleZ3U) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 595 | [High Protein Flourless Red Lentil Crackers \| No Eggs, No Flour, Crispy & Healthy Snack](https://www.youtube.com/watch?v=XfWiCMHeLf4) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 596 | [A High-Protein, High-Fiber Day in My Kitchen \| Healthy Recipes from Breakfast to Dinner](https://www.youtube.com/watch?v=-87oEHNYlR8) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 597 | [Rich & Chewy Coconut Chocolate Bites (Fiber-Rich, No Added Sugar)](https://www.youtube.com/watch?v=sm4vCB-1nrk) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 598 | [15-Minute Spicy Cauliflower](https://www.youtube.com/watch?v=jAKqz-SNA5E) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 599 | [High-Protein Cheesy Almond Flour Crackers \| Flourless & Keto-Friendly](https://www.youtube.com/watch?v=OjVmyXk-jaQ) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 600 | [High Protein Powerhouse Crackers \| No Flour, No Egg, Protein-Rich Snack](https://www.youtube.com/watch?v=M1zbIyTP3CM) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 601 | [HIGH PROTEIN CRACKERS : NO FLOUR, NO EGGS, AND SO CRISPY I COULDN'T STOP EATING THEM](https://www.youtube.com/watch?v=bCZhYwVYRlA) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 602 | [High Protein Tofu & Spinach Pockets \| Easy 20-Minute Vegetarian Wraps](https://www.youtube.com/watch?v=WVBQSE9bCvQ) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 603 | [The Only Momo Recipe You'll Ever Need (Healthy & Delicious)](https://www.youtube.com/watch?v=BVT-v86aIuQ) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 604 | [3-Ingredient Creamy Homemade Cashew Milk](https://www.youtube.com/watch?v=oSebsyLhey4) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 605 | [High-Protein Smashed Tofu & Watermelon Salad](https://www.youtube.com/watch?v=Dj4nY7nPYRU) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 606 | [High-Protein Chickpea Crackers \| 100% Whole Food Plant-Based Snack](https://www.youtube.com/watch?v=lwUh87XfL64) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 607 | [High Protein Kidney Beans & Soy Chunks Curry \| 20g Protein Per Serving \| Vegan](https://www.youtube.com/watch?v=UMabw_d72Zw) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 608 | [High Protein Tofu Wrap \| Spicy Sesame Tofu Wrap with 23g Protein](https://www.youtube.com/watch?v=LmBgO8wwJJc) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 609 | [Quick Green Beans Stir Fry that tastes better than restaurant!](https://www.youtube.com/watch?v=395ZXG2HO54) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 610 | [High-Protein Frozen Tofu Sandwich \| Easy Spicy Tofu Recipe](https://www.youtube.com/watch?v=xa5IVMXyAYA) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 611 | [High Protein Lentil & Tofu Bake (30g Protein lunch), If You Don't Eat Flour This is For You](https://www.youtube.com/watch?v=SAqS3HvUriU) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 612 | [3-Ingredients Kidney-Friendly Applesauce Oat Cookies \| Flourless, Oil-Free, Egg-Free](https://www.youtube.com/watch?v=2JsJEpu8C80) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 613 | [HIGH PROTEIN CRACKERS : 7 Flourless Recipes So Crispy You'll Stop Buying Store-Bought](https://www.youtube.com/watch?v=XaczaVmF9VE) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 614 | [High Protein Flourless Cracker : Sweet Potato & Red Lentil Power Snap](https://www.youtube.com/watch?v=4Y6qvs7D8lc) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 615 | [High-Protein Crispy Smoky Tofu Pockets with Avocado Dip](https://www.youtube.com/watch?v=JGBlalIru_Q) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 616 | [High Protein Powerhouse Crackers \| Flourless, Eggless, Oil Free, Protein-Rich Snack](https://www.youtube.com/watch?v=ZWcIU1CJnmI) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 617 | [High Protein Powerhouse Crackers \| No Flour, No Egg, Oil Free, Protein-Rich Snack](https://www.youtube.com/watch?v=qMZNSrODw0w) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 618 | [High Protein Flourless Vegan Lentil & Tofu Bites \| Crispy & Easy](https://www.youtube.com/watch?v=8PCURtlyvjM) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 619 | [High Protein Flourless Red Lentil Crackers \| Crispy, Vegan & Easy](https://www.youtube.com/watch?v=RswtA4CV0Ps) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 620 | [High Protein Flourless Red Lentil Crackers \| Crispy, Vegan & Easy](https://www.youtube.com/watch?v=WKrKQjVrHxQ) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 621 | [High-Protein Chickpea Crackers \| 2 Ways: Soaked vs Cooked Chickpeas](https://www.youtube.com/watch?v=gxZGSJ2tfOQ) | 1 | 404 | Inconclusive automated access; browser verification needed |
| 622 | [High-Protein Avocado Crackers You’ll Love \| Crispy Oil Free & Plant-Based](https://www.youtube.com/watch?v=KROjysZf60I) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 623 | [High-Protein Red Lentil Sweet Potato Crackers](https://www.youtube.com/watch?v=UeCVqybGEyY) | 0 | 404 | Inconclusive automated access; browser verification needed |
| 624 | [High-Protein Red Lentil Crackers \| Sweet Potato, Seeds & Spices](https://www.youtube.com/watch?v=FRSJSo1wS5Y) | 1 | 404 | Inconclusive automated access; browser verification needed |

### Appendix G — sitemap and auxiliary endpoint checks

| Endpoint | HTTP | Notes |
| --- | --- | --- |
| [/.private/config.json](http://quickfixwithbindu-local.local/.private/config.json) | 404 | HEAD only; no private contents retained |
| [/wp-json/](http://quickfixwithbindu-local.local/wp-json/) | 200 | application/json; charset=UTF-8 |
| [/wp-json/wp/v2/recipes?per_page=1](http://quickfixwithbindu-local.local/wp-json/wp/v2/recipes?per_page=1) | 200 | application/json; charset=UTF-8 |
| [/wp-login.php](http://quickfixwithbindu-local.local/wp-login.php) | 200 | text/html; charset=UTF-8 |
| [/sitemap.xml](http://quickfixwithbindu-local.local/sitemap.xml) | 200 | text/xml; charset=UTF-8 |
| [/sitemap.rss](http://quickfixwithbindu-local.local/sitemap.rss) | 200 | text/xml; charset=UTF-8 |
| [/wp-sitemap.xml](http://quickfixwithbindu-local.local/wp-sitemap.xml) | 200 | text/xml; charset=UTF-8 |

**/page-sitemap.xml**: 200; 9 location entries.

**/recipes-sitemap.xml**: 200; 462 location entries.

Recipe sitemap contains 10 of the orphan recipe URLs.

**/post-archive-sitemap.xml**: 200; 1 location entries.

