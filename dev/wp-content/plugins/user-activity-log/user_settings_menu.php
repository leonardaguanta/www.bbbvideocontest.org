<?php
add_action('init', 'ual_filter_user_role');
/**
 * Filter user Roles
 * 
 */
if (!function_exists('ual_filter_user_role')):

    function ual_filter_user_role() {
        $paged = 1;
        $admin_url = get_admin_url();
        $display = '';
        if (isset($_POST['user_role'])) {
            $display = $_POST['user_role'];
        }
        if (isset($_POST['btn_filter_user_role'])) {
            $display = $_POST['user_role'];
            $header_uri = $admin_url . "admin.php?page=user_settings_menu&paged=$paged&display=$display&txtsearch=$search";
            header("Location: " . $header_uri, true);
            exit();
        }
        if (isset($_POST['btnSearch_user_role'])) {
            $search = ual_test_input($_POST['txtSearchinput']);
            $header_uri = $admin_url . "admin.php?page=user_settings_menu&paged=$paged&display=$display&txtsearch=$search";
            header("Location: " . $header_uri, true);
            exit();
        }
    }

endif;

/**
 * User activity Settings
 */
if (!function_exists('ual_user_activity_setting_function')):

    function ual_user_activity_setting_function() {
        global $wpdb;
        $paged = $total_pages = 1;
        $srno = 0;
        $active = $_GET['page'];
        $recordperpage = 10;
        $display = "roles";
        $search = "";
        if (isset($_GET['paged']))
            $paged = $_GET['paged'];
        $offset = ($paged - 1) * $recordperpage;
        $where = "where 1=1";
        if (isset($_GET['display'])) {
            $display = $_GET['display'];
        }
        if (isset($_GET['txtsearch'])) {
            $search = $_GET['txtsearch'];
            if ($search != "") {
                if ($display == "users")
                    $where.=" and user_login like '%$search%' or user_email like '%$search%' or display_name like '%$search%'";
            }
        }
        if (isset($_POST['saveLogin'])) {
            if ($display == "users") {
                add_option('enable_user_list');
                $enableuser = isset($_POST['usersID']) ? $_POST['usersID'] : "";
                update_option('enable_user_list', $enableuser);
            }
            if ($display == "roles") {
                $enablerole = isset($_POST['rolesID']) ? $_POST['rolesID'] : array();
                add_option('enable_role_list');
                $enable_user_login = array();
                for ($i = 0; $i < count($enablerole); $i++) {
                    $condition = "um.meta_key='" . $wpdb->prefix . "capabilities' and um.meta_value like '%" . $enablerole[$i] . "%' and u.ID = um.user_id";
                    $enable_list_user = "SELECT * FROM " . $wpdb->prefix . "usermeta as um, " . $wpdb->prefix . "users as u WHERE $condition";
                    $get_user = $wpdb->get_results($enable_list_user);
                    foreach ($get_user as $k => $v) {
                        $enable_user_login[] = $v->user_login;
                    }
                }
                update_option('enable_role_list', $enablerole);
                update_option('enable_user_list', $enable_user_login);
            }
        }
        
        // query for display all the users data start
        $get_user_data = "";
        $get_data = "";
        if ($display == "users") {
            $table_name = $wpdb->prefix . "users";
            $select_query = "SELECT * from $table_name $where LIMIT $offset,$recordperpage";
            $get_user_data = $wpdb->get_results($select_query);
            $total_items_query = "SELECT count(*) FROM $table_name $where";
            $total_items = $wpdb->get_var($total_items_query, 0, 0);
        } else {
            $table_name = $wpdb->prefix . "usermeta as um";
            $where.=" and um.meta_key='" . $wpdb->prefix . "capabilities'";
            $select_query = "SELECT distinct um.meta_value from $table_name $where LIMIT $offset,$recordperpage";
            $get_data = $wpdb->get_results($select_query);
            $total_items_query = "SELECT count(distinct um.meta_value) FROM $table_name $where";
            $total_items = $wpdb->get_var($total_items_query, 0, 0);
        }
        
        // query for pagination
        $total_pages = ceil($total_items / $recordperpage);
        $next_page = (int) $paged + 1;
        if ($next_page > $total_pages)
            $next_page = $total_pages;
        $prev_page = (int) $paged - 1;
        if ($prev_page < 1)
            $prev_page = 1;
        ?>
        <div class="wrap">
            <h2><?php _e('Notification Settings', 'wp_user_log'); ?></h2>
            <div class="tab_parent_parent">
                <div class="tab_parent">
                    <ul>
                        <li><a href="?page=general_settings_menu" class="<?php
                            if ($active == 'general_settings_menu') {
                                echo 'current';
                            }
                            ?>"><?php _e('General', 'wp_user_log'); ?></a></li>
                        <li><a href="?page=user_settings_menu" class="<?php
                            if ($active == 'user_settings_menu') {
                                echo 'current';
                            }
                            ?>"><?php _e('Users/Roles', 'wp_user_log'); ?></a></li>
                        <li><a href="?page=email_settings_menu" class="<?php
                            if ($active == 'email_settings_menu') {
                                echo 'current';
                            }
                            ?>"><?php _e('Email', 'wp_user_log'); ?></a></li>
                    </ul>
                </div>
            </div>
            <form class="sol-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?" . $_SERVER['QUERY_STRING']); ?>">
                <div class="sol-box-border">
                    <h3 class="sol-header-text"><?php _e('Select Users/Roles', 'wp_user_log'); ?></h3>
                    <p><?php _e('Email will be sent upon login of these selected users/roles.', 'wp_user_log'); ?></p>
                    <!-- Search Box start -->
                    <?php if ($display == 'users') {
                        ?>
                        <div class="sol-search-user-div">
                            <p class="search-box">
                                <label class="screen-reader-text" for="search-input"><?php _e('Search', 'wp_user_log'); ?> :</label>
                                <input id="user-search-input" class="sol-search-user" type="search" title="Search user by username,email,firstname and lastname" width="275px" placeholder="Username, Email, Firstname, Lastname" value="<?php echo $search; ?>" name="txtSearchinput">
                                <input id="search-submit" class="button" type="submit" value="<?php esc_attr_e('Search', 'wp_user_log'); ?>" name="btnSearch_user_role">
                            </p>
                        </div>
                    <?php }
                    ?>
                    <!-- Search Box end -->
                    <div class="tablenav top <?php if ($display == 'roles') echo 'sol-display-roles'; ?>">
                        <!-- Drop down menu for user and Role Start -->
                        <div class="alignleft actions sol-dropdown">
                            <select name="user_role">
                                <option selected value="roles"><?php _e('Role', 'wp_user_log'); ?></option>
                                <option <?php selected($display, 'users'); ?> value="users"><?php _e('User', 'wp_user_log'); ?></option>
                            </select>
                        </div>
                        <!-- Drop down menu for user and Role end -->
                        <input class="button-secondary action sol-filter-btn" type="submit" value="Filter" name="btn_filter_user_role">
                        <!-- top pagination start -->
                        <div class="tablenav-pages">
                            <?php $items = sprintf(_n('%s item', '%s items', $total_items, 'wp_user_log'), $total_items); ?>
                            <span class="displaying-num"><?php echo $items; ?></span>
                            <div class="tablenav-pages" <?php
                            if ((int) $total_pages <= 1) {
                                echo 'style="display:none;"';
                            }
                            ?>>
                                <span class="pagination-links">
                                    <a class="first-page <?php if ($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=user_settings_menu&paged=1&display=' . $display . '&txtsearch=' . $search; ?>" title="Go to the first page">&laquo;</a>
                                    <a class="prev-page <?php if ($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=user_settings_menu&paged=' . $prev_page . '&display=' . $display . '&txtsearch=' . $search; ?>" title="Go to the previous page">&lsaquo;</a>
                                    <span class="paging-input">
                                        <input class="current-page" type="text" size="1" value="<?php echo $paged; ?>" name="paged" title="Current page"> of
                                        <span class="total-pages"><?php echo $total_pages; ?></span>
                                    </span>
                                    <a class="next-page <?php if ($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=user_settings_menu&paged=' . $next_page . '&display=' . $display . '&txtsearch=' . $search; ?>" title="Go to the next page">&rsaquo;</a>
                                    <a class="last-page <?php if ($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=user_settings_menu&paged=' . $total_pages . '&display=' . $display . '&txtsearch=' . $search; ?>" title="Go to the last page">&raquo;</a>
                                </span>
                            </div>
                        </div>
                        <!-- top pagination end -->
                    </div>
                    <!-- display users details start -->
                    <table class="widefat post fixed striped" cellspacing="0" style="
                    <?php
                    if ($display == "users") {
                        echo 'display:table';
                    }
                    if ($display == "roles") {
                        echo 'display:none';
                    }
                    ?>">
                        <thead>
                            <tr>
                                <th scope="col" class="check-column"><input type="checkbox" /></th>
                                <th width="50px" scope="col"><?php _e('No.', 'wp_user_log'); ?></th>
                                <th scope="col"><?php _e('User', 'wp_user_log'); ?></th>
                                <th scope="col"><?php _e('First name', 'wp_user_log'); ?></th>
                                <th scope="col"><?php _e('Last name', 'wp_user_log'); ?></th>
                                <th scope="col" class="role-width"><?php _e('Role', 'wp_user_log'); ?></th>
                                <th scope="col" class="email-id-width"><?php _e('Email address', 'wp_user_log'); ?></th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th scope="col" class="check-column"><input type="checkbox" /></th>
                                <th width="50px" scope="col"><?php _e('No.', 'wp_user_log'); ?></th>
                                <th scope="col"><?php _e('User', 'wp_user_log'); ?></th>
                                <th scope="col"><?php _e('First name', 'wp_user_log'); ?></th>
                                <th scope="col"><?php _e('Last name', 'wp_user_log'); ?></th>
                                <th scope="col" class="role-width"><?php _e('Role', 'wp_user_log'); ?></th>
                                <th scope="col" class="email-id-width"><?php _e('Email address', 'wp_user_log'); ?></th>
                            </tr>
                        </tfoot>
                        <tbody>
                            <?php
                            if ($get_user_data) {
                                $srno = 1 + $offset;
                                foreach ($get_user_data as $data) {
                                    $u_d = get_userdata($data->ID);
                                    $first_name = $u_d->user_firstname;
                                    $last_name = $u_d->user_lastname;
                                    ?>
                                    <tr>
                                        <?php
                                        $user_enable = get_option('enable_user_list');
                                        $checked = '';
                                        if ($user_enable != ""):
                                            if (in_array($data->user_login, $user_enable)) {
                                                $checked = "checked=checked";
                                            }
                                        endif;
                                        ?>
                                        <th scope="row" class="check-column"><input type="checkbox" <?php echo $checked; ?> name="usersID[]" value="<?php echo $data->user_login; ?>" /></th>
                                        <td><?php
                                            echo $srno;
                                            $srno++;
                                            ?>
                                        </td>
                                        <td><?php echo ucfirst($data->user_login); ?></td>
                                        <td><?php echo ucfirst($first_name); ?></td>
                                        <td><?php echo ucfirst($last_name); ?></td>
                                        <td><?php
                                            $user = new WP_User($data->ID);
                                            if (!empty($user->roles) && is_array($user->roles)) {
                                                foreach ($user->roles as $role)
                                                    echo ucfirst($role);
                                            }
                                            ?></td>
                                        <td class="email-id-width"><?php echo $data->user_email; ?></td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr class="no-items">';
                                echo '<td class="colspanchange" colspan="4">' . __('No record found.', 'wp_user_log') . '</td>';
                                echo '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                    <!-- display users details end -->
                    <!-- display roles details start -->
                    <table class="widefat post fixed sol-display-roles striped" cellspacing="0" style="
                    <?php
                    if ($display == "users") {
                        echo 'display:none';
                    }
                    if ($display == "roles") {
                        echo 'display:table';
                    }
                    ?>">
                        <thead>
                            <tr>
                                <th scope="col" class="check-column"><input type="checkbox" /></th>
                                <th scope="col"><?php _e('No.', 'wp_user_log'); ?></th>
                                <th scope="col"><?php _e('Role', 'wp_user_log'); ?></th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th scope="col" class="check-column"><input type="checkbox" /></th>
                                <th scope="col"><?php _e('No.', 'wp_user_log'); ?></th>
                                <th scope="col"><?php _e('Role', 'wp_user_log'); ?></th>
                            </tr>
                        </tfoot>
                        <tbody>
                            <?php
                            if ($get_data) {
                                $srno = 1 + $offset;
                                foreach ($get_data as $data) {
                                    $final_roles = unserialize($data->meta_value);
                                    $final_roles = key($final_roles);
                                    ?>
                                    <tr>
                                        <?php
                                        $role_enable = get_option('enable_role_list');
                                        $checked = '';
                                        if ($role_enable != ""):
                                            if (in_array($final_roles, $role_enable)) {
                                                $checked = "checked=checked";
                                            }
                                        endif;
                                        ?>
                                        <th scope="row" class="check-column"><input type="checkbox" <?php echo $checked; ?> name="rolesID[]" value="<?php echo $final_roles; ?>" /></th>
                                        <td><?php
                                            echo $srno;
                                            $srno++;
                                            ?></td>
                                        <td><?php echo ucfirst($final_roles); ?></td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr class="no-items">';
                                echo '<td class="colspanchange" colspan="4">' . __('No record found.', 'wp_user_log') . '</td>';
                                echo '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                    <!-- display roles details end -->
                    <!-- bottom pagination start -->
                    <div class="tablenav top <?php if ($display == 'roles') echo 'sol-display-roles'; ?>">
                        <div class="tablenav-pages">
                            <span class="displaying-num"><?php echo $items; ?></span>
                            <div class="tablenav-pages" <?php
                            if ((int) $total_pages <= 1) {
                                echo 'style="display:none;"';
                            }
                            ?>>
                                <span class="pagination-links">
                                    <a class="first-page <?php if ($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=user_settings_menu&paged=1&display=' . $display . '&txtsearch=' . $search; ?>" title="Go to the first page">&laquo;</a>
                                    <a class="prev-page <?php if ($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=user_settings_menu&paged=' . $prev_page . '&display=' . $display . '&txtsearch=' . $search; ?>" title="Go to the previous page">&lsaquo;</a>
                                    <span class="paging-input">
                                        <input class="current-page" type="text" size="1" value="<?php echo $paged; ?>" name="paged" title="Current page"> of
                                        <span class="total-pages"><?php echo $total_pages; ?></span>
                                    </span>
                                    <a class="next-page <?php if ($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=user_settings_menu&paged=' . $next_page . '&display=' . $display . '&txtsearch=' . $search; ?>" title="Go to the next page">&rsaquo;</a>
                                    <a class="last-page <?php if ($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=user_settings_menu&paged=' . $total_pages . '&display=' . $display . '&txtsearch=' . $search; ?>" title="Go to the last page">&raquo;</a>
                                </span>
                            </div>
                        </div>
                    </div>
                    <!-- bottom pagination end -->
                    <p class="submit">
                        <input id="submit" class="button button-primary" type="submit" value="<?php esc_attr_e('Save Changes', 'wp_user_log'); ?>" name="saveLogin">
                    </p>
                </div>
            </form>
            <div class="user-activity-ad-block">
                <div class="ual-help">
                    <h2><?php _e('Help to improve this plugin!', 'wp_user_log'); ?></h2>
                    <span><?php _e('Enjoyed this plugin?', 'wp_user_log'); ?></span>
                    <span><?php _e('You can help by', 'wp_user_log'); ?>
                        <a href="https://wordpress.org/support/view/plugin-reviews/user-activity-log" target="_blank">
                            <?php _e(' rating this plugin on wordpress.org', 'wp_user_log'); ?>
                        </a>                    
                    </span>
                    <div class="ual-total-download">
                        <?php _e('Downloads:', 'wp_user_log'); ?><?php get_total_downloads_user_activity_log_plguin(); ?>
                        <?php
                        $wp_version = get_bloginfo('version');
                        if ($wp_version > 3.8) {
                            wp_custom_star_rating_user_activity_log();
                        }
                        ?>
                    </div>                    
                </div>
                <div class="ual-support">
                    <h3><?php _e('Need Support?', 'wp_user_log'); ?></h3>
                    <span><?php _e('Check out the', 'wp_user_log') ?>
                        <a href="https://wordpress.org/plugins/user-activity-log/faq/" target="_blank"><?php _e('FAQs', 'wp_user_log'); ?></a>
                        <?php _e('and', 'wp_user_log') ?>
                        <a href="https://wordpress.org/support/plugin/user-activity-log" target="_blank"><?php _e('Support Forums', 'wp_user_log') ?></a>
                    </span>
                </div>
                <div class="useful_plugins">
                    <h3><?php _e('Our Other Works', 'wp_user_log'); ?></h3>
                    <ul class="plugins_list">
                        <li>
                            <span class="plugin_img">
                                <img src="http://www.solwininfotech.com/wp-content/uploads/2015/10/avartan-slider-300x300.png" / >
                            </span>
                            <span>
                                <a href="http://www.solwininfotech.com/product/wordpress-plugins/avartan-slider/" target="_blank"><?php _e('Avartan Premium Slider Plugin', 'wp_user_log'); ?></a>
                            </span>
                            <span class="plugins_content">
                                <?php _e('Avartan Slider is a great way to create stunning text, image and video slider for your WordPress websites.', 'wp_user_log'); ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }

endif;

/**
 * Email settings
 */
if (!function_exists('ual_email_settings')):

    function ual_email_settings() {
        $active = $_GET['page'];
        $msg = "";
        add_option('enable_email');
        add_option('to_email');
        add_option('from_email');
        add_option('email_message');
        global $current_user;
        get_currentuserinfo();
        $to_email = get_option('to_email') ? get_option('to_email') : $current_user->user_email;
        $from_email = get_option('from_email') ? get_option('from_email') : get_option('admin_email');
        $emailEnable = get_option('enable_email') ? get_option('enable_email') : 0;
        $user_details = "[user_details]";
        $mail_msg = get_option('email_message') ? get_option('email_message') : __('Hi, following user is logged in your site', 'wp_user_log') . " \n$user_details";
        if (isset($_POST['btnsolEmail'])) {
            $to_email = $_POST['sol-mail-to'];
            $from_email = $_POST['sol-mail-from'];
            $mail_msg = ual_test_input($_POST['sol-mail-msg']);
            $emailEnable = $_POST['emailEnable'];
            update_option('enable_email', $emailEnable);
            if (isset($_POST['emailEnable'])) {
                if ($_POST['emailEnable'] == '1') {
                    if ($mail_msg == "") {
                        $msg = __("Please enter message", 'wp_user_log');
                    }
                    if ($to_email == "" || $from_email == "") {
                        $msg = __("Please enter the email address", 'wp_user_log');
                    }
                    if (!filter_var($to_email, FILTER_VALIDATE_EMAIL) || !filter_var($from_email, FILTER_VALIDATE_EMAIL) || !is_email($to_email) || !is_email($from_email)) {
                        $msg = __("Please enter valid email address", 'wp_user_log');
                    } else {
                        update_option('to_email', $to_email);
                        update_option('from_email', $from_email);
                        update_option('email_message', $mail_msg);
                    }
                }
            }
        }
        ?>
        <div class="wrap">
            <h2><?php _e('Email Settings', 'wp_user_log'); ?></h2>
            <?php
            if ($msg != "") {
                ?>
                <div id="message" class="updated notice notice-success is-dismissible below-h2 error">
                    <p><?php echo $msg; ?></p>
                </div>
            <?php }
            ?>
            <div class="tab_parent_parent">
                <div class="tab_parent">
                    <ul>
                        <li><a href="?page=general_settings_menu" class="<?php
                            if ($active == 'general_settings_menu') {
                                echo 'current';
                            }
                            ?>"><?php _e('General', 'wp_user_log'); ?></a></li>
                        <li><a href="?page=user_settings_menu" class="<?php
                            if ($active == 'user_settings_menu') {
                                echo 'current';
                            }
                            ?>"><?php _e('Users/Roles', 'wp_user_log'); ?></a></li>
                        <li><a href="?page=email_settings_menu" class="<?php
                            if ($active == 'email_settings_menu') {
                                echo 'current';
                            }
                            ?>"><?php _e('Email', 'wp_user_log'); ?></a></li>
                    </ul>
                </div>
            </div>
            <form method="POST" class="sol-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?" . $_SERVER['QUERY_STRING']); ?>">
                <div class="sol-box-border">
                    <h3 class="sol-header-text"><?php _e('Email', 'wp_user_log'); ?></h3>
                    <p class="margin_bottom_30"><?php _e('This email will be sent upon login of selected users/roles.', 'wp_user_log'); ?></p>
                    <table class="sol-email-table" cellspacing="0">
                        <tr>
                            <th><?php _e('Enable?', 'wp_user_log'); ?></th>
                            <td>
                                <input type="radio" <?php checked($emailEnable, "1"); ?> value="1" name="emailEnable"><?php _e('Yes', 'wp_user_log'); ?>
                                <input type="radio" <?php checked($emailEnable, "0"); ?> value="0" name="emailEnable"><?php _e('No', 'wp_user_log'); ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('From Email', 'wp_user_log'); ?></th>
                            <td>
                                <input type="email" name="sol-mail-from" value="<?php echo $from_email; ?>">
                                <p class="description"><?php _e('The source Email address', 'wp_user_log'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('To Email', 'wp_user_log'); ?></th>
                            <td>
                                <input type="email" name="sol-mail-to" value="<?php echo $to_email; ?>">
                                <p class="description"><?php _e('The Email address notifications will be sent to', 'wp_user_log'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Message', 'wp_user_log'); ?></th>
                            <td>
                                <textarea cols="50" name="sol-mail-msg" rows="5"><?php echo $mail_msg; ?></textarea>
                                <p class="description"><?php _e('Customize the message as per your requirement', 'wp_user_log'); ?></p>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input class="button button-primary" type="submit" value="<?php esc_attr_e('Save Changes', 'wp_user_log'); ?>" name="btnsolEmail">
                    </p>
                </div>
            </form>
            <div class="user-activity-ad-block">
                <div class="ual-help">
                    <h2><?php _e('Help to improve this plugin!', 'wp_user_log'); ?></h2>
                    <span><?php _e('Enjoyed this plugin?', 'wp_user_log'); ?></span>
                    <span><?php _e(' You can help by', 'wp_user_log'); ?>
                        <a href="https://wordpress.org/support/view/plugin-reviews/user-activity-log" target="_blank">
                            <?php _e(' rating this plugin on wordpress.org', 'wp_user_log'); ?>
                        </a>                    
                    </span>
                    <div class="ual-total-download">
                        <?php _e('Downloads:', 'wp_user_log'); ?><?php get_total_downloads_user_activity_log_plguin(); ?>
                        <?php
                        $wp_version = get_bloginfo('version');
                        if ($wp_version > 3.8) {
                            wp_custom_star_rating_user_activity_log();
                        }
                        ?>
                    </div>                    
                </div>
                <div class="ual-support">
                    <h3><?php _e('Need Support?', 'wp_user_log'); ?></h3>
                    <span><?php _e('Check out the', 'wp_user_log') ?>
                        <a href="https://wordpress.org/plugins/user-activity-log/faq/" target="_blank"><?php _e('FAQs', 'wp_user_log'); ?></a>
                        <?php _e('and', 'wp_user_log') ?>
                        <a href="https://wordpress.org/support/plugin/user-activity-log" target="_blank"><?php _e('Support Forums', 'wp_user_log') ?></a>
                    </span>
                </div>
                <div class="useful_plugins">
                    <h3><?php _e('Our Other Works', 'wp_user_log'); ?></h3>
                    <ul class="plugins_list">
                        <li>
                            <span class="plugin_img">
                                <img src="http://www.solwininfotech.com/wp-content/uploads/2015/10/avartan-slider-300x300.png" / >
                            </span>
                            <span>
                                <a href="http://www.solwininfotech.com/product/wordpress-plugins/avartan-slider/" target="_blank"><?php _e('Avartan Premium Slider Plugin', 'wp_user_log'); ?></a>
                            </span>
                            <span class="plugins_content">
                                <?php _e('Avartan Slider is a great way to create stunning text, image and video slider for your WordPress websites.', 'wp_user_log'); ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }

endif;

add_action('wp_login', 'ual_send_email', 99);
/**
 * Send email when selected user login
 * 
 * @param string $login current username when login
 */
if (!function_exists('ual_send_email')) {
    function ual_send_email($login) {
        $user = get_user_by('login', $login);
        $user_ID = $user->ID;
        $current_user1 = get_userdata($user_ID);
        $current_user = !empty($current_user1->user_login) ? $current_user1->user_login : "-";
        $enable_unm = get_option('enable_user_list');
        for ($i = 0; $i < count($enable_unm); $i++) {
            if ($enable_unm[$i] == $current_user) {
                $to_email = get_option('to_email');
                $from_email = get_option('from_email');
                $ip = $_SERVER['REMOTE_ADDR'];
                $firstname = ucfirst($current_user1->user_firstname);
		$lastname = ucfirst($current_user1->user_lastname);
                $user_firstnm = !empty($firstname) ? ucfirst($firstname) : "-";
                $user_lastnm = !empty($lastname) ? $lastname : "-";
                $user_email = !empty($current_user1->user_email) ? $current_user1->user_email : "-";
                $user_reg = !empty($current_user1->user_registered) ? $current_user1->user_registered : "-";
                $current_user = ucfirst($current_user);
                $user_details = "<table cellspacing='0' border='1px solid #ccc' class='sol-msg' style='margin-top:30px'>
                                <tr>
                                    <td style='padding:5px 10px;'>" . __('Username', 'wp_user_log') . "</td>
                                    <td style='padding:5px 10px;'>" . __('Firstname', 'wp_user_log') . "</td>
                                    <td style='padding:5px 10px;'>" . __('Lastname', 'wp_user_log') . "</td>
                                    <td style='padding:5px 10px;'>" . __('Email', 'wp_user_log') . "</td>
                                    <td style='padding:5px 10px;'>" . __('Date Time', 'wp_user_log') . "</td>
                                    <td style='padding:5px 10px;'>" . __('IP address', 'wp_user_log') . "</td>
                                </tr>
                                <tr>
                                    <td style='padding:5px 10px;'>$current_user</td>
                                    <td style='padding:5px 10px;'>$user_firstnm</td>
                                    <td style='padding:5px 10px;'>$user_lastnm</td>
                                    <td style='padding:5px 10px;'>$user_email</td>
                                    <td style='padding:5px 10px;'>$user_reg</td>
                                    <td style='padding:5px 10px;'>$ip</td>
                                </tr>
                            </table>";
                $mail_msg = __('Hi, following user is logged in your site', 'wp_user_log') . " \n \n$user_details";
                if ($to_email != "" && $mail_msg != "" && $from_email != "") {

                    $headers = "From: " . strip_tags($from_email) . "\r\n";
                    $headers .= "Reply-To: " . strip_tags($from_email) . "\r\n";
                    $headers .= "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
                    wp_mail($to_email, __('User Login Notification', 'wp_user_log'), $mail_msg, $headers);
                }
            }
        }
    }

}

add_action('user_register', 'ual_enable_user_notification_at_login');
/**
 * Enable user notification of email at login
 * 
 * @param int $user_id user ID
 */
if (!function_exists('ual_enable_user_notification_at_login')) {

    function ual_enable_user_notification_at_login($user_id) {
        $user_info = get_userdata($user_id);
        $user_role = $user_info->roles[0];
        $user_role_enable = get_option('enable_role_list');
        $user_enabled = get_option('enable_user_list');
        for ($i = 0; $i < count($user_role_enable); $i++) {
            if ($user_role_enable[$i] == $user_role) {
                array_push($user_enabled, $user_info->user_login);
                update_option('enable_user_list', $user_enabled);
            }
        }
    }

}

/**
 * Get rating star and total downloads of current plugin
 */
$wp_version = get_bloginfo('version');
if ($wp_version > 3.8) {
    if (!function_exists('wp_custom_star_rating_user_activity_log')) {

        function wp_custom_star_rating_user_activity_log($args = array()) {
            $plugins = $response = "";
            $args = array(
                'author' => 'solwininfotech',
                'fields' => array(
                    'downloaded' => true,
                    'downloadlink' => true
                )
            );
            // Make request and extract plug-in object. Action is query_plugins
            $response = wp_remote_post(
                    'http://api.wordpress.org/plugins/info/1.0/', array(
                'body' => array(
                    'action' => 'query_plugins',
                    'request' => serialize((object) $args)
                )
                    )
            );
            if (!is_wp_error($response)) {
                $returned_object = unserialize(wp_remote_retrieve_body($response));
                $plugins = $returned_object->plugins;
            }
            $current_slug = 'user-activity-log';
            if ($plugins) {
                foreach ($plugins as $plugin) {
                    if ($current_slug == $plugin->slug) {
                        $rating = $plugin->rating * 5 / 100;
                        if ($rating > 0) {
                            $args = array(
                                'rating' => $rating,
                                'type' => 'rating',
                                'number' => $plugin->num_ratings,
                            );
                            wp_star_rating($args);
                        }
                    }
                }
            }
        }

    }
}

/**
 * Get total downloads of current plugin
 */
if (!function_exists('get_total_downloads_user_activity_log_plguin')) {

    function get_total_downloads_user_activity_log_plguin() {
        // Set the arguments. For brevity of code, I will set only a few fields.        
        $plugins = $response = "";
        $args = array(
            'author' => 'solwininfotech',
            'fields' => array(
                'downloaded' => true,
                'downloadlink' => true
            )
        );
        // Make request and extract plug-in object. Action is query_plugins
        $response = wp_remote_post(
                'http://api.wordpress.org/plugins/info/1.0/', array(
            'body' => array(
                'action' => 'query_plugins',
                'request' => serialize((object) $args)
            )
                )
        );
        if (!is_wp_error($response)) {
            $returned_object = unserialize(wp_remote_retrieve_body($response));
            $plugins = $returned_object->plugins;
        } else {
            
        }
        $current_slug = 'user-activity-log';
        if ($plugins) {
            foreach ($plugins as $plugin) {
                if ($current_slug == $plugin->slug) {
                    if ($plugin->downloaded) {
                        ?>
                        <span class="total-downloads">
                            <span class="download-number"><?php echo $plugin->downloaded; ?></span>
                        </span>
                        <?php
                    }
                }
            }
        }
    }

}

/**
 * General settings
 */
if (!function_exists('ual_general_settings')) {

    function ual_general_settings() {
        $active = $_GET['page'];
        global $wpdb;
        $table_nm = $wpdb->prefix . "user_activity";
        if (isset($_GET['db'])) {
            $wpdb->query('TRUNCATE ' . $table_nm);
        }
        if (isset($_POST['submit_display'])) {
            $time_ago = $_POST['logdel'];
            $wpdb->query("DELETE FROM wp_user_activity WHERE modified_date < NOW() - INTERVAL $time_ago DAY");
        }
        ?>
        <div class="wrap">
            <h2><?php _e('General Settings', 'wp_user_log'); ?></h2>
            <?php if(isset($_SESSION['success_msg'])) { ?>
            <div class="success_msg"><?php echo $_SESSION['success_msg'];
            unset($_SESSION['success_msg']);
            ?></div>
            <?php } ?>
            <div class="tab_parent_parent">
                <div class="tab_parent">
                    <ul>
                        <li><a href="?page=general_settings_menu" class="<?php
                            if ($active == 'general_settings_menu') {
                                echo 'current';
                            }
                            ?>"><?php _e('General', 'wp_user_log'); ?></a></li>
                        <li><a href="?page=user_settings_menu" class="<?php
                            if ($active == 'user_settings_menu') {
                                echo 'current';
                            }
                            ?>"><?php _e('Users/Roles', 'wp_user_log'); ?></a></li>
                        <li><a href="?page=email_settings_menu" class="<?php
                            if ($active == 'email_settings_menu') {
                                echo 'current';
                            }
                            ?>"><?php _e('Email', 'wp_user_log'); ?></a></li>
                    </ul>
                </div>
            </div>
            <form class="sol-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?" . $_SERVER['QUERY_STRING']); ?>" method="POST" name="general_setting_form">
                <div class="sol-box-border">
                    <h3 class="sol-header-text"><?php _e('Display Option', 'wp_user_log'); ?></h3>
                    <p class="margin_bottom_30"><?php _e('There are some basic options for display User Action Log', 'wp_user_log'); ?></p>
                    <table class="sol-email-table">
                        <tr>
                            <th><?php _e('Keep logs for', 'wp_user_log'); ?></th>
                            <td>
                                <input type="number" step="1" min="1" value="30" name="logdel">
                                <p><?php _e('Maximum number of days to keep activity log. Leave blank to keep activity log forever (not recommended).', 'wp_user_log'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Delete Log Activities', 'wp_user_log'); ?></th>
                            <td>
                                <a href="?page=general_settings_menu&db=reset" onClick="return confirm('<?php _e('Are you sure want to Reset Database?', 'wp_user_log'); ?>');"><?php _e('Reset Database', 'wp_user_log'); ?></a>
                                <p><?php _e('Warning: Clicking this will delete all activities from the database.', 'wp_user_log'); ?></p>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input id="submit" class="button button-primary" type="submit" value="<?php esc_attr_e('Save Changes', 'wp_user_log'); ?>" name="submit_display">
                    </p>
                </div>
            </form>
            <div class="user-activity-ad-block">
                <div class="ual-help">
                    <h2><?php _e('Help to improve this plugin!', 'wp_user_log'); ?></h2>
                    <span><?php _e('Enjoyed this plugin?', 'wp_user_log'); ?></span>
                    <span><?php _e('You can help by', 'wp_user_log'); ?>
                        <a href="https://wordpress.org/support/view/plugin-reviews/user-activity-log" target="_blank">
                            <?php _e(' rating this plugin on wordpress.org', 'wp_user_log'); ?>
                        </a>                    
                    </span>
                    <div class="ual-total-download">
                        <?php _e('Downloads:', 'wp_user_log'); ?><?php get_total_downloads_user_activity_log_plguin(); ?>
                        <?php
                        $wp_version = get_bloginfo('version');
                        if ($wp_version > 3.8) {
                            wp_custom_star_rating_user_activity_log();
                        }
                        ?>
                    </div>                    
                </div>
                <div class="ual-support">
                    <h3><?php _e('Need Support?', 'wp_user_log'); ?></h3>
                    <span><?php _e('Check out the', 'wp_user_log') ?>
                        <a href="https://wordpress.org/plugins/user-activity-log/faq/" target="_blank"><?php _e('FAQs', 'wp_user_log'); ?></a>
                        <?php _e('and', 'wp_user_log') ?>
                        <a href="https://wordpress.org/support/plugin/user-activity-log" target="_blank"><?php _e('Support Forums', 'wp_user_log') ?></a>
                    </span>
                </div>
                <div class="useful_plugins">
                    <h3><?php _e('Our Other Works', 'wp_user_log'); ?></h3>
                    <ul class="plugins_list">
                        <li>
                            <span class="plugin_img">
                                <img src="http://www.solwininfotech.com/wp-content/uploads/2015/10/avartan-slider-300x300.png" />
                            </span>
                            <span>
                                <a href="http://www.solwininfotech.com/product/wordpress-plugins/avartan-slider/" target="_blank"><?php _e('Avartan Premium Slider Plugin', 'wp_user_log'); ?></a>
                            </span>
                            <span class="plugins_content">
                                <?php _e('Avartan Slider is a great way to create stunning text, image and video slider for your WordPress websites.', 'wp_user_log'); ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
    
}


/**
 * admin scripts
 */
if (!function_exists('ual_admin_scripts')) {
    function ual_admin_scripts() {
        $screen = get_current_screen();
        $plugin_data = get_plugin_data( WP_PLUGIN_DIR.'/user-activity-log/user_activity_log.php', $markup = true, $translate = true );
        $current_version = $plugin_data['Version'];
        $old_version = get_option('ual_version');
        if($old_version != $current_version)
        {
            update_option('is_user_subscribed_cancled', '');
            update_option('ual_version',$current_version);
        }
        if(get_option('is_user_subscribed') != 'yes' && get_option('is_user_subscribed_cancled') != 'yes')
        {
            wp_enqueue_script( 'thickbox' );
            wp_enqueue_style( 'thickbox' );
            wp_register_script( 'custom_wp_admin_js', plugins_url('js/admin_script.js', __FILE__));
            wp_enqueue_script( 'custom_wp_admin_js' );
        }
    }    
}
add_action( 'admin_enqueue_scripts', 'ual_admin_scripts' );
/**
 * subscribe email form
 */
if(!function_exists('ual_subscribe_mail'))
{
    function ual_subscribe_mail()
    {
        if(session_id() == '') {
            session_start();
        }
        $customer_email = get_option('admin_email');
        $current_user = wp_get_current_user();
        $f_name = $current_user -> user_firstname;
        $l_name = $current_user -> user_lastname;
        if(isset($_POST['sbtEmail']))
        {
            $_SESSION['success_msg'] = 'Thank you for your subscription.';
            //Email To Admin
            update_option('is_user_subscribed', 'yes');
            $customer_email = trim($_POST['txtEmail']);
            $customer_name = trim($_POST['txtName']);
            //$to = 'info@solwininfotech.com';
            $to = 'khushbu@citycenter.com';
            $from = get_option('admin_email');

            $headers = "MIME-Version: 1.0;\r\n";
            $headers .= "From: " . strip_tags($from) . "\r\n";
            $headers .= "Content-Type: text/html; charset: utf-8;\r\n";
            $headers .= "X-Priority: 3\r\n";
            $headers .= "X-Mailer: PHP" . phpversion() . "\r\n";
            $subject = __('New user subscribed from Plugin - User Activity Log', 'wp_user_log');
            
            $body = '';

            ob_start();
            ?>
            <div style="background: #F5F5F5; border-width: 1px; border-style: solid; padding-bottom: 20px; margin: 0px auto; width: 750px; height: auto; border-radius: 3px 3px 3px 3px; border-color: #5C5C5C;">
                
                <div style="border: #FFF 1px solid; background-color: #ffffff !important; margin: 20px 20px 0;
                     height: auto; -moz-border-radius: 3px; padding-top: 15px;">
                    <div style="padding: 20px 20px 20px 20px; font-family: Arial, Helvetica, sans-serif;
                         height: auto; color: #333333; font-size: 13px;">
                        <div style="width: 100%;">
                            <strong><?php _e('Dear', 'wp_user_log'); ?></strong> <?php _e('Admin', 'wp_user_log'); ?> <?php _e('( User Activity Log plugin developer)','wp_user_log'); ?>,
                            <br />
                            <br />
                            <?php _e('Thank you for developing useful plugin.', 'wp_user_log'); ?>
                            <br />
                            <br />
                            I <?php echo $customer_name; ?> want to notify you that I have installed plugin on my <a href="<?php echo home_url(); ?>">website</a>. Also I want to subscribe to your newsletter, and I do allow you to enroll me to your free newsletter subscription to get update with new products, news, offers and updates.
                            <br />
                            <br />
                            <?php _e('I hope this will motivate you to develop more good plugins and expecting good support form your side.', 'wp_user_log'); ?>
                            <br />
                            <br />
                            <?php _e('Followining is details for newsletter subscription.', 'wp_user_log'); ?>
                            <br />
                            <br />
                            <div>
                                <table border='0' cellpadding='5' cellspacing='0' style="font-family: Arial, Helvetica, sans-serif; font-size: 13px;color: #333333;width: 100%;">
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <th style="padding: 8px 5px; text-align: left;width: 120px;">
                                            <?php _e('Website', 'wp_user_log'); ?><span style="float:right">:</span>
                                        </th>
                                        <td style="padding: 8px 5px;">
                                            <?php echo home_url(); ?>
                                        </td>
                                    </tr>
                                    <?php if($customer_name !='' )
                                    { ?>
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <th style="padding: 8px 5px; text-align: left;width: 120px;">
                                            <?php _e('Name', 'wp_user_log'); ?><span style="float:right">:</span>
                                        </th>
                                        <td style="padding: 8px 5px;">
                                            <?php echo $customer_name; ?>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <th style="padding: 8px 5px; text-align: left;width: 120px;">
                                            <?php _e('Email', 'wp_user_log'); ?><span style="float:right">:</span>
                                        </th>
                                        <td style="padding: 8px 5px;">
                                            <?php echo $customer_email; ?>
                                        </td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <th style="padding: 8px 5px; text-align: left; width: 120px;">
                                            <?php _e('Date', 'wp_user_log'); ?><span style="float:right">:</span>
                                        </th>
                                        <td style="padding: 8px 5px;">
                                            <?php echo date('d-M-Y  h:i  A'); ?>
                                        </td>
                                    </tr>                                
                                </table>
                                <br /><br />
                                <?php _e('Again Thanks you', 'wp_user_log'); ?>
                                <br />
                                <br />
                                <?php _e('Regards,', 'wp_user_log'); ?>
                                <br />
                                <?php echo $customer_name; ?>
                                <br />
                                <?php echo home_url(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            $body = ob_get_clean();
            wp_mail($to, $subject, $body, $headers);
        }
        if(get_option('is_user_subscribed') != 'yes' && get_option('is_user_subscribed_cancled') != 'yes')
        {
        ?>
        <div id="subscribe_widget" style="display:none;">
            <div class="subscribe_widget">
            <h3>Notify to plugin developer and subscribe to newsletter</h3>
            and stay up to date with latest updates, news, other products and offers by plugin developer.
            <form class='sub_form' name="frmSubscribe" method="post" action="<?php echo admin_url().'admin.php?page=general_settings_menu'; ?>">
                <div class="sub_row"><label>Your Name: </label><input placeholder="Your Name" name="txtName" type="text" value="<?php echo $f_name.' '.$l_name; ?>" /></div>
                <div class="sub_row"><label>Email Address: </label><input placeholder="Email Address" required name="txtEmail" type="email" value="<?php echo $customer_email; ?>" /></div>
                <div class="sub_row sub_row_checkbox"><input class="sub_checkbox" required="" type="checkbox" />Yes, I want to motivate plugin developer by notifying plugin developer about installation of "User activity log" plugin and also want to subscribe to their newsletter.</div>
                <input class="button button-primary" type="submit" name="sbtEmail" value="Notify & Subscribe" />                
            </form>
            </div>
        </div>
        <?php
        }
        if(get_option('is_user_subscribed') != 'yes' && get_option('is_user_subscribed_cancled') != 'yes' && ($_GET['page'] == 'general_settings_menu' || $_GET['page'] == 'user_action_log' || $_GET['page'] == 'user_settings_menu' || $_GET['page'] == 'email_settings_menu'))
        {
            ?>
            <a style="display:none" href="#TB_inline?width=600&height=300&inlineId=subscribe_widget" class="thickbox" id="subscribe_thickbox"></a>            
            <?php
        }
    }
}
add_action('init','ual_subscribe_mail',10);

/**
 * user cancel subscribe
 */
if(!function_exists('wp_ajax_close_tab'))
{
    function wp_ajax_close_tab()
    {
        update_option('is_user_subscribed_cancled', 'yes');
        exit();
    }
}
add_action('wp_ajax_close_tab','wp_ajax_close_tab');