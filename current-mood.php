<?php
/*
Plugin Name: Current Mood
Plugin URI: http://kouloumbris.com/weblog/pugins/current-mood/
Description: Adds a "Current Mood" icon to each post after the date. Unzip moods into wp-content/plugins/moods and activate plugin. Use the dropdown option to select a current mood.
Version: 1.2.2
Author: Constantinos Kouloumbris
Author URI: http://kouloumbris.com/weblog
*/

/* This is the extension of your images can be any image type. */
$imgtype = ".jpg";

/* Do not change anything under this line. */
$moody_server_path = $_SERVER['DOCUMENT_ROOT'];

function moody_checkbox() {
	global $postdata, $id, $post, $imgtype, $moody_server_path;

	$curpath = rtrim($_SERVER['PHP_SELF'], "wp-admin/post.php");
	$dir = $moody_server_path.$curpath. "/wp-content/plugins/moods/";
	$n = 1;

	$mood = get_post_meta($post->ID, '_mood', TRUE);
	$cmood = get_post_meta($post->ID, '_cmood', TRUE);
	
	if ($cmood == '') { $cmood = $mood; }
	
	echo '<fieldset id="curmood" class="dbx-box"><h3 class="dbx-handle">'.__('Moody', 'Moody').'</h3>';
	echo '<div class="dbx-content"><label for="moody">'. __('Current Mood: ', 'Moody').'<select name="moody" id="moody"><option value=""></option>';
	//to open dir and read file name in an array.
	if (is_dir($dir)) {
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if ($file != "." || $file != ".." || $file != "") {
					if (filetype($dir . $file) != "dir") {
							$n++;
							$files[$n] = substr($file, 0, -4);
					}
				}
	       	}
			closedir($dh);
		}
	}

	sort($files);

	$arsize = sizeof($files);

	for($i=2; $i < $arsize; $i++) {
		if (strtolower($mood) == strtolower($files[$i])) {
			echo '<option value="'. $files[$i] . '" selected="selected">'.$files[$i].'</option>\n';
		} else {
			echo '<option value="'. $files[$i] . '">'.$files[$i].'</option>\n';
		}
	}
	echo '</select></label><br/>';
	echo '<laber for="cmood">'.__('Custom message: ', 'Moody').'<input type="text" name="cmood" id="cmood" value="'.$cmood.'" /></label>';
	echo moody_wimg();
	echo '</div></fieldset>';
}

function moody_wimg() {
	global $postdata, $original, $post, $imgtype, $moody_server_path;

	$curpath = rtrim($_SERVER['PHP_SELF'], "wp-admin/post.php");
	$dir = $curpath . "/wp-content/plugins/moods/";

	$mood = get_post_meta($post->ID, '_mood', TRUE);
	$cmood = get_post_meta($post->ID, '_cmood', TRUE);

	$moodstring = $moody_server_path . $dir . $mood . $imgtype;
	$mood_size = getimagesize($moodstring);

	echo '<p><b>Current mood:</b> <img src="'.$dir.$mood.$imgtype.'" '. $mood_size[3] .' align="middle"> '.$cmood.'.</p>';
}

function moody_update($id) {
	global $postdata;

	delete_post_meta($id, '_mood');
	delete_post_meta($id, '_cmood');

	$mood = (!($_POST["moody"] == '')) ? $_POST["moody"] : '';
	$cmood = (!($_POST["cmood"] == '')) ? $_POST["cmood"] : '';

	if ($mood != '') {
		add_post_meta($id, '_mood', $mood);
		if ($cmood != '') {
			add_post_meta($id, '_cmood', $cmood);
		} else {
			add_post_meta($id, '_cmood', $mood);
		}
	} else {
		delete_post_meta($id, '_mood');
		delete_post_meta($id, 'cmood');
	}
}

function moody($original) {
	global $wpdb, $imgtype, $moody_server_path;

	$curpath = rtrim($_SERVER['PHP_SELF'], "index.php");
	$mood_icon_dir = "/wp-content/plugins/moods/";
	$dir = $curpath . $mood_icon_dir;
	$moodef = 'noicon';

	$values = get_post_custom_values('_mood');
	$mood = $values[0];
	$values = get_post_custom_values('_cmood');
	$cmood = $values[0];
	
	if ($cmood == '') { $cmood = $mood; }

	// Auto-update: Check's to see if you where using version 1 of this script and updates it to v2
	if (0 != $wpdb->get_var("SELECT count(meta_value) FROM $wpdb->postmeta WHERE meta_key = 'mood'")) {
		if ($posts = $wpdb->get_results("SELECT * FROM $wpdb->posts")) {
			foreach ($posts as $post) {
				$moodys = get_post_meta($post->ID, 'mood', true);
				delete_post_meta($post->ID, 'mood');
				add_post_meta($post->ID, '_mood', $moodys);
			}
		}
	}
	/* Print original text first */
	$output .= $original;

	if (!empty($mood)) {
		$moodstring = $moody_server_path . $dir . $mood . $imgtype;
		$mood_size = getimagesize($moodstring);

		$moodefstring = $moody_server_path . $dir . $moodef . $imgtype;
		$mood_def_size = getimagesize($moodefstring);

		if (file_exists($moodstring)) {
			$output .= '<p><b>Current Mood:</b> ';
			$output .= '<img src="' . get_settings('siteurl') . $mood_icon_dir . $mood . $imgtype . '" '.$mood_size[3].' alt="' . $mood . '" title="' . $mood . '" align="middle" /> ' . $cmood . '.</p>';
		}
		else {
			$output .= '<p><b>Current Mood:</b> ';
			$output .= '<img src="' . get_settings('siteurl') . $mood_icon_dir . $moodef . $imgtype . '" '.$mood_def_size[3].' alt="' . $mood . '" title="' . $mood . '" align="middle" /> ' . $cmood . '.</p>';	
		}
	}
	return $output;
}
function cm_vercheck() {
	$wpcver = bloginfo('version');
	$status = true;

	if ($wpcver <= 2.0) { $status = false; }

	return $status;
}

if (cm_version) {
	add_action('dbx_post_sidebar', 'moody_checkbox');
} else {
	add_action('edit_form_advanced', 'moody_checkbox');
}
if (cm_version) {
	add_action('dbx_page_sidebar', 'moody_checkbox');
} else {
	add_action('edit_page_form', 'moody_checkbox');
}
add_action('save_post', 'moody_update');
add_action('edit_post', 'moody_update');
add_action('publish_post', 'moody_update');
add_filter('the_content', 'moody');

?>