<?php
/*
 * Plugin Name: Frank's Super Cool InDesign Tagged Text Exporter
 * Plugin URI: https://github.com/fabravo/wordpress-indesign-tagged-text-exporter
 * Description: Export selected posts as Adobe InDesign tagged text documents.
 * Version: 1.2.1
 * Author: Frank A. Bravo
 * Author URI: https://www.LinkedIn.com/in/fabravo/
*/

include('config.inc.php'); // this is where all the settings are kept

// Add a custom menu item in the admin panel
add_action('admin_menu', 'indesign_export_menu');

function indesign_export_menu() {
    add_menu_page(
        'InDesign Exporter',
        'InDesign Exporter',
        WORDPRESS_ROLE_LEVEL, 
        'indesign-export',
        'indesign_export_page'
    );
}

// Callback function to display the export page
function indesign_export_page() {
    ?>
    <style>
        .publish-date, .post-status {
            margin-left: 10px; /* Adjust the margin as needed */
            color: #888; /* Adjust the color as needed */
        }
    </style>

    <div class="wrap">
        <h2>Frank's Super Cool InDesign Tagged Text Exporter</h2>
        <p>Select one post and press the export button.</p>
        <?php
        if (isset($_GET['exported_filename'])) {
            echo '<div class="updated"><p>File exported: ' . esc_html($_GET['exported_filename']) . '</p></div>';
        }
        ?>
        <form method="post" action="">
            <p>
                <input type="submit" class="button-primary" name="export_posts" value="Export Selected Post">
            </p>
            <?php
            $args = array(
                'post_type' => 'post',
                'post_status' => WORDPRESS_POST_STATUS, 
                'posts_per_page' => POSTS_PER_PAGE, 
            );

            if (WORDPRESS_CATEGORIES_INCLUDED)
            {
                $included_categories = explode(', ', WORDPRESS_CATEGORIES_INCLUDED);

                if (!empty($included_categories)) {
                    $args['tax_query'] = array(
                        array(
                            'taxonomy' => 'category',
                            'field'    => 'slug',
                            'terms'    => $included_categories,
                        ),
                    );
                }
            }
    
            if (WORDPRESS_CATEGORIES_EXCLUDE_UNCATEGORIZED)
            {
                // Get the excluded category IDs from the list
                $excluded_category = get_category_by_slug('uncategorized');
                if ($excluded_category) {
                    $args['category__not_in'] = array($excluded_category->term_id);
                }
            }

            $posts = get_posts($args);

            foreach ($posts as $post) {
                ?>
                <label>
                    <input type="checkbox" name="export_post[]" value="<?php echo esc_attr($post->ID); ?>">
                    <a href="<?php echo esc_url(get_permalink($post->ID)); ?>" target="_blank"><?php echo esc_html($post->post_title); ?></a>
                    <span class="post-status">(<?php echo esc_html($post->post_status); ?>)</span>
                    <span class="publish-date"><?php echo esc_html(get_the_date('F j, Y', $post->ID)); ?></span>
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
            foreach ($_POST['export_post'] as $selected_post_id) {
                $selected_post_id = absint($selected_post_id);
                indesign_export_post($selected_post_id);
            }
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
    $author = INDESIGN_BYLINE_STYLE . get_the_author_meta('display_name', $post->post_author) . "\r\n";

    // Remove images (figures) and captions from the content
    $content = indesign_remove_images_and_captions($content);

    // Extract the content of <h4> if it exists
    preg_match('/<h4[^>]*>(.*?)<\/h4>/', $content, $h4_matches);
    $subhead_content = isset($h4_matches[1]) ? $h4_matches[1] : '';

    // Remove the extracted <h4> content from the main content
    $content = str_replace($h4_matches[0], '', $content);

    // Apply transformations to the content
    $content = preg_replace('/<!--.*?-->/', '', $content); // Remove all HTML comments
    $content = str_replace('<blockquote class="wp-block-quote">', INDESIGN_PULLQUOTE_STYLE, $content); // Replace pullquote
    $content = str_replace('<cite>', INDESIGN_PULLQUOTE_STYLE_NAME, $content); // Replace pullquote name
    $content = str_replace('<p>', INDESIGN_PARAGRAPH_STYLE, $content); // Replace <p> with 
    $content = str_replace('<strong>', '<cTypeface:Bold>', $content); // Replace <strong> with <cTypeface:Bold>
    $content = str_replace('</strong>', '<cTypeface:>', $content); // Replace <strong> with <cTypeface:Bold>
    $content = str_replace('<em>', '<cTypeface:Italic>', $content); // Replace <em> with <cTypeface:Italic>
    $content = str_replace('</em>', '<cTypeface:>', $content); // Replace <em> with <cTypeface:Bold>
    $content = str_replace('<!-- wp:paragraph -->', '', $content); // Remove <!-- wp:paragraph -->
    $content = preg_replace('/<\/?[a-zA-Z]+>/', '', $content); // Strip other HTML tags
    $content = preg_replace('/<a[^>]+>/', '', $content); // Remove <a> tags
	$content = indesign_convert_for_print($content);
    $content = str_replace('&nbsp;', ' ', $content); // Replace &nbsp; with a space
    $content = str_replace('&amp;', '&', $content); // Replace &amp; with &
    $content = str_replace('&lt;', '<', $content); // Replace &lt; with <
    $content = str_replace('&gt;', '>', $content); // Replace &gt; with >
    $content = str_replace("\xC2\xA0", ' ', $content); // Replace non-breaking space UTF-8 character

    // Remove extra line breaks between paragraphs
    $content = preg_replace('/\n{2,}/', "\r\n", $content); // Preserve line breaks
    
    if ($subhead_content)
    {
        $subhead = INDESIGN_SUBHEAD_STYLE . indesign_convert_for_print($subhead_content) . "\r\n";
    }
    
    // Create a temporary file for each post
    $temp_file = tempnam(sys_get_temp_dir(), 'indesign_export_') . '.txt';
    file_put_contents($temp_file, "<ASCII-WIN>\r\n" . INDESIGN_HEADLINE_STYLE . indesign_convert_for_print($post->post_title) . "\r\n" . $subhead . $author . $content . INDESIGN_END_OF_STORY_ICON);

    // Send the file for download using JavaScript
    echo "<script>window.location.href = '" . plugins_url('download.php', __FILE__) . "?file=" . urlencode($temp_file) . "&filename=" . urlencode($filename) . "';</script>";

    // Clean up: delete the temporary file
    // unlink($temp_file);

    exit;
}

// Function to remove images (figures) and captions from the content
function indesign_remove_images_and_captions($content) {
    // Remove images (figures)
    $content = preg_replace('/<figure[^>]*>.*?<\/figure>/is', '', $content);

    // Remove captions
    $content = preg_replace('/<figcaption[^>]*>.*?<\/figcaption>/is', '', $content);

    return $content;
}

function indesign_convert_for_print($string) // For new web design, combines wiki_link and html_converter methods
{
    $patterns = array('/\[(http|ftp)?(s)?\:\/\/?([^"\s]+)\s?/i', '/([0-9a-zA-Z\s]*)\]/i'); //\.[a-zA-Z\s]{2,4}+
    //First search for [, http OR ftp, possibly followed by a single "s", ://,
    //anything except a " or space, followed by a single space
    //Second search any combo of numbers/characters/spaces followed by ]
    //Both searches are case-insensitive
    $replace = array('', '\1');
    $string = preg_replace($patterns, $replace, $string);

    $string = str_ireplace('--', '<0x2014>', $string);
    $string = str_ireplace('chr(151)', '<0x2014>', $string);
    $string = str_ireplace('•', '<CharStyle:bullet>n<CharStyle:>', $string);
    $string = str_ireplace('“', '"', $string);
    $string = str_ireplace('”', '"', $string);
    $string = str_ireplace('‘', "'", $string);
    $string = str_ireplace('’', "'", $string);
    $string = str_ireplace('–', '<0x2014>', $string);
    $string = str_ireplace('…', '...', $string);
    $string = str_ireplace('ā', '<0x0101>', $string);
    $string = str_ireplace('à', '<0x00E0>', $string);
    $string = str_ireplace('é', '<0x00E9>', $string);
    $string = str_ireplace('è', '<0x00E8>', $string);
    $string = str_ireplace('ê', '<0x00EA>', $string);
    $string = str_ireplace('É', '<0x00C9>', $string);
    $string = str_ireplace('È', '<0x00C8>', $string);
    $string = str_ireplace('í', '<0x00ED>', $string);
    $string = str_ireplace('ñ', '<0x00F1>', $string);
    $string = str_ireplace('Ñ', '<0x00D1>', $string);
    $string = str_ireplace('Ö', '<0x00F6>', $string);
    $string = str_ireplace('ô', '<0x00F4>', $string);
    $string = str_ireplace('ő', '<0x0151>', $string);
    $string = str_ireplace('û', '<0x00FB>', $string);
    $string = str_ireplace('Û', '<0x00DB>', $string);
    $string = str_ireplace('ú', '<0x00FA>', $string);

    return $string;
}
