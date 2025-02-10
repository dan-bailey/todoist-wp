<?php
/**
 * Plugin Name: Todoist WP Integration
 * Description: Integrates Todoist with WordPress, allowing users to display tasks on their site.
 * Version: 1.0.0
 * Author: Dan Bailey
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class TodoistWPIntegration {
    private $option_name = 'todoist_wp_api_key';

    public function __construct() {
        add_action('admin_menu', [$this, 'create_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_shortcode('todoist_tasks', [$this, 'display_todoist_tasks']);
    }

    public function create_settings_page() {
        add_options_page(
            'Todoist Integration',
            'Todoist Integration',
            'manage_options',
            'todoist-wp-settings',
            [$this, 'settings_page_html']
        );
    }

    public function register_settings() {
        register_setting('todoist_wp_group', $this->option_name);
    }

    public function settings_page_html() {
        ?>
        <div class="wrap">
            <h1>Todoist WP Integration</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('todoist_wp_group');
                do_settings_sections('todoist_wp_group');
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Todoist API Key:</th>
                        <td><input type="text" name="<?php echo $this->option_name; ?>" value="<?php echo esc_attr(get_option($this->option_name)); ?>" size="50"></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function display_todoist_tasks($atts) {
        $api_key = get_option($this->option_name);
        if (!$api_key) {
            return 'Todoist API key not set.';
        }

        $response = wp_remote_get('https://api.todoist.com/rest/v2/tasks', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
            ],
        ]);

        if (is_wp_error($response)) {
            return 'Error fetching tasks.';
        }

        $tasks = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($tasks)) {
            return 'No tasks found.';
        }

        $output = '<ul class="todoist-tasks">';
        foreach ($tasks as $task) {
            $output .= '<li>' . esc_html($task['content']) . '</li>';
        }
        $output .= '</ul>';

        return $output;
    }
}

new TodoistWPIntegration();