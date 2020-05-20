<?php
/**
 * Events file.
 *
 * Events are defined in this file.
 *
 * @package mwp-al-ext
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// If not included correctly, then return.
if ( ! class_exists( '\WSAL\MainWPExtension\Activity_Log' ) ) {
	exit();
}

// Define custom / new PHP constants.
defined( 'E_CRITICAL' ) || define( 'E_CRITICAL', 'E_CRITICAL' );
defined( 'E_DEBUG' ) || define( 'E_DEBUG', 'E_DEBUG' );
defined( 'E_RECOVERABLE_ERROR' ) || define( 'E_RECOVERABLE_ERROR', 'E_RECOVERABLE_ERROR' );
defined( 'E_DEPRECATED' ) || define( 'E_DEPRECATED', 'E_DEPRECATED' );
defined( 'E_USER_DEPRECATED' ) || define( 'E_USER_DEPRECATED', 'E_USER_DEPRECATED' );

/**
 * Define Default Alerts.
 *
 * Define default alerts for the plugin.
 */
function mwpal_defaults_init() {
	$activity_log = \WSAL\MainWPExtension\mwpal_extension();

	if ( ! isset( $activity_log->constants ) ) {
		$activity_log->constants = new \WSAL\MainWPExtension\ConstantManager( $activity_log );
	}

	if ( ! isset( $activity_log->alerts ) ) {
		$activity_log->alerts = new \WSAL\MainWPExtension\AlertManager( $activity_log );
	}

	$activity_log->constants->UseConstants(
		array(
			// Default PHP constants.
			array(
				'name'        => 'E_ERROR',
				'description' => __( 'Fatal run-time error.', 'mwp-al-ext' ),
			),
			array(
				'name'        => 'E_WARNING',
				'description' => __( 'Run-time warning (non-fatal error).', 'mwp-al-ext' ),
			),
			array(
				'name'        => 'E_PARSE',
				'description' => __( 'Compile-time parse error.', 'mwp-al-ext' ),
			),
			array(
				'name'        => 'E_NOTICE',
				'description' => __( 'Run-time notice.', 'mwp-al-ext' ),
			),
			array(
				'name'        => 'E_CORE_ERROR',
				'description' => __( 'Fatal error that occurred during startup.', 'mwp-al-ext' ),
			),
			array(
				'name'        => 'E_CORE_WARNING',
				'description' => __( 'Warnings that occurred during startup.', 'mwp-al-ext' ),
			),
			array(
				'name'        => 'E_COMPILE_ERROR',
				'description' => __( 'Fatal compile-time error.', 'mwp-al-ext' ),
			),
			array(
				'name'        => 'E_COMPILE_WARNING',
				'description' => __( 'Compile-time warning.', 'mwp-al-ext' ),
			),
			array(
				'name'        => 'E_USER_ERROR',
				'description' => __( 'User-generated error message.', 'mwp-al-ext' ),
			),
			array(
				'name'        => 'E_USER_WARNING',
				'description' => __( 'User-generated warning message.', 'mwp-al-ext' ),
			),
			array(
				'name'        => 'E_USER_NOTICE',
				'description' => __( 'User-generated notice message.', 'mwp-al-ext' ),
			),
			array(
				'name'        => 'E_STRICT',
				'description' => __( 'Non-standard/optimal code warning.', 'mwp-al-ext' ),
			),
			array(
				'name'        => 'E_RECOVERABLE_ERROR',
				'description' => __( 'Catchable fatal error.', 'mwp-al-ext' ),
			),
			array(
				'name'        => 'E_DEPRECATED',
				'description' => __( 'Run-time deprecation notices.', 'mwp-al-ext' ),
			),
			array(
				'name'        => 'E_USER_DEPRECATED',
				'description' => __( 'Run-time user deprecation notices.', 'mwp-al-ext' ),
			),
			// Custom constants.
			array(
				'name'        => 'E_CRITICAL',
				'description' => __( 'Critical, high-impact messages.', 'mwp-al-ext' ),
			),
			array(
				'name'        => 'E_DEBUG',
				'description' => __( 'Debug informational messages.', 'mwp-al-ext' ),
			),
		)
	);

	$activity_log->constants->AddConstant( 'WSAL_CRITICAL', 1, __( 'Critical, high-impact messages.', 'mwp-al-ext' ) );
	$activity_log->constants->AddConstant( 'WSAL_HIGH', 6, __( 'High severity messages.', 'mwp-al-ext' ) );
	$activity_log->constants->AddConstant( 'WSAL_MEDIUM', 10, __( 'Medium severity messages.', 'mwp-al-ext' ) );
	$activity_log->constants->AddConstant( 'WSAL_LOW', 15, __( 'Low severity messages.', 'mwp-al-ext' ) );
	$activity_log->constants->AddConstant( 'WSAL_INFORMATIONAL', 20, __( 'Run-time notice.', 'mwp-al-ext' ) );

	// Create list of default alerts.
	$activity_log->alerts->RegisterGroup(
		array(
			/**
			 * Section: Users Profiles & Activity
			 */
			__( 'Users Logins & Sessions Events', 'mwp-al-ext' ) => array(
				__( 'User Activity', 'mwp-al-ext' ) => array(
					array( 1000, WSAL_LOW, __( 'User logged in', 'mwp-al-ext' ), '', 'user', 'login' ),
					array( 1001, WSAL_LOW, __( 'User logged out', 'mwp-al-ext' ), '', 'user', 'logout' ),
					array( 1002, WSAL_MEDIUM, __( 'Login failed', 'mwp-al-ext' ), '', 'user', 'failed-login' ),
					array( 1003, WSAL_LOW, __( 'Login failed  / non existing user', 'mwp-al-ext' ), __( '%Attempts% failed login(s) %LineBreak% %LogFileText%', 'mwp-al-ext' ), 'system', 'failed-login' ),
					array( 1004, WSAL_MEDIUM, __( 'Login blocked', 'mwp-al-ext' ), __( 'Login blocked because other session(s) already exist for this user. %LineBreak% IP address: %ClientIP%', 'mwp-al-ext' ), 'user', 'blocked' ),
					array( 1005, WSAL_LOW, __( 'User logged in with existing session(s)', 'mwp-al-ext' ), __( 'User logged in however there are other session(s) already exist for this user. %LineBreak% IP address: %IPAddress%', 'mwp-al-ext' ), 'user', 'login' ),
					array( 1006, WSAL_MEDIUM, __( 'User logged out all other sessions with the same username', 'mwp-al-ext' ), __( 'Logged out all other sessions with the same user.', 'mwp-al-ext' ), 'user', 'logout' ),
					array( 1007, WSAL_MEDIUM, __( 'User session destroyed and logged out', 'mwp-al-ext' ), __( 'Terminated the session of another user. %LineBreak% User: %TargetUserName% %LineBreak% Session ID: %TargetSessionID%', 'mwp-al-ext' ), 'user', 'logout' ),
					array( 1008, WSAL_MEDIUM, __( 'Switched to another user', 'mwp-al-ext' ), __( 'Switched to another user. %LineBreak% User: %TargetUserName% %LineBreak% Role: %TargetUserRole%', 'mwp-al-ext' ), 'user', 'login' ),
					array( 2010, WSAL_MEDIUM, __( 'User uploaded file from Uploads directory', 'mwp-al-ext' ), __( 'Filename: %FileName% %LineBreak% Directory: %FilePath%', 'mwp-al-ext' ), 'file', 'uploaded' ),
					array( 2011, WSAL_LOW, __( 'User deleted file from Uploads directory', 'mwp-al-ext' ), __( 'Filename: %FileName% %LineBreak% Directory: %FilePath%', 'mwp-al-ext' ), 'file', 'deleted' ),
				),
			),
			/**
			 * Section: Content & Comments
			 */
			__( 'Content & Comments', 'mwp-al-ext' ) => array(
				__( 'Content', 'mwp-al-ext' ) => array(
					array( 2000, WSAL_INFORMATIONAL, __( 'User created a new post and saved it as draft', 'mwp-al-ext' ), __( 'Created the post %PostTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'post', 'created' ),
					array( 2001, WSAL_LOW, __( 'User published a post', 'mwp-al-ext' ), __( 'Published the post %PostTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% Post URL is: %PostUrl% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'post', 'published' ),
					array( 2002, WSAL_LOW, __( 'User modified a post', 'mwp-al-ext' ), __( 'Modified the post %PostTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType%', 'mwp-al-ext' ), 'post', 'modified' ),
					array( 2008, WSAL_MEDIUM, __( 'User permanently deleted a post from the trash', 'mwp-al-ext' ), __( 'Permanently deleted the post %PostTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'post', 'deleted' ),
					array( 2012, WSAL_MEDIUM, __( 'User moved a post to the trash', 'mwp-al-ext' ), __( 'Moved the post %PostTitle% to trash %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% %PostUrl%.', 'mwp-al-ext' ), 'post', 'deleted' ),
					array( 2014, WSAL_LOW, __( 'User restored a post from trash', 'mwp-al-ext' ), __( 'Restored the post %PostTitle% from trash %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'post', 'restored' ),
					array( 2017, WSAL_INFORMATIONAL, __( 'User changed post URL', 'mwp-al-ext' ), __( 'Changed the URL of the post %PostTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% Previous URL: %OldUrl% %LineBreak% New URL: %NewUrl% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'post', 'modified' ),
					array( 2019, WSAL_INFORMATIONAL, __( 'User changed post author', 'mwp-al-ext' ), __( 'Changed the author of the post %PostTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% Previous author: %OldAuthor% %LineBreak% New author: %NewAuthor% %LineBreak% %EditorLinkPost%.', 'mwp-al-ext' ), 'post', 'modified' ),
					array( 2021, WSAL_MEDIUM, __( 'User changed post status', 'mwp-al-ext' ), __( 'Changed the status of the post %PostTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status was: %OldStatus% %LineBreak% New status: %NewStatus% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'post', 'modified' ),
					array( 2025, WSAL_LOW, __( 'User changed the visibility of a post', 'mwp-al-ext' ), __( 'Changed the visibility of the post %PostTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% Visibility was: %OldVisibility% %LineBreak% Visibility is: %NewVisibility% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'post', 'modified' ),
					array( 2027, WSAL_INFORMATIONAL, __( 'User changed the date of a post', 'mwp-al-ext' ), __( 'Changed the date of the post %PostTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% Previous date: %OldDate% %LineBreak% New date: %NewDate% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'post', 'modified' ),
					array( 2047, WSAL_LOW, __( 'User changed the parent of a page', 'mwp-al-ext' ), __( 'Changed the parent of the post %PostTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% Previous parent: %OldParentName% %LineBreak% New parent: %NewParentName% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'post', 'modified' ),
					array( 2048, WSAL_LOW, __( 'User changed the template of a page', 'mwp-al-ext' ), __( 'Changed the template of the post %PostTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% Previous template: %OldTemplate% %LineBreak% New template: %NewTemplate% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'post', 'modified' ),
					array( 2049, WSAL_INFORMATIONAL, __( 'User set a post as sticky', 'mwp-al-ext' ), __( 'Set the post %PostTitle% as sticky %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'post', 'modified' ),
					array( 2050, WSAL_INFORMATIONAL, __( 'User removed post from sticky', 'mwp-al-ext' ), __( 'Removed the post %PostTitle% from sticky %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'post', 'modified' ),
					array( 2065, WSAL_LOW, __( 'User modified the content of a post', 'mwp-al-ext' ), __( 'Modified the content of the post %PostTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% %RevisionLink% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'post', 'modified' ),
					array( 2073, WSAL_INFORMATIONAL, __( 'User submitted a post for review', 'mwp-al-ext' ), __( 'Submitted the post %PostTitle% for review %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'post', 'modified' ),
					array( 2074, WSAL_LOW, __( 'User scheduled a post', 'mwp-al-ext' ), __( 'Scheduled the post %PostTitle% to be published on %PublishingDate% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% Post URL is: %PostUrl% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'post', 'modified' ),
					array( 2086, WSAL_INFORMATIONAL, __( 'User changed title of a post', 'mwp-al-ext' ), __( 'Changed the title of the post %OldTitle% %LineBreak% New title: %NewTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'post', 'modified' ),
					array( 2100, WSAL_INFORMATIONAL, __( 'User opened a post in the editor', 'mwp-al-ext' ), __( 'Opened the post %PostTitle% in the editor %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'post', 'opened' ),
					array( 2101, WSAL_INFORMATIONAL, __( 'User viewed a post', 'mwp-al-ext' ), __( 'Viewed the post %PostTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% URL: %PostUrl% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'post', 'viewed' ),
					array( 2106, WSAL_MEDIUM, __( 'A plugin modified a post', 'mwp-al-ext' ), __( 'Plugin modified the post %PostTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'post', 'modified' ),
					array( 2111, WSAL_LOW, __( 'User enabled/disabled comments in a post', 'mwp-al-ext' ), __( 'The comments in the post %PostTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'post', 'enabled' ),
					array( 2112, WSAL_LOW, __( 'User enabled/disabled trackbacks and pingbacks in a post', 'mwp-al-ext' ), __( 'Pingbacks and Trackbacks in the post %PostTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'post', 'enabled' ),
				),

				__( 'Tags', 'mwp-al-ext' ) => array(
					array( 2119, WSAL_INFORMATIONAL, __( 'User added post tag', 'mwp-al-ext' ), __( 'Added tag(s) to the post %PostTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% Added tag(s): %tag% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'post', 'modified' ),
					array( 2120, WSAL_INFORMATIONAL, __( 'User removed post tag', 'mwp-al-ext' ), __( 'Removed tag(s) from the post %PostTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% Removed tag(s): %tag% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'post', 'modified' ),
					array( 2121, WSAL_INFORMATIONAL, __( 'User created new tag', 'mwp-al-ext' ), __( 'Created the tag %TagName% %LineBreak% Slug: %Slug% %LineBreak% %TagLink%', 'mwp-al-ext' ), 'tag', 'created' ),
					array( 2122, WSAL_LOW, __( 'User deleted tag', 'mwp-al-ext' ), __( 'Deleted the tag %TagName% %LineBreak% Slug: %Slug%', 'mwp-al-ext' ), 'tag', 'deleted' ),
					array( 2123, WSAL_INFORMATIONAL, __( 'User renamed tag', 'mwp-al-ext' ), __( 'Renamed the tag %old_name% %LineBreak% New name: %new_name% %LineBreak% Slug: %Slug% %LineBreak% %TagLink%', 'mwp-al-ext' ), 'tag', 'modified' ),
					array( 2124, WSAL_INFORMATIONAL, __( 'User changed tag slug', 'mwp-al-ext' ), __( 'Changed the slug of the tag %tag% %LineBreak% Previous slug: %old_slug% %LineBreak% New slug: %new_slug% %LineBreak% %TagLink%', 'mwp-al-ext' ), 'tag', 'modified' ),
					array( 2125, WSAL_INFORMATIONAL, __( 'User changed tag description', 'mwp-al-ext' ), __( 'Changed the description of the tag %tag% %LineBreak% Slug: %Slug% %LineBreak% Previous description: %old_desc% %LineBreak% New description: %new_desc% %LineBreak% %TagLink%', 'mwp-al-ext' ), 'tag', 'modified' ),
				),

				__( 'Categories', 'mwp-al-ext' ) => array(
					array( 2016, WSAL_LOW, __( 'User changed post category', 'mwp-al-ext' ), __( 'Changed the category of the post %PostTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% Previous category(ies): %OldCategories% %LineBreak% New category(ies): %NewCategories% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'post', 'modified' ),
					array( 2023, WSAL_MEDIUM, __( 'User created new category', 'mwp-al-ext' ), __( 'Created the category %CategoryName% %LineBreak% Slug: %Slug% %LineBreak% %CategoryLink%', 'mwp-al-ext' ), 'category', 'created' ),
					array( 2024, WSAL_MEDIUM, __( 'User deleted category', 'mwp-al-ext' ), __( 'Deleted the category %CategoryName% %LineBreak% Slug: %Slug%', 'mwp-al-ext' ), 'category', 'deleted' ),
					array( 2052, WSAL_LOW, __( 'Changed the parent of a category', 'mwp-al-ext' ), __( 'Changed the parent of the category %CategoryName% %LineBreak% Slug: %Slug% %LineBreak% Previous parent: %OldParent% %LineBreak% New parent: %NewParent% %LineBreak% %CategoryLink%', 'mwp-al-ext' ), 'category', 'modified' ),
					array( 2127, WSAL_LOW, __( 'User changed category name', 'mwp-al-ext' ), __( 'Changed the name of the category: %new_name% %LineBreak% Slug: %Slug% %LineBreak% Previous name: %old_name% %LineBreak% %cat_link%', 'mwp-al-ext' ), 'category', 'modified' ),
					array( 2128, WSAL_LOW, __( 'User changed category slug', 'mwp-al-ext' ), __( 'Changed the slug of the category: %CategoryName% %LineBreak% Previous slug: %old_slug% %LineBreak% New slug: %new_slug% %LineBreak% %cat_link%', 'mwp-al-ext' ), 'category', 'modified' ),
				),

				__( 'Custom Fields', 'mwp-al-ext' ) => array(
					array( 2053, WSAL_LOW, __( 'User created a custom field for a post', 'mwp-al-ext' ), __( 'Created a new custom field called %MetaKey% in the post %PostTitle% %LineBreak% Post ID: %PostID% %LineBreak% Post Type: %PostType% %LineBreak% Post Status: %PostStatus% %LineBreak% Custom field value: %MetaValue% %LineBreak% %EditorLinkPost% %LineBreak% %MetaLink%', 'mwp-al-ext' ), 'post', 'modified' ),
					array( 2054, WSAL_LOW, __( 'User updated a custom field value for a post', 'mwp-al-ext' ), __( 'Modified the value of the custom field %MetaKey% in the post %PostTitle% %LineBreak% Post ID: %PostID% %LineBreak% Post Type: %PostType% %LineBreak% Post Status: %PostStatus% %LineBreak% Previous custom field value: %MetaValueOld% %LineBreak% New custom field value: %MetaValueNew% %LineBreak% %EditorLinkPost% %LineBreak% %MetaLink%.', 'mwp-al-ext' ), 'custom-field', 'modified' ),
					array( 2055, WSAL_MEDIUM, __( 'User deleted a custom field from a post', 'mwp-al-ext' ), __( 'Deleted the custom field %MetaKey% from the post %PostTitle% %LineBreak% Post ID: %PostID% %LineBreak% Post Type: %PostType% %LineBreak% Post Status: %PostStatus% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'custom-field', 'deleted' ),
					array( 2062, WSAL_LOW, __( 'User updated a custom field name for a post', 'mwp-al-ext' ), __( 'Renamed the custom field %MetaKeyOld% in the post %PostTitle% %LineBreak% New name: %MetaKeyNew% %LineBreak% Post ID: %PostID% %LineBreak% Post Type: %PostType% %LineBreak% Post Status: %PostStatus% %LineBreak% %EditorLinkPost% %LineBreak% %MetaLink%', 'mwp-al-ext' ), 'custom-field', 'modified' ),
				),

				/**
				 * Alerts: Comments
				 */
				__( 'Comments', 'mwp-al-ext' ) => array(
					array( 2090, WSAL_INFORMATIONAL, __( 'User approved a comment', 'mwp-al-ext' ), __( 'Approved the comment posted by %Author% on the post %PostTitle% %LineBreak% Post ID: %PostID% %LineBreak% Post Type: %PostType% %LineBreak% Post Status: %PostStatus% %LineBreak% Comment ID: %CommentID% %LineBreak% %CommentLink%', 'mwp-al-ext' ), 'comment', 'approved' ),
					array( 2091, WSAL_INFORMATIONAL, __( 'User unapproved a comment', 'mwp-al-ext' ), __( 'Unapproved the comment posted by %Author% on the post %PostTitle% %LineBreak% Post ID: %PostID% %LineBreak% Post Type: %PostType% %LineBreak% Post Status: %PostStatus% %LineBreak% Comment ID: %CommentID% %LineBreak% %CommentLink%', 'mwp-al-ext' ), 'comment', 'unapproved' ),
					array( 2092, WSAL_INFORMATIONAL, __( 'User replied to a comment', 'mwp-al-ext' ), __( 'Replied to the comment posted by %Author% on the post %PostTitle% %LineBreak% Post ID: %PostID% %LineBreak% Post Type: %PostType% %LineBreak% Post Status: %PostStatus% %LineBreak% Comment ID: %CommentID% %LineBreak% %CommentLink%', 'mwp-al-ext' ), 'comment', 'created' ),
					array( 2093, WSAL_LOW, __( 'User edited a comment', 'mwp-al-ext' ), __( 'Edited the comment posted by %Author% on the post %PostTitle% %LineBreak% Post ID: %PostID% %LineBreak% Post Type: %PostType% %LineBreak% Post Status: %PostStatus% %LineBreak% Comment ID: %CommentID% %LineBreak% %CommentLink%', 'mwp-al-ext' ), 'comment', 'modified' ),
					array( 2094, WSAL_INFORMATIONAL, __( 'User marked a comment as Spam', 'mwp-al-ext' ), __( 'Marked the comment posted by %Author% on the post %PostTitle% as spam %LineBreak% Post ID: %PostID% %LineBreak% Post Type: %PostType% %LineBreak% Post Status: %PostStatus% %LineBreak% Comment ID: %CommentID% %LineBreak% %CommentLink%', 'mwp-al-ext' ), 'comment', 'unapproved' ),
					array( 2095, WSAL_LOW, __( 'User marked a comment as Not Spam', 'mwp-al-ext' ), __( 'Marked the comment posted by %Author% on the post %PostTitle% as not spam %LineBreak% Post ID: %PostID% %LineBreak% Post Type: %PostType% %LineBreak% Post Status: %PostStatus% %LineBreak% Comment ID: %CommentID% %LineBreak% %CommentLink%', 'mwp-al-ext' ), 'comment', 'approved' ),
					array( 2096, WSAL_LOW, __( 'User moved a comment to trash', 'mwp-al-ext' ), __( 'Moved the comment posted by %Author% on the post %PostTitle% to trash %LineBreak% Post ID: %PostID% %LineBreak% Post Type: %PostType% %LineBreak% Post Status: %PostStatus% %LineBreak% Comment ID: %CommentID% %LineBreak% %CommentLink%', 'mwp-al-ext' ), 'comment', 'deleted' ),
					array( 2097, WSAL_INFORMATIONAL, __( 'User restored a comment from the trash', 'mwp-al-ext' ), __( 'Restored the comment posted by %Author% on the post %PostTitle% from trash %LineBreak% Post ID: %PostID% %LineBreak% Post Type: %PostType% %LineBreak% Post Status: %PostStatus% %LineBreak% Comment ID: %CommentID% %LineBreak% %CommentLink%', 'mwp-al-ext' ), 'comment', 'restored' ),
					array( 2098, WSAL_LOW, __( 'User permanently deleted a comment', 'mwp-al-ext' ), __( 'Permanently deleted the comment posted by %Author% on the post %PostTitle% %LineBreak% Post ID: %PostID% %LineBreak% Post Type: %PostType% %LineBreak% Post Status: %PostStatus% %LineBreak% Comment ID: %CommentID%', 'mwp-al-ext' ), 'comment', 'restored' ),
					array( 2099, WSAL_INFORMATIONAL, __( 'User posted a comment', 'mwp-al-ext' ), __( 'Posted a comment on the post %PostTitle% %LineBreak% Post ID: %PostID% %LineBreak% Post Type: %PostType% %LineBreak% Post Status: %PostStatus% %LineBreak% Comment ID: %CommentID% %LineBreak% %CommentLink%', 'mwp-al-ext' ), 'comment', 'created' ),
					array( 2126, WSAL_INFORMATIONAL, __( 'Visitor posted a comment', 'mwp-al-ext' ), __( 'Posted a comment on the post %PostTitle% %LineBreak% Post ID: %PostID% %LineBreak% Post Type: %PostType% %LineBreak% Post Status: %PostStatus% %LineBreak% Comment ID: %CommentID% %LineBreak% %CommentLink%', 'mwp-al-ext' ), 'comment', 'created' ),
				),

				/**
				 * Alerts: Widgets
				 */
				__( 'Widgets', 'mwp-al-ext' ) => array(
					array( 2042, WSAL_MEDIUM, __( 'User added a new widget', 'mwp-al-ext' ), __( 'Added a new %WidgetName% widget in  %Sidebar%.', 'mwp-al-ext' ), 'widget', 'added' ),
					array( 2043, WSAL_HIGH, __( 'User modified a widget', 'mwp-al-ext' ), __( 'Modified the %WidgetName% widget in %Sidebar%.', 'mwp-al-ext' ), 'widget', 'modified' ),
					array( 2044, WSAL_MEDIUM, __( 'User deleted widget', 'mwp-al-ext' ), __( 'Deleted the %WidgetName% widget from %Sidebar%.', 'mwp-al-ext' ), 'widget', 'deleted' ),
					array( 2045, WSAL_LOW, __( 'User moved widget', 'mwp-al-ext' ), __( 'Moved the %WidgetName% widget %LineBreak% From: %OldSidebar% %LineBreak% To: %NewSidebar%', 'mwp-al-ext' ), 'widget', 'modified' ),
					array( 2071, WSAL_LOW, __( 'User changed widget position', 'mwp-al-ext' ), __( 'Changed the position of the %WidgetName% widget in %Sidebar%.', 'mwp-al-ext' ), 'widget', 'modified' ),
				),

				/**
				 * Alerts: Menus
				 */
				__( 'Menus', 'mwp-al-ext' ) => array(
					array( 2078, WSAL_LOW, __( 'User created new menu', 'mwp-al-ext' ), __( 'New menu called %MenuName%.', 'mwp-al-ext' ), 'menu', 'created' ),
					array( 2079, WSAL_LOW, __( 'User added content to a menu', 'mwp-al-ext' ), __( 'Added new item to the menu %MenuName% %LineBreak% Item type: %ContentType% %LineBreak% Item name: %ContentName% ', 'mwp-al-ext' ), 'menu', 'modified' ),
					array( 2080, WSAL_LOW, __( 'User removed content from a menu', 'mwp-al-ext' ), __( 'Removed item from the menu %MenuName% %LineBreak% Item type: %ContentType% %LineBreak% Item name: %ContentName%', 'mwp-al-ext' ), 'menu', 'modified' ),
					array( 2081, WSAL_MEDIUM, __( 'User deleted menu', 'mwp-al-ext' ), __( 'Deleted the menu %MenuName%', 'mwp-al-ext' ), 'menu', 'deleted' ),
					array( 2082, WSAL_LOW, __( 'User changed menu setting', 'mwp-al-ext' ), __( 'The setting in the %MenuName% %LineBreak% Setting: %MenuSetting%', 'mwp-al-ext' ), 'menu', 'enabled' ),
					array( 2083, WSAL_LOW, __( 'User modified content in a menu', 'mwp-al-ext' ), __( 'Modified an item in the menu %MenuName% %LineBreak% Item type: %ContentType% %LineBreak% Item name: %ContentName%', 'mwp-al-ext' ), 'menu', 'modified' ),
					array( 2084, WSAL_LOW, __( 'User changed name of a menu', 'mwp-al-ext' ), __( 'Renamed the menu %OldMenuName% %LineBreak% New name: %NewMenuName%', 'mwp-al-ext' ), 'menu', 'modified' ),
					array( 2085, WSAL_LOW, __( 'User changed order of the objects in a menu', 'mwp-al-ext' ), __( 'Changed the order of the items in the menu %MenuName%', 'mwp-al-ext' ), 'menu', 'modified' ),
					array( 2089, WSAL_LOW, __( 'User moved objects as a sub-item', 'mwp-al-ext' ), __( 'Menu name: %MenuName% %LineBreak% Moved item %ItemName% as a sub-item of %ParentName%', 'mwp-al-ext' ), 'menu', 'modified' ),
				),

				/**
				 * Alerts: Custom Post Types
				 *
				 * IMPORTANT: These alerts should not be removed from here
				 * for backwards compatibilty.
				 *
				 * @deprecated 3.1.0
				 */
				__( 'Custom Post Types', 'mwp-al-ext' ) => array(
					array( 2003, E_NOTICE, __( 'User modified a draft blog post', 'mwp-al-ext' ), __( 'Modified the draft post with the %PostTitle%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2029, E_NOTICE, __( 'User created a new post with custom post type and saved it as draft', 'mwp-al-ext' ), __( 'Created a new custom post called %PostTitle% of type %PostType%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2030, E_NOTICE, __( 'User published a post with custom post type', 'mwp-al-ext' ), __( 'Published a custom post %PostTitle% of type %PostType%. Post URL is %PostUrl%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2031, E_NOTICE, __( 'User modified a post with custom post type', 'mwp-al-ext' ), __( 'Modified the custom post %PostTitle% of type %PostType%. Post URL is %PostUrl%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2032, E_NOTICE, __( 'User modified a draft post with custom post type', 'mwp-al-ext' ), __( 'Modified the draft custom post %PostTitle% of type is %PostType%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2033, E_WARNING, __( 'User permanently deleted post with custom post type', 'mwp-al-ext' ), __( 'Permanently Deleted the custom post %PostTitle% of type %PostType%.', 'mwp-al-ext' ) ),
					array( 2034, E_WARNING, __( 'User moved post with custom post type to trash', 'mwp-al-ext' ), __( 'Moved the custom post %PostTitle% of type %PostType% to trash. Post URL was %PostUrl%.', 'mwp-al-ext' ) ),
					array( 2035, E_CRITICAL, __( 'User restored post with custom post type from trash', 'mwp-al-ext' ), __( 'The custom post %PostTitle% of type %PostType% has been restored from trash. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2036, E_NOTICE, __( 'User changed the category of a post with custom post type', 'mwp-al-ext' ), __( 'Changed the category(ies) of the custom post %PostTitle% of type %PostType% from %OldCategories% to %NewCategories%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2037, E_NOTICE, __( 'User changed the URL of a post with custom post type', 'mwp-al-ext' ), __( 'Changed the URL of the custom post %PostTitle% of type %PostType% from %OldUrl% to %NewUrl%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2038, E_NOTICE, __( 'User changed the author or post with custom post type', 'mwp-al-ext' ), __( 'Changed the author of custom post %PostTitle% of type %PostType% from %OldAuthor% to %NewAuthor%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2039, E_NOTICE, __( 'User changed the status of post with custom post type', 'mwp-al-ext' ), __( 'Changed the status of custom post %PostTitle% of type %PostType% from %OldStatus% to %NewStatus%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2040, E_WARNING, __( 'User changed the visibility of a post with custom post type', 'mwp-al-ext' ), __( 'Changed the visibility of the custom post %PostTitle% of type %PostType% from %OldVisibility% to %NewVisibility%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2041, E_NOTICE, __( 'User changed the date of post with custom post type', 'mwp-al-ext' ), __( 'Changed the date of the custom post %PostTitle% of type %PostType% from %OldDate% to %NewDate%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2056, E_CRITICAL, __( 'User created a custom field for a custom post type', 'mwp-al-ext' ), __( 'Created a new custom field %MetaKey% with value %MetaValue% in custom post %PostTitle% of type %PostType%.' . ' %EditorLinkPost%.' . '<br>%MetaLink%.', 'mwp-al-ext' ) ),
					array( 2057, E_CRITICAL, __( 'User updated a custom field for a custom post type', 'mwp-al-ext' ), __( 'Modified the value of the custom field %MetaKey% from %MetaValueOld% to %MetaValueNew% in custom post %PostTitle% of type %PostType%' . ' %EditorLinkPost%.' . '<br>%MetaLink%.', 'mwp-al-ext' ) ),
					array( 2058, E_CRITICAL, __( 'User deleted a custom field from a custom post type', 'mwp-al-ext' ), __( 'Deleted the custom field %MetaKey% with id %MetaID% from custom post %PostTitle% of type %PostType%' . ' %EditorLinkPost%.' . '<br>%MetaLink%.', 'mwp-al-ext' ) ),
					array( 2063, E_CRITICAL, __( 'User updated a custom field name for a custom post type', 'mwp-al-ext' ), __( 'Changed the custom field name from %MetaKeyOld% to %MetaKeyNew% in custom post %PostTitle% of type %PostType%' . ' %EditorLinkPost%.' . '<br>%MetaLink%.', 'mwp-al-ext' ) ),
					array( 2067, E_WARNING, __( 'User modified content for a published custom post type', 'mwp-al-ext' ), __( 'Modified the content of the published custom post type %PostTitle%. Post URL is %PostUrl%.' . '%EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2068, E_NOTICE, __( 'User modified content for a draft post', 'mwp-al-ext' ), __( 'Modified the content of the draft post %PostTitle%.' . '%RevisionLink%' . ' %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2070, E_NOTICE, __( 'User modified content for a draft custom post type', 'mwp-al-ext' ), __( 'Modified the content of the draft custom post type %PostTitle%.' . '%EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2072, E_NOTICE, __( 'User modified content of a post', 'mwp-al-ext' ), __( 'Modified the content of post %PostTitle% which is submitted for review.' . '%RevisionLink%' . ' %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2076, E_NOTICE, __( 'User scheduled a custom post type', 'mwp-al-ext' ), __( 'Scheduled the custom post type %PostTitle% to be published %PublishingDate%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2088, E_NOTICE, __( 'User changed title of a custom post type', 'mwp-al-ext' ), __( 'Changed the title of the custom post %OldTitle% to %NewTitle%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2104, E_NOTICE, __( 'User opened a custom post type in the editor', 'mwp-al-ext' ), __( 'Opened the custom post %PostTitle% of type %PostType% in the editor. View the post: %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2105, E_NOTICE, __( 'User viewed a custom post type', 'mwp-al-ext' ), __( 'Viewed the custom post %PostTitle% of type %PostType%. View the post: %PostUrl%.', 'mwp-al-ext' ) ),
					array( 5021, E_CRITICAL, __( 'A plugin created a custom post', 'mwp-al-ext' ), __( 'A plugin automatically created the following custom post: %PostTitle%.', 'mwp-al-ext' ) ),
					array( 5027, E_CRITICAL, __( 'A plugin deleted a custom post', 'mwp-al-ext' ), __( 'A plugin automatically deleted the following custom post: %PostTitle%.', 'mwp-al-ext' ) ),
					array( 2108, E_NOTICE, __( 'A plugin modified a custom post', 'mwp-al-ext' ), __( 'Plugin modified the custom post %PostTitle%. View the post: %EditorLinkPost%.', 'mwp-al-ext' ) ),
				),

				/**
				 * Alerts: Pages
				 *
				 * IMPORTANT: These alerts should not be removed from here
				 * for backwards compatibilty.
				 *
				 * @deprecated 3.1.0
				 */
				__( 'Pages', 'mwp-al-ext' ) => array(
					array( 2004, E_NOTICE, __( 'User created a new WordPress page and saved it as draft', 'mwp-al-ext' ), __( 'Created a new page called %PostTitle% and saved it as draft. %EditorLinkPage%.', 'mwp-al-ext' ) ),
					array( 2005, E_NOTICE, __( 'User published a WordPress page', 'mwp-al-ext' ), __( 'Published a page called %PostTitle%. Page URL is %PostUrl%. %EditorLinkPage%.', 'mwp-al-ext' ) ),
					array( 2006, E_NOTICE, __( 'User modified a published WordPress page', 'mwp-al-ext' ), __( 'Modified the published page %PostTitle%. Page URL is %PostUrl%. %EditorLinkPage%.', 'mwp-al-ext' ) ),
					array( 2007, E_NOTICE, __( 'User modified a draft WordPress page', 'mwp-al-ext' ), __( 'Modified the draft page %PostTitle%. Page ID is %PostID%. %EditorLinkPage%.', 'mwp-al-ext' ) ),
					array( 2009, E_WARNING, __( 'User permanently deleted a page from the trash', 'mwp-al-ext' ), __( 'Permanently deleted the page %PostTitle%.', 'mwp-al-ext' ) ),
					array( 2013, E_WARNING, __( 'User moved WordPress page to the trash', 'mwp-al-ext' ), __( 'Moved the page %PostTitle% to trash. Page URL was %PostUrl%.', 'mwp-al-ext' ) ),
					array( 2015, E_CRITICAL, __( 'User restored a WordPress page from trash', 'mwp-al-ext' ), __( 'Page %PostTitle% has been restored from trash. %EditorLinkPage%.', 'mwp-al-ext' ) ),
					array( 2018, E_NOTICE, __( 'User changed page URL', 'mwp-al-ext' ), __( 'Changed the URL of the page %PostTitle% from %OldUrl% to %NewUrl%. %EditorLinkPage%.', 'mwp-al-ext' ) ),
					array( 2020, E_NOTICE, __( 'User changed page author', 'mwp-al-ext' ), __( 'Changed the author of the page %PostTitle% from %OldAuthor% to %NewAuthor%. %EditorLinkPage%.', 'mwp-al-ext' ) ),
					array( 2022, E_NOTICE, __( 'User changed page status', 'mwp-al-ext' ), __( 'Changed the status of the page %PostTitle% from %OldStatus% to %NewStatus%. %EditorLinkPage%.', 'mwp-al-ext' ) ),
					array( 2026, E_WARNING, __( 'User changed the visibility of a page post', 'mwp-al-ext' ), __( 'Changed the visibility of the page %PostTitle% from %OldVisibility% to %NewVisibility%. %EditorLinkPage%.', 'mwp-al-ext' ) ),
					array( 2028, E_NOTICE, __( 'User changed the date of a page post', 'mwp-al-ext' ), __( 'Changed the date of the page %PostTitle% from %OldDate% to %NewDate%. %EditorLinkPage%.', 'mwp-al-ext' ) ),
					array( 2059, E_CRITICAL, __( 'User created a custom field for a page', 'mwp-al-ext' ), __( 'Created a new custom field called %MetaKey% with value %MetaValue% in the page %PostTitle%' . ' %EditorLinkPage%.' . '<br>%MetaLink%.', 'mwp-al-ext' ) ),
					array( 2060, E_CRITICAL, __( 'User updated a custom field value for a page', 'mwp-al-ext' ), __( 'Modified the value of the custom field %MetaKey% from %MetaValueOld% to %MetaValueNew% in the page %PostTitle%' . ' %EditorLinkPage%.' . '<br>%MetaLink%.', 'mwp-al-ext' ) ),
					array( 2061, E_CRITICAL, __( 'User deleted a custom field from a page', 'mwp-al-ext' ), __( 'Deleted the custom field %MetaKey% with id %MetaID% from page %PostTitle%' . ' %EditorLinkPage%.' . '<br>%MetaLink%.', 'mwp-al-ext' ) ),
					array( 2064, E_CRITICAL, __( 'User updated a custom field name for a page', 'mwp-al-ext' ), __( 'Changed the custom field name from %MetaKeyOld% to %MetaKeyNew% in the page %PostTitle%' . ' %EditorLinkPage%.' . '<br>%MetaLink%.', 'mwp-al-ext' ) ),
					array( 2066, E_WARNING, __( 'User modified content for a published page', 'mwp-al-ext' ), __( 'Modified the content of the published page %PostTitle%. Page URL is %PostUrl%. %RevisionLink% %EditorLinkPage%.', 'mwp-al-ext' ) ),
					array( 2069, E_NOTICE, __( 'User modified content for a draft page', 'mwp-al-ext' ), __( 'Modified the content of draft page %PostTitle%.' . '%RevisionLink%' . ' %EditorLinkPage%.', 'mwp-al-ext' ) ),
					array( 2075, E_NOTICE, __( 'User scheduled a page', 'mwp-al-ext' ), __( 'Scheduled the page %PostTitle% to be published %PublishingDate%.' . ' %EditorLinkPage%.', 'mwp-al-ext' ) ),
					array( 2087, E_NOTICE, __( 'User changed title of a page', 'mwp-al-ext' ), __( 'Changed the title of the page %OldTitle% to %NewTitle%.' . ' %EditorLinkPage%.', 'mwp-al-ext' ) ),
					array( 2102, E_NOTICE, __( 'User opened a page in the editor', 'mwp-al-ext' ), __( 'Opened the page %PostTitle% in the editor. View the page: %EditorLinkPage%.', 'mwp-al-ext' ) ),
					array( 2103, E_NOTICE, __( 'User viewed a page', 'mwp-al-ext' ), __( 'Viewed the page %PostTitle%. View the page: %PostUrl%.', 'mwp-al-ext' ) ),
					array( 2113, E_NOTICE, __( 'User disabled Comments/Trackbacks and Pingbacks on a draft post', 'mwp-al-ext' ), __( 'Disabled %Type% on the draft post %PostTitle%. View the post: %PostUrl%.', 'mwp-al-ext' ) ),
					array( 2114, E_NOTICE, __( 'User enabled Comments/Trackbacks and Pingbacks on a draft post', 'mwp-al-ext' ), __( 'Enabled %Type% on the draft post %PostTitle%. View the post: %PostUrl%.', 'mwp-al-ext' ) ),
					array( 2115, E_NOTICE, __( 'User disabled Comments/Trackbacks and Pingbacks on a published page', 'mwp-al-ext' ), __( 'Disabled %Type% on the published page %PostTitle%. View the page: %PostUrl%.', 'mwp-al-ext' ) ),
					array( 2116, E_NOTICE, __( 'User enabled Comments/Trackbacks and Pingbacks on a published page', 'mwp-al-ext' ), __( 'Enabled %Type% on the published page %PostTitle%. View the page: %PostUrl%.', 'mwp-al-ext' ) ),
					array( 2117, E_NOTICE, __( 'User disabled Comments/Trackbacks and Pingbacks on a draft page', 'mwp-al-ext' ), __( 'Disabled %Type% on the draft page %PostTitle%. View the page: %PostUrl%.', 'mwp-al-ext' ) ),
					array( 2118, E_NOTICE, __( 'User enabled Comments/Trackbacks and Pingbacks on a draft page', 'mwp-al-ext' ), __( 'Enabled %Type% on the draft page %PostTitle%. View the page: %PostUrl%.', 'mwp-al-ext' ) ),
					array( 5020, E_CRITICAL, __( 'A plugin created a page', 'mwp-al-ext' ), __( 'A plugin automatically created the following page: %PostTitle%.', 'mwp-al-ext' ) ),
					array( 5026, E_CRITICAL, __( 'A plugin deleted a page', 'mwp-al-ext' ), __( 'A plugin automatically deleted the following page: %PostTitle%.', 'mwp-al-ext' ) ),
					array( 2107, E_NOTICE, __( 'A plugin modified a page', 'mwp-al-ext' ), __( 'Plugin modified the page %PostTitle%. View the page: %EditorLinkPage%.', 'mwp-al-ext' ) ),
				),
			),

			/**
			 * Section: WordPress Install
			 */
			__( 'User Accounts', 'mwp-al-ext' ) => array(
				__( 'User Profiles', 'mwp-al-ext' ) => array(
					array( 4000, WSAL_CRITICAL, __( 'New user was created on WordPress', 'mwp-al-ext' ), __( 'New user: %NewUserData->Username% %LineBreak% Role: %NewUserData->Roles% %LineBreak% First name: %NewUserData->FirstName% %LineBreak% Last name: %NewUserData->LastName% %LineBreak% %EditUserLink%', 'mwp-al-ext' ), 'user', 'created' ),
					array( 4001, WSAL_CRITICAL, __( 'User created another WordPress user', 'mwp-al-ext' ), __( 'New user: %NewUserData->Username% %LineBreak% Role: %NewUserData->Roles% %LineBreak% First name: %NewUserData->FirstName% %LineBreak% Last name: %NewUserData->LastName% %LineBreak% %EditUserLink%', 'mwp-al-ext' ), 'user', 'created' ),
					array( 4002, WSAL_CRITICAL, __( 'The role of a user was changed by another WordPress user', 'mwp-al-ext' ), __( 'Changed the role of the user %TargetUsername% %LineBreak% New role: %NewRole% %LineBreak% Previous role: %OldRole% %LineBreak% First name: %FirstName% %LineBreak% Last name: %LastName% %LineBreak% %EditUserLink%', 'mwp-al-ext' ), 'user', 'modified' ),
					array( 4003, WSAL_HIGH, __( 'User has changed his or her password', 'mwp-al-ext' ), __( 'Changed the password %LineBreak% First name: %TargetUserData->FirstName% %LineBreak% Last name: %TargetUserData->LastName% %LineBreak% %EditUserLink%', 'mwp-al-ext' ), 'user', 'modified' ),
					array( 4004, WSAL_HIGH, __( 'User changed another user\'s password', 'mwp-al-ext' ), __( 'Changed the password of the user %TargetUserData->Username% %LineBreak% Role: %TargetUserData->Roles% %LineBreak% First name: %TargetUserData->FirstName% %LineBreak% Last name: %TargetUserData->LastName% %LineBreak% %EditUserLink%', 'mwp-al-ext' ), 'user', 'modified' ),
					array( 4005, WSAL_MEDIUM, __( 'User changed his or her email address', 'mwp-al-ext' ), __( 'Changed the email address to %NewEmail% %LineBreak% Role: %Roles% %LineBreak% First name: %FirstName% %LineBreak% Last name: %LastName% %LineBreak% %EditUserLink%', 'mwp-al-ext' ), 'user', 'modified' ),
					array( 4006, WSAL_MEDIUM, __( 'User changed another user\'s email address', 'mwp-al-ext' ), __( 'Changed the email address of the user %TargetUsername% %LineBreak% New email address: %NewEmail% %LineBreak% Previous email address: %OldEmail% %LineBreak% Role: %Roles% %LineBreak% First name: %FirstName% %LineBreak% Last name: %LastName% %LineBreak% %EditUserLink%', 'mwp-al-ext' ), 'user', 'modified' ),
					array( 4007, WSAL_HIGH, __( 'User was deleted by another user', 'mwp-al-ext' ), __( 'User: %TargetUserData->Username% %LineBreak% Role: %TargetUserData->Roles% %LineBreak% First name: %NewUserData->FirstName% %LineBreak% Last name: %NewUserData->LastName%', 'mwp-al-ext' ), 'user', 'deleted' ),
					array( 4014, WSAL_INFORMATIONAL, __( 'User opened the profile page of another user', 'mwp-al-ext' ), __( 'The profile page of the user %TargetUsername% %LineBreak% Role: %Roles% %LineBreak% First name: %FirstName% %LineBreak% Last name: %LastName% %LineBreak% %EditUserLink%', 'mwp-al-ext' ), 'user', 'opened' ),
					array( 4015, WSAL_LOW, __( 'User updated a custom field value for a user', 'mwp-al-ext' ), __( 'Changed the value of a custom field in the user profile %TargetUsername% %LineBreak% Custom field: %custom_field_name% %LineBreak% Previous value: %old_value% %LineBreak% New value: %new_value% %LineBreak% Role: %Roles% %LineBreak% First name: %FirstName% %LineBreak% Last name: %LastName% %LineBreak% %EditUserLink%', 'mwp-al-ext' ), 'user', 'modified' ),
					array( 4016, WSAL_LOW, __( 'User created a custom field value for a user', 'mwp-al-ext' ), __( 'Created a new custom field in the user profile %TargetUsername% %LineBreak% Custom field: %custom_field_name% %LineBreak% Custom field value: %new_value% %LineBreak% Role: %Roles% %LineBreak% First name: %FirstName% %LineBreak% Last name: %LastName% %LineBreak% %EditUserLink%', 'mwp-al-ext' ), 'user', 'modified' ),
					array( 4017, WSAL_INFORMATIONAL, __( 'User changed first name for a user', 'mwp-al-ext' ), __( 'Changed the first name of the user %TargetUsername% %LineBreak% Previous name: %old_firstname% %LineBreak% New name: %new_firstname% %LineBreak% Role: %Roles% %LineBreak% Last name: %LastName% %LineBreak% %EditUserLink%', 'mwp-al-ext' ), 'user', 'modified' ),
					array( 4018, WSAL_INFORMATIONAL, __( 'User changed last name for a user', 'mwp-al-ext' ), __( 'Changed the last name of the user %TargetUsername% %LineBreak% Previous last name: %old_lastname% %LineBreak% New last name: %new_lastname% %LineBreak% Role: %Roles% %LineBreak% First name: %FirstName% %LineBreak% %EditUserLink%', 'mwp-al-ext' ), 'user', 'modified' ),
					array( 4019, WSAL_INFORMATIONAL, __( 'User changed nickname for a user', 'mwp-al-ext' ), __( 'Changed the nickname of the user %TargetUsername% %LineBreak% Previous nickname: %old_nickname% New nickname: %new_nickname% %LineBreak% Role: %Roles% %LineBreak% First name: %FirstName% %LineBreak% Last name: %LastName% %LineBreak% %EditUserLink%', 'mwp-al-ext' ), 'user', 'modified' ),
					array( 4020, WSAL_LOW, __( 'User changed the display name for a user', 'mwp-al-ext' ), __( 'Changed the display name of the user %TargetUsername% %LineBreak% Previous display name: %old_displayname% New display name: %new_displayname% %LineBreak% Role: %Roles% %LineBreak% First name: %FirstName% %LineBreak% Last name: %LastName% %LineBreak% %EditUserLink%', 'mwp-al-ext' ), 'user', 'modified' ),
				),

				__( 'Multisite User Profiles', 'mwp-al-ext' ) => array(
					array( 4008, WSAL_CRITICAL, __( 'User granted Super Admin privileges', 'mwp-al-ext' ), __( 'Granted Super Admin privileges to %TargetUsername% %LineBreak% First name: %FirstName% %LineBreak% Last name: %LastName% %LineBreak% %EditUserLink%', 'mwp-al-ext' ), 'user', 'modified' ),
					array( 4009, WSAL_CRITICAL, __( 'User revoked from Super Admin privileges', 'mwp-al-ext' ), __( 'Revoked Super Admin privileges from %TargetUsername% %LineBreak% First name: %FirstName% %LineBreak% Last name: %LastName% %LineBreak% %EditUserLink%', 'mwp-al-ext' ), 'user', 'modified' ),
					array( 4010, WSAL_MEDIUM, __( 'Existing user added to a site', 'mwp-al-ext' ), __( 'Added user %TargetUsername% to site: %SiteName% %LineBreak% Role: %TargetUserRole% %LineBreak% First name: %FirstName% %LineBreak% Last name: %LastName% %LineBreak% %EditUserLink%', 'mwp-al-ext' ), 'user', 'modified' ),
					array( 4011, WSAL_MEDIUM, __( 'User removed from site', 'mwp-al-ext' ), __( 'Removed user %TargetUsername% from site: %SiteName% %LineBreak% Previous role: %TargetUserRole% %LineBreak% First name: %FirstName% %LineBreak% Last name: %LastName% %LineBreak% %EditUserLink%', 'mwp-al-ext' ), 'user', 'modified' ),
					array( 4012, WSAL_CRITICAL, __( 'New network user created', 'mwp-al-ext' ), __( 'Created a new network user %NewUserData->Username% %LineBreak% First name: %NewUserData->FirstName% %LineBreak% Last name: %NewUserData->LastName% %LineBreak% %EditUserLink%', 'mwp-al-ext' ), 'user', 'created' ),
				),

				__( 'bbPress User Profiles', 'mwp-al-ext' ) => array(
					array( 4013, WSAL_LOW, __( 'The forum role of a user was changed by another WordPress user', 'mwp-al-ext' ), __( 'Change the forum role of user %TargetUsername% %LineBreak% Previous role: %OldRole% %LineBreak% New role: %NewRole% %LineBreak% First name: %FirstName% %LineBreak% Last name: %LastName% %LineBreak% %EditUserLink%', 'mwp-al-ext' ), 'user', 'modified' ),
				),
			),

			__( 'Plugins & Themes', 'mwp-al-ext' ) => array(
				__( 'Plugins', 'mwp-al-ext' ) => array(
					array( 5000, WSAL_CRITICAL, __( 'User installed a plugin', 'mwp-al-ext' ), __( 'Name: %Plugin->Name% %LineBreak% Install location: %Plugin->plugin_dir_path%', 'mwp-al-ext' ), 'plugin', 'installed' ),
					array( 5001, WSAL_HIGH, __( 'User activated a WordPress plugin', 'mwp-al-ext' ), __( 'Name: %PluginData->Name% %LineBreak% Install location: %PluginFile%', 'mwp-al-ext' ), 'plugin', 'activated' ),
					array( 5002, WSAL_HIGH, __( 'User deactivated a WordPress plugin', 'mwp-al-ext' ), __( 'Name: %PluginData->Name% %LineBreak% Install location: %PluginFile%', 'mwp-al-ext' ), 'plugin', 'deactivated' ),
					array( 5003, WSAL_HIGH, __( 'User uninstalled a plugin', 'mwp-al-ext' ), __( 'Name: %PluginData->Name% %LineBreak% Install location: %PluginFile%', 'mwp-al-ext' ), 'plugin', 'uninstalled' ),
					array( 5004, WSAL_LOW, __( 'User upgraded a plugin', 'mwp-al-ext' ), __( 'Name: %PluginData->Name% %LineBreak% Install location: %PluginFile%', 'mwp-al-ext' ), 'plugin', 'updated' ),
					array( 5010, WSAL_LOW, __( 'Plugin created tables', 'mwp-al-ext' ), __( 'Plugin created these tables in the database %LineBreak% Plugin: %Plugin->Name% %LineBreak% Tables: %TableNames%', 'mwp-al-ext' ), 'database', 'created' ),
					array( 5011, WSAL_LOW, __( 'Plugin modified tables structure', 'mwp-al-ext' ), __( 'Plugin modified the structure of these database tables %LineBreak% Plugin: %Plugin->Name% %LineBreak% Tables: %TableNames%', 'mwp-al-ext' ), 'database', 'modified' ),
					array( 5012, WSAL_MEDIUM, __( 'Plugin deleted tables', 'mwp-al-ext' ), __( 'Plugin deleted these tables from the database %LineBreak% Plugin: %Plugin->Name% %LineBreak% Tables: %TableNames%', 'mwp-al-ext' ), 'database', 'deleted' ),
					array( 5019, WSAL_MEDIUM, __( 'A plugin created a post', 'mwp-al-ext' ), __( 'Plugin %PluginName% created the post %PostTitle% %LineBreak% Post ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% %EditorLinkPage%', 'mwp-al-ext' ), 'post', 'created' ),
					array( 5025, WSAL_LOW, __( 'A plugin deleted a post', 'mwp-al-ext' ), __( 'Plugin %PluginName% deleted the post %PostTitle% %LineBreak% Post ID: %PostID% %LineBreak% Type: %PostType%', 'mwp-al-ext' ), 'post', 'deleted' ),
					array( 2051, WSAL_HIGH, __( 'User changed a file using the plugin editor', 'mwp-al-ext' ), __( 'Modified a file with the plugin editor %LineBreak% File: %File%', 'mwp-al-ext' ), 'file', 'modified' ),
				),

				__( 'Themes', 'mwp-al-ext' ) => array(
					array( 5005, WSAL_CRITICAL, __( 'User installed a theme', 'mwp-al-ext' ), __( 'Theme: "%Theme->Name%" %LineBreak% Install location: %Theme->get_template_directory%', 'mwp-al-ext' ), 'theme', 'installed' ),
					array( 5006, WSAL_HIGH, __( 'User activated a theme', 'mwp-al-ext' ), __( 'Theme "%Theme->Name%" %LineBreak% Install location: %Theme->get_template_directory%', 'mwp-al-ext' ), 'theme', 'activated' ),
					array( 5007, WSAL_HIGH, __( 'User uninstalled a theme', 'mwp-al-ext' ), __( 'Theme "%Theme->Name%" %LineBreak% Install location: %Theme->get_template_directory%', 'mwp-al-ext' ), 'theme', 'deleted' ),
					array( 5013, WSAL_LOW, __( 'Theme created tables', 'mwp-al-ext' ), __( 'Theme created these tables in the database %LineBreak% Theme: %Theme->Name% %LineBreak% Tables: %TableNames%', 'mwp-al-ext' ), 'database', 'created' ),
					array( 5014, WSAL_LOW, __( 'Theme modified tables structure', 'mwp-al-ext' ), __( 'Theme modified the structure of these database tables %LineBreak% Theme: %Theme->Name% %LineBreak% Tables: %TableNames%', 'mwp-al-ext' ), 'database', 'modified' ),
					array( 5015, WSAL_MEDIUM, __( 'Theme deleted tables', 'mwp-al-ext' ), __( 'Theme deleted these tables from the database %LineBreak% Theme: %Theme->Name% %LineBreak% Tables: %TableNames%', 'mwp-al-ext' ), 'database', 'deleted' ),
					array( 5031, WSAL_LOW, __( 'User updated a theme', 'mwp-al-ext' ), __( 'Name: %Theme->Name% %LineBreak% Install location: %Theme->get_template_directory%', 'mwp-al-ext' ), 'theme', 'updated' ),
					array( 2046, WSAL_HIGH, __( 'User changed a file using the theme editor', 'mwp-al-ext' ), __( 'Modified a file with the theme editor %LineBreak% File: %Theme%/%File%', 'mwp-al-ext' ), 'file', 'modified' ),
				),

				__( 'Themes on Multisite', 'mwp-al-ext' ) => array(
					array( 5008, WSAL_HIGH, __( 'Activated theme on network', 'mwp-al-ext' ), __( 'Network activated the theme %Theme->Name% %LineBreak% Install location: %Theme->get_template_directory%', 'mwp-al-ext' ), 'theme', 'activated' ),
					array( 5009, WSAL_MEDIUM, __( 'Deactivated theme from network', 'mwp-al-ext' ), __( 'Network deactivated the theme %Theme->Name% %LineBreak% Install location: %Theme->get_template_directory%', 'mwp-al-ext' ), 'theme', 'deactivated' ),
				),

				__( 'Database Events', 'mwp-al-ext' ) => array(
					array( 5016, WSAL_HIGH, __( 'Unknown component created tables', 'mwp-al-ext' ), __( 'An unknown component created these tables in the database %LineBreak% Tables: %TableNames%', 'mwp-al-ext' ), 'database', 'created' ),
					array( 5017, WSAL_HIGH, __( 'Unknown component modified tables structure', 'mwp-al-ext' ), __( 'An unknown component modified the structure of these database tables %LineBreak% Tables: %TableNames%', 'mwp-al-ext' ), 'database', 'modified' ),
					array( 5018, WSAL_HIGH, __( 'Unknown component deleted tables', 'mwp-al-ext' ), __( 'An unknown component deleted these tables from the database %LineBreak% Tables: %TableNames%', 'mwp-al-ext' ), 'database', 'deleted' ),
				),
			),

			__( 'WordPress & System', 'mwp-al-ext' ) => array(
				__( 'System', 'mwp-al-ext' ) => array(
					array( 0000, E_CRITICAL, __( 'Unknown Error', 'mwp-al-ext' ), __( 'An unexpected error has occurred .', 'mwp-al-ext' ) ),
					array( 0001, E_CRITICAL, __( 'PHP error', 'mwp-al-ext' ), __( '%Message%.', 'mwp-al-ext' ) ),
					array( 0002, E_WARNING, __( 'PHP warning', 'mwp-al-ext' ), __( '%Message%.', 'mwp-al-ext' ) ),
					array( 0003, E_NOTICE, __( 'PHP notice', 'mwp-al-ext' ), __( '%Message%.', 'mwp-al-ext' ) ),
					array( 0004, E_CRITICAL, __( 'PHP exception', 'mwp-al-ext' ), __( '%Message%.', 'mwp-al-ext' ) ),
					array( 0005, E_CRITICAL, __( 'PHP shutdown error', 'mwp-al-ext' ), __( '%Message%.', 'mwp-al-ext' ) ),
					array( 6004, WSAL_MEDIUM, __( 'WordPress was updated', 'mwp-al-ext' ), __( 'Updated WordPress %LineBreak% Previous version: %OldVersion% %LineBreak% New version: %NewVersion%', 'mwp-al-ext' ), 'system', 'updated' ),
					array( 9999, E_CRITICAL, __( 'Advertising Add-ons', 'mwp-al-ext' ), __( '%PromoName% %PromoMessage%', 'mwp-al-ext' ) ),
				),

				__( 'Activity log plugin', 'mwp-al-ext' ) => array(
					array( 6000, WSAL_INFORMATIONAL, __( 'Events automatically pruned by system', 'mwp-al-ext' ), __( 'System automatically deleted %EventCount% event(s)', 'mwp-al-ext' ), 'activity-logs', 'deleted' ),
					array( 6006, WSAL_MEDIUM, __( 'Reset plugin\'s settings to default', 'mwp-al-ext' ), __( 'Reset the WP Activity Log plugin settings to default', 'mwp-al-ext' ), 'plugin', 'modified' ),
					array( 6034, WSAL_CRITICAL, __( 'Purged the activity log', 'mwp-al-ext' ), __( 'Purged the activity log', 'mwp-al-ext' ), 'activity-logs', 'deleted' ),
				),

				__( 'File Changes', 'mwp-al-ext' ) => array(
					array( 6028, WSAL_HIGH, __( 'File content has been modified', 'mwp-al-ext' ), __( 'Content of the file on site modified %LineBreak% File: %File% %LineBreak% Location: %FileLocation%', 'mwp-al-ext' ), 'file', 'modified' ),
					array( 6029, WSAL_CRITICAL, __( 'File added to the site', 'mwp-al-ext' ), __( 'File added to site %LineBreak% File: %File% %LineBreak% Location: %FileLocation%', 'mwp-al-ext' ), 'file', 'added' ),
					array( 6030, WSAL_MEDIUM, __( 'File deleted from the site', 'mwp-al-ext' ), __( 'File deleted from site %LineBreak% File: %File% %LineBreak% Location: %FileLocation%', 'mwp-al-ext' ), 'file', 'deleted' ),
					array( 6031, WSAL_INFORMATIONAL, __( 'File not scanned because it is bigger than the maximum file size limit', 'mwp-al-ext' ), __( 'File not scanned because it is bigger than the maximum file size limit %LineBreak% File: %File% %LineBreak% Location: %FileLocation% %LineBreak% %FileSettings%', 'mwp-al-ext' ), 'system', 'blocked' ),
					array( 6032, WSAL_INFORMATIONAL, __( 'File integrity scan stopped due to the limit of 1 million files', 'mwp-al-ext' ), __( 'Your website has more than 1 million files so the file integrity scanner cannot scan them all. Contact support for more information. %LineBreak% %ContactSupport%', 'mwp-al-ext' ), 'system', 'blocked' ),
					array( 6033, WSAL_INFORMATIONAL, __( 'File integrity scan started/stopped', 'mwp-al-ext' ), __( 'The file integrity scan has %ScanStatus%.', 'mwp-al-ext' ), 'system', 'started' ),
				),

				__( 'User/Visitor Actions', 'mwp-al-ext' ) => array(
					array( 6007, WSAL_INFORMATIONAL, __( 'User requests non-existing pages (404 Error Pages)', 'mwp-al-ext' ), __( 'Has requested a non existing page (404 error) %LineBreak% Number of times: %Attempts%', 'mwp-al-ext' ), 'system', 'opened' ),
					array( 6023, WSAL_INFORMATIONAL, __( 'Website Visitor User requests non-existing pages (404 Error Pages)', 'mwp-al-ext' ), __( 'Website visitor has requested a non existing page (404 error) %LineBreak% Number of times: %Attempts%', 'mwp-al-ext' ), 'system', 'opened' ),
				),

				__( 'WordPress Site Settings', 'mwp-al-ext' ) => array(
					array( 6001, WSAL_CRITICAL, __( 'Option Anyone Can Register in WordPress settings changed', 'mwp-al-ext' ), __( 'The option Anyone can register', 'mwp-al-ext' ), 'system-setting', 'enabled' ),
					array( 6002, WSAL_CRITICAL, __( 'New User Default Role changed', 'mwp-al-ext' ), __( 'Changed the new user default role %LineBreak% Previous role: %OldRole% %LineBreak% New role: %NewRole%', 'mwp-al-ext' ), 'system-setting', 'modified' ),
					array( 6003, WSAL_CRITICAL, __( 'WordPress Administrator Notification email changed', 'mwp-al-ext' ), __( 'Changed the WordPress administrator notification email address %LineBreak% Previous address %OldEmail% %LineBreak% New address: %NewEmail%', 'mwp-al-ext' ), 'system-setting', 'modified' ),
					array( 6005, WSAL_HIGH, __( 'User changes the WordPress Permalinks', 'mwp-al-ext' ), __( 'Changed the WordPress permalinks %LineBreak% Previous permalinks: %OldPattern% %LineBreak% New permalinks: %NewPattern%', 'mwp-al-ext' ), 'system-setting', 'modified' ),
					array( 6008, WSAL_INFORMATIONAL, __( 'Enabled/Disabled the option Discourage search engines from indexing this site', 'mwp-al-ext' ), __( 'Discourage search engines from indexing this site.', 'mwp-al-ext' ), 'system-setting', 'enabled' ),
					array( 6009, WSAL_MEDIUM, __( 'Enabled/Disabled comments on all the website', 'mwp-al-ext' ), __( 'Comments on the website', 'mwp-al-ext' ), 'system-setting', 'enabled' ),
					array( 6010, WSAL_MEDIUM, __( 'Enabled/Disabled the option Comment author must fill out name and email', 'mwp-al-ext' ), __( 'The option Comment author must fill out name and email', 'mwp-al-ext' ), 'system-setting', 'enabled' ),
					array( 6011, WSAL_MEDIUM, __( 'Enabled/Disabled the option Users must be logged in and registered to comment', 'mwp-al-ext' ), __( 'The option Users must be logged in and registered to comment', 'mwp-al-ext' ), 'system-setting', 'enabled' ),
					array( 6012, WSAL_INFORMATIONAL, __( 'Enabled/Disabled the option to automatically close comments', 'mwp-al-ext' ), __( 'The option to Automatically close comments after %Value% days', 'mwp-al-ext' ), 'system-setting', 'enabled' ),
					array( 6013, WSAL_INFORMATIONAL, __( 'Changed the value of the option Automatically close comments', 'mwp-al-ext' ), __( 'Changed the value of the option to Automatically close comments after a number of days %LineBreak% Previous value: %OldValue% %LineBreak% New value: %NewValue%', 'mwp-al-ext' ), 'system-setting', 'modified' ),
					array( 6014, WSAL_MEDIUM, __( 'Enabled/Disabled the option for comments to be manually approved', 'mwp-al-ext' ), __( 'The option for comments to be manually approved', 'mwp-al-ext' ), 'system-setting', 'enabled' ),
					array( 6015, WSAL_LOW, __( 'Enabled/Disabled the option for an author to have previously approved comments for the comments to appear', 'mwp-al-ext' ), __( 'The option for an author to have previously approved comments for the comments to appear', 'mwp-al-ext' ), 'system-setting', 'enabled' ),
					array( 6016, WSAL_LOW, __( 'Changed the number of links that a comment must have to be held in the queue', 'mwp-al-ext' ), __( 'Changed the minimum number of links a comment must have to be held in the queue %LineBreak% Previous value: %OldValue% %LineBreak% New value: %NewValue%', 'mwp-al-ext' ), 'system-setting', 'modified' ),
					array( 6017, WSAL_INFORMATIONAL, __( 'Modified the list of keywords for comments moderation', 'mwp-al-ext' ), __( 'Modified the list of keywords for comments medoration', 'mwp-al-ext' ), 'system-setting', 'modified' ),
					array( 6018, WSAL_INFORMATIONAL, __( 'Modified the list of keywords for comments blacklisting', 'mwp-al-ext' ), __( 'Modified the list of keywords for comments blacklisting', 'mwp-al-ext' ), 'system-setting', 'modified' ),
					array( 6024, WSAL_CRITICAL, __( 'Option WordPress Address (URL) in WordPress settings changed', 'mwp-al-ext' ), __( 'Changed the WordPress address (URL) %LineBreak% Previous URL: %old_url% %LineBreak% New URL: %new_url%', 'mwp-al-ext' ), 'system-setting', 'modified' ),
					array( 6025, WSAL_CRITICAL, __( 'Option Site Address (URL) in WordPress settings changed', 'mwp-al-ext' ), __( 'Changed the site address (URL) %LineBreak% Previous URL: %old_url% %LineBreak% New URL: %new_url%', 'mwp-al-ext' ), 'system-setting', 'modified' ),
				),
			),

			__( 'bbPress Forums', 'mwp-al-ext' ) => array(
				__( 'Forums', 'mwp-al-ext' ) => array(
					array( 8000, WSAL_INFORMATIONAL, __( 'User created new forum', 'mwp-al-ext' ), __( 'New forum called %ForumName% %LineBreak% %EditorLinkForum%', 'mwp-al-ext' ), 'bbpress-forum', 'created' ),
					array( 8001, WSAL_MEDIUM, __( 'User changed status of a forum', 'mwp-al-ext' ), __( 'Changed the status of the forum %ForumName% %LineBreak% Previous Status: %OldStatus% %LineBreak% New Status: %NewStatus% %LineBreak% %EditorLinkForum%', 'mwp-al-ext' ), 'bbpress-forum', 'modified' ),
					array( 8002, WSAL_MEDIUM, __( 'User changed visibility of a forum', 'mwp-al-ext' ), __( 'Changed the visibility of the forum %ForumName% %LineBreak% Previous visibility: %OldVisibility% %LineBreak% New visibility: %NewVisibility% %LineBreak% %EditorLinkForum%', 'mwp-al-ext' ), 'bbpress-forum', 'modified' ),
					array( 8003, WSAL_LOW, __( 'User changed the URL of a forum', 'mwp-al-ext' ), __( 'Changed the URL of the forum %ForumName% %LineBreak% Previous URL: %OldUrl% %LineBreak% New URL: %NewUrl% %LineBreak% %EditorLinkForum%', 'mwp-al-ext' ), 'bbpress-forum', 'modified' ),
					array( 8004, WSAL_INFORMATIONAL, __( 'User changed order of a forum', 'mwp-al-ext' ), __( 'Changed the sorting order of the forum %ForumName% %LineBreak% Previous sorting order: %OldOrder% %LineBreak% New sorting order: %NewOrder% %LineBreak% %EditorLinkForum%', 'mwp-al-ext' ), 'bbpress-forum', 'modified' ),
					array( 8005, WSAL_HIGH, __( 'User moved forum to trash', 'mwp-al-ext' ), __( 'Moved the forum %ForumName% to trash', 'mwp-al-ext' ), 'bbpress-forum', 'deleted' ),
					array( 8006, WSAL_HIGH, __( 'User permanently deleted forum', 'mwp-al-ext' ), __( 'Permanently deleted the forum %ForumName%', 'mwp-al-ext' ), 'bbpress-forum', 'deleted' ),
					array( 8007, WSAL_HIGH, __( 'User restored forum from trash', 'mwp-al-ext' ), __( 'Restored the forum %ForumName% from trash', 'mwp-al-ext' ), 'bbpress-forum', 'restored' ),
					array( 8008, WSAL_LOW, __( 'User changed the parent of a forum', 'mwp-al-ext' ), __( 'Changed the parent of the forum %ForumName% %LineBreak% Previous parent: %OldParent% %LineBreak% New parent: %NewParent% %LineBreak% %EditorLinkForum%', 'mwp-al-ext' ), 'bbpress-forum', 'modified' ),
					array( 8011, WSAL_LOW, __( 'User changed type of a forum', 'mwp-al-ext' ), __( 'Changed the type of the forum %ForumName% %LineBreak% Previous type: %OldType% %LineBreak% New type: %NewType% %LineBreak% %EditorLinkForum%', 'mwp-al-ext' ), 'bbpress-forum', 'modified' ),
				),

				__( 'bbPress Forum Topics', 'mwp-al-ext' ) => array(
					array( 8014, WSAL_INFORMATIONAL, __( 'User created new topic', 'mwp-al-ext' ), __( 'New topic called %TopicName% %LineBreak% %EditorLinkTopic%', 'mwp-al-ext' ), 'bbpress-forum', 'created' ),
					array( 8015, WSAL_INFORMATIONAL, __( 'User changed status of a topic', 'mwp-al-ext' ), __( 'Changed the status of the topic %TopicName% %LineBreak% Previous status: %OldStatus% %LineBreak% New status: %NewStatus% %LineBreak% %EditorLinkTopic%', 'mwp-al-ext' ), 'bbpress-forum', 'modified' ),
					array( 8016, WSAL_INFORMATIONAL, __( 'User changed type of a topic', 'mwp-al-ext' ), __( 'Changed the type of the topic %TopicName% %LineBreak% Previous type: %OldType% %LineBreak% New type: %NewType% %LineBreak% %EditorLinkTopic%', 'mwp-al-ext' ), 'bbpress-forum', 'modified' ),
					array( 8017, WSAL_INFORMATIONAL, __( 'User changed URL of a topic', 'mwp-al-ext' ), __( 'Changed the URL of the topic %TopicName% %LineBreak% Previous URL: %OldUrl% %LineBreak% New URL: %NewUrl% %LineBreak% %EditorLinkTopic%', 'mwp-al-ext' ), 'bbpress-forum', 'modified' ),
					array( 8018, WSAL_INFORMATIONAL, __( 'User changed the forum of a topic', 'mwp-al-ext' ), __( 'Changed the forum of the topic %TopicName% %LineBreak% Previous forum: %OldForum% %LineBreak% New forum: %NewForum% %LineBreak% %EditorLinkTopic%', 'mwp-al-ext' ), 'bbpress-forum', 'modified' ),
					array( 8019, WSAL_MEDIUM, __( 'User moved topic to trash', 'mwp-al-ext' ), __( 'Moved the %TopicName% to trash', 'mwp-al-ext' ), 'bbpress-forum', 'deleted' ),
					array( 8020, WSAL_MEDIUM, __( 'User permanently deleted topic', 'mwp-al-ext' ), __( 'Permanently deleted the topic %TopicName%', 'mwp-al-ext' ), 'bbpress-forum', 'deleted' ),
					array( 8021, WSAL_INFORMATIONAL, __( 'User restored topic from trash', 'mwp-al-ext' ), __( 'Restored the topic %TopicName% from trash', 'mwp-al-ext' ), 'bbpress-forum', 'restored' ),
					array( 8022, WSAL_LOW, __( 'User changed visibility of a topic', 'mwp-al-ext' ), __( 'Changed the visibility of the topic %TopicName% %LineBreak% Previous visibility: %OldVisibility% %LineBreak% New visibility: %NewVisibility% %LineBreak% %EditorLinkTopic%', 'mwp-al-ext' ), 'bbpress-forum', 'modified' ),
				),

				__( 'bbPress Settings', 'mwp-al-ext' ) => array(
					array( 8009, WSAL_HIGH, __( 'User changed forum\'s role', 'mwp-al-ext' ), __( 'Changed the forum user\'s auto role %LineBreak% Previous role: %OldRole% %LineBreak% New role: %NewRole%', 'mwp-al-ext' ), 'bbpress', 'modified' ),
					array( 8010, WSAL_CRITICAL, __( 'User changed option of a forum', 'mwp-al-ext' ), __( 'The option for anonymous posting on the forums', 'mwp-al-ext' ), 'bbpress', 'enabled' ),
					array( 8012, WSAL_MEDIUM, __( 'User changed time to disallow post editing', 'mwp-al-ext' ), __( 'Changed the time to disallow post editing in the forums %LineBreak% Previous time: %OldTime% %LineBreak% New time: %NewTime%', 'mwp-al-ext' ), 'bbpress', 'modified' ),
					array( 8013, WSAL_HIGH, __( 'User changed the forum setting posting throttle time', 'mwp-al-ext' ), __( 'Changed the posting throttle time in the forums %LineBreak% Previous time: %OldTime% %LineBreak% New time: %NewTime%', 'mwp-al-ext' ), 'bbpress', 'modified' ),
				),
			),

			__( 'Multisite Network Sites', 'mwp-al-ext' ) => array(
				__( 'MultiSite', 'mwp-al-ext' ) => array(
					array( 7000, WSAL_CRITICAL, __( 'New site added on the network', 'mwp-al-ext' ), __( 'New site on the network: %SiteName% %LineBreak% URL: %BlogURL%', 'mwp-al-ext' ), 'multisite-network', 'added' ),
					array( 7001, WSAL_HIGH, __( 'Existing site archived', 'mwp-al-ext' ), __( 'Archived the site: %SiteName% %LineBreak% URL: %BlogURL%', 'mwp-al-ext' ), 'multisite-network', 'modified' ),
					array( 7002, WSAL_HIGH, __( 'Archived site has been unarchived', 'mwp-al-ext' ), __( 'Unarchived the site: %SiteName% %LineBreak% URL: %BlogURL%', 'mwp-al-ext' ), 'multisite-network', 'modified' ),
					array( 7003, WSAL_HIGH, __( 'Deactivated site has been activated', 'mwp-al-ext' ), __( 'Activated the site: %SiteName% %LineBreak% URL: %BlogURL%', 'mwp-al-ext' ), 'multisite-network', 'activated' ),
					array( 7004, WSAL_HIGH, __( 'Site has been deactivated', 'mwp-al-ext' ), __( 'Deactivated the site: %SiteName% %LineBreak% URL: %BlogURL%', 'mwp-al-ext' ), 'multisite-network', 'deactivated' ),
					array( 7005, WSAL_HIGH, __( 'Existing site deleted from network', 'mwp-al-ext' ), __( 'The site: %SiteName% %LineBreak% URL: %BlogURL%', 'mwp-al-ext' ), 'multisite-network', 'deleted' ),
				),
			),

			__( 'WooCommerce', 'mwp-al-ext' ) => array(
				__( 'Products', 'mwp-al-ext' ) => array(
					array( 9000, WSAL_LOW, __( 'User created a new product', 'mwp-al-ext' ), __( 'Created a new product called %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'created' ),
					array( 9001, WSAL_MEDIUM, __( 'User published a product', 'mwp-al-ext' ), __( 'Published the product called %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% Product URL is: %PostUrl% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'published' ),
					array( 9003, WSAL_LOW, __( 'User changed the category of a product', 'mwp-al-ext' ), __( 'Changed the category of the product %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% Previous categories: %OldCategories% %LineBreak% New categories: %NewCategories% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'modified' ),
					array( 9004, WSAL_INFORMATIONAL, __( 'User modified the short description of a product', 'mwp-al-ext' ), __( 'Changed the short description of the product %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'modified' ),
					array( 9005, WSAL_LOW, __( 'User modified the text of a product', 'mwp-al-ext' ), __( 'Changed the text of the product %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'modified' ),
					array( 9006, WSAL_LOW, __( 'User changed the URL of a product', 'mwp-al-ext' ), __( 'Changed the URL of the product %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% Previous URL: %OldUrl% %LineBreak% New URL: %NewUrl% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'modified' ),
					array( 9007, WSAL_MEDIUM, __( 'User changed the Product Data of a product', 'mwp-al-ext' ), __( 'Changed the type of the product %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% Previous type: %OldType% %LineBreak% New type: %NewType% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'modified' ),
					array( 9008, WSAL_INFORMATIONAL, __( 'User changed the date of a product', 'mwp-al-ext' ), __( 'Changed the date of the product %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% Previous date: %OldDate% %LineBreak% New date: %NewDate% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'modified' ),
					array( 9009, WSAL_MEDIUM, __( 'User changed the visibility of a product', 'mwp-al-ext' ), __( 'Changed the visibility of the product %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% Previous visibility: %OldVisibility% %LineBreak% New visibility: %NewVisibility% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'modified' ),
					array( 9010, WSAL_MEDIUM, __( 'User modified the product', 'mwp-al-ext' ), __( 'Modified the product %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'modified' ),
					array( 9011, E_NOTICE, __( 'User modified the draft product', 'mwp-al-ext' ), __( 'Modified the draft product %ProductTitle%. View the product: %EditorLinkProduct%.', 'mwp-al-ext' ), 'woocommerce-product' ),
					array( 9012, WSAL_HIGH, __( 'User moved a product to trash', 'mwp-al-ext' ), __( 'Moved the product %ProductTitle% to trash %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus%', 'mwp-al-ext' ), 'woocommerce-product', 'deleted' ),
					array( 9013, WSAL_MEDIUM, __( 'User permanently deleted a product', 'mwp-al-ext' ), __( 'Permanently deleted the product %ProductTitle% %LineBreak% ID: %PostID%', 'mwp-al-ext' ), 'woocommerce-product', 'deleted' ),
					array( 9014, WSAL_HIGH, __( 'User restored a product from the trash', 'mwp-al-ext' ), __( 'Restored the product %ProductTitle% from trash %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'restored' ),
					array( 9015, WSAL_MEDIUM, __( 'User changed status of a product', 'mwp-al-ext' ), __( 'Changed the status of the product %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Previous status: %OldStatus% %LineBreak% New status: %NewStatus% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'modified' ),
					array( 9072, WSAL_INFORMATIONAL, __( 'User opened a product in the editor', 'mwp-al-ext' ), __( 'Opened the product %ProductTitle% in the editor %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'opened' ),
					array( 9073, WSAL_INFORMATIONAL, __( 'User viewed a product', 'mwp-al-ext' ), __( 'Viewed the product %ProductTitle% page %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'viewed' ),
					array( 9077, WSAL_MEDIUM, __( 'User renamed a product', 'mwp-al-ext' ), __( 'Renamed the product %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% Previous name: %OldTitle% %LineBreak% New name: %NewTitle% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'modified' ),
					array( 9016, WSAL_MEDIUM, __( 'User changed type of a price', 'mwp-al-ext' ), __( 'Changed the %PriceType% of the product %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% Previous price: %OldPrice% %LineBreak% New price: %NewPrice% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'modified' ),
					array( 9017, WSAL_MEDIUM, __( 'User changed the SKU of a product', 'mwp-al-ext' ), __( 'Changed the SKU of the product %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% Previous SKU: %OldSku% %LineBreak% New SKU: %NewSku% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'modified' ),
					array( 9018, WSAL_LOW, __( 'User changed the stock status of a product', 'mwp-al-ext' ), __( 'Changed the stock status of the product %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% Previous stock status: %OldStatus% %LineBreak% New stock status: %NewStatus% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'modified' ),
					array( 9019, WSAL_LOW, __( 'User changed the stock quantity', 'mwp-al-ext' ), __( 'Changed the stock quantity of the product %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% Previous quantity: %OldValue% %LineBreak% New quantity: %NewValue% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'modified' ),
					array( 9020, WSAL_MEDIUM, __( 'User set a product type', 'mwp-al-ext' ), __( 'Changed the type of the %NewType% product %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% Previous type: %OldType% %LineBreak% New type: %NewType% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'modified' ),
					array( 9021, WSAL_INFORMATIONAL, __( 'User changed the weight of a product', 'mwp-al-ext' ), __( 'Changed the weight of the product %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% Previous weight: %OldWeight% %LineBreak% New weight: %NewWeight% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'modified' ),
					array( 9022, WSAL_INFORMATIONAL, __( 'User changed the dimensions of a product', 'mwp-al-ext' ), __( 'Changed the %DimensionType% dimensions of the product %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% Previous value: %OldDimension% %LineBreak% New value: %NewDimension% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'modified' ),
					array( 9023, WSAL_MEDIUM, __( 'User added the Downloadable File to a product', 'mwp-al-ext' ), __( 'Added a downloadable file to the product %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% File name: %FileName% %LineBreak% File URL: %FileUrl% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'modified' ),
					array( 9024, WSAL_MEDIUM, __( 'User Removed the Downloadable File from a product', 'mwp-al-ext' ), __( 'Removed the downloadable file from the product %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% File name: %FileName% %LineBreak% File URL: %FileUrl% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'modified' ),
					array( 9025, WSAL_INFORMATIONAL, __( 'User changed the name of a Downloadable File in a product', 'mwp-al-ext' ), __( 'Changed the name of the downloadable file to the product %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% Previous file name: %OldName% %LineBreak% New file name: %NewName% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'modified' ),
					array( 9026, WSAL_MEDIUM, __( 'User changed the URL of the Downloadable File in a product', 'mwp-al-ext' ), __( 'Changed the URL of the downloadable file to the product %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% File name: %FileName% %LineBreak% Previous URL: %OldUrl% %LineBreak% New URL: %NewUrl% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'modified' ),
					array( 9042, WSAL_INFORMATIONAL, __( 'User changed the catalog visibility of a product', 'mwp-al-ext' ), __( 'Changed the product visibility of the product %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% Previous setting: %OldVisibility% %LineBreak% New setting: %NewVisibility% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'modified' ),
					array( 9043, WSAL_INFORMATIONAL, __( 'User changed the setting Featured Product of a product', 'mwp-al-ext' ), __( 'The setting Featured Product for the product %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'enabled' ),
					array( 9044, WSAL_INFORMATIONAL, __( 'User changed the Allow Backorders setting of a product', 'mwp-al-ext' ), __( 'Changed the Allow Backorders setting for the product %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% Previous status: %OldStatus% %LineBreak% New status: %NewStatus% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'modified' ),
					array( 9045, WSAL_MEDIUM, __( 'User added/removed products to upsell of a product', 'mwp-al-ext' ), __( 'Products to Upsell in the product %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% Product: %UpsellTitle% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'added' ),
					array( 9046, WSAL_MEDIUM, __( 'User added/removed products to cross-sells of a product', 'mwp-al-ext' ), __( 'Product to Cross-sell in the product %ProductTitle% %LineBreak% ID: %PostID% %LineBreak% Status: %ProductStatus% %LineBreak% Product: %CrossSellTitle% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'added' ),
					array( 9047, WSAL_LOW, __( 'Added a new attribute of a product', 'mwp-al-ext' ), __( 'A new attribute to the product %ProductTitle% %LineBreak% ID: %ProductID% %LineBreak% Status: %ProductStatus% %LineBreak% Attribute name: %AttributeName% %LineBreak% Attribute value: %AttributeValue% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'added' ),
					array( 9048, WSAL_LOW, __( 'Modified the value of an attribute of a product', 'mwp-al-ext' ), __( 'Modified the value of an attribute in the product %ProductTitle% %LineBreak% ID: %ProductID% %LineBreak% Status: %ProductStatus% %LineBreak% Attribute name: %AttributeName% %LineBreak% Previous attribute value: %OldValue% %LineBreak% New attribute value: %NewValue% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'modified' ),
					array( 9049, WSAL_LOW, __( 'Changed the name of an attribute of a product', 'mwp-al-ext' ), __( 'Changed the name of an attribute in the product %ProductTitle% %LineBreak% ID: %ProductID% %LineBreak% Status: %ProductStatus% %LineBreak% Previous attribute name: %OldValue% %LineBreak% New attribute name: %NewValue% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'modified' ),
					array( 9050, WSAL_LOW, __( 'Deleted an attribute of a product', 'mwp-al-ext' ), __( 'An attribute from the product %ProductTitle% %LineBreak% ID: %ProductID% %LineBreak% Status: %ProductStatus% %LineBreak% Attribute name: %AttributeName% %LineBreak% Attribute value: %AttributeValue% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'deleted' ),
					array( 9051, WSAL_LOW, __( 'Set the attribute visibility of a product', 'mwp-al-ext' ), __( 'Changed the visibility of an attribute in the product %ProductTitle% %LineBreak% ID: %ProductID% %LineBreak% Status: %ProductStatus% %LineBreak% Attribute name: %AttributeName% %LineBreak% Attribute visibility: %AttributeVisiblilty% %LineBreak% %EditorLinkProduct%', 'mwp-al-ext' ), 'woocommerce-product', 'modified' ),
				),

				__( 'Store', 'mwp-al-ext' ) => array(
					array( 9027, WSAL_HIGH, __( 'User changed the Weight Unit', 'mwp-al-ext' ), __( 'Changed the weight unit of the store %LineBreak% Previous weight unit: %OldUnit% %LineBreak% New weight unit: %NewUnit%', 'mwp-al-ext' ), 'woocommerce-store', 'modified' ),
					array( 9028, WSAL_HIGH, __( 'User changed the Dimensions Unit', 'mwp-al-ext' ), __( 'Changed the dimensions unit of the store %LineBreak% Previous dimensions unit: %OldUnit% %LineBreak% New dimensions unit: %NewUnit%', 'mwp-al-ext' ), 'woocommerce-store', 'modified' ),
					array( 9029, WSAL_HIGH, __( 'User changed the Base Location', 'mwp-al-ext' ), __( 'Changed the base location %LineBreak% Previous location: %OldLocation% %LineBreak% New location: %NewLocation%', 'mwp-al-ext' ), 'woocommerce-store', 'modified' ),
					array( 9030, WSAL_HIGH, __( 'User enabled/disabled taxes', 'mwp-al-ext' ), __( 'Taxes in the WooCommerce store', 'mwp-al-ext' ), 'woocommerce-store', 'enabled' ),
					array( 9031, WSAL_HIGH, __( 'User changed the currency', 'mwp-al-ext' ), __( 'Changed the currency of the store %LineBreak% Previous currency: %OldCurrency% %LineBreak% New currency: %NewCurrency%', 'mwp-al-ext' ), 'woocommerce-store', 'modified' ),
					array( 9032, WSAL_HIGH, __( 'User enabled/disabled the use of coupons during checkout', 'mwp-al-ext' ), __( 'The use of coupons during checkout', 'mwp-al-ext' ), 'woocommerce-store', 'enabled' ),
					array( 9033, WSAL_MEDIUM, __( 'User enabled/disabled guest checkout', 'mwp-al-ext' ), __( 'Guest checkout in the store', 'mwp-al-ext' ), 'woocommerce-store', 'enabled' ),
					array( 9034, WSAL_HIGH, __( 'User enabled/disabled cash on delivery', 'mwp-al-ext' ), __( 'The option cash on delivery', 'mwp-al-ext' ), 'woocommerce-store', 'enabled' ),
				),

				__( 'Payment Gateways', 'mwp-al-ext' ) => array(
					array( 9074, WSAL_HIGH, __( 'User enabled/disabled a payment gateway', 'mwp-al-ext' ), __( 'The payment gateway %GatewayName%', 'mwp-al-ext' ), 'woocommerce-store', 'enabled' ),
					array( 9075, E_CRITICAL, __( 'User disabled a payment gateway', 'mwp-al-ext' ), __( 'Disabled the payment gateway %GatewayName%', 'mwp-al-ext' ), 'woocommerce-store' ),
					array( 9076, WSAL_HIGH, __( 'User modified a payment gateway', 'mwp-al-ext' ), __( 'The payment gateway %GatewayName%', 'mwp-al-ext' ), 'woocommerce-store', 'modified' ),
				),

				__( 'Tax Settings', 'mwp-al-ext' ) => array(
					array( 9078, WSAL_LOW, __( 'User modified prices with tax option', 'mwp-al-ext' ), __( 'Set the option that prices are %TaxStatus% of tax', 'mwp-al-ext' ), 'woocommerce-store', 'modified' ),
					array( 9079, WSAL_LOW, __( 'User modified tax calculation base', 'mwp-al-ext' ), __( 'Set the setting Calculate tax based on to %Setting%', 'mwp-al-ext' ), 'woocommerce-store', 'modified' ),
					array( 9080, WSAL_MEDIUM, __( 'User modified shipping tax class', 'mwp-al-ext' ), __( 'Set the Shipping tax class to %Setting%', 'mwp-al-ext' ), 'woocommerce-store', 'modified' ),
					array( 9081, WSAL_MEDIUM, __( 'User enabled/disabled rounding of tax', 'mwp-al-ext' ), __( 'Rounding of tax at subtotal level', 'mwp-al-ext' ), 'woocommerce-store', 'enabled' ),
					array( 9082, WSAL_MEDIUM, __( 'User modified a shipping zone', 'mwp-al-ext' ), __( 'The shipping zone %ShippingZoneName%', 'mwp-al-ext' ), 'woocommerce-store', 'created' ),
				),

				__( 'WC Categories', 'mwp-al-ext' ) => array(
					array( 9002, WSAL_INFORMATIONAL, __( 'User created a new product category', 'mwp-al-ext' ), __( 'A new product category called %CategoryName% %LineBreak% Category slug is %Slug% %LineBreak% %ProductCatLink%', 'mwp-al-ext' ), 'woocommerce-store', 'created' ),
					array( 9052, WSAL_MEDIUM, __( 'User deleted a product category', 'mwp-al-ext' ), __( 'The product category called %CategoryName% %LineBreak% Category slug: %CategorySlug%', 'mwp-al-ext' ), 'woocommerce-store', 'deleted' ),
					array( 9053, WSAL_INFORMATIONAL, __( 'User changed the slug of a product category', 'mwp-al-ext' ), __( 'Changed the slug of the product category called %CategoryName% %LineBreak% Previous category slug: %OldSlug% %LineBreak% New category slug: %NewSlug% %LineBreak% %ProductCatLink%', 'mwp-al-ext' ), 'woocommerce-store', 'modified' ),
					array( 9054, WSAL_MEDIUM, __( 'User changed the parent category of a product category', 'mwp-al-ext' ), __( 'Changed the parent of the product category %CategoryName% %LineBreak% Category slug: %CategorySlug% Previous parent: %OldParentCat% %LineBreak% New parent: %NewParentCat% %LineBreak% %ProductCatLink%', 'mwp-al-ext' ), 'woocommerce-store', 'modified' ),
					array( 9055, WSAL_INFORMATIONAL, __( 'User changed the display type of a product category', 'mwp-al-ext' ), __( 'Changed the display type of the product category %CategoryName% %LineBreak% %CategorySlug% %LineBreak% Previous display type: %OldDisplayType% %LineBreak% New display type: %NewDisplayType% %LineBreak% %ProductCatLink%', 'mwp-al-ext' ), 'woocommerce-store', 'modified' ),
					array( 9056, WSAL_LOW, __( 'User changed the name of a product category', 'mwp-al-ext' ), __( 'Changed the name of the product category %CategoryName% %LineBreak% %CategorySlug% %LineBreak% Previous name: %OldName% %LineBreak% New name: %NewName% %LineBreak% %ProductCatLink%', 'mwp-al-ext' ), 'woocommerce-store', 'modified' ),
				),

				__( 'Attributes', 'mwp-al-ext' ) => array(
					array( 9057, WSAL_MEDIUM, __( 'User created a new attribute', 'mwp-al-ext' ), __( 'A new attribute in WooCommerce called %AttributeName% %LineBreak% Attribute slug: %AttributeSlug%', 'mwp-al-ext' ), 'woocommerce-store', 'created' ),
					array( 9058, WSAL_LOW, __( 'User deleted an attribute', 'mwp-al-ext' ), __( 'The WooCommerce attribute called %AttributeName% %LineBreak% Attribute slug: %AttributeSlug%', 'mwp-al-ext' ), 'woocommerce-store', 'deleted' ),
					array( 9059, WSAL_LOW, __( 'User changed the slug of an attribute', 'mwp-al-ext' ), __( 'Changed the slug of the WooCommerce attribute %AttributeName% %LineBreak% Previous slug: %OldSlug% %LineBreak% New slug: %NewSlug%', 'mwp-al-ext' ), 'woocommerce-store', 'modified' ),
					array( 9060, WSAL_LOW, __( 'User changed the name of an attribute', 'mwp-al-ext' ), __( 'Changed the name of the WooCommerce attribute %AttributeName% %LineBreak% Attribute slug: %AttributeSlug% %LineBreak% Previous name: %OldName% %LineBreak% New name: %NewName%', 'mwp-al-ext' ), 'woocommerce-store', 'modified' ),
					array( 9061, WSAL_LOW, __( 'User changed the default sort order of an attribute', 'mwp-al-ext' ), __( 'Changed the Default Sort Order of the attribute %AttributeName% in WooCommerce %LineBreak% Attribute slug: %AttributeSlug% %LineBreak% Previous sorting order: %OldSortOrder% %LineBreak% New sorting order: %NewSortOrder%', 'mwp-al-ext' ), 'woocommerce-store', 'modified' ),
					array( 9062, WSAL_LOW, __( 'User enabled/disabled the option Enable Archives of an attribute', 'mwp-al-ext' ), __( 'The option Enable Archives in WooCommerce attribute %AttributeName%', 'mwp-al-ext' ), 'woocommerce-store', 'enabled' ),
				),

				__( 'Coupons', 'mwp-al-ext' ) => array(
					array( 9063, WSAL_LOW, __( 'User published a new coupon', 'mwp-al-ext' ), __( 'Published a new coupon called %CouponName% %LineBreak% %EditorLinkCoupon%', 'mwp-al-ext' ), 'woocommerce-store', 'published' ),
					array( 9064, WSAL_LOW, __( 'User changed the discount type of a coupon', 'mwp-al-ext' ), __( 'Changed the Discount Type in coupon %CouponName% %LineBreak% Previous discount type: %OldDiscountType% %LineBreak% New discount type: %NewDiscountType% %LineBreak% %EditorLinkCoupon%', 'mwp-al-ext' ), 'woocommerce-store', 'modified' ),
					array( 9065, WSAL_LOW, __( 'User changed the coupon amount of a coupon', 'mwp-al-ext' ), __( 'Changed the Coupon amount in coupon %CouponName% %LineBreak% Previous amount: %OldAmount% %LineBreak% New amount: %NewAmount% %LineBreak% %EditorLinkCoupon%', 'mwp-al-ext' ), 'woocommerce-store', 'modified' ),
					array( 9066, WSAL_LOW, __( 'User changed the coupon expire date of a coupon', 'mwp-al-ext' ), __( 'Changed the expire date of the coupon %CouponName% %LineBreak% Previous date: %OldDate% %LineBreak% New date: %NewDate% %LineBreak% %EditorLinkCoupon%', 'mwp-al-ext' ), 'woocommerce-store', 'modified' ),
					array( 9067, WSAL_LOW, __( 'User changed the usage restriction settings of a coupon', 'mwp-al-ext' ), __( 'Changed the Usage Restriction of the coupon %CouponName% %LineBreak% Previous usage restriction: %OldMetaValue% %LineBreak% New usage restriction: %NewMetaValue% %LineBreak% %EditorLinkCoupon%', 'mwp-al-ext' ), 'woocommerce-store', 'modified' ),
					array( 9068, WSAL_LOW, __( 'User changed the usage limits settings of a coupon', 'mwp-al-ext' ), __( 'Changed the Usage Limits of the coupon %CouponName% %LineBreak% Previous usage limits: %OldMetaValue% %LineBreak% New usage limits: %NewMetaValue% %LineBreak% %EditorLinkCoupon%', 'mwp-al-ext' ), 'woocommerce-store', 'modified' ),
					array( 9069, WSAL_INFORMATIONAL, __( 'User changed the description of a coupon', 'mwp-al-ext' ), __( 'Changed the description of the coupon %CouponName% %LineBreak% Previous description: %OldDescription% %LineBreak% New description: %NewDescription% %LineBreak% %EditorLinkCoupon%', 'mwp-al-ext' ), 'woocommerce-store', 'modified' ),
					array( 9070, E_WARNING, __( 'User changed the status of a coupon', 'mwp-al-ext' ), __( 'Changed the Status of the WooCommerce coupon %CouponName% from %OldStatus% to %NewStatus%.', 'mwp-al-ext' ), 'woocommerce-store' ),
					array( 9071, WSAL_INFORMATIONAL, __( 'User renamed a WooCommerce coupon', 'mwp-al-ext' ), __( 'Renamed the coupon %OldName% %LineBreak% New name: %NewName% %LineBreak% %LineBreak% %EditorLinkCoupon%', 'mwp-al-ext' ), 'woocommerce-store', 'modified' ),
				),

				__( 'Orders', 'mwp-al-ext' ) => array(
					array( 9035, WSAL_LOW, __( 'A WooCommerce order has been placed', 'mwp-al-ext' ), __( 'A new order has been placed %LineBreak% Order name: %OrderTitle% %LineBreak% %EditorLinkOrder%', 'mwp-al-ext' ), 'woocommerce-store', 'created' ),
					array( 9036, WSAL_INFORMATIONAL, __( 'WooCommerce order status changed', 'mwp-al-ext' ), __( 'Marked an order %OrderTitle% as %OrderStatus% %LineBreak% %EditorLinkOrder%', 'mwp-al-ext' ), 'woocommerce-store', 'modified' ),
					array( 9037, WSAL_MEDIUM, __( 'User moved a WooCommerce order to trash', 'mwp-al-ext' ), __( 'Moved the order %OrderTitle% to trash', 'mwp-al-ext' ), 'woocommerce-store', 'deleted' ),
					array( 9038, WSAL_LOW, __( 'User moved a WooCommerce order out of trash', 'mwp-al-ext' ), __( 'Restored the order %OrderTitle% from the trash %LineBreak% %EditorLinkOrder%', 'mwp-al-ext' ), 'woocommerce-store', 'restored' ),
					array( 9039, WSAL_LOW, __( 'User permanently deleted a WooCommerce order', 'mwp-al-ext' ), __( 'Permanently deleted the order %OrderTitle%', 'mwp-al-ext' ), 'woocommerce-store', 'deleted' ),
					array( 9040, WSAL_MEDIUM, __( 'User edited a WooCommerce order', 'mwp-al-ext' ), __( 'Edited the details in order %OrderTitle% %LineBreak% %EditorLinkOrder%', 'mwp-al-ext' ), 'woocommerce-store', 'modified' ),
					array( 9041, WSAL_HIGH, __( 'User refunded a WooCommerce order', 'mwp-al-ext' ), __( 'Refunded the order %OrderTitle% %LineBreak% %EditorLinkOrder%', 'mwp-al-ext' ), 'woocommerce-store', 'modified' ),
				),

				__( 'User Profile', 'mwp-al-ext' ) => array(
					array( 9083, WSAL_INFORMATIONAL, __( 'User changed the billing address details', 'mwp-al-ext' ), __( 'Changed the billing address details of the user %TargetUsername% %LineBreak% Role: %Roles% %LineBreak% Billing address field: %AddressField% %LineBreak% Previous value: %OldValue% %LineBreak% New value: %NewValue% %LineBreak% %EditUserLink%', 'mwp-al-ext' ), 'user', 'modified' ),
					array( 9084, WSAL_INFORMATIONAL, __( 'User changed the shipping address details', 'mwp-al-ext' ), __( 'Changed the shipping address details of the user %TargetUsername% %LineBreak% Role: %Roles% %LineBreak% Shipping address field: %AddressField% %LineBreak% Previous value: %OldValue% %LineBreak% New value: %NewValue% %LineBreak% %EditUserLink%', 'mwp-al-ext' ), 'user', 'modified' ),
				),
			),

			__( 'Yoast SEO', 'mwp-al-ext' ) => array(
				__( 'Post Changes', 'mwp-al-ext' ) => array(
					array( 8801, WSAL_INFORMATIONAL, __( 'User changed title of a SEO post', 'mwp-al-ext' ), __( 'Changed the Meta title of the post %PostTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% Previous title: %OldSEOTitle% %LineBreak% New title: %NewSEOTitle% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'yoast-seo-metabox', 'modified' ),
					array( 8802, WSAL_INFORMATIONAL, __( 'User changed the meta description of a SEO post', 'mwp-al-ext' ), __( 'Changed the Meta Description of the post %PostTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% Previous description: %old_desc% %LineBreak% New description: %new_desc% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'yoast-seo-metabox', 'modified' ),
					array( 8803, WSAL_INFORMATIONAL, __( 'User changed setting to allow search engines to show post in search results of a SEO post', 'mwp-al-ext' ), __( 'Changed the setting to allow search engines to show post in search results for the post %PostTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% Previous setting: %OldStatus% %LineBreak% New setting: %NewStatus% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'yoast-seo-metabox', 'modified' ),
					array( 8804, WSAL_INFORMATIONAL, __( 'User Enabled/Disabled the option for search engine to follow links of a SEO post', 'mwp-al-ext' ), __( 'The option for search engine to follow links in post %PostTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'yoast-seo-metabox', 'enabled' ),
					array( 8805, WSAL_LOW, __( 'User set the meta robots advanced setting of a SEO post', 'mwp-al-ext' ), __( 'Changed the Meta Robots Advanced setting for the post %PostTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% Previous setting: %OldStatus% %LineBreak% New setting: %NewStatus% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'yoast-seo-metabox', 'modified' ),
					array( 8806, WSAL_INFORMATIONAL, __( 'User changed the canonical URL of a SEO post', 'mwp-al-ext' ), __( 'Changed the Canonical URL of the post %PostTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% Previous URL: %OldCanonicalUrl% %LineBreak% New URL: %NewCanonicalUrl% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'yoast-seo-metabox', 'modified' ),
					array( 8807, WSAL_INFORMATIONAL, __( 'User changed the focus keyword of a SEO post', 'mwp-al-ext' ), __( 'Changed the focus keyword for the post %PostTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% Previous keyword: %old_keywords% %LineBreak% New keyword: %new_keywords% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'yoast-seo-metabox', 'modified' ),
					array( 8808, WSAL_INFORMATIONAL, __( 'User Enabled/Disabled the option Cornerston Content of a SEO post', 'mwp-al-ext' ), __( 'The option Cornerstone Content in the post %PostTitle% %LineBreak% ID: %PostID% %LineBreak% Type: %PostType% %LineBreak% Status: %PostStatus% %LineBreak% %EditorLinkPost%', 'mwp-al-ext' ), 'yoast-seo-metabox', 'enabled' ),
				),

				__( 'Website Changes', 'mwp-al-ext' ) => array(
					array( 8809, WSAL_INFORMATIONAL, __( 'User changed the Title Separator setting', 'mwp-al-ext' ), __( 'Changed the default title separator %LineBreak% Previous separator: %old% %LineBreak% New separator: %new%', 'mwp-al-ext' ), 'yoast-seo', 'modified' ),
					array( 8810, WSAL_MEDIUM, __( 'User changed the Homepage Title setting', 'mwp-al-ext' ), __( 'Changed the homepage Meta title %LineBreak% Previous title: %old% %LineBreak% New title: %new%', 'mwp-al-ext' ), 'yoast-seo', 'modified' ),
					array( 8811, WSAL_MEDIUM, __( 'User changed the Homepage Meta description setting', 'mwp-al-ext' ), __( 'Changed the homepage Meta description %LineBreak% Previous description: %old% %LineBreak% New description: %new%', 'mwp-al-ext' ), 'yoast-seo', 'modified' ),
					array( 8812, WSAL_INFORMATIONAL, __( 'User changed the Company or Person setting', 'mwp-al-ext' ), __( 'Changed the Company or Person setting %LineBreak% Previous setting: %old% %LineBreak% New setting: %new%', 'mwp-al-ext' ), 'yoast-seo', 'modified' ),
				),

				__( 'Plugin Settings Changes', 'mwp-al-ext' ) => array(
					array( 8813, WSAL_MEDIUM, __( 'User Enabled/Disabled the option Show Posts/Pages in Search Results in the Yoast SEO plugin settings', 'mwp-al-ext' ), __( 'The option to show %SEOPostType% in search results', 'mwp-al-ext' ), 'yoast-seo', 'enabled' ),
					array( 8814, WSAL_INFORMATIONAL, __( 'User changed the Posts/Pages title template in the Yoast SEO plugin settings', 'mwp-al-ext' ), __( 'Changed the %SEOPostType% Meta (SEO) title template %LineBreak% Previous template: %old% %LineBreak% New template: %new%', 'mwp-al-ext' ), 'yoast-seo', 'modified' ),
					array( 8815, WSAL_MEDIUM, __( 'User Enabled/Disabled SEO analysis in the Yoast SEO plugin settings', 'mwp-al-ext' ), __( 'The SEO Analysis feature', 'mwp-al-ext' ), 'yoast-seo', 'enabled' ),
					array( 8816, WSAL_MEDIUM, __( 'User Enabled/Disabled readability analysis in the Yoast SEO plugin settings', 'mwp-al-ext' ), __( 'The Readability Analysis feature', 'mwp-al-ext' ), 'yoast-seo', 'enabled' ),
					array( 8817, WSAL_MEDIUM, __( 'User Enabled/Disabled cornerstone content in the Yoast SEO plugin settings', 'mwp-al-ext' ), __( 'The Cornerstone content feature', 'mwp-al-ext' ), 'yoast-seo', 'enabled' ),
					array( 8818, WSAL_MEDIUM, __( 'User Enabled/Disabled the text link counter in the Yoast SEO plugin settings', 'mwp-al-ext' ), __( 'The Text link counter feature', 'mwp-al-ext' ), 'yoast-seo', 'enabled' ),
					array( 8819, WSAL_MEDIUM, __( 'User Enabled/Disabled XML sitemaps in the Yoast SEO plugin settings', 'mwp-al-ext' ), __( 'The XML sitemap feature', 'mwp-al-ext' ), 'yoast-seo', 'enabled' ),
					array( 8820, WSAL_MEDIUM, __( 'User Enabled/Disabled ryte integration in the Yoast SEO plugin settings', 'mwp-al-ext' ), __( 'The Ryte integration feature', 'mwp-al-ext' ), 'yoast-seo', 'enabled' ),
					array( 8821, WSAL_MEDIUM, __( 'User Enabled/Disabled the admin bar menu in the Yoast SEO plugin settings', 'mwp-al-ext' ), __( 'The Admin bar menu feature', 'mwp-al-ext' ), 'yoast-seo', 'enabled' ),
					array( 8822, WSAL_INFORMATIONAL, __( 'User changed the Posts/Pages meta description template in the Yoast SEO plugin settings', 'mwp-al-ext' ), __( 'Changed the %SEOPostType% Meta description template %LineBreak% Previous template: %old% New template: %new%', 'mwp-al-ext' ), 'yoast-seo', 'modified' ),
					array( 8823, WSAL_LOW, __( 'User set the option Date in Snippet Preview for Posts/Pages in the Yoast SEO plugin settings', 'mwp-al-ext' ), __( 'The option Date in Snippet Preview for %SEOPostType%', 'mwp-al-ext' ), 'yoast-seo', 'enabled' ),
					array( 8824, WSAL_LOW, __( 'User set the option Yoast SEO Meta Box for Posts/Pages in the Yoast SEO plugin settings', 'mwp-al-ext' ), __( 'The option Yoast SEO Meta Box for %SEOPostType%', 'mwp-al-ext' ), 'yoast-seo', 'enabled' ),
					array( 8825, WSAL_LOW, __( 'User Enabled/Disabled the advanced settings for authors in the Yoast SEO plugin settings', 'mwp-al-ext' ), __( 'The Security: no advanced settings for authors feature', 'mwp-al-ext' ), 'yoast-seo', 'enabled' ),
				),

				/**
				 * Alerts: MainWP
				 */
				__( 'MainWP', 'mwp-al-ext' ) => array(
					array( 7700, WSAL_CRITICAL, __( 'User added the child site', 'mwp-al-ext' ), __( 'Name: %friendly_name% %LineBreak% URL: %site_url%', 'mwp-al-ext' ), 'child-site', 'added' ),
					array( 7701, WSAL_CRITICAL, __( 'User removed the child site', 'mwp-al-ext' ), __( 'Name: %friendly_name% %LineBreak% URL: %site_url%', 'mwp-al-ext' ), 'child-site', 'removed' ),
					array( 7702, WSAL_MEDIUM, __( 'User edited the child site', 'mwp-al-ext' ), __( 'Name: %friendly_name% %LineBreak% URL: %site_url%', 'mwp-al-ext' ), 'child-site', 'modified' ),
					array( 7703, WSAL_INFORMATIONAL, __( 'User synced data with the child site', 'mwp-al-ext' ), __( 'Synced data with the following child site: %LineBreak% Name: %friendly_name% %LineBreak% URL: %site_url%.', 'mwp-al-ext' ), 'mainwp', 'synced' ),
					array( 7704, WSAL_INFORMATIONAL, __( 'User synced data with all the child sites', 'mwp-al-ext' ), __( 'Synced data with all child sites.', 'mwp-al-ext' ), 'mainwp', 'synced' ),
					array( 7705, WSAL_CRITICAL, __( 'User installed the extension', 'mwp-al-ext' ), __( 'Extension name: %extension_name%', 'mwp-al-ext' ), 'extension', 'installed' ),
					array( 7706, WSAL_HIGH, __( 'User activated the extension', 'mwp-al-ext' ), __( 'Extension name: %extension_name%', 'mwp-al-ext' ), 'extension', 'activated' ),
					array( 7707, WSAL_HIGH, __( 'User deactivated the extension', 'mwp-al-ext' ), __( 'Extension name: %extension_name%', 'mwp-al-ext' ), 'extension', 'deactivated' ),
					array( 7708, WSAL_CRITICAL, __( 'User uninstalled the extension', 'mwp-al-ext' ), __( 'Extension name: %extension_name%', 'mwp-al-ext' ), 'extension', 'uninstalled' ),
					array( 7709, WSAL_INFORMATIONAL, __( 'User added/removed extension to/from the menu', 'mwp-al-ext' ), __( '%action% %extension% %option% the menu.', 'mwp-al-ext' ), 'mainwp', 'updated' ),
					array( 7710, WSAL_LOW, __( 'Extension failed to retrieve the activity log of a child site', 'mwp-al-ext' ), __( 'Extension failed to retrieve the activity logs of a child site. %LineBreak% Name: %friendly_name% %LineBreak% URL: %site_url%', 'mwp-al-ext' ), 'activity-logs', 'failed' ),
					array( 7711, WSAL_INFORMATIONAL, __( 'Extension started retrieving activity logs from the child sites', 'mwp-al-ext' ), __( 'Extension started retrieving activity logs from the child sites.', 'mwp-al-ext' ), 'activity-logs', 'started' ),
					array( 7712, WSAL_INFORMATIONAL, __( 'Extension is ready retrieving activity logs from the child sites', 'mwp-al-ext' ), __( 'Extension finished retrieving activity logs from the child sites.', 'mwp-al-ext' ), 'activity-logs', 'finished' ),
					array( 7750, WSAL_INFORMATIONAL, __( 'User added a monitor for site', 'mwp-al-ext' ), __( 'Added monitor for site child site: %LineBreak% %friendly_name% %LineBreak% %site_url%', 'mwp-al-ext' ), 'uptime-monitor', 'added' ),
					array( 7751, WSAL_MEDIUM, __( 'User deleted a monitor for site', 'mwp-al-ext' ), __( 'Deleted monitor for site child site: %LineBreak% %friendly_name% %LineBreak% %site_url%', 'mwp-al-ext' ), 'uptime-monitor', 'deleted' ),
					array( 7752, WSAL_INFORMATIONAL, __( 'User started the monitor for the site', 'mwp-al-ext' ), __( 'Started monitor for site child site: %LineBreak% %friendly_name% %LineBreak% %site_url%', 'mwp-al-ext' ), 'uptime-monitor', 'started' ),
					array( 7753, WSAL_MEDIUM, __( 'User stopped the monitor for the site', 'mwp-al-ext' ), __( 'Stopped monitor for site child site: %LineBreak% %friendly_name% %LineBreak% %site_url%', 'mwp-al-ext' ), 'uptime-monitor', 'stopped' ),
					array( 7754, WSAL_INFORMATIONAL, __( 'User created monitors for all child sites', 'mwp-al-ext' ), __( 'Created monitors for all child sites.', 'mwp-al-ext' ), 'uptime-monitor', 'created' ),
				),
			),

			__( 'WPForms', 'mwp-al-ext' ) => array(
				__( 'Form Content', 'mwp-al-ext' ) => array(
					array(
						5500,
						WSAL_LOW,
						__( 'A form was created, modified or deleted', 'mwp-al-ext' ),
						__( 'Form name %PostTitle% %LineBreak% ID: %PostID% %LineBreak% %EditorLinkForm%', 'mwp-al-ext' ),
						'wpforms',
						'created',
					),
					array(
						5501,
						WSAL_MEDIUM,
						__( 'A field was created, deleted or modified', 'mwp-al-ext' ),
						__( 'Field name %field_name% %LineBreak% Form name %form_name% %LineBreak% Form ID: %PostID% %LineBreak% %EditorLinkForm%', 'mwp-al-ext' ),
						'wpforms_fields',
						'deleted',
					),
					array(
						5502,
						WSAL_MEDIUM,
						__( 'A form was duplicated', 'mwp-al-ext' ),
						__( 'Source form %OldPostTitle% %LineBreak% New form name %PostTitle% %LineBreak% Source form ID %SourceID% %LineBreak% New form ID: %PostID% %LineBreak% %EditorLinkForm%', 'mwp-al-ext' ),
						'wpforms',
						'duplicated',
					),
					array(
						5503,
						WSAL_LOW,
						__( 'A notification was added to a form, enabled or modified', 'mwp-al-ext' ),
						__( 'Notification name %notifiation_name% %LineBreak% Form name %form_name% %LineBreak% Form ID %PostID% %LineBreak% %EditorLinkForm%', 'mwp-al-ext' ),
						'wpforms_notifications',
						'added',
					),
					array(
						5504,
						WSAL_MEDIUM,
						__( 'An entry was deleted', 'mwp-al-ext' ),
						__( 'Entry email address: %entry_email% %LineBreak% Entry ID: %entry_id% %LineBreak% Form name: %form_name% %LineBreak% Form ID: %form_id% %LineBreak% %EditorLinkForm%', 'mwp-al-ext' ),
						'wpforms_entries',
						'deleted',
					),
					array(
						5505,
						WSAL_LOW,
						__( 'Notifications were disabled for a form', 'mwp-al-ext' ),
						__( 'All the notifications in the form. %LineBreak% Form name %form_name% %LineBreak% Form ID %PostID% %LineBreak% %EditorLinkForm%', 'mwp-al-ext' ),
						'wpforms_notifications',
						'deleted',
					),
					array(
						5506,
						WSAL_LOW,
						__( 'A form was renamed', 'mwp-al-ext' ),
						__( 'New form name %new_form_name% %LineBreak% Old form name %old_form_name% %LineBreak% Form ID %PostID% %LineBreak% %EditorLinkForm%', 'mwp-al-ext' ),
						'wpforms',
						'renamed',
					),
				),
			),
		)
	);
}
add_action( 'init', 'mwpal_defaults_init' );
