<?php
/*
 * Configuration file for InDesign Tagged Text Exporter
*/

// Settings
define('POSTS_PER_PAGE', 50); // -1 to include all posts
define('WORDPRESS_ROLE_LEVEL', "publish_posts"); // 'publish_posts' for 'author' role and above
define('WORDPRESS_POST_STATUS', array('publish', 'draft')); // Include both published and draft posts
define('WORDPRESS_CATEGORIES_INCLUDED', ''); // list of categories ('real estate, print ready') or blank for all 
define('WORDPRESS_CATEGORIES_EXCLUDE_UNCATEGORIZED', '1'); // values are 0 (false, or include them) or 1 (true, or exclude them) 
define('INDESIGN_HEADLINE_STYLE', "<pstyle:24head>");
define('INDESIGN_INITIAL_PARAGRAPH_STYLE', "<pstyle:dropcap>");
define('INDESIGN_PARAGRAPH_STYLE', "<pstyle:text>");
define('INDESIGN_SUBHEAD_STYLE', "<pstyle:12sub>");
define('INDESIGN_BYLINE_STYLE', "<pstyle:byline>By ");
define('INDESIGN_PULLQUOTE_STYLE', "<pstyle:pullquote>");
define('INDESIGN_PULLQUOTE_STYLE_NAME', "<pstyle:pullquotename>");
define('INDESIGN_END_OF_STORY_ICON', "<cstyle:endbullet>n<cstyle:>");
