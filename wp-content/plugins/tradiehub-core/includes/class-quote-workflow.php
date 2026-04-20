<?php
if (!defined('ABSPATH')) exit;

class TradieHub_Quote_Workflow {
    public static function init(): void {
        add_action('init', [self::class, 'register_post_types']);
        add_action('rest_api_init', [self::class, 'register_rest_routes']);
        add_action('tradiehub_quote_submitted', [self::class, 'notify_homeowner_of_quote'], 10, 2);
    }

    public static function register_post_types(): void {
        register_post_type('tradiehub_job', [
            'labels' => [
                'name'          => __('Jobs', 'tradiehub'),
                'singular_name' => __('Job', 'tradiehub'),
                'add_new_item'  => __('Post a Job', 'tradiehub'),
            ],
            'public'          => true,
            'has_archive'     => 'jobs',
            'rewrite'         => ['slug' => 'jobs', 'with_front' => false],
            'supports'        => ['title', 'editor', 'author', 'custom-fields'],
            'show_in_rest'    => true,
            'rest_base'       => 'jobs',
            'menu_icon'       => 'dashicons-hammer',
            'capability_type' => ['tradiehub_job', 'tradiehub_jobs'],
            'map_meta_cap'    => true,
        ]);

        register_post_type('tradiehub_quote', [
            'labels' => [
                'name'          => __('Quotes', 'tradiehub'),
                'singular_name' => __('Quote', 'tradiehub'),
            ],
            'public'          => false,
            'show_ui'         => true,
            'show_in_menu'    => 'edit.php?post_type=tradiehub_job',
            'supports'        => ['title', 'author', 'custom-fields'],
            'show_in_rest'    => true,
            'rest_base'       => 'quotes',
            'capability_type' => ['tradiehub_quote', 'tradiehub_quotes'],
            'map_meta_cap'    => true,
        ]);

        foreach (['pending', 'accepted', 'rejected', 'completed'] as $status) {
            register_post_status("quote_{$status}", [
                'label'                     => ucfirst($status),
                'public'                    => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'post_type'                 => ['tradiehub_quote'],
            ]);
        }
    }

    public static function register_rest_routes(): void {
        register_rest_route('tradiehub/v1', '/jobs', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'rest_create_job'],
            'permission_callback' => fn() => current_user_can('publish_tradiehub_jobs'),
            'args' => [
                'title'       => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'description' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'wp_kses_post'],
                'zip'         => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'budget_min'  => ['required' => false, 'type' => 'integer'],
                'budget_max'  => ['required' => false, 'type' => 'integer'],
                'specialty'   => ['required' => true, 'type' => 'string'],
                'deadline'    => ['required' => false, 'type' => 'string'],
            ],
        ]);

        register_rest_route('tradiehub/v1', '/quotes', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'rest_submit_quote'],
            'permission_callback' => fn() => current_user_can('submit_tradiehub_quote'),
            'args' => [
                'job_id'   => ['required' => true, 'type' => 'integer'],
                'amount'   => ['required' => true, 'type' => 'number'],
                'timeline' => ['required' => false, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'message'  => ['required' => false, 'type' => 'string', 'sanitize_callback' => 'wp_kses_post'],
            ],
        ]);

        register_rest_route('tradiehub/v1', '/quotes/(?P<id>\d+)/accept', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'rest_accept_quote'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args' => ['id' => ['required' => true, 'type' => 'integer']],
        ]);

        register_rest_route('tradiehub/v1', '/jobs/(?P<id>\d+)/complete', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'rest_complete_job'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args' => ['id' => ['required' => true, 'type' => 'integer']],
        ]);
    }

    public static function rest_create_job(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $job_id = wp_insert_post([
            'post_type'    => 'tradiehub_job',
            'post_title'   => $request->get_param('title'),
            'post_content' => $request->get_param('description'),
            'post_status'  => 'publish',
            'post_author'  => get_current_user_id(),
            'meta_input'   => [
                'zip'        => sanitize_text_field($request->get_param('zip')),
                'budget_min' => absint($request->get_param('budget_min') ?? 0),
                'budget_max' => absint($request->get_param('budget_max') ?? 0),
                'deadline'   => sanitize_text_field($request->get_param('deadline') ?? ''),
                'job_status' => 'open',
            ],
        ], true);

        if (is_wp_error($job_id)) {
            return new WP_Error('create_failed', $job_id->get_error_message(), ['status' => 500]);
        }

        if ($specialty = $request->get_param('specialty')) {
            wp_set_post_terms($job_id, [sanitize_text_field($specialty)], 'tradiehub_specialty');
        }

        return new WP_REST_Response(['job_id' => $job_id, 'url' => get_permalink($job_id)], 201);
    }

    public static function rest_submit_quote(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $job_id = $request->get_param('job_id');
        $job    = get_post($job_id);

        if (!$job || $job->post_type !== 'tradiehub_job') {
            return new WP_Error('invalid_job', 'Job not found.', ['status' => 404]);
        }

        $contractor_id = get_current_user_id();

        // Enforce one quote per contractor per job
        $existing = get_posts([
            'post_type'   => 'tradiehub_quote',
            'author'      => $contractor_id,
            'meta_query'  => [['key' => 'job_id', 'value' => $job_id]],
            'post_status' => 'any',
            'numberposts' => 1,
            'fields'      => 'ids',
        ]);
        if ($existing) {
            return new WP_Error('duplicate_quote', 'You have already submitted a quote for this job.', ['status' => 409]);
        }

        $quote_id = wp_insert_post([
            'post_type'   => 'tradiehub_quote',
            'post_title'  => sprintf('Quote for job #%d', $job_id),
            'post_status' => 'quote_pending',
            'post_author' => $contractor_id,
            'meta_input'  => [
                'job_id'   => $job_id,
                'amount'   => floatval($request->get_param('amount')),
                'timeline' => sanitize_text_field($request->get_param('timeline') ?? ''),
                'message'  => wp_kses_post($request->get_param('message') ?? ''),
            ],
        ], true);

        if (is_wp_error($quote_id)) {
            return new WP_Error('create_failed', $quote_id->get_error_message(), ['status' => 500]);
        }

        do_action('tradiehub_quote_submitted', $quote_id, $job_id);

        return new WP_REST_Response(['quote_id' => $quote_id], 201);
    }

    public static function rest_accept_quote(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $quote_id = (int) $request->get_param('id');
        $quote    = get_post($quote_id);

        if (!$quote || $quote->post_type !== 'tradiehub_quote') {
            return new WP_Error('invalid_quote', 'Quote not found.', ['status' => 404]);
        }

        $job = get_post((int) get_post_meta($quote_id, 'job_id', true));
        if (!$job || (int) $job->post_author !== get_current_user_id()) {
            return new WP_Error('forbidden', 'You can only accept quotes on your own jobs.', ['status' => 403]);
        }

        wp_update_post(['ID' => $quote_id, 'post_status' => 'quote_accepted']);
        do_action('tradiehub_quote_accepted', $quote_id, $job->ID);

        return new WP_REST_Response(['status' => 'accepted', 'quote_id' => $quote_id], 200);
    }

    public static function rest_complete_job(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $job_id = (int) $request->get_param('id');
        $job    = get_post($job_id);

        if (!$job || $job->post_type !== 'tradiehub_job') {
            return new WP_Error('invalid_job', 'Job not found.', ['status' => 404]);
        }

        $user_id      = get_current_user_id();
        $is_homeowner = (int) $job->post_author === $user_id;

        // Find the accepted quote to determine the contractor
        $accepted_quotes = get_posts([
            'post_type'   => 'tradiehub_quote',
            'post_status' => 'quote_accepted',
            'meta_query'  => [['key' => 'job_id', 'value' => $job_id]],
            'numberposts' => 1,
            'fields'      => 'all',
        ]);
        $accepted_quote   = $accepted_quotes[0] ?? null;
        $contractor_id    = $accepted_quote ? (int) $accepted_quote->post_author : 0;
        $is_contractor    = $contractor_id === $user_id;

        if (!$is_homeowner && !$is_contractor) {
            return new WP_Error('forbidden', 'Only the homeowner or assigned contractor can confirm job completion.', ['status' => 403]);
        }

        $meta_key = $is_homeowner ? 'homeowner_confirmed_complete' : 'contractor_confirmed_complete';
        update_post_meta($job_id, $meta_key, true);

        $both = get_post_meta($job_id, 'homeowner_confirmed_complete', true) &&
                get_post_meta($job_id, 'contractor_confirmed_complete', true);

        if ($both) {
            update_post_meta($job_id, 'job_status', 'completed');
            do_action('tradiehub_job_completed', $job_id);
        }

        return new WP_REST_Response(['confirmed' => true, 'both_confirmed' => $both], 200);
    }

    public static function notify_homeowner_of_quote(int $quote_id, int $job_id): void {
        $job        = get_post($job_id);
        $homeowner  = get_userdata((int) $job->post_author);
        $contractor = get_userdata((int) get_post_field('post_author', $quote_id));
        $amount     = (float) get_post_meta($quote_id, 'amount', true);

        if (!$homeowner || !$contractor) return;

        wp_mail(
            $homeowner->user_email,
            sprintf(__('New quote received for "%s"', 'tradiehub'), $job->post_title),
            sprintf(
                __("Hi %s,\n\n%s submitted a quote of \$%s for your job: %s\n\nView quotes: %s", 'tradiehub'),
                $homeowner->display_name,
                $contractor->display_name,
                number_format($amount, 2),
                $job->post_title,
                home_url('/my-jobs/')
            )
        );
    }
}
