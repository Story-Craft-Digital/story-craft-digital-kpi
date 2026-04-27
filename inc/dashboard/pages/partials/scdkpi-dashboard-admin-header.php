<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the Story Craft Digital KPI dashboard header.
 */
function scdkpi_render_dashboard_header($title = '', $svg = '')
{
    $current_user = wp_get_current_user();
    $full_name    = !empty($current_user->user_firstname) ? $current_user->user_firstname . ' ' . $current_user->user_lastname : $current_user->display_name;
    $user_login   = $current_user->user_login;

    $initials = !empty($current_user->user_firstname) && !empty($current_user->user_lastname)
        ? strtoupper(substr($current_user->user_firstname, 0, 1) . substr($current_user->user_lastname, 0, 1))
        : strtoupper(substr($user_login, 0, 2));

    $title = !empty($title) ? $title : __('SCD: Dashboard', 'story-craft-digital-kpi');
    // Updated helper function name
    if (empty($svg) && function_exists('scdkpi_get_logo_svg')) {
        $svg = scdkpi_get_logo_svg();
    }
?>

    <div class="scdkpi-global-header-wrapper px-6 pt-6">
        <h2 class="sr-only"><?php esc_html_e('Notifications', 'story-craft-digital-kpi'); ?></h2>
        <div class="scdkpi-header-container">
            <div class="scdkpi-header-left">
                <div class="scdkpi-logo-wrapper">
                    <?php
                    echo wp_kses(
                        $svg,
                        array(
                            'svg'  => array(
                                'xmlns'       => true,
                                'fill'        => true,
                                'viewbox'     => true,
                                'role'        => true,
                                'aria-hidden' => true,
                                'width'       => true,
                                'height'      => true,
                            ),
                            'path' => array(
                                'd'    => true,
                                'fill' => true,
                            ),
                        )
                    );
                    ?>
                </div>
                <h1 class="scdkpi-header-title">
                    <?php echo esc_html($title); ?>
                </h1>
            </div>

            <div class="scdkpi-user-badge-wrapper">
                <div class="scdkpi-user-badge">
                    <div class="scdkpi-user-initials">
                        <?php echo esc_html($initials); ?>
                    </div>
                    <div class="scdkpi-user-info">
                        <div class="scdkpi-user-name-row">
                            <span class="scdkpi-user-name"><?php echo esc_html($full_name); ?></span>
                            <span class="scdkpi-status-indicator">
                                <span class="scdkpi-status-ping"></span>
                                <span class="scdkpi-status-dot-active"></span>
                            </span>
                        </div>
                        <span class="scdkpi-user-login">
							<?php
							/* translators: %s: User login name */
							echo sprintf( esc_html__( 'WP: %s', 'story-craft-digital-kpi' ), '<span class="scdkpi-login-name">' . esc_html( $user_login ) . '</span>' );
							?>
						</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
}
