# Wordpress InDesing Tagged text exporter
A Wordpress Plugin to export posts as InDesign Tagged text

## Background
I'm [Frank Bravo](https://www.linkedin.com/in/fabravo) and I am a [technologist](https://www.bravoitc.com). My regular full-time job is CTO at a hyper-local, non-profit media organization, the [Embarcadero Media Foundation](https://www.EmbarcaderoMediaFoundation.org) in the San Francisco Bay Area. In 2024, we are migrating our home-grown CMS to a Wordpress-based system. Since we still produce several print products but are digital first, we needed a way to export **InDesign Tagged Text** from Wordpress. Since there wasn't a good solution out there that I could find, I built my own.

## Setup
The basis for this code is what the [Embarcadero Media Foundation](https://www.EmbarcaderoMediaFoundation.org) needed to continue to export files. I believe I've made it functional enough to work for most needs. There is a series of setup values that need to be set at the top of the **/indesign-export/config.inc.php** file.

* **POSTS_PER_PAGE** -- Number of posts to show on the selection page (Default: *50*, set to -1 to include all posts)
* **WORDPRESS_ROLE_LEVEL** -- Level of Wordpress user who will see the menu item (Default: *'publish_posts'* for 'author' role and above)
* **WORDPRESS_POST_STATUS** -- Which type of posts should show up on the selection page (Default: *'publish', 'draft'* which will include all posts)
* **WORDPRESS_CATEGORIES_INCLUDED** -- List of categories to include (Default: *all categories [blank]* but format is *'real-estate, election*)
* **WORDPRESS_CATEGORIES_EXCLUDE_UNCATEGORIZED** -- Sets if to include uncategorized posts (Default *1*, the values are 0 (false, or include them) or 1 (true, or exclude them) 
* **WORDPRESS_TAGS_INCLUDED** -- List of tags to include, using the slug of the tag (Default: *all categories [blank]* but format is *'real-estate, print-ready*)
* **INDESIGN_HEADLINE_STYLE** -- InDesign Style for headlines (Default *<pstyle:24head>* but can leave blank if you don't want to include a style)
* **INDESIGN_PARAGRAPH_STYLE** -- InDesign Style for paragraphs (Default *<pstyle:text>* but can leave blank if you don't want to include a style)
* **INDESIGN_SUBHEAD_STYLE** -- InDesign Style for subheads (Default *<pstyle:12sub>* but can leave blank if you don't want to include a style)
* **INDESIGN_BYLINE_STYLE** -- InDesign Style for the byline (Default *<pstyle:byline>By * but can leave blank if you don't want to include a style)
* **INDESIGN_END_OF_STORY_ICON** -- InDesign Style for an icon at the end of the story (Default *<cstyle:endbullet>n<cstyle:>* but can leave blank if you don't want to include a style)

## Installation
* Download the repository
* Edit any variables in the /indesign-export/config.inc.php file and save
* Zip the /indesign-export/ directory (which will include two files)
* Upload the zipped file to Wordpress

You will see a new menu item named "InDesign Exporter".

## Update plugin
This plugin needs to be updated manually on your site for now. It's something like 60 days to get it approved into the library of automatically updated plugins. This is why I've separated out the config information. 

To update the plugin, you'd just need to open the *Plugin File Editor*, select the correct plugin from the pull-down menu and then the correct file (indesign-export.php). Delete what's there and copy in the new code. If there are new config options, you may have to manually add those to the config.inc.php file, but I'll note that within the change logs.

It sounds hard, but it's really not. 

## Support
If you find something not working within this plugin, please feel free to email me at **support [@] bravoitc.com**. 

## Versions
v. 1.2.2
  * added ability to select tags of posts to include
  
v. 1.2.1
  * added ability to select tags of posts to include
  * added aditional variables to config

v. 1.2
  * added ability to select categories of posts to include
  * added ability to exclue uncategorized posts
  * added link to post in list of posts

v. 1.1
  * added ability to change the role where the option is available in the menu
  * added variable to select type of post to show in the list (published and/or draft)
  * added publish status and date to list of posts

v. 1.0
  * First fully working version
  
  
##### Credits
Thanks to CP for a starter in the convert_for_print() function! ChatGPT aided in the creation of this plugin. 
