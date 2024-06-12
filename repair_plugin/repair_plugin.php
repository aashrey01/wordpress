<?php
/*
Plugin Name: Repair Plugin Admin
Description: Custom admin page for Repair Plugin settings.
Version: 1.0
*/

if (!defined('ABSPATH')) {
    exit;
}

register_activation_hook(__FILE__, 'repair_plugin_install');

function repair_plugin_install() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $table_categories = $wpdb->prefix . 'categories';
    $table_brands = $wpdb->prefix . 'brands';

    $sql = "CREATE TABLE $table_categories (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        type varchar(255) NOT NULL,
        active tinyint(1) NOT NULL DEFAULT 1,
        PRIMARY KEY  (id)
    ) $charset_collate;

    CREATE TABLE $table_brands (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        category_id mediumint(9) NOT NULL,
        name varchar(255) NOT NULL,
        models int(11) NOT NULL DEFAULT 0,
        active tinyint(1) NOT NULL DEFAULT 1,
        PRIMARY KEY  (id),
        FOREIGN KEY (category_id) REFERENCES $table_categories(id) ON DELETE CASCADE
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Add sample data
    repair_plugin_add_sample_data();
}

// Add sample data function
function repair_plugin_add_sample_data() {
    global $wpdb;

    // Sample categories
    $categories = [
        ['name' => 'Smartphones', 'type' => 'Device'],
        ['name' => 'Tablets', 'type' => 'Device'],
        ['name' => 'Laptops', 'type' => 'Device']
    ];

    // Insert sample categories
    foreach ($categories as $category) {
        $wpdb->insert(
            "{$wpdb->prefix}categories",
            [
                'name' => $category['name'],
                'type' => $category['type'],
                'active' => 1
            ]
        );
        $category_id = $wpdb->insert_id;

        // Sample brands for each category
        $brands = [
            ['name' => 'Brand A', 'models' => 10],
            ['name' => 'Brand B', 'models' => 15],
            ['name' => 'Brand C', 'models' => 20]
        ];

        // Insert sample brands for this category
        foreach ($brands as $brand) {
            $wpdb->insert(
                "{$wpdb->prefix}brands",
                [
                    'category_id' => $category_id,
                    'name' => $brand['name'],
                    'models' => $brand['models'],
                    'active' => 1
                ]
            );
        }
    }
}

// Add menu item to the admin menu.
add_action('admin_menu', 'repair_plugin_admin_menu');

function repair_plugin_admin_menu() {
    add_menu_page(
        'Repair Plugin Pro', // Page title
        'Settings',  // Menu title
        'manage_options',    // Capability
        'repair_plugin',     // Menu slug
        'repair_plugin_settings_page', // Callback function
        'dashicons-admin-generic', // Icon
        6                    // Position
    );

    // Add subpages
    add_submenu_page(
        'repair_plugin',
        'Categories', // Page title
        'Categories', // Menu title
        'manage_options', // Capability
        'repair_plugin_categories', // Menu slug
        'repair_plugin_categories_page' // Callback function
    );

    add_submenu_page(
        'repair_plugin',
        'Appointments', // Page title
        'Appointments', // Menu title
        'manage_options', // Capability
        'repair_plugin_appointments', // Menu slug
        'repair_plugin_appointments_page' // Callback function
    );

    add_submenu_page(
        'repair_plugin',
        'Status', // Page title
        'Status', // Menu title
        'manage_options', // Capability
        'repair_plugin_status', // Menu slug
        'repair_plugin_status_page' // Callback function
    );
}

// Display the settings page content.
function repair_plugin_settings_page() {
    ?>
    <div class="wrap">
        <div class="settings-container">
            <div class="settings-sidebar">
                <h1>Settings</h1>
                <div class="nav-tab-wrapper">
                    <a href="#general_info" class="nav-tab nav-tab-active" onclick="showTab(event, 'general_info')">General Info</a>
                    <a href="#enhanced_locations" class="nav-tab" onclick="showTab(event, 'enhanced_locations')">Enhanced Locations</a>
                    <a href="#special_opening_hours" class="nav-tab" onclick="showTab(event, 'special_opening_hours')">Special Opening Hours</a>
                </div>
            </div>
            <div class="settings-content">
                <div id="general_info" class="tab-content active">
                    <form method="post" action="options.php">
                        <?php
                        settings_fields('repair_plugin_general_settings');
                        do_settings_sections('repair_plugin_general');
                        submit_button();
                        ?>
                    </form>
                </div>
                <div id="enhanced_locations" class="tab-content">
                    <h2>Enhanced Location Settings</h2>
                    <div class="button-row">
                        <button class="button">Licence</button>
                        <button class="button" onclick="showSchedule()">Schedule</button>
                        <button class="button">Locations</button>
                        <button class="button">Settings</button>
                    </div>
                    <div id="schedule-table" style="display:none;">
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Group Name</th>
                                    <th>Used</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>Group 1</td>
                                    <td>10</td>
                                    <td><button class="button button-primary">Update</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <form method="post" action="options.php">
                        <?php
                        settings_fields('repair_plugin_enhanced_locations_settings');
                        do_settings_sections('repair_plugin_enhanced_locations');
                        submit_button();
                        ?>
                    </form>
                </div>
                <div id="special_opening_hours" class="tab-content">
                    <form method="post" action="options.php">
                        <?php
                        settings_fields('repair_plugin_special_opening_hours_settings');
                        do_settings_sections('repair_plugin_special_opening_hours');
                        submit_button();
                        ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <style>
        .settings-container {
            display: flex;
            align-items: stretch;
            border: 1px solid #ccc;
        }
        .settings-sidebar {
            flex: 0 0 auto;
            width: 200px;
            padding: 20px;
            border-right: 1px solid #ccc;
        }
        .settings-content {
            flex: 1 1 auto;
            padding: 20px;
        }
        .nav-tab-wrapper {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
        }
        .nav-tab {
            margin-bottom: 10px;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .button-row {
            margin-bottom: 20px;
        }
        .button-row .button {
            margin-right: 5px;
        }
    </style>
    <script type="text/javascript">
        function showTab(event, tabId) {
            event.preventDefault();

            var tabs = document.getElementsByClassName('tab-content');
            for (var i = 0; i < tabs.length; i++) {
                tabs[i].style.display = 'none';
                tabs[i].classList.remove('active');
            }

            document.getElementById(tabId).style.display = 'block';
            document.getElementById(tabId).classList.add('active');

            var tabLinks = document.getElementsByClassName('nav-tab');
            for (var i = 0; i < tabLinks.length; i++) {
                tabLinks[i].classList.remove('nav-tab-active');
            }

            event.target.classList.add('nav-tab-active');
        }

        function showSchedule() {
            var scheduleTable = document.getElementById('schedule-table');
            if (scheduleTable.style.display === 'none') {
                scheduleTable.style.display = 'block';
            } else {
                scheduleTable.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('general_info').style.display = 'block';
            document.getElementById('general_info').classList.add('active');
        });
    </script>
    <?php
}

// Handle the sample data insertion request
add_action('admin_post_repair_plugin_add_sample_data', 'repair_plugin_handle_add_sample_data');

function repair_plugin_handle_add_sample_data() {
    if (isset($_POST['repair_plugin_add_sample_data']) && $_POST['repair_plugin_add_sample_data'] == '1') {
        repair_plugin_add_sample_data();
        wp_redirect(admin_url('admin.php?page=repair_plugin'));
        exit;
    }
}

// Display the categories page content.
function repair_plugin_categories_page() {
    global $wpdb;

    // Fetch categories
    $categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}categories", ARRAY_A);

    // Fetch brands for each category
    foreach ($categories as &$category) {
        $category_id = $category['id'];
        $category['brands'] = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}brands WHERE category_id = $category_id", ARRAY_A);
    }
    unset($category);

    ?>
    <div class="wrap">
        <h1>Manage Categories</h1>
        <button class="button button-primary" style="margin-bottom: 20px;">Save Order</button>
        <button class="button" style="margin-bottom: 20px;">New Type</button>
        <button class="button" style="margin-bottom: 20px;">New Brand</button>
        <div>
            <?php foreach ($categories as $category): ?>
                <div class="category">
                    <div class="category-header">
                        <label class="switch">
                            <input type="checkbox" class="toggle-category" data-category="<?php echo $category['id']; ?>" <?php echo $category['active'] ? 'checked' : ''; ?>>
                            <span class="slider round"></span>
                        </label>
                        <span><?php echo $category['name']; ?> <small><?php echo $category['type']; ?></small></span>
                    </div>
                    <?php foreach ($category['brands'] as $brand): ?>
                        <div class="brand">
                            <label class="switch">
                                <input type="checkbox" class="toggle-brand" data-brand="<?php echo $brand['id']; ?>" data-category="<?php echo $category['id']; ?>" <?php echo $brand['active'] ? 'checked' : ''; ?>>
                                <span class="slider round"></span>
                            </label>
                            <span><?php echo $brand['name']; ?> <small>brand</small></span>
                            <span><?php echo $brand['models']; ?> models</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="button button-primary" style="margin-top: 20px;">Save Order</button>
    </div>
    <?php
}

// Display the appointments page content.
function repair_plugin_appointments_page() {
    ?>
    <div class="wrap">
        <h1>Manage Appointments</h1>
    </div>
    <?php
}

// Display the status page content.
function repair_plugin_status_page() {
    ?>
    <div class="wrap">
        <h1>Check Status</h1>
    </div>
    <?php
}

// Register settings, sections and fields.
add_action('admin_init', 'repair_plugin_admin_init');

function repair_plugin_admin_init() {
    // General Info Settings
    register_setting('repair_plugin_general_settings', 'repair_plugin_general_settings', 'repair_plugin_validate_settings');

    add_settings_section(
        'repair_plugin_general_settings_section',
        'General Info Settings',
        'repair_plugin_general_settings_section_callback',
        'repair_plugin_general'
    );

    add_settings_field(
        'company_name',
        'Company Name',
        'repair_plugin_setting_string',
        'repair_plugin_general',
        'repair_plugin_general_settings_section',
        ['id' => 'company_name']
    );

    add_settings_field(
        'email_address',
        'Email Address',
        'repair_plugin_setting_string',
        'repair_plugin_general',
        'repair_plugin_general_settings_section',
        ['id' => 'email_address']
    );

    add_settings_field(
        'phone_number',
        'Phone Number',
        'repair_plugin_setting_string',
        'repair_plugin_general',
        'repair_plugin_general_settings_section',
        ['id' => 'phone_number']
    );

    add_settings_field(
        'company_registration_number',
        'Company Registration Number',
        'repair_plugin_setting_string',
        'repair_plugin_general',
        'repair_plugin_general_settings_section',
        ['id' => 'company_registration_number']
    );

    add_settings_field(
        'tax_registration_number',
        'Tax Registration Number',
        'repair_plugin_setting_string',
        'repair_plugin_general',
        'repair_plugin_general_settings_section',
        ['id' => 'tax_registration_number']
    );

    add_settings_field(
        'terms_condition_page',
        'Terms & Condition Page',
        'repair_plugin_setting_string',
        'repair_plugin_general',
        'repair_plugin_general_settings_section',
        ['id' => 'terms_condition_page']
    );

    // Enhanced Locations Settings
    register_setting('repair_plugin_enhanced_locations_settings', 'repair_plugin_enhanced_locations_settings');

    add_settings_section(
        'repair_plugin_enhanced_locations_section',
        'Settings',
        'repair_plugin_enhanced_locations_section_callback',
        'repair_plugin_enhanced_locations'
    );

    add_settings_field(
        'location_example',
        'Location Setting',
        'repair_plugin_setting_string',
        'repair_plugin_enhanced_locations',
        'repair_plugin_enhanced_locations_section',
        ['id' => 'location_example']
    );

    // Special Opening Hours Settings
    register_setting('repair_plugin_special_opening_hours_settings', 'repair_plugin_special_opening_hours_settings');

    add_settings_section(
        'repair_plugin_special_opening_hours_section',
        'Special Opening Hours Settings',
        'repair_plugin_special_opening_hours_section_callback',
        'repair_plugin_special_opening_hours'
    );

    add_settings_field(
        'opening_hours_example',
        'Opening Hours Setting',
        'repair_plugin_setting_string',
        'repair_plugin_special_opening_hours',
        'repair_plugin_special_opening_hours_section',
        ['id' => 'opening_hours_example']
    );

}

function repair_plugin_general_settings_section_callback() {
    echo 'General settings for the Repair Plugin.';
}

function repair_plugin_enhanced_locations_section_callback() {
    echo 'Enhanced locations settings for the Repair Plugin.';
}

function repair_plugin_special_opening_hours_section_callback() {
    echo 'Special opening hours settings for the Repair Plugin.';
}

function repair_plugin_setting_string($args) {
    $options = get_option($args['id']);
    ?>
    <input type="text" name="<?php echo $args['id']; ?>" value="<?php echo isset($options) ? esc_attr($options) : ''; ?>">
    <?php
}

function repair_plugin_validate_settings($input) {
    // validation
    return $input;
}
