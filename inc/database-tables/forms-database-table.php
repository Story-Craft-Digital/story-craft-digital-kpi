<?php
if (! defined('ABSPATH')) exit;

function scdkpi_lead_tables_activate()
{
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $charset_collate = $wpdb->get_charset_collate();

    // 1. Submissions Table (Existing)
    $table_submissions = $wpdb->prefix . 'scdkpi_form_submissions';
    $sql_submissions = "CREATE TABLE $table_submissions (
        submission_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        form_id VARCHAR(100) NOT NULL,
        block_id VARCHAR(100) NOT NULL,
        entry_id VARCHAR(255) NULL DEFAULT NULL,
        status TINYINT(1) NOT NULL DEFAULT 0, 
        lead_status VARCHAR(50) DEFAULT 'new', 
        contact_owner_id BIGINT(20) UNSIGNED DEFAULT 0, 
        submitted_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        last_activity DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        user_id BIGINT(20) UNSIGNED,
        submission_hash VARCHAR(32) NOT NULL,
        PRIMARY KEY  (submission_id),
        KEY form_id (form_id),
        KEY block_id (block_id),
        KEY entry_id (entry_id(191)),
        KEY lead_status (lead_status),
        KEY contact_owner_id (contact_owner_id),
        KEY last_activity (last_activity),
        KEY submission_hash (submission_hash)
    ) $charset_collate;";
    dbDelta($sql_submissions);

    // 2. Meta Table (Existing)
    $table_meta = $wpdb->prefix . 'scdkpi_form_submission_meta';
    $sql_meta = "CREATE TABLE $table_meta (
        meta_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        submission_id BIGINT(20) UNSIGNED NOT NULL,
        meta_key VARCHAR(255) NOT NULL,
        meta_value LONGTEXT,
        PRIMARY KEY  (meta_id),
        KEY submission_id (submission_id),
        KEY meta_key (meta_key(191))
    ) $charset_collate;";
    dbDelta($sql_meta);

    // 3. Timeline Table (Existing)
    $table_timeline = $wpdb->prefix . 'scdkpi_form_submission_timeline';
    $sql_timeline = "CREATE TABLE $table_timeline (
        timeline_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        submission_id BIGINT(20) UNSIGNED NOT NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL, 
        type VARCHAR(50) NOT NULL, 
        note LONGTEXT, 
        created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        PRIMARY KEY  (timeline_id),
        KEY submission_id (submission_id),
        KEY type (type)
    ) $charset_collate;";
    dbDelta($sql_timeline);

    // 4. Tasks Table 
    $table_tasks = $wpdb->prefix . 'scdkpi_tasks';
    $sql_tasks = "CREATE TABLE $table_tasks (
        task_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        submission_id BIGINT(20) UNSIGNED NOT NULL,
        created_by BIGINT(20) UNSIGNED NOT NULL,
        assigned_to BIGINT(20) UNSIGNED DEFAULT 0,
        title VARCHAR(255) NOT NULL,
        description LONGTEXT,
        priority VARCHAR(20) DEFAULT 'medium',
        status VARCHAR(50) DEFAULT 'pending',
        due_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        updated_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        PRIMARY KEY  (task_id),
        KEY submission_id (submission_id),
        KEY assigned_to (assigned_to),
        KEY status (status)
    ) $charset_collate;";
    dbDelta($sql_tasks);

    // ✅ 5. Task Logs Table
    $table_task_logs = $wpdb->prefix . 'scdkpi_task_logs';
    $sql_task_logs = "CREATE TABLE $table_task_logs (
        log_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        task_id BIGINT(20) UNSIGNED NOT NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        type VARCHAR(50) NOT NULL, -- 'status_change', 'note', 'update', 'created'
        note LONGTEXT,
        created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        PRIMARY KEY  (log_id),
        KEY task_id (task_id)
    ) $charset_collate;";
    dbDelta($sql_task_logs);

    // 6. Chats Table (Standalone, not mixed with logs)
    $table_chats = $wpdb->prefix . 'scdkpi_chats';
    $sql_chats = "CREATE TABLE $table_chats (
    chat_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    object_id BIGINT(20) UNSIGNED NOT NULL, -- Lead ID or Task ID
    object_type VARCHAR(20) NOT NULL DEFAULT 'lead', -- 'lead' or 'task'
    user_id BIGINT(20) UNSIGNED NOT NULL, -- Sender
    message LONGTEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY  (chat_id),
    KEY object_lookup (object_id, object_type)
) $charset_collate;";
    dbDelta($sql_chats);

    // 7. Notifications Table (For Mentions)
    $table_notifs = $wpdb->prefix . 'scdkpi_notifications';
    $sql_notifs = "CREATE TABLE $table_notifs (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT(20) UNSIGNED NOT NULL, -- Recipient (Who was mentioned)
    created_by BIGINT(20) UNSIGNED NOT NULL, -- Sender (Who mentioned them)
    object_id BIGINT(20) UNSIGNED NOT NULL, -- Lead ID or Task ID
    object_type VARCHAR(20) NOT NULL DEFAULT 'lead',
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY  (id),
    KEY user_read (user_id, is_read)
) $charset_collate;";
    dbDelta($sql_notifs);
}
