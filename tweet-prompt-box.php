<?php
/*
Plugin Name: Tweet Box Prompt
Description: Add a small "Tweet this" call-to-action box that floats in the bottom right corner of the reader's browser. Customize the call to action message and choose a light or dark theme to match your site.
Author: Dan Hauk
Version: 0.1
Author URI: http://danhauk.com/
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

// functions for enqueueing scripts and styles
function tweet_prompt_box_enqueue_styles() {
	wp_enqueue_style( 'tweet-prompt-box', plugins_url('tweet-prompt-box.css', __FILE__) );
}

function tweet_prompt_box_enqueue_scripts() {
	wp_enqueue_script( 'tweet-prompt-box', plugins_url('tweet-prompt-box.js', __FILE__), array( 'jquery' ) );
}

function tweet_prompt_box_enqueue_admin_styles() {
    wp_enqueue_style( 'tweet-prompt-box-admin', plugins_url('tweet-prompt-box-admin.css', __FILE__) );
}

if ( is_admin() ) {
	add_action( 'admin_enqueue_scripts', 'tweet_prompt_box_enqueue_admin_styles' );
	add_action( 'admin_menu', 'tweet_prompt_box_menu' );
	add_action( 'admin_init', 'tweet_prompt_box_process' );
}
else {
	add_action( 'wp_enqueue_scripts', 'tweet_prompt_box_enqueue_styles' );
	add_action( 'wp_enqueue_scripts', 'tweet_prompt_box_enqueue_scripts' );
}

// This function adds the tweet prompt box below the content
add_action( 'wp_footer', 'tweet_prompt_box_popup' );
function tweet_prompt_box_popup() {
	if ( is_single() ) {
		if ( get_option( 'tweet_prompt_box_theme' ) == 'dark' ) {
			$dark_theme_class = ' class="tweet-prompt-box-dark"';
		} else {
			$dark_theme_class = '';
		}

		if ( get_option( 'tweet_prompt_box_username' ) ) {
			$via_username = ' by @' . get_option( 'tweet_prompt_box_username' );
		} else {
			$via_username = ' on ' . get_bloginfo( 'name' );
		}

		echo '<div id="tweet-prompt-box"' . $dark_theme_class . '>
				<h4>' . get_option( 'tweet_prompt_box_heading', 'Like this post? Tweet it!' ) . '</h4>
				<p>"' . get_the_title() . '"' . $via_username . '</p>
				<a href="javascript:;" class="tweet-prompt-box-button" onclick="tweet_prompt_box_open_win(\'' . tweet_prompt_box_create_tweet() . '\');"><span>Tweet</span></a>
				<a href="javascript:;" class="tweet-prompt-box-close">Close</a>
			  </div>';
	}
}

// create the tweet intent URL
function tweet_prompt_box_create_tweet() {
	if ( get_option( 'tweet_prompt_box_shortlink' ) == 'on' ) {
		$permalink = wp_get_shortlink();
	} else {
		$permalink = get_the_permalink();
	}

	global $post;
	$tweet_link_text = urlencode( '"' . $post->post_title . '"' );

	$tweet_link = 'https://twitter.com/intent/tweet?url=' . urlencode($permalink) . '&text=' . $tweet_link_text;

	if ( get_option( 'tweet_prompt_box_username' ) != '' ) {
		$username = get_option( 'tweet_prompt_box_username' );
		$tweet_link .= '&via=' . $username;
	}

	return $tweet_link;
}


/* ==== ADMIN FUNCTIONS ==== */

// create the menu item under the "Settings" tab in /wp-admin/
function tweet_prompt_box_menu() {
  add_options_page('Tweet Prompt Box', 'Tweet Prompt Box', 'manage_options', 'tweetpromptbox', 'tweet_prompt_box_options');
}

// add settings link on plugin listing
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'tweet_prompt_box_plugin_settings' );
function tweet_prompt_box_plugin_settings( $links ) {
   $links[] = '<a href="'. get_admin_url(null, 'options-general.php?page=tweetpromptbox') .'">Settings</a>';
   return $links;
}

// here we create the options page
function tweet_prompt_box_options() {
?>
	<h1><?php _e( 'Tweet Prompt Box' ); ?></h1>

	<form method="post" action="options.php" id="options">
	
		<?php wp_nonce_field( 'update-options '); ?>
		<?php settings_fields( 'tweet-prompt-box-group' ); ?>

		<table class="form-table">
			<tr>
				<th><label for="tweet_prompt_box_username">Twitter username</label></th>
				<td>
					@<input type="text" name="tweet_prompt_box_username" id="tweet_prompt_box_username" value="<?php echo get_option( 'tweet_prompt_box_username' ); ?>" />
					<p class="description">Enter your Twitter username to add "via @username" to the default tweet</p>
				</td>
			</tr>
			<tr>
				<th><label for="tweet_prompt_box_heading">Prompt headline</label></th>
				<td>
					<input type="text" name="tweet_prompt_box_heading" id="tweet_prompt_box_heading" value="<?php echo get_option( 'tweet_prompt_box_heading', 'Like this post? Tweet it!' ); ?>" />
				</td>
			</tr>
			<tr>
				<th><label for="tweet_prompt_box_shortlink">Use shortlink</label></th>
				<td>
					<input type="checkbox" name="tweet_prompt_box_shortlink" <?php if(get_option('tweet_prompt_box_shortlink') == 'on') { echo 'checked'; } ?>/>
					<p class="description">example: <?php echo wp_get_shortlink(1); ?></p>
				</td>
			</tr>
		</table>

		<hr>

		<h3>Choose a theme</h3>
		
		<div id="tweet-prompt-box-preview-light" class="tweet-prompt-box-theme-admin-select<?php if ( get_option( 'tweet_prompt_box_theme' ) == 'light' ) { echo ' tweet-prompt-box-preview-active'; } ?>" onclick="tweet_prompt_box_admin_theme('light')">
			<div class="tweet-prompt-box-theme-admin tweet-prompt-box-light-preview">
				<input type="radio" name="tweet_prompt_box_theme" value="light" <?php if ( get_option( 'tweet_prompt_box_theme', 'light' ) == 'light' ) { echo 'checked'; } ?> />
				
				<h4 class="tweet-prompt-box-heading"><?php echo get_option( 'tweet_prompt_box_heading', 'Like this post? Tweet it!' ); ?></h4>
				<p>"Your excellent post title" by @<span class="tweet-prompt-box-username"><?php echo get_option( 'tweet_prompt_box_username' ); ?></span></p>
				<a class="tweet-prompt-box-button"><span>Tweet</span></a>
				<a class="tweet-prompt-box-close">Close</a>
			</div>
		</div>

		<div id="tweet-prompt-box-preview-dark" class="tweet-prompt-box-theme-admin-select<?php if ( get_option( 'tweet_prompt_box_theme' ) == 'dark' ) { echo ' tweet-prompt-box-preview-active'; } ?>" onclick="tweet_prompt_box_admin_theme('dark')">
			<div class="tweet-prompt-box-theme-admin tweet-prompt-box-dark-preview">
				<input type="radio" name="tweet_prompt_box_theme" value="dark" <?php if ( get_option( 'tweet_prompt_box_theme' ) == 'dark' ) { echo 'checked'; } ?> />
				
				<h4 class="tweet-prompt-box-heading"><?php echo get_option( 'tweet_prompt_box_heading', 'Like this post? Tweet it!' ); ?></h4>
				<p>"Your excellent post title" by @<span class="tweet-prompt-box-username"><?php echo get_option( 'tweet_prompt_box_username' ); ?></span></p>
				<a class="tweet-prompt-box-button"><span>Tweet</span></a>
				<a class="tweet-prompt-box-close">Close</a>
			</div>
		</div>

		<br><br>
		<hr>

		<input type="hidden" name="action" value="update" />

	    <p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" /></p>

	</form>

	<script>
	function tweet_prompt_box_admin_theme( theme ) {
		var tweet_prompt_box_preview = document.getElementById( 'tweet-prompt-box-preview-' + theme );
		var tweet_prompt_box_preview_radios = document.getElementsByName( 'tweet_prompt_box_theme' );
		var tweet_prompt_box_preview_sibling,
		    tweet_prompt_box_preview_check,
		    tweet_prompt_box_preview_uncheck;

		if ( theme == 'light' ) {
			tweet_prompt_preview_sibling = document.getElementById( 'tweet-prompt-box-preview-dark' );
			tweet_prompt_box_preview_check = tweet_prompt_box_preview_radios[0];
			tweet_prompt_box_preview_uncheck = tweet_prompt_box_preview_radios[1];
		} else {
			tweet_prompt_preview_sibling = document.getElementById( 'tweet-prompt-box-preview-light' );
			tweet_prompt_box_preview_check = tweet_prompt_box_preview_radios[1];
			tweet_prompt_box_preview_uncheck = tweet_prompt_box_preview_radios[0];
		}

		tweet_prompt_box_preview.className = tweet_prompt_box_preview.className + ' tweet-prompt-box-preview-active';
		tweet_prompt_preview_sibling.className = 'tweet-prompt-box-theme-admin-select';
		tweet_prompt_box_preview_check.setAttribute( 'checked', '1' );
		tweet_prompt_box_preview_uncheck.removeAttribute( 'checked' );
	}

	var prompt_heading = document.getElementById( 'tweet_prompt_box_heading' );
	prompt_heading.onblur = function() {
		var prompt_heading_val = prompt_heading.value;
		for( var els = document.getElementsByTagName( 'h4' ), i = 0; i < els.length; i++ ) {
			if ( els[i].className.indexOf( 'tweet-prompt-box-heading' ) > -1 ) {
				els[i].innerHTML = prompt_heading_val;
			}
		}
	}

	var prompt_username = document.getElementById( 'tweet_prompt_box_username' );
	prompt_username.onblur = function() {
		var prompt_username_val = prompt_username.value;
		for( var els = document.getElementsByTagName( 'span' ), i = 0; i < els.length; i++ ) {
			if ( els[i].className.indexOf( 'tweet-prompt-box-username' ) > -1 ) {
				els[i].innerHTML = prompt_username_val;
			}
		}
	}
	</script>

<?php

}

// save the admin options
function tweet_prompt_box_process() { // whitelist options
  register_setting( 'tweet-prompt-box-group', 'tweet_prompt_box_username' );
  register_setting( 'tweet-prompt-box-group', 'tweet_prompt_box_heading' );
  register_setting( 'tweet-prompt-box-group', 'tweet_prompt_box_shortlink' );
  register_setting( 'tweet-prompt-box-group', 'tweet_prompt_box_theme' );
}