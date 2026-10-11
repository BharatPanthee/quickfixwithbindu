<?php
namespace QuickFix;
defined('ABSPATH') || exit;

final class Community {
    public static function lock_user(array $user, bool $verified = true): void {
        Store::row('users', (string) $user['id'], true); Identity::active($user, $verified);
    }
    public static function enabled(string $id, string $channel, string $kind): array {
        $recipe = Catalog::get($id, $channel); $settings = Settings::get();
        if (!$settings[$kind . '_enabled'] || !(bool) ($recipe[$kind . '_enabled'] ?? 1)) { throw new Fault('engagement_disabled', 'This recipe is read-only.', 403); } return $recipe;
    }
    public static function stats(string $id): array {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . Store::table('rating_stats') . ' WHERE recipe_id=%s', $id), ARRAY_A);
        $count = (int) ($row['count'] ?? 0); $distribution = []; for ($i = 1; $i <= 5; $i++) { $distribution[(string) $i] = (int) ($row['star' . $i] ?? 0); }
        return ['average' => $count ? round((int) $row['total'] / $count, 1) : null, 'count' => $count, 'distribution' => $distribution];
    }
    public static function lock_stats(string $id): void {
        global $wpdb; $table = Store::table('rating_stats');
        Store::query($wpdb->prepare("INSERT INTO $table (recipe_id,calculated_at) VALUES (%s,%s) ON DUPLICATE KEY UPDATE recipe_id=recipe_id", $id, Store::now()));
        $wpdb->get_var($wpdb->prepare("SELECT recipe_id FROM $table WHERE recipe_id=%s FOR UPDATE", $id));
    }
    public static function reconcile(string $id): void {
        global $wpdb; self::lock_stats($id);
        $row = $wpdb->get_row($wpdb->prepare('SELECT COALESCE(SUM(stars),0) total,COUNT(*) count,SUM(stars=1) star1,SUM(stars=2) star2,SUM(stars=3) star3,SUM(stars=4) star4,SUM(stars=5) star5 FROM ' . Store::table('ratings') . " WHERE recipe_id=%s AND status='active' FOR UPDATE", $id), ARRAY_A);
        foreach ($row as $key => $value) { $row[$key] = (int) $value; } $row['calculated_at'] = Store::now(); Store::update('rating_stats', $row, ['recipe_id' => $id]);
    }
    public static function comment_view(array $row): array {
        return ['id' => (string) $row['id'], 'recipe_id' => (string) $row['recipe_id'], 'parent_id' => $row['parent_comment_id'] ? (string) $row['parent_comment_id'] : null, 'author' => ['id' => $row['user_id'] ? (string) $row['user_id'] : null, 'name' => $row['author_type'] === 'brand' ? Settings::get()['brand_name'] : Input::plain($row['display_name'] ?? 'Recipe cook'), 'type' => $row['author_type']], 'text' => Input::plain($row['body']), 'status' => $row['status'], 'revision' => (int) $row['revision'], 'created_at' => str_replace(' ', 'T', $row['created_at']) . 'Z'];
    }
    public static function comments(string $id, string $channel, array $params, ?array $user): array {
        global $wpdb; Catalog::get($id, $channel);
        Input::only($params, ['cursor', 'limit']); $limit = Input::integer($params['limit'] ?? 20, 1, 50);
        if (Settings::get()['hide_comments']) { return ['items' => [], 'next_cursor' => null]; }
        $binding = ['kind' => 'comments', 'recipe' => $id, 'channel' => $channel, 'user' => $user['id'] ?? 'public', 'limit' => $limit, 'boundary' => Catalog::boundary()];
        [$offset, $cursor] = Store::cursor($binding, $params['cursor'] ?? null);
        $where = $wpdb->prepare("c.recipe_id=%s AND c.status='approved' AND (c.parent_comment_id IS NULL OR parent.status='approved')", $id);
        if ($user) { $where .= $wpdb->prepare(' AND NOT EXISTS (SELECT 1 FROM ' . Store::table('user_blocks') . ' b WHERE b.blocker_user_id=%s AND (b.blocked_user_id=c.user_id OR b.blocked_user_id=parent.user_id))', $user['id']); }
        $from = Store::table('comments') . ' c LEFT JOIN ' . Store::table('users') . ' u ON u.id=c.user_id LEFT JOIN ' . Store::table('comments') . ' parent ON parent.id=c.parent_comment_id';
        $rows = $wpdb->get_results($wpdb->prepare("SELECT c.*,u.display_name FROM $from WHERE $where ORDER BY c.created_at,c.id LIMIT %d OFFSET %d", $limit + 1, $offset), ARRAY_A);
        $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM $from WHERE $where");
        $more = count($rows) > $limit;
        return ['items' => array_map([self::class, 'comment_view'], array_slice($rows, 0, $limit)), 'approved_count' => $count, 'next_cursor' => $more ? $cursor($offset + $limit) : null];
    }
    public static function community(string $id, string $channel, array $params, ?array $user): array {
        global $wpdb; $comments = self::comments($id, $channel, $params, $user); $mine = null;
        if ($user) { $row = $wpdb->get_row($wpdb->prepare('SELECT id,stars,status,revision FROM ' . Store::table('ratings') . ' WHERE recipe_id=%s AND user_id=%s', $id, $user['id']), ARRAY_A); if ($row && $row['status'] !== 'deleted') { $mine = ['id' => (string) $row['id'], 'stars' => (int) $row['stars'], 'status' => $row['status'], 'revision' => (int) $row['revision']]; } }
        return ['comments' => $comments, 'rating' => self::stats($id), 'my_rating' => $mine];
    }
    public static function create_comment(string $id, string $channel, array $user, array $data, string $key): array {
        Input::only($data, ['text']); Identity::active($user); self::enabled($id, $channel, 'comments'); $body = Input::text($data['text'] ?? '', 1, 2000, true);
        return Store::idem('user:' . $user['id'], 'comment:' . $id, $key, ['text' => $body, 'channel' => $channel], static function () use ($id, $channel, $user, $body) {
            self::lock_user($user); self::enabled($id, $channel, 'comments');
            Store::rate('comment-hour', $user['id'], 5, 3600); Store::rate('comment-day', $user['id'], 20, 86400);
            // Hold all comments; optional local filters may reject obvious abuse, never auto-approve.
            if (!apply_filters('quickfix_comment_allowed', true, $body)) { throw new Fault('comment_rejected', 'Please revise this comment before submitting.'); }
            $comment = Store::insert('comments', ['recipe_id' => $id, 'user_id' => $user['id'], 'body' => $body, 'status' => 'pending', 'source_channel' => $channel, 'created_at' => Store::now(), 'updated_at' => Store::now()]);
            Store::audit('comment.created', 'comment:' . $comment, ['status' => 'pending'], (int) $user['id']); return ['id' => (string) $comment, 'status' => 'pending', 'revision' => 1];
        });
    }
    public static function own_comment(string $id, array $user, ?array $data): array {
        global $wpdb; Identity::active($user, false);
        return Store::transaction(static function () use ($id, $user, $data, $wpdb) {
            self::lock_user($user, $data !== null);
            $row = Store::row('comments', $id, true); if ((string) $row['user_id'] !== (string) $user['id'] || $row['author_type'] !== 'consumer') { throw new Fault('not_found', 'Comment unavailable.', 404); }
            if ($data === null) {
                self::remove_comment($id); Store::audit('comment.deleted', 'comment:' . $id, [], (int) $user['id']); Store::bump(); return ['deleted' => true];
            }
            Identity::active($user); Input::only($data, ['text','revision']); Store::revision($row, $data['revision'] ?? null);
            $text = Input::text($data['text'] ?? '', 1, 2000, true);
            Store::update('comments', ['body' => $text, 'status' => 'pending', 'revision' => (int) $row['revision'] + 1, 'updated_at' => Store::now(), 'moderation_reason' => ''], ['id' => $id]);
            Store::audit('comment.edited', 'comment:' . $id, ['status' => 'pending'], (int) $user['id']); Store::bump(); return ['id' => $id, 'status' => 'pending', 'revision' => (int) $row['revision'] + 1];
        });
    }
    public static function remove_comment(string $id): void {
        global $wpdb;
        $reply_ids = $wpdb->get_col($wpdb->prepare('SELECT id FROM ' . Store::table('comments') . ' WHERE parent_comment_id=%s', $id));
        foreach (array_merge([$id], $reply_ids) as $target) { Store::query($wpdb->prepare('DELETE FROM ' . Store::table('reports') . " WHERE target_type IN ('comment','reply') AND target_id=%s", $target)); }
        Store::query($wpdb->prepare('DELETE FROM ' . Store::table('comments') . ' WHERE id=%s OR parent_comment_id=%s', $id, $id));
    }
    public static function rating(string $id, string $channel, array $user, ?array $data): array {
        global $wpdb; Identity::active($user, $data !== null); $stars = null;
        if ($data !== null) { self::enabled($id, $channel, 'ratings'); Input::only($data, ['stars','revision']); $stars = Input::integer($data['stars'] ?? null, 1, 5); }
        return Store::transaction(static function () use ($id, $user, $data, $stars, $channel, $wpdb) {
            self::lock_user($user, $data !== null);
            if ($data !== null) { self::enabled($id, $channel, 'ratings'); }
            self::lock_stats($id);
            $table = Store::table('ratings'); $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE recipe_id=%s AND user_id=%s FOR UPDATE", $id, $user['id']), ARRAY_A);
            if ($data === null) { if ($row) { Store::query($wpdb->prepare("DELETE FROM $table WHERE id=%s", $row['id'])); } }
            else {
                Store::revision($row ?: ['revision' => 0], $data['revision'] ?? 0);
                if ($row && in_array($row['status'], ['hidden','held'], true)) { throw new Fault('rating_moderated', 'This rating requires moderator review.', 403); }
                $update = ['stars' => $stars, 'status' => 'active', 'source_channel' => $channel, 'revision' => $row ? (int) $row['revision'] + 1 : 1, 'updated_at' => Store::now()];
                if ($row) { Store::update('ratings', $update, ['id' => $row['id']]); $rating_id = (string) $row['id']; }
                else { $rating_id = (string) Store::insert('ratings', array_merge($update, ['recipe_id' => $id, 'user_id' => $user['id'], 'created_at' => Store::now()])); }
            }
            self::reconcile($id); Store::audit($data ? 'rating.saved' : 'rating.deleted', 'recipe:' . $id, [], (int) $user['id']); Store::bump();
            return ['rating' => self::stats($id), 'my_rating' => $data ? ['id' => $rating_id, 'stars' => $stars, 'revision' => $update['revision']] : null];
        });
    }
    public static function own_rating(string $rating, array $user): array {
        $row = Store::row('ratings', $rating); if ((string) $row['user_id'] !== (string) $user['id']) { throw new Fault('not_found', 'Rating unavailable.', 404); }
        return self::rating((string) $row['recipe_id'], 'app', $user, null);
    }
    public static function report(array $user, array $data, string $key): array {
        global $wpdb; Identity::active($user); Input::only($data, ['target_type','target_id','reason','details','channel']);
        $type = Input::choice($data['target_type'] ?? '', ['comment','reply','author']); $id = Input::id($data['target_id'] ?? '');
        $reason = Input::choice($data['reason'] ?? '', ['spam','abuse','misleading_content','other']); $details = Input::text($data['details'] ?? '', 0, 500);
        $channel = Input::choice($data['channel'] ?? 'app', ['app','website']);
        if ($type === 'author') { $target = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . Store::table('comments') . " WHERE user_id=%s AND status='approved' AND parent_comment_id IS NULL ORDER BY id DESC LIMIT 1", $id), ARRAY_A); }
        else { $target = Store::row('comments', $id); }
        if (!$target || $target['status'] !== 'approved' || ($type === 'reply' && $target['author_type'] !== 'brand') || ($type === 'comment' && $target['author_type'] !== 'consumer')) { throw new Fault('not_found', 'Report target unavailable.', 404); }
        Catalog::get((string) $target['recipe_id'], $channel);
        if ($target['user_id'] && $wpdb->get_var($wpdb->prepare('SELECT 1 FROM ' . Store::table('user_blocks') . ' WHERE blocker_user_id=%s AND blocked_user_id=%s', $user['id'], $target['user_id']))) { throw new Fault('not_found', 'Report target unavailable.', 404); }
        return Store::idem('user:' . $user['id'], 'report', $key, $data, static function () use ($user, $type, $id, $reason, $details, $wpdb) {
            self::lock_user($user);
            Store::rate('report-day', $user['id'], 10, 86400); $unique = $user['id'] . ':' . $type . ':' . $id;
            if ($wpdb->get_var($wpdb->prepare('SELECT id FROM ' . Store::table('reports') . ' WHERE unresolved_key=%s', $unique))) { throw new Fault('report_open', 'An unresolved report already exists.', 409); }
            $report = Store::insert('reports', ['reporter_id' => $user['id'], 'target_type' => $type, 'target_id' => $id, 'reason' => $reason, 'details' => $details, 'unresolved_key' => $unique, 'created_at' => Store::now(), 'updated_at' => Store::now()]);
            Store::audit('report.created', 'report:' . $report, ['reason' => $reason], (int) $user['id']); return ['id' => (string) $report, 'status' => 'open'];
        });
    }
    public static function block(string $id, array $user, string $method): array {
        global $wpdb; Input::id($id); $table = Store::table('user_blocks');
        if ($method !== 'GET') { Identity::active($user, false); if ($id === (string) $user['id']) { throw new Fault('invalid_block', 'You cannot block yourself.'); } }
        if ($method === 'PUT') { $target = Store::row('users', $id); if ($target['status'] === 'deleted') { throw new Fault('not_found', 'User unavailable.', 404); } Store::query($wpdb->prepare("INSERT INTO $table (blocker_user_id,blocked_user_id,created_at) VALUES (%s,%s,%s) ON DUPLICATE KEY UPDATE blocked_user_id=blocked_user_id", $user['id'], $id, Store::now())); }
        if ($method === 'DELETE') { Store::query($wpdb->prepare("DELETE FROM $table WHERE blocker_user_id=%s AND blocked_user_id=%s", $user['id'], $id)); }
        if ($method !== 'GET') { Store::audit('block.' . strtolower($method), 'user:' . $id, [], (int) $user['id']); Store::bump(); }
        return ['user_id' => $id, 'blocked' => (bool) $wpdb->get_var($wpdb->prepare("SELECT 1 FROM $table WHERE blocker_user_id=%s AND blocked_user_id=%s", $user['id'], $id))];
    }
    public static function moderate(string $kind, string $id, array $data): array {
        global $wpdb;
        return Store::transaction(static function () use ($kind, $id, $data, $wpdb) {
            $initial = Store::row($kind, $id);
            if ($kind === 'ratings') { self::lock_stats((string) $initial['recipe_id']); }
            $row = Store::row($kind, $id, true); Store::revision($row, $data['revision'] ?? null);
            if ($kind === 'comments' && isset($data['reply'])) {
                Input::only($data, ['revision','reply']); if ($row['author_type'] !== 'consumer' || $row['status'] !== 'approved') { throw new Fault('invalid_reply', 'Reply to an approved top-level comment.'); }
                $body = Input::text($data['reply'], 1, 2000, true); $reply = Store::insert('comments', ['recipe_id' => $row['recipe_id'], 'parent_comment_id' => $id, 'author_type' => 'brand', 'body' => $body, 'status' => 'approved', 'source_channel' => 'brand', 'created_at' => Store::now(), 'updated_at' => Store::now()]);
                Store::audit('comment.brand_reply', 'comment:' . $reply, ['status' => 'approved']); Store::bump(); return ['id' => (string) $reply, 'status' => 'approved'];
            }
            Input::only($data, ['revision','status','reason','assigned_to']); $reason = Input::text($data['reason'] ?? '', 0, 500);
            $choices = ['comments' => ['pending','approved','rejected','hidden','deleted'], 'ratings' => ['active','held','hidden','deleted'], 'reports' => ['open','investigating','resolved','dismissed'], 'users' => ['active','community_suspended']];
            $status = Input::choice($data['status'] ?? '', $choices[$kind]);
            if (in_array($status, ['hidden','rejected','deleted','community_suspended','resolved','dismissed'], true) && !$reason) { throw new Fault('reason_required', 'Provide a moderation reason.'); }
            if ($kind === 'users' && in_array($row['status'], ['deletion_pending','deleted'], true)) { throw new Fault('deletion_in_progress', 'A deletion workflow cannot be reversed.', 409); }
            if ($kind === 'comments' && $status === 'deleted') { self::remove_comment($id); Store::audit('comment.deleted', 'comment:' . $id, ['reason' => $reason]); Store::bump(); return ['deleted' => true]; }
            $update = ['status' => $status, 'revision' => (int) $row['revision'] + 1];
            if ($kind !== 'users') { $update['updated_at'] = Store::now(); }
            if ($kind === 'comments' || $kind === 'ratings') { $update['moderation_reason'] = $reason; }
            if ($kind === 'reports') { $update['resolution'] = $reason; $update['unresolved_key'] = in_array($status, ['resolved','dismissed'], true) ? null : $row['reporter_id'] . ':' . $row['target_type'] . ':' . $row['target_id']; }
            if (isset($data['assigned_to']) && in_array($kind, ['comments','reports'], true)) { $actor = get_user_by('id', (int) $data['assigned_to']); if (!$actor || !user_can($actor, 'quickfix_moderate')) { throw new Fault('invalid_moderator', 'Assign an authorized moderator.'); } $update['assigned_to'] = (int) $actor->ID; }
            if ($kind === 'ratings' && $status === 'deleted') { Store::query($wpdb->prepare('DELETE FROM ' . Store::table('ratings') . ' WHERE id=%s', $id)); }
            else { Store::update($kind, $update, ['id' => $id]); }
            if ($kind === 'ratings') { self::reconcile((string) $row['recipe_id']); }
            Store::audit($kind . '.moderated', $kind . ':' . $id, ['before' => $row['status'], 'after' => $status, 'reason' => $reason]); Store::bump(); return ['id' => $id, 'status' => $status, 'revision' => $update['revision']];
        });
    }
}
