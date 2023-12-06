<?php
/*
Plugin Name: Custom Profile Picture
Description: Allows users to set a custom profile picture.
Version: 1.0
Author: pervez iqbal
*/

// Add a new field for profile image in the user edit page
function add_profile_image_field($user) {
    ?>
    <h3><?php _e('Profile Image', 'custom-profile-picture'); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="profile_image"><?php _e('Profile Image', 'custom-profile-picture'); ?></label></th>
            <td>
                <?php
                $profile_image = get_user_meta($user->ID, 'profile_image', true);
                $profile_image_url = ($profile_image) ? wp_get_attachment_url($profile_image) : '';
                ?>
                <input type="hidden" name="profile_image" id="profile_image" value="<?php echo esc_attr($profile_image); ?>">
                <img src="<?php echo esc_url($profile_image_url); ?>" alt="<?php _e('Profile Image', 'custom-profile-picture'); ?>" style="max-width: 100px; height: auto;">
                <br>
                <button class="button button-secondary" id="upload_profile_image"><?php _e('Upload Profile Image', 'custom-profile-picture'); ?></button>
                <button class="button button-secondary" id="remove_profile_image"><?php _e('Remove Profile Image', 'custom-profile-picture'); ?></button>
            </td>
        </tr>
    </table>

    <script>
        jQuery(document).ready(function ($) {
            // Media uploader
            var customUploader = wp.media({
                title: '<?php _e("Choose or Upload Profile Image", "custom-profile-picture"); ?>',
                button: {
                    text: '<?php _e("Choose Image", "custom-profile-picture"); ?>'
                },
                multiple: false
            });

            // Handle image selection
            $('#upload_profile_image').on('click', function (e) {
                e.preventDefault();
                customUploader.open();
            });

            // Remove image
            $('#remove_profile_image').on('click', function (e) {
                e.preventDefault();
                $('#profile_image').val('');
                $('img').attr('src', '');
            });

            // When an image is selected, run a callback
            customUploader.on('select', function () {
                var attachment = customUploader.state().get('selection').first().toJSON();
                $('#profile_image').val(attachment.id);
                $('img').attr('src', attachment.url);
            });
        });
    </script>
    <?php
}
add_action('show_user_profile', 'add_profile_image_field');
add_action('edit_user_profile', 'add_profile_image_field');

// Save the profile image value when the user is updated
function save_profile_image_field($user_id) {
    if (current_user_can('edit_user', $user_id)) {
        update_user_meta($user_id, 'profile_image', $_POST['profile_image']);
    }
}
add_action('personal_options_update', 'save_profile_image_field');
add_action('edit_user_profile_update', 'save_profile_image_field');

// Add custom profile picture to user list in WordPress admin
function add_custom_profile_picture_to_user_list($column) {
    if ($column == 'user_login') {
        echo '<style>.column-user_login { width: 20%; }</style>';
    }
    if ($column == 'user_login' || $column == 'profile_image') {
        echo '<style>.column-profile_image { width: 10%; }</style>';
    }
}
add_action('admin_head-users.php', 'add_custom_profile_picture_to_user_list');

function display_custom_profile_picture_in_user_list($value, $column_name, $user_id) {
    if ($column_name == 'profile_image') {
        $profile_image = get_user_meta($user_id, 'profile_image', true);
        $profile_image_url = ($profile_image) ? wp_get_attachment_url($profile_image) : '';

        if ($profile_image_url) {
            return '<img src="' . esc_url($profile_image_url) . '" style="max-width: 40px; height: auto;" />';
        } else {
            return 'N/A';
        }
    }
    return $value;
}
add_filter('manage_users_custom_column', 'display_custom_profile_picture_in_user_list', 10, 3);

// Filter to modify the author's avatar
function custom_profile_picture_author_avatar($avatar, $id_or_email, $size, $default, $alt) {
    $user = false;

    if (is_numeric($id_or_email)) {
        $id = (int) $id_or_email;
        $user = get_user_by('id', $id);
    } elseif (is_object($id_or_email)) {
        if (!empty($id_or_email->user_id)) {
            $id = (int) $id_or_email->user_id;
            $user = get_user_by('id', $id);
        }
    } else {
        $user = get_user_by('email', $id_or_email);
    }

    if ($user && is_object($user)) {
        $profile_image = get_user_meta($user->ID, 'profile_image', true);
        $profile_image_url = ($profile_image) ? wp_get_attachment_url($profile_image) : '';

        if ($profile_image_url) {
            $avatar = '<img alt="' . esc_attr($alt) . '" src="' . esc_url($profile_image_url) . '" class="avatar avatar-' . esc_attr($size) . '" width="' . esc_attr($size) . '" height="' . esc_attr($size) . '" />';
        }
    }

    return $avatar;
}
add_filter('get_avatar', 'custom_profile_picture_author_avatar', 10, 5);
