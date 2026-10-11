<?php
namespace QuickFix;
defined('ABSPATH') || exit;

final class Catalog {
    public static function source_table(string $suffix): string { global $wpdb; return $wpdb->prefix . $suffix; }
    public static function joins(): string {
        global $wpdb;
        return self::source_table('recipes') . " r INNER JOIN {$wpdb->posts} p ON p.ID=r.wp_post_id LEFT JOIN " . Store::table('mobile_recipes') . ' m ON m.recipe_id=r.id LEFT JOIN ' . Store::table('rating_stats') . ' s ON s.recipe_id=r.id';
    }
    public static function website_predicate(): string {
        $type = get_post_type_object('recipes');
        if (!$type || !is_post_type_viewable($type)) { return '1=0'; }
        return "p.post_status='publish' AND p.post_type='recipes' AND p.post_password=''";
    }
    public static function predicate(): string {
        global $wpdb;
        $now = esc_sql(Store::now());
        $ingredients = self::source_table('recipe_ingredients'); $steps = self::source_table('recipe_steps');
        return self::website_predicate() . " AND m.orphaned=0 AND (m.state='published' OR (m.state='scheduled' AND m.available_from IS NOT NULL)) AND (m.available_from IS NULL OR m.available_from<='$now') AND (m.available_to IS NULL OR m.available_to>'$now') AND TRIM(r.title)<>'' AND EXISTS (SELECT 1 FROM $ingredients i WHERE i.recipe_id=r.id AND TRIM(i.ingredient)<>'') AND EXISTS (SELECT 1 FROM $steps st WHERE st.recipe_id=r.id AND TRIM(st.instruction)<>'') AND (m.image_override<>'' OR r.featured_image_url<>'' OR EXISTS (SELECT 1 FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} attachment ON attachment.ID=CAST(pm.meta_value AS UNSIGNED) WHERE pm.post_id=p.ID AND pm.meta_key='_thumbnail_id' AND attachment.post_type='attachment'))";
    }
    public static function select(): string {
        return 'r.*,p.post_status,p.post_title,p.post_modified_gmt,m.state,m.available_from,m.available_to,m.published_at,m.summary_override,m.image_override,m.dietary_tags,m.comments_enabled,m.ratings_enabled,m.revision,m.orphaned,s.total rating_sum,s.count rating_count';
    }
    public static function get(string $id, string $channel = 'app'): array {
        global $wpdb; Input::id($id);
        $predicate = $channel === 'website' ? self::website_predicate() : self::predicate();
        $row = $wpdb->get_row($wpdb->prepare('SELECT ' . self::select() . ' FROM ' . self::joins() . " WHERE r.id=%s AND $predicate", $id), ARRAY_A);
        if (!$row) { throw new Fault('recipe_unavailable', 'Recipe unavailable.', 404); }
        // Suitability is checked after serialization as well as the coarse SQL image guard.
        if ($channel === 'app' && !self::image($row)) { throw new Fault('recipe_unavailable', 'Recipe unavailable.', 404); }
        return $row;
    }
    public static function image(array $row): string {
        $url = $row['image_override'] ?: (wp_get_attachment_image_url(get_post_thumbnail_id((int) $row['wp_post_id']), 'medium_large') ?: ($row['featured_image_url'] ?? ''));
        try { return Input::url((string) $url, true); } catch (Fault $e) { return ''; }
    }
    public static function metadata(string $id, bool $lock = false): array {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . Store::table('mobile_recipes') . ' WHERE recipe_id=%s' . ($lock ? ' FOR UPDATE' : ''), $id), ARRAY_A);
        return $row ?: ['recipe_id' => $id, 'state' => 'excluded', 'available_from' => null, 'available_to' => null, 'published_at' => null, 'summary_override' => '', 'image_override' => '', 'dietary_tags' => '[]', 'comments_enabled' => 1, 'ratings_enabled' => 1, 'revision' => 0, 'orphaned' => 0];
    }
    public static function categories_for(string $id): array {
        global $wpdb;
        return array_map(static fn($r) => ['id' => (string) $r['id'], 'name' => Input::plain($r['name']), 'slug' => $r['slug']], $wpdb->get_results($wpdb->prepare('SELECT c.id,c.name,c.slug FROM ' . Store::table('app_categories') . ' c INNER JOIN ' . Store::table('recipe_categories') . ' rc ON rc.category_id=c.id WHERE rc.recipe_id=%s AND c.enabled=1 ORDER BY c.sort_order,c.id', $id), ARRAY_A));
    }
    public static function card(array $row): array {
        $count = (int) ($row['rating_count'] ?? 0); $image = self::image($row);
        return ['id' => (string) $row['id'], 'title' => Input::plain($row['title']), 'summary' => mb_substr(Input::plain($row['summary_override'] ?: $row['description']), 0, 500), 'thumbnail' => $image, 'thumbnail_variants' => $image ? [['url' => $image, 'purpose' => 'card']] : [], 'canonical_url' => get_permalink((int) $row['wp_post_id']), 'categories' => self::categories_for((string) $row['id']), 'tags' => json_decode($row['dietary_tags'] ?: '[]', true), 'total_minutes' => (int) $row['total_time_minutes'] > 0 ? (int) $row['total_time_minutes'] : null, 'rating' => ['average' => $count ? round((int) $row['rating_sum'] / $count, 1) : null, 'count' => $count], 'content_version' => hash('sha256', implode(':', [$row['updated_at'], $row['post_modified_gmt'], $row['revision'] ?? 0])), 'availability_verified_at' => gmdate('c'), 'cache_expires_at' => gmdate('c', time() + 60)];
    }
    public static function detail(string $id, string $channel = 'app'): array {
        global $wpdb;
        $row = self::get($id, $channel); $detail = self::card($row);
        $ingredients = $wpdb->get_results($wpdb->prepare('SELECT id,ingredient,quantity,notes FROM ' . self::source_table('recipe_ingredients') . " WHERE recipe_id=%s AND TRIM(ingredient)<>'' ORDER BY id", $id), ARRAY_A);
        $steps = $wpdb->get_results($wpdb->prepare('SELECT id,step_number,section,instruction,timer_minutes FROM ' . self::source_table('recipe_steps') . " WHERE recipe_id=%s AND TRIM(instruction)<>'' ORDER BY step_number,id", $id), ARRAY_A);
        $detail['ingredients'] = array_map(static fn($i) => ['id' => (string) $i['id'], 'name' => Input::plain($i['ingredient']), 'quantity' => Input::plain($i['quantity']), 'notes' => Input::plain($i['notes'])], $ingredients);
        $images = $wpdb->get_results($wpdb->prepare('SELECT si.step_id,si.image_url FROM ' . self::source_table('recipe_step_images') . ' si INNER JOIN ' . self::source_table('recipe_steps') . ' st ON st.id=si.step_id WHERE st.recipe_id=%s ORDER BY si.id', $id), ARRAY_A);
        $map = []; foreach ($images as $image) { try { $url = Input::url($image['image_url'], true); if ($url) { $map[$image['step_id']][] = $url; } } catch (Fault $e) {} }
        $detail['steps'] = array_map(static fn($s) => ['id' => (string) $s['id'], 'position' => (int) $s['step_number'], 'section' => Input::plain($s['section']), 'instruction' => Input::plain($s['instruction']), 'timer_minutes' => (int) $s['timer_minutes'] > 0 ? (int) $s['timer_minutes'] : null, 'images' => $map[$s['id']] ?? []], $steps);
        $detail['servings'] = $row['servings'] ? Input::plain($row['servings']) : null;
        $detail['prep_minutes'] = (int) $row['prep_time_minutes'] > 0 ? (int) $row['prep_time_minutes'] : null;
        $detail['cook_minutes'] = (int) $row['cook_time_minutes'] > 0 ? (int) $row['cook_time_minutes'] : null;
        $detail['notes'] = Input::plain($row['notes']); $detail['nutrition'] = null;
        $nutrition = []; foreach (['calories', 'protein_g', 'carbs_g', 'fat_g', 'fiber_g', 'sugar_g'] as $field) { if ((float) $row[$field] > 0) { $nutrition[$field] = (float) $row[$field]; } }
        if ($nutrition) { $detail['nutrition'] = ['values' => $nutrition, 'provenance' => 'Legacy website data; not independently verified']; }
        $video = $wpdb->get_row($wpdb->prepare('SELECT youtube_id,visible,upload_date FROM ' . self::source_table('cooking_videos') . ' WHERE id=%s', $row['videoid']), ARRAY_A);
        $detail['video'] = $video && (int) $video['visible'] && preg_match('/^[A-Za-z0-9_-]{11}$/D', $video['youtube_id']) ? ['provider' => 'youtube', 'id' => $video['youtube_id'], 'url' => 'https://www.youtube.com/watch?v=' . $video['youtube_id'], 'uploaded_at' => $video['upload_date']] : null;
        $settings = Settings::get(); $detail['capabilities'] = ['comments' => $settings['comments_enabled'] && (bool) ($row['comments_enabled'] ?? 1), 'ratings' => $settings['ratings_enabled'] && (bool) ($row['ratings_enabled'] ?? 1)];
        $detail['rating'] = Community::stats($id); return $detail;
    }
    public static function boundary(): string {
        global $wpdb; $table = Store::table('mobile_recipes');
        return (string) $wpdb->get_var($wpdb->prepare("SELECT MAX(boundary) FROM (SELECT available_from boundary FROM $table WHERE available_from<=%s UNION ALL SELECT available_to FROM $table WHERE available_to<=%s) boundaries", Store::now(), Store::now()));
    }
    public static function listing(array $params): array {
        global $wpdb;
        Input::only($params, ['q','category','tags','max_total_minutes','sort','cursor','limit']);
        $q = isset($params['q']) && $params['q'] !== '' ? Input::text($params['q'], 2, 100) : '';
        $sort = Input::choice($params['sort'] ?? 'latest', ['latest', 'cooking_time', 'highest_rated']);
        $limit = Input::integer($params['limit'] ?? 20, 1, 50); $where = self::predicate();
        if ($q) { $like = '%' . $wpdb->esc_like(mb_strtolower($q)) . '%'; $where .= $wpdb->prepare(' AND (LOWER(r.title COLLATE utf8mb4_unicode_ci) LIKE %s OR LOWER(COALESCE(NULLIF(m.summary_override,\'\') COLLATE utf8mb4_unicode_ci,r.description COLLATE utf8mb4_unicode_ci)) LIKE %s OR EXISTS (SELECT 1 FROM ' . self::source_table('recipe_ingredients') . ' i WHERE i.recipe_id=r.id AND LOWER(i.ingredient COLLATE utf8mb4_unicode_ci) LIKE %s))', $like, $like, $like); }
        if (!empty($params['category'])) { $category = Input::id($params['category']); $where .= $wpdb->prepare(' AND EXISTS (SELECT 1 FROM ' . Store::table('recipe_categories') . ' rc INNER JOIN ' . Store::table('app_categories') . ' c ON c.id=rc.category_id WHERE rc.recipe_id=r.id AND c.id=%s AND c.enabled=1)', $category); }
        if (isset($params['max_total_minutes'])) { $max = Input::integer($params['max_total_minutes'], 1, 10080); $where .= $wpdb->prepare(' AND r.total_time_minutes>0 AND r.total_time_minutes<=%d', $max); }
        if (!empty($params['tags'])) {
            $tags = is_array($params['tags']) ? $params['tags'] : explode(',', $params['tags']); if (count($tags) > 10) { throw new Fault('invalid_tags', 'Choose up to ten dietary tags.'); }
            $clauses = []; foreach ($tags as $tag) { $tag = Input::text($tag, 1, 50); $clauses[] = $wpdb->prepare('m.dietary_tags LIKE %s', '%' . $wpdb->esc_like(wp_json_encode($tag)) . '%'); }
            $where .= ' AND (' . implode(' OR ', $clauses) . ')';
        }
        $binding = $params; unset($binding['cursor']); $binding['boundary'] = self::boundary();
        [$offset, $cursor] = Store::cursor($binding, $params['cursor'] ?? null);
        $cache_key = 'qf_list_' . hash('sha256', wp_json_encode([$binding, $offset, get_option('quickfix_catalog_revision', '1')]));
        $cached = get_transient($cache_key); if (is_array($cached)) { return $cached; }
        $orders = ['latest' => 'COALESCE(m.published_at,m.available_from) DESC,r.id DESC', 'cooking_time' => '(r.total_time_minutes<=0),r.total_time_minutes,r.id', 'highest_rated' => 'COALESCE(s.total/NULLIF(s.count,0),0) DESC,COALESCE(s.count,0) DESC,r.id DESC'];
        $rows = $wpdb->get_results($wpdb->prepare('SELECT ' . self::select() . ' FROM ' . self::joins() . " WHERE $where ORDER BY {$orders[$sort]} LIMIT %d OFFSET %d", $limit + 1, $offset), ARRAY_A);
        $more = count($rows) > $limit; $rows = array_slice($rows, 0, $limit);
        if ($wpdb->last_error) { throw new Fault('storage_unavailable', 'Catalog temporarily unavailable.', 503); }
        $result = ['items' => array_values(array_map([self::class, 'card'], array_filter($rows, static fn($r) => self::image($r) !== ''))), 'next_cursor' => $more ? $cursor($offset + $limit) : null];
        set_transient($cache_key, $result, 60); return $result;
    }
    public static function categories(): array {
        global $wpdb; $hide = Settings::get()['hide_empty_categories'];
        $where = 'c.enabled=1'; if ($hide) { $where .= ' AND EXISTS (SELECT 1 FROM ' . self::joins() . ' INNER JOIN ' . Store::table('recipe_categories') . ' rc ON rc.recipe_id=r.id WHERE rc.category_id=c.id AND ' . self::predicate() . ')'; }
        return array_map(static fn($r) => ['id' => (string) $r['id'], 'name' => Input::plain($r['name']), 'slug' => $r['slug'], 'description' => Input::plain($r['description']), 'image' => $r['image_ref'], 'sort_order' => (int) $r['sort_order']], $wpdb->get_results('SELECT c.* FROM ' . Store::table('app_categories') . " c WHERE $where ORDER BY c.sort_order,c.id", ARRAY_A));
    }
    public static function home(): array {
        global $wpdb; $sections = [];
        foreach (Settings::get()['home_sections'] as $section) {
            if ($section === 'categories') { $items = self::categories(); }
            elseif ($section === 'latest') { $items = self::listing(['limit' => 10])['items']; }
            else {
                $now = Store::now();
                $rows = $wpdb->get_results($wpdb->prepare('SELECT ' . self::select() . ' FROM ' . self::joins() . ' INNER JOIN ' . Store::table('home_placements') . " h ON h.recipe_id=r.id WHERE h.section='featured' AND " . self::predicate() . ' AND (h.starts_at IS NULL OR h.starts_at<=%s) AND (h.ends_at IS NULL OR h.ends_at>%s) ORDER BY h.position,r.id LIMIT 10', $now, $now), ARRAY_A);
                $items = array_values(array_map([self::class, 'card'], array_filter($rows, static fn($r) => self::image($r) !== '')));
            }
            $sections[] = ['type' => $section, 'items' => $items];
        }
        return ['sections' => $sections];
    }
    public static function inspect(string $id): array {
        global $wpdb; Input::id($id);
        $source = $wpdb->get_row($wpdb->prepare('SELECT r.*,p.post_status FROM ' . self::source_table('recipes') . " r LEFT JOIN {$wpdb->posts} p ON p.ID=r.wp_post_id WHERE r.id=%s", $id), ARRAY_A);
        $meta = self::metadata($id); $meta['dietary_tags'] = json_decode($meta['dietary_tags'], true);
        $meta['categories'] = array_map('strval', $wpdb->get_col($wpdb->prepare('SELECT category_id FROM ' . Store::table('recipe_categories') . ' WHERE recipe_id=%s', $id)));
        foreach (['available_from', 'available_to', 'published_at'] as $key) { if ($meta[$key]) { $meta[$key] = str_replace(' ', 'T', $meta[$key]) . 'Z'; } }
        $issues = []; $preview = null;
        try { $preview = self::detail($id, 'website'); if (!$preview['thumbnail']) { $issues[] = 'A suitable image is required.'; } if (!$preview['ingredients']) { $issues[] = 'Ingredients are required.'; } if (!$preview['steps']) { $issues[] = 'Instructions are required.'; } if (!$preview['title']) { $issues[] = 'Title is required.'; } } catch (Fault $e) { $issues[] = 'Source recipe must be publicly available.'; }
        $featured = (bool) $wpdb->get_var($wpdb->prepare('SELECT id FROM ' . Store::table('home_placements') . ' WHERE recipe_id=%s', $id));
        return ['id' => $id, 'title' => $source ? Input::plain($source['title']) : 'Orphaned recipe', 'source_status' => $source['post_status'] ?? 'missing', 'metadata' => $meta, 'featured' => $featured, 'validation' => $issues, 'preview' => $preview];
    }
    public static function save(string $id, array $data): array {
        global $wpdb; Input::id($id);
        Input::only($data, ['revision','state','available_from','available_to','summary_override','image_override','dietary_tags','comments_enabled','ratings_enabled','categories']);
        return Store::transaction(static function () use ($id, $data, $wpdb) {
            $table = Store::table('mobile_recipes');
            // Reserve the primary key first so two first-time edits also conflict correctly.
            Store::query($wpdb->prepare("INSERT INTO $table (recipe_id,summary_override,dietary_tags,revision,updated_at) VALUES (%s,'','[]',0,%s) ON DUPLICATE KEY UPDATE recipe_id=recipe_id", $id, Store::now()));
            $meta = self::metadata($id, true); Store::revision($meta, $data['revision'] ?? null);
            $before = $meta['state'];
            foreach ($data as $field => $value) {
                if ($field === 'revision' || $field === 'categories') { continue; }
                if ($field === 'state') { $meta[$field] = Input::choice($value, ['excluded','draft','scheduled','published','withdrawn']); }
                elseif (in_array($field, ['available_from','available_to'], true)) { $meta[$field] = Input::date($value); }
                elseif ($field === 'summary_override') { $meta[$field] = Input::text($value, 0, 2000); }
                elseif ($field === 'image_override') { $meta[$field] = Input::url($value, true); }
                elseif ($field === 'dietary_tags') { if (!is_array($value) || count($value) > 10) { throw new Fault('invalid_tags', 'Choose up to ten curated tags.'); } $meta[$field] = wp_json_encode(array_values(array_unique(array_map(static fn($tag) => Input::text($tag, 1, 50), $value)))); }
                else { $meta[$field] = Input::boolean($value); }
            }
            $transitions = ['excluded' => ['excluded','draft'], 'draft' => ['draft','excluded','published','scheduled'], 'scheduled' => ['scheduled','draft','published','withdrawn'], 'published' => ['published','withdrawn'], 'withdrawn' => ['withdrawn','draft','published','scheduled']];
            if (!in_array($meta['state'], $transitions[$before], true)) { throw new Fault('invalid_transition', 'Add an excluded recipe to Draft before publishing.'); }
            if ($meta['available_to'] && $meta['available_from'] && $meta['available_to'] <= $meta['available_from']) { throw new Fault('invalid_schedule', 'End time must follow start time.'); }
            if ($meta['state'] === 'scheduled' && (!$meta['available_from'] || $meta['available_from'] <= Store::now())) { throw new Fault('invalid_schedule', 'Scheduled recipes need a future start.'); }
            if (in_array($meta['state'], ['published','scheduled'], true)) {
                $preview = self::detail($id, 'website');
                if (!$preview['title'] || !($meta['image_override'] ?: $preview['thumbnail']) || !$preview['ingredients'] || !$preview['steps']) { throw new Fault('recipe_incomplete', 'Publication requires a public source, title, image, ingredients and instructions.'); }
                $meta['orphaned'] = 0;
                if ($before !== $meta['state'] || !$meta['published_at']) { $meta['published_at'] = $meta['state'] === 'scheduled' ? $meta['available_from'] : Store::now(); }
            }
            if (array_key_exists('categories', $data)) {
                if (!is_array($data['categories']) || count($data['categories']) > 20) { throw new Fault('invalid_categories', 'Choose up to twenty categories.'); }
                $ids = array_unique(array_map([Input::class, 'id'], $data['categories'])); foreach ($ids as $category) { Store::row('app_categories', $category); }
                Store::query($wpdb->prepare('DELETE FROM ' . Store::table('recipe_categories') . ' WHERE recipe_id=%s', $id));
                foreach ($ids as $category) { Store::insert('recipe_categories', ['recipe_id' => $id, 'category_id' => $category]); }
            }
            $meta['revision']++; $meta['updated_by'] = get_current_user_id(); $meta['updated_at'] = Store::now();
            Store::update('mobile_recipes', $meta, ['recipe_id' => $id]);
            Store::audit('recipe.updated', 'recipe:' . $id, ['before' => $before, 'after' => $meta['state'], 'revision' => $meta['revision']]); Store::bump();
            return self::inspect($id);
        });
    }
    public static function source_transition(string $new, string $old, \WP_Post $post): void { if ($post->post_type !== 'recipes' || $new === $old) { return; } if ($new !== 'publish') { self::source_deleted($post->ID); } else { Store::bump(); } }
    public static function source_deleted(int $post_id): void {
        global $wpdb;
        Store::query($wpdb->prepare('UPDATE ' . Store::table('mobile_recipes') . ' m INNER JOIN ' . self::source_table('recipes') . " r ON r.id=m.recipe_id SET m.orphaned=1,m.state=IF(m.state IN ('published','scheduled'),'withdrawn',m.state),m.revision=m.revision+1 WHERE r.wp_post_id=%d", $post_id)); Store::bump();
    }
}
