<?php
/*
Plugin Name: Frank's Super Cool InDesign Tagged Text Exporter
Description: Export selected posts as Adobe InDesign tagged text documents.
Version: 1.0
Author: Frank A. Bravo
*/

// Add a custom menu item in the admin panel
add_action('admin_menu', 'indesign_export_menu');

function indesign_export_menu() {
    add_menu_page(
        'InDesign Export',
        'InDesign Export',
        'publish_posts', // Change this line to 'publish_posts' for 'author' role and above
        'indesign-export',
        'indesign_export_page'
    );
}

// Callback function to display the export page
function indesign_export_page() {
    ?>
    <div class="wrap">
        <h2>InDesign Export</h2>
        <?php
        if (isset($_GET['exported_filename'])) {
            echo '<div class="updated"><p>File exported: ' . esc_html($_GET['exported_filename']) . '</p></div>';
        }
        ?>
        <form method="post" action="">
            <p>
                <input type="submit" class="button-primary" name="export_posts" value="Export Selected Posts">
            </p>
            <?php
            $args = array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => 20, // Adjust as needed
            );

            $posts = get_posts($args);

            foreach ($posts as $post) {
                ?>
                <label>
                    <input type="checkbox" name="export_post[]" value="<?php echo esc_attr($post->ID); ?>">
                    <?php echo esc_html($post->post_title); ?>
                </label><br>
                <?php
            }
            ?>
        </form>
    </div>

    <?php

    // Handle form submission
    if (isset($_POST['export_posts'])) {
        if (isset($_POST['export_post']) && is_array($_POST['export_post'])) {
            $selected_post_id = absint(current($_POST['export_post']));
            indesign_export_post($selected_post_id);
        } else {
            echo '<p>No posts selected for export.</p>';
        }
    }
}

// Function to export a single post as Adobe InDesign tagged text document
function indesign_export_post($post_id) {
    $post = get_post($post_id);

    if (!$post) {
        echo '<p>Error: Post not found.</p>';
        return;
    }

    // Get the post content without applying filters
    $content = $post->post_content;
    $filename = sanitize_title($post->post_title) . '.txt';

    // Extract the author information
    $author = get_the_author_meta('display_name', $post->post_author);

    // Remove images (figures) and captions from the content
    $content = remove_images_and_captions($content);

    // Extract the content of <h4> if it exists
    preg_match('/<h4[^>]*>(.*?)<\/h4>/', $content, $h4_matches);
    $h4_content = isset($h4_matches[1]) ? $h4_matches[1] : '';

    // Remove the extracted <h4> content from the main content
    $content = str_replace($h4_matches[0], '', $content);

    // Apply transformations to the content
    $content = preg_replace('/<!--.*?-->/', '', $content); // Remove all HTML comments
    $content = str_replace('<p>', '<pstyle:text>', $content); // Replace <p> with <pstyle:text>
    $content = str_replace('<!-- wp:paragraph -->', '', $content); // Remove <!-- wp:paragraph -->
    $content = preg_replace('/<\/?[a-zA-Z]+>/', '', $content); // Strip other HTML tags
    $content = preg_replace('/<a[^>]+>/', '', $content); // Remove <a> tags
    $content = str_replace('&nbsp;', ' ', $content); // Replace &nbsp; with a space
    $content = str_replace('&amp;', '&', $content); // Replace &amp; with &
    $content = str_replace('&lt;', '<', $content); // Replace &lt; with <
    $content = str_replace('&gt;', '>', $content); // Replace &gt; with >

    // Remove extra line breaks between paragraphs
    $content = preg_replace('/\n{2,}/', "\n", $content);

    // Replace <h4> tags with <pstyle:12sub>
    $h4_content = preg_replace('/<h4[^>]*>/', '<pstyle:12sub>', $h4_content);

    // Remove closing </h4> from the replaced <h4>
    $h4_content = preg_replace('/<\/h4>/', '', $h4_content);

    // Create a temporary file
    $temp_file = tempnam(sys_get_temp_dir(), 'indesign_export_');
    file_put_contents($temp_file, "<ASCII-MAC>\r\n<pstyle:24head>" . $post->post_title . "\r\n<pstyle:12sub>" . $h4_content . "\r\n<pstyle:byline>" . $author . "\r\n" . $content . "<cstyle:endbullet>n<cstyle:>");

    // Send the file for download using JavaScript
    echo "<script>window.location.href = '" . plugins_url('download.php', __FILE__) . "?file=" . urlencode($temp_file) . "&filename=" . urlencode($filename) . "';</script>";

    // Clean up: delete the temporary file
    // unlink($temp_file);

    exit;
}

// Function to remove images (figures) and captions from the content
function remove_images_and_captions($content) {
    // Remove images (figures)
    $content = preg_replace('/<figure[^>]*>.*?<\/figure>/is', '', $content);

    // Remove captions
    $content = preg_replace('/<figcaption[^>]*>.*?<\/figcaption>/is', '', $content);

    return $content;
}
?>
