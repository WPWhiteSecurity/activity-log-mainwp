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
 *
 * @param \WSAL\MainWPExtension\Activity_Log $activity_log - Instance of main plugin.
 */
function mwpal_defaults_init( \WSAL\MainWPExtension\Activity_Log $activity_log ) {
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
	// Create list of default alerts.
	$activity_log->alerts->RegisterGroup(
		array(
			/**
			 * Section: Users Profiles & Activity
			 */
			__( 'Users Profiles & Activity', 'mwp-al-ext' ) => array(
				/**
				 * Alerts: Other User Activity
				 */
				__( 'Other User Activity', 'mwp-al-ext' ) => array(
					array( 1000, E_NOTICE, __( 'User logged in', 'mwp-al-ext' ), __( 'Successfully logged in.', 'mwp-al-ext' ) ),
					array( 1001, E_NOTICE, __( 'User logged out', 'mwp-al-ext' ), __( 'Successfully logged out.', 'mwp-al-ext' ) ),
					array( 1002, E_WARNING, __( 'Login failed', 'mwp-al-ext' ), __( '%Attempts% failed login(s) detected.', 'mwp-al-ext' ) ),
					array( 1003, E_WARNING, __( 'Login failed  / non existing user', 'mwp-al-ext' ), __( '%Attempts% failed login(s) detected using non existing user. %LogFileText%', 'mwp-al-ext' ) ),
					array( 1004, E_WARNING, __( 'Login blocked', 'mwp-al-ext' ), __( 'Blocked from logging in because the same WordPress user is logged in from %ClientIP%.', 'mwp-al-ext' ) ),
					array( 1005, E_WARNING, __( 'User logged in with existing session(s)', 'mwp-al-ext' ), __( 'Successfully logged in. Another session from %IPAddress% for this user already exist.', 'mwp-al-ext' ) ),
					array( 1006, E_CRITICAL, __( 'User logged out all other sessions with the same username', 'mwp-al-ext' ), __( 'Logged out all other sessions with the same username.', 'mwp-al-ext' ) ),
					array( 1007, E_CRITICAL, __( 'User session destroyed and logged out.', 'mwp-al-ext' ), __( 'Logged out session %TargetSessionID% which belonged to %TargetUserName%', 'mwp-al-ext' ) ),
					array( 2010, E_NOTICE, __( 'User uploaded file from Uploads directory', 'mwp-al-ext' ), __( 'Uploaded the file %FileName% in %FilePath%.', 'mwp-al-ext' ) ),
					array( 2011, E_WARNING, __( 'User deleted file from Uploads directory', 'mwp-al-ext' ), __( 'Deleted the file %FileName% from %FilePath%.', 'mwp-al-ext' ) ),
					array( 6007, E_NOTICE, __( 'User requests non-existing pages (404 Error Pages)', 'mwp-al-ext' ), __( 'Has requested a non existing page (404 Error Pages) %Attempts% %Msg%. %LinkFile%%URL%', 'mwp-al-ext' ) ),
					array( 6023, E_NOTICE, __( 'Website Visitor User requests non-existing pages (404 Error Pages)', 'mwp-al-ext' ), __( 'Website Visitor Has requested a non existing page (404 Error Pages) %Attempts% %Msg%. %LinkFile%%URL%', 'mwp-al-ext' ) ),
				),

				/**
				 * Alerts: User Profiles
				 */
				__( 'User Profiles', 'mwp-al-ext' ) => array(
					array( 4000, E_CRITICAL, __( 'New user was created on WordPress', 'mwp-al-ext' ), __( 'A new user %NewUserData->Username% was created with role of %NewUserData->Roles%.', 'mwp-al-ext' ) ),
					array( 4001, E_CRITICAL, __( 'User created another WordPress user', 'mwp-al-ext' ), __( '%UserChanger% created a new user %NewUserData->Username% with the role of %NewUserData->Roles%.', 'mwp-al-ext' ) ),
					array( 4002, E_CRITICAL, __( 'The role of a user was changed by another WordPress user', 'mwp-al-ext' ), __( 'Changed the role of the user %TargetUsername% from %OldRole% to %NewRole%%multisite_text%.', 'mwp-al-ext' ) ),
					array( 4003, E_CRITICAL, __( 'User has changed his or her password', 'mwp-al-ext' ), __( 'Changed the password.', 'mwp-al-ext' ) ),
					array( 4004, E_CRITICAL, __( 'User changed another user\'s password', 'mwp-al-ext' ), __( 'Changed the password for the user %TargetUserData->Username% with the role of %TargetUserData->Roles%.', 'mwp-al-ext' ) ),
					array( 4005, E_NOTICE, __( 'User changed his or her email address', 'mwp-al-ext' ), __( 'Changed the email address from %OldEmail% to %NewEmail%.', 'mwp-al-ext' ) ),
					array( 4006, E_NOTICE, __( 'User changed another user\'s email address', 'mwp-al-ext' ), __( 'Changed the email address of the user %TargetUsername% from %OldEmail% to %NewEmail%.', 'mwp-al-ext' ) ),
					array( 4007, E_CRITICAL, __( 'User was deleted by another user', 'mwp-al-ext' ), __( 'Deleted the user %TargetUserData->Username% with the role of %TargetUserData->Roles%.', 'mwp-al-ext' ) ),
					array( 4014, E_NOTICE, __( 'User opened the profile page of another user', 'mwp-al-ext' ), __( '%UserChanger% opened the profile page of the user %TargetUsername%.', 'mwp-al-ext' ) ),
					array( 4015, E_NOTICE, __( 'User updated a custom field value for a user', 'mwp-al-ext' ), __( 'Changed the value of the custom field %custom_field_name%%ReportText% for the user %TargetUsername%.%ChangeText%', 'mwp-al-ext' ) ),
					array( 4016, E_NOTICE, __( 'User created a custom field value for a user', 'mwp-al-ext' ), __( 'Created the value of the custom field %custom_field_name% with %new_value% for the user %TargetUsername%.', 'mwp-al-ext' ) ),
					array( 4017, E_NOTICE, __( 'User changed first name for a user', 'mwp-al-ext' ), __( 'Changed the first name of the user %TargetUsername% from %old_firstname% to %new_firstname%', 'mwp-al-ext' ) ),
					array( 4018, E_NOTICE, __( 'User changed last name for a user', 'mwp-al-ext' ), __( 'Changed the last name of the user %TargetUsername% from %old_lastname% to %new_lastname%', 'mwp-al-ext' ) ),
					array( 4019, E_NOTICE, __( 'User changed nickname for a user', 'mwp-al-ext' ), __( 'Changed the nickname of the user %TargetUsername% from %old_nickname% to %new_nickname%', 'mwp-al-ext' ) ),
					array( 4020, E_WARNING, __( 'User changed the display name for a user', 'mwp-al-ext' ), __( 'Changed the Display name publicly of user %TargetUsername% from %old_displayname% to %new_displayname%', 'mwp-al-ext' ) ),
				),
			),

			/**
			 * Section: Content & Comments
			 */
			__( 'Content & Comments', 'mwp-al-ext' ) => array(
				/**
				 * Alerts: Content
				 */
				__( 'Content', 'mwp-al-ext' ) => array(
					array( 2000, E_NOTICE, __( 'User created a new post and saved it as draft', 'mwp-al-ext' ), __( 'Created a new %PostType% titled %PostTitle% and saved it as draft. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2001, E_NOTICE, __( 'User published a post', 'mwp-al-ext' ), __( 'Published a %PostType% titled %PostTitle%. URL is %PostUrl%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2002, E_NOTICE, __( 'User modified a post', 'mwp-al-ext' ), __( 'Modified the %PostStatus% %PostType% titled %PostTitle%. URL is: %PostUrl%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2008, E_WARNING, __( 'User permanently deleted a post from the trash', 'mwp-al-ext' ), __( 'Permanently deleted the %PostType% titled %PostTitle%. URL was %PostUrl%.', 'mwp-al-ext' ) ),
					array( 2012, E_WARNING, __( 'User moved a post to the trash', 'mwp-al-ext' ), __( 'Moved the %PostStatus% %PostType% titled %PostTitle% to trash. URL is %PostUrl%.', 'mwp-al-ext' ) ),
					array( 2014, E_CRITICAL, __( 'User restored a post from trash', 'mwp-al-ext' ), __( 'The %PostStatus% %PostType% titled %PostTitle% has been restored from trash. URL is: %PostUrl%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2017, E_NOTICE, __( 'User changed post URL', 'mwp-al-ext' ), __( 'Changed the URL of the %PostStatus% %PostType% titled %PostTitle%%ReportText%.%ChangeText% %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2019, E_NOTICE, __( 'User changed post author', 'mwp-al-ext' ), __( 'Changed the author of the %PostStatus% %PostType% titled %PostTitle% from %OldAuthor% to %NewAuthor%. URL is: %PostUrl%.  %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2021, E_NOTICE, __( 'User changed post status', 'mwp-al-ext' ), __( 'Changed the status of the %PostType% titled %PostTitle% from %OldStatus% to %NewStatus%. URL is: %PostUrl%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2025, E_WARNING, __( 'User changed the visibility of a post', 'mwp-al-ext' ), __( 'Changed the visibility of the %PostStatus% %PostType% titled %PostTitle% from %OldVisibility% to %NewVisibility%. URL is: %PostUrl%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2027, E_NOTICE, __( 'User changed the date of a post', 'mwp-al-ext' ), __( 'Changed the date of the %PostStatus% %PostType% titled %PostTitle% from %OldDate% to %NewDate%. URL is: %PostUrl%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2047, E_NOTICE, __( 'User changed the parent of a page', 'mwp-al-ext' ), __( 'Changed the parent of the %PostStatus% %PostType% titled %PostTitle% from %OldParentName% to %NewParentName%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2048, E_CRITICAL, __( 'User changed the template of a page', 'mwp-al-ext' ), __( 'Changed the template of the %PostStatus% %PostType% titled %PostTitle% from %OldTemplate% to %NewTemplate%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2049, E_NOTICE, __( 'User set a post as sticky', 'mwp-al-ext' ), __( 'Set the post %PostTitle% as Sticky. Post URL is %PostUrl%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2050, E_NOTICE, __( 'User removed post from sticky', 'mwp-al-ext' ), __( 'Removed the post %PostTitle% from Sticky. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2065, E_WARNING, __( 'User modified the content of a post.', 'mwp-al-ext' ), __( 'Modified the content of the %PostStatus% %PostType% titled %PostTitle%. Post URL is %PostUrl%. %RevisionLink% %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2073, E_NOTICE, __( 'User submitted a post for review', 'mwp-al-ext' ), __( 'Submitted the %PostType% titled %PostTitle% for review. URL is: %PostUrl%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2074, E_NOTICE, __( 'User scheduled a post', 'mwp-al-ext' ), __( 'Scheduled the %PostType% titled %PostTitle% to be published on %PublishingDate%. URL is: %PostUrl%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2086, E_NOTICE, __( 'User changed title of a post', 'mwp-al-ext' ), __( 'Changed the title of the %PostStatus% %PostType% from %OldTitle% to %NewTitle%. URL is: %PostUrl%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2100, E_NOTICE, __( 'User opened a post in the editor', 'mwp-al-ext' ), __( 'Opened the %PostStatus% %PostType% titled %PostTitle% in the editor. URL is: %PostUrl%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2101, E_NOTICE, __( 'User viewed a post', 'mwp-al-ext' ), __( 'Viewed the %PostStatus% %PostType% titled %PostTitle%. URL is: %PostUrl%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2106, E_NOTICE, __( 'A plugin modified a post', 'mwp-al-ext' ), __( 'Plugin modified the %PostStatus% %PostType% titled %PostTitle% of type %PostType%. URL is: %PostUrl%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2111, E_NOTICE, __( 'User disabled Comments/Trackbacks and Pingbacks in a post.', 'mwp-al-ext' ), __( 'Disabled %Type% on the %PostStatus% %PostType% titled %PostTitle%. URL is: %PostUrl%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2112, E_NOTICE, __( 'User enabled Comments/Trackbacks and Pingbacks in a post.', 'mwp-al-ext' ), __( 'Enabled %Type% on the %PostStatus% %PostType% titled %PostTitle%. URL is: %PostUrl%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2119, E_NOTICE, __( 'User added post tag', 'mwp-al-ext' ), __( 'Added the tag %tag% to the %PostStatus% post titled %PostTitle%. URL is: %PostUrl%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2120, E_NOTICE, __( 'User removed post tag', 'mwp-al-ext' ), __( 'Removed the tag %tag% from the %PostStatus% post titled %PostTitle%. URL is: %PostUrl%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2121, E_NOTICE, __( 'User created new tag', 'mwp-al-ext' ), __( 'Added a new tag called %TagName%. View the tag: %TagLink%.', 'mwp-al-ext' ) ),
					array( 2122, E_NOTICE, __( 'User deleted tag', 'mwp-al-ext' ), __( 'Deleted the tag %TagName%.', 'mwp-al-ext' ) ),
					array( 2123, E_NOTICE, __( 'User renamed tag', 'mwp-al-ext' ), __( 'Renamed a tag from %old_name% to %new_name%. View the tag: %TagLink%.', 'mwp-al-ext' ) ),
					array( 2124, E_NOTICE, __( 'User changed tag slug', 'mwp-al-ext' ), __( 'Changed the slug of tag %tag% from %old_slug% to %new_slug%. View the tag: %TagLink%.', 'mwp-al-ext' ) ),
					array( 2125, E_NOTICE, __( 'User changed tag description', 'mwp-al-ext' ), __( 'Changed the description of the tag %tag%%ReportText%.%ChangeText% View the tag: %TagLink%.', 'mwp-al-ext' ) ),
					array( 2016, E_NOTICE, __( 'User changed post category', 'mwp-al-ext' ), __( 'Changed the category of the %PostStatus% %PostType% titled %PostTitle% from %OldCategories% to %NewCategories%. URL is: %PostUrl%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2023, E_NOTICE, __( 'User created new category', 'mwp-al-ext' ), __( 'Created a new category called %CategoryName%. Category slug is %Slug%. %CategoryLink%.', 'mwp-al-ext' ) ),
					array( 2024, E_WARNING, __( 'User deleted category', 'mwp-al-ext' ), __( 'Deleted the %CategoryName% category. Category slug was %Slug%. %CategoryLink%.', 'mwp-al-ext' ) ),
					array( 2052, E_NOTICE, __( 'Changed the parent of a category.', 'mwp-al-ext' ), __( 'Changed the parent of the category %CategoryName% from %OldParent% to %NewParent%. %CategoryLink%.', 'mwp-al-ext' ) ),
					array( 2127, E_WARNING, __( 'User changed category name', 'mwp-al-ext' ), __( 'Changed the name of the category %old_name% to %new_name%.', 'mwp-al-ext' ) ),
					array( 2128, E_CRITICAL, __( 'User changed category slug', 'mwp-al-ext' ), __( 'Changed the slug of the category %CategoryName% from %old_slug% to %new_slug%.', 'mwp-al-ext' ) ),
					array( 2053, E_CRITICAL, __( 'User created a custom field for a post', 'mwp-al-ext' ), __( 'Created a new custom field called %MetaKey% with value %MetaValue% in the %PostStatus% %PostType% titled %PostTitle%. URL is: %PostUrl%. %EditorLinkPost%.<br>%MetaLink%.', 'mwp-al-ext' ) ),
					array( 2054, E_CRITICAL, __( 'User updated a custom field value for a post', 'mwp-al-ext' ), __( 'Modified the value of the custom field %MetaKey%%ReportText% in the %PostStatus% %PostType% titled %PostTitle%.%ChangeText% URL is: %PostUrl%. %EditorLinkPost%.<br>%MetaLink%.', 'mwp-al-ext' ) ),
					array( 2055, E_CRITICAL, __( 'User deleted a custom field from a post', 'mwp-al-ext' ), __( 'Deleted the custom field %MetaKey% with value %MetaValue% from %PostStatus% %PostType% titled %PostTitle%. URL is: %PostUrl%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 2062, E_CRITICAL, __( 'User updated a custom field name for a post', 'mwp-al-ext' ), __( 'Changed the custom field\'s name from %MetaKeyOld% to %MetaKeyNew% in the %PostStatus% %PostType% titled %PostTitle%. URL is: %PostUrl%. %EditorLinkPost%.<br>%MetaLink%.', 'mwp-al-ext' ) ),
				),

				/**
				 * Alerts: Comments
				 */
				__( 'Comments', 'mwp-al-ext' ) => array(
					array( 2090, E_NOTICE, __( 'User approved a comment', 'mwp-al-ext' ), __( 'Approved the comment posted in response to the post %PostTitle% by %Author% on %CommentLink%.', 'mwp-al-ext' ) ),
					array( 2091, E_NOTICE, __( 'User unapproved a comment', 'mwp-al-ext' ), __( 'Unapproved the comment posted in response to the post %PostTitle% by %Author% on %CommentLink%.', 'mwp-al-ext' ) ),
					array( 2092, E_NOTICE, __( 'User replied to a comment', 'mwp-al-ext' ), __( 'Replied to the comment posted in response to the post %PostTitle% by %Author% on %CommentLink%.', 'mwp-al-ext' ) ),
					array( 2093, E_NOTICE, __( 'User edited a comment', 'mwp-al-ext' ), __( 'Edited a comment posted in response to the post %PostTitle% by %Author% on %CommentLink%.', 'mwp-al-ext' ) ),
					array( 2094, E_NOTICE, __( 'User marked a comment as Spam', 'mwp-al-ext' ), __( 'Marked the comment posted in response to the post %PostTitle% by %Author% on %CommentLink% as Spam.', 'mwp-al-ext' ) ),
					array( 2095, E_NOTICE, __( 'User marked a comment as Not Spam', 'mwp-al-ext' ), __( 'Marked the comment posted in response to the post %PostTitle% by %Author% on %CommentLink% as Not Spam.', 'mwp-al-ext' ) ),
					array( 2096, E_NOTICE, __( 'User moved a comment to trash', 'mwp-al-ext' ), __( 'Moved the comment posted in response to the post %PostTitle% by %Author% on %Date% to trash.', 'mwp-al-ext' ) ),
					array( 2097, E_NOTICE, __( 'User restored a comment from the trash', 'mwp-al-ext' ), __( 'Restored the comment posted in response to the post %PostTitle% by %Author% on %CommentLink% from the trash.', 'mwp-al-ext' ) ),
					array( 2098, E_NOTICE, __( 'User permanently deleted a comment', 'mwp-al-ext' ), __( 'Permanently deleted the comment posted in response to the post %PostTitle% by %Author% on %Date%.', 'mwp-al-ext' ) ),
					array( 2099, E_NOTICE, __( 'User posted a comment', 'mwp-al-ext' ), __( '%CommentMsg% on %CommentLink%.', 'mwp-al-ext' ) ),
					array( 2126, E_NOTICE, __( 'Visitor posted a comment', 'mwp-al-ext' ), __( '%CommentMsg% on %CommentLink%.', 'mwp-al-ext' ) ),
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
			__( 'WordPress Install', 'mwp-al-ext' ) => array(
				/**
				 * Alerts: Database
				 */
				__( 'Database', 'mwp-al-ext' ) => array(
					array( 5016, E_CRITICAL, __( 'Unknown component created tables', 'mwp-al-ext' ), __( 'An unknown component created these tables in the database: %TableNames%.', 'mwp-al-ext' ) ),
					array( 5017, E_CRITICAL, __( 'Unknown component modified tables structure', 'mwp-al-ext' ), __( 'An unknown component modified the structure of these database tables: %TableNames%.', 'mwp-al-ext' ) ),
					array( 5018, E_CRITICAL, __( 'Unknown component deleted tables', 'mwp-al-ext' ), __( 'An unknown component deleted the following tables from the database: %TableNames%.', 'mwp-al-ext' ) ),
				),

				/**
				 * Alerts: Plugins
				 */
				__( 'Plugins', 'mwp-al-ext' ) => array(
					array( 5000, E_CRITICAL, __( 'User installed a plugin', 'mwp-al-ext' ), __( 'Installed the plugin %Plugin->Name% in %Plugin->plugin_dir_path%.', 'mwp-al-ext' ) ),
					array( 5001, E_CRITICAL, __( 'User activated a WordPress plugin', 'mwp-al-ext' ), __( 'Activated the plugin %PluginData->Name% installed in %PluginFile%.', 'mwp-al-ext' ) ),
					array( 5002, E_CRITICAL, __( 'User deactivated a WordPress plugin', 'mwp-al-ext' ), __( 'Deactivated the plugin %PluginData->Name% installed in %PluginFile%.', 'mwp-al-ext' ) ),
					array( 5003, E_CRITICAL, __( 'User uninstalled a plugin', 'mwp-al-ext' ), __( 'Uninstalled the plugin %PluginData->Name% which was installed in %PluginFile%.', 'mwp-al-ext' ) ),
					array( 5004, E_WARNING, __( 'User upgraded a plugin', 'mwp-al-ext' ), __( 'Upgraded the plugin %PluginData->Name% installed in %PluginFile%.', 'mwp-al-ext' ) ),
					array( 5010, E_CRITICAL, __( 'Plugin created tables', 'mwp-al-ext' ), __( 'Plugin %Plugin->Name% created these tables in the database: %TableNames%.', 'mwp-al-ext' ) ),
					array( 5011, E_CRITICAL, __( 'Plugin modified tables structure', 'mwp-al-ext' ), __( 'Plugin %Plugin->Name% modified the structure of these database tables: %TableNames%.', 'mwp-al-ext' ) ),
					array( 5012, E_CRITICAL, __( 'Plugin deleted tables', 'mwp-al-ext' ), __( 'Plugin %Plugin->Name% deleted the following tables from the database: %TableNames%.', 'mwp-al-ext' ) ),
					array( 5019, E_CRITICAL, __( 'A plugin created a post', 'mwp-al-ext' ), __( 'A plugin automatically created the following %PostType% called %PostTitle%. View the post: %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 5025, E_CRITICAL, __( 'A plugin deleted a post', 'mwp-al-ext' ), __( 'A plugin automatically deleted the following %PostType% called %PostTitle%.', 'mwp-al-ext' ) ),
					array( 2051, E_CRITICAL, __( 'User changed a file using the plugin editor', 'mwp-al-ext' ), __( 'Modified %File% with the Plugin Editor.', 'mwp-al-ext' ) ),
				),

				/**
				 * Alerts: Themes
				 */
				__( 'Themes', 'mwp-al-ext' ) => array(
					array( 5005, E_WARNING, __( 'User installed a theme', 'mwp-al-ext' ), __( 'Installed the theme "%Theme->Name%" in %Theme->get_template_directory%.', 'mwp-al-ext' ) ),
					array( 5006, E_CRITICAL, __( 'User activated a theme', 'mwp-al-ext' ), __( 'Activated the theme "%Theme->Name%", installed in %Theme->get_template_directory%.', 'mwp-al-ext' ) ),
					array( 5007, E_CRITICAL, __( 'User uninstalled a theme', 'mwp-al-ext' ), __( 'Deleted the theme "%Theme->Name%" installed in %Theme->get_template_directory%.', 'mwp-al-ext' ) ),
					array( 5008, E_CRITICAL, __( 'Activated theme on network', 'mwp-al-ext' ), __( 'Network activated the theme %Theme->Name% installed in %Theme->get_template_directory%.', 'mwp-al-ext' ) ),
					array( 5009, E_CRITICAL, __( 'Deactivated theme from network', 'mwp-al-ext' ), __( 'Network deactivated the theme %Theme->Name% installed in %Theme->get_template_directory%.', 'mwp-al-ext' ) ),
					array( 5013, E_CRITICAL, __( 'Theme created tables', 'mwp-al-ext' ), __( 'Theme %Theme->Name% created these tables in the database: %TableNames%.', 'mwp-al-ext' ) ),
					array( 5014, E_CRITICAL, __( 'Theme modified tables structure', 'mwp-al-ext' ), __( 'Theme %Theme->Name% modified the structure of these database tables: %TableNames%.', 'mwp-al-ext' ) ),
					array( 5015, E_CRITICAL, __( 'Theme deleted tables', 'mwp-al-ext' ), __( 'Theme %Theme->Name% deleted the following tables from the database: %TableNames%.', 'mwp-al-ext' ) ),
					array( 5031, E_WARNING, __( 'User updated a theme', 'mwp-al-ext' ), __( 'Updated the theme "%Theme->Name%" installed in %Theme->get_template_directory%.', 'mwp-al-ext' ) ),
					array( 2046, E_CRITICAL, __( 'User changed a file using the theme editor', 'mwp-al-ext' ), __( 'Modified %File% with the Theme Editor.', 'mwp-al-ext' ) ),
				),

				/**
				 * Alerts: System
				 */
				__( 'System', 'mwp-al-ext' ) => array(
					array( 0000, E_CRITICAL, __( 'Unknown Error', 'mwp-al-ext' ), __( 'An unexpected error has occurred .', 'mwp-al-ext' ) ),
					array( 0001, E_CRITICAL, __( 'PHP error', 'mwp-al-ext' ), __( '%Message%.', 'mwp-al-ext' ) ),
					array( 0002, E_WARNING, __( 'PHP warning', 'mwp-al-ext' ), __( '%Message%.', 'mwp-al-ext' ) ),
					array( 0003, E_NOTICE, __( 'PHP notice', 'mwp-al-ext' ), __( '%Message%.', 'mwp-al-ext' ) ),
					array( 0004, E_CRITICAL, __( 'PHP exception', 'mwp-al-ext' ), __( '%Message%.', 'mwp-al-ext' ) ),
					array( 0005, E_CRITICAL, __( 'PHP shutdown error', 'mwp-al-ext' ), __( '%Message%.', 'mwp-al-ext' ) ),
					array( 6000, E_NOTICE, __( 'Events automatically pruned by system', 'mwp-al-ext' ), __( 'System automatically deleted %EventCount% event(s).', 'mwp-al-ext' ) ),
					array( 6004, E_CRITICAL, __( 'WordPress was updated', 'mwp-al-ext' ), __( 'Updated WordPress from version %OldVersion% to %NewVersion%.', 'mwp-al-ext' ) ),
					array( 6006, E_NOTICE, __( 'Reset plugin\'s settings to default.', 'mwp-al-ext' ), __( 'Reset plugin\'s settings to default.', 'mwp-al-ext' ) ),
					array( 6028, E_CRITICAL, __( 'File content has been modified.', 'mwp-al-ext' ), __( 'The content of the file %FileLocation% has been modified.', 'mwp-al-ext' ) ),
					array( 6029, E_CRITICAL, __( 'File added to the site.', 'mwp-al-ext' ), __( 'The file %FileLocation% has been added to your website.', 'mwp-al-ext' ) ),
					array( 6030, E_CRITICAL, __( 'File deleted from the site.', 'mwp-al-ext' ), __( 'The file %FileLocation% has been deleted from your website.', 'mwp-al-ext' ) ),
					array( 6031, E_CRITICAL, __( 'File not scanned because it is bigger than 5MB.', 'mwp-al-ext' ), __( 'The file %FileLocation% was not scanned because it is bigger than 5MB. Please <a href="https://www.wpsecurityauditlog.com/contact/" target="_blank">contact our support</a> for more information.', 'mwp-al-ext' ) ),
					array( 6032, E_CRITICAL, __( 'File integrity scan stopped due to the limit of 1 million files.', 'mwp-al-ext' ), __( 'The file changes scanning engine has reached the limit of 1 million files and stopped the scan. Please <a href="https://www.wpsecurityauditlog.com/contact/" target="_blank">contact our support</a> for more information.', 'mwp-al-ext' ) ),
					array( 6033, E_NOTICE, __( 'File integrity scan started/stopped.', 'mwp-al-ext' ), __( 'The file integrity scanner has %ScanStatus% scanning %ScanLocation%%ScanError%.', 'mwp-al-ext' ) ),
					array( 6034, E_NOTICE, __( 'Purged the activity log.', 'mwp-al-ext' ), __( 'Purged the activity log.', 'mwp-al-ext' ) ),
					array( 9999, E_CRITICAL, __( 'Advertising Add-ons.', 'mwp-al-ext' ), __( '%PromoName% %PromoMessage%', 'mwp-al-ext' ) ),
				),

				/**
				 * Alerts: Menus
				 */
				__( 'Menus', 'mwp-al-ext' ) => array(
					array( 2078, E_NOTICE, __( 'User created new menu', 'mwp-al-ext' ), __( 'Created a new menu called %MenuName%.', 'mwp-al-ext' ) ),
					array( 2079, E_WARNING, __( 'User added content to a menu', 'mwp-al-ext' ), __( 'Added the %ContentType% called %ContentName% to menu %MenuName%.', 'mwp-al-ext' ) ),
					array( 2080, E_WARNING, __( 'User removed content from a menu', 'mwp-al-ext' ), __( 'Removed the %ContentType% called %ContentName% from the menu %MenuName%.', 'mwp-al-ext' ) ),
					array( 2081, E_CRITICAL, __( 'User deleted menu', 'mwp-al-ext' ), __( 'Deleted the menu %MenuName%.', 'mwp-al-ext' ) ),
					array( 2082, E_WARNING, __( 'User changed menu setting', 'mwp-al-ext' ), __( '%Status% the menu setting %MenuSetting% in %MenuName%.', 'mwp-al-ext' ) ),
					array( 2083, E_NOTICE, __( 'User modified content in a menu', 'mwp-al-ext' ), __( 'Modified the %ContentType% called %ContentName% in menu %MenuName%.', 'mwp-al-ext' ) ),
					array( 2084, E_WARNING, __( 'User changed name of a menu', 'mwp-al-ext' ), __( 'Changed the name of menu %OldMenuName% to %NewMenuName%.', 'mwp-al-ext' ) ),
					array( 2085, E_NOTICE, __( 'User changed order of the objects in a menu', 'mwp-al-ext' ), __( 'Changed the order of the %ItemName% in menu %MenuName%.', 'mwp-al-ext' ) ),
					array( 2089, E_NOTICE, __( 'User moved objects as a sub-item', 'mwp-al-ext' ), __( 'Moved %ItemName% as a sub-item of %ParentName% in menu %MenuName%.', 'mwp-al-ext' ) ),
				),

				/**
				 * Alerts: Widgets
				 */
				__( 'Widgets', 'mwp-al-ext' ) => array(
					array( 2042, E_CRITICAL, __( 'User added a new widget', 'mwp-al-ext' ), __( 'Added a new %WidgetName% widget in  %Sidebar%.', 'mwp-al-ext' ) ),
					array( 2043, E_WARNING, __( 'User modified a widget', 'mwp-al-ext' ), __( 'Modified the %WidgetName% widget in %Sidebar%.', 'mwp-al-ext' ) ),
					array( 2044, E_CRITICAL, __( 'User deleted widget', 'mwp-al-ext' ), __( 'Deleted the %WidgetName% widget from %Sidebar%.', 'mwp-al-ext' ) ),
					array( 2045, E_NOTICE, __( 'User moved widget', 'mwp-al-ext' ), __( 'Moved the %WidgetName% widget from %OldSidebar% to %NewSidebar%.', 'mwp-al-ext' ) ),
					array( 2071, E_NOTICE, __( 'User changed widget position', 'mwp-al-ext' ), __( 'Changed the position of the widget %WidgetName% in sidebar %Sidebar%.', 'mwp-al-ext' ) ),
				),

				/**
				 * Alerts: WordPress Settings
				 */
				__( 'WordPress Settings', 'mwp-al-ext' ) => array(
					array( 6001, E_CRITICAL, __( 'Option Anyone Can Register in WordPress settings changed', 'mwp-al-ext' ), __( '%NewValue% the option "Anyone can register".', 'mwp-al-ext' ) ),
					array( 6002, E_CRITICAL, __( 'New User Default Role changed', 'mwp-al-ext' ), __( 'Changed the New User Default Role from %OldRole% to %NewRole%.', 'mwp-al-ext' ) ),
					array( 6003, E_CRITICAL, __( 'WordPress Administrator Notification email changed', 'mwp-al-ext' ), __( 'Changed the WordPress administrator notifications email address from %OldEmail% to %NewEmail%.', 'mwp-al-ext' ) ),
					array( 6005, E_CRITICAL, __( 'User changes the WordPress Permalinks', 'mwp-al-ext' ), __( 'Changed the WordPress permalinks from %OldPattern% to %NewPattern%.', 'mwp-al-ext' ) ),
					array( 6008, E_CRITICAL, __( 'Enabled/Disabled the option Discourage search engines from indexing this site', 'mwp-al-ext' ), __( '%Status% the option Discourage search engines from indexing this site.', 'mwp-al-ext' ) ),
					array( 6009, E_CRITICAL, __( 'Enabled/Disabled comments on all the website', 'mwp-al-ext' ), __( '%Status% comments on all the website.', 'mwp-al-ext' ) ),
					array( 6010, E_CRITICAL, __( 'Enabled/Disabled the option Comment author must fill out name and email', 'mwp-al-ext' ), __( '%Status% the option Comment author must fill out name and email.', 'mwp-al-ext' ) ),
					array( 6011, E_CRITICAL, __( 'Enabled/Disabled the option Users must be logged in and registered to comment', 'mwp-al-ext' ), __( '%Status% the option Users must be logged in and registered to comment.', 'mwp-al-ext' ) ),
					array( 6012, E_CRITICAL, __( 'Enabled/Disabled the option to automatically close comments', 'mwp-al-ext' ), __( '%Status% the option to automatically close comments after %Value% days.', 'mwp-al-ext' ) ),
					array( 6013, E_NOTICE, __( 'Changed the value of the option Automatically close comments', 'mwp-al-ext' ), __( 'Changed the value of the option Automatically close comments from %OldValue% to %NewValue% days.', 'mwp-al-ext' ) ),
					array( 6014, E_CRITICAL, __( 'Enabled/Disabled the option for comments to be manually approved', 'mwp-al-ext' ), __( '%Status% the option for comments to be manually approved.', 'mwp-al-ext' ) ),
					array( 6015, E_CRITICAL, __( 'Enabled/Disabled the option for an author to have previously approved comments for the comments to appear', 'mwp-al-ext' ), __( '%Status% the option for an author to have previously approved comments for the comments to appear.', 'mwp-al-ext' ) ),
					array( 6016, E_CRITICAL, __( 'Changed the number of links that a comment must have to be held in the queue', 'mwp-al-ext' ), __( 'Changed the number of links from %OldValue% to %NewValue% that a comment must have to be held in the queue.', 'mwp-al-ext' ) ),
					array( 6017, E_CRITICAL, __( 'Modified the list of keywords for comments moderation', 'mwp-al-ext' ), __( 'Modified the list of keywords for comments moderation.', 'mwp-al-ext' ) ),
					array( 6018, E_CRITICAL, __( 'Modified the list of keywords for comments blacklisting', 'mwp-al-ext' ), __( 'Modified the list of keywords for comments blacklisting.', 'mwp-al-ext' ) ),
					array( 6024, E_CRITICAL, __( 'Option WordPress Address (URL) in WordPress settings changed', 'mwp-al-ext' ), __( 'Changed the WordPress address (URL) from %old_url% to %new_url%.', 'mwp-al-ext' ) ),
					array( 6025, E_CRITICAL, __( 'Option Site Address (URL) in WordPress settings changed', 'mwp-al-ext' ), __( 'Changed the site address (URL) from %old_url% to %new_url%.', 'mwp-al-ext' ) ),
					array( 6019, E_CRITICAL, __( 'Created a New cron job', 'mwp-al-ext' ), __( 'A new cron job called %name% was created and is scheduled to run %schedule%.', 'mwp-al-ext' ) ),
					array( 6020, E_CRITICAL, __( 'Changed status of the cron job', 'mwp-al-ext' ), __( 'The cron job %name% was %status%.', 'mwp-al-ext' ) ),
					array( 6021, E_CRITICAL, __( 'Deleted the cron job', 'mwp-al-ext' ), __( 'The cron job %name% was deleted.', 'mwp-al-ext' ) ),
					array( 6022, E_NOTICE, __( 'Started the cron job', 'mwp-al-ext' ), __( 'The cron job %name% has just started.', 'mwp-al-ext' ) ),
				),
			),

			/**
			 * Section: Multisite Network
			 */
			__( 'Multisite Network', 'mwp-al-ext' ) => array(
				/**
				 * Alerts: MultiSite
				 */
				__( 'MultiSite', 'mwp-al-ext' ) => array(
					array( 4008, E_CRITICAL, __( 'User granted Super Admin privileges', 'mwp-al-ext' ), __( 'Granted Super Admin privileges to %TargetUsername%.', 'mwp-al-ext' ) ),
					array( 4009, E_CRITICAL, __( 'User revoked from Super Admin privileges', 'mwp-al-ext' ), __( 'Revoked Super Admin privileges from %TargetUsername%.', 'mwp-al-ext' ) ),
					array( 4010, E_CRITICAL, __( 'Existing user added to a site', 'mwp-al-ext' ), __( 'Added the existing user %TargetUsername% with %TargetUserRole% role to site %SiteName%.', 'mwp-al-ext' ) ),
					array( 4011, E_CRITICAL, __( 'User removed from site', 'mwp-al-ext' ), __( 'Removed the user %TargetUsername% with role %TargetUserRole% from %SiteName% site.', 'mwp-al-ext' ) ),
					array( 4012, E_CRITICAL, __( 'New network user created', 'mwp-al-ext' ), __( 'Created a new network user %NewUserData->Username%.', 'mwp-al-ext' ) ),
					array( 4013, E_CRITICAL, __( 'The forum role of a user was changed by another WordPress user', 'mwp-al-ext' ), __( 'Change the forum role of the user %TargetUsername% from %OldRole% to %NewRole% by %UserChanger%.', 'mwp-al-ext' ) ),
					array( 7000, E_CRITICAL, __( 'New site added on the network', 'mwp-al-ext' ), __( 'Added the site %SiteName% to the network.', 'mwp-al-ext' ) ),
					array( 7001, E_CRITICAL, __( 'Existing site archived', 'mwp-al-ext' ), __( 'Archived the site %SiteName%.', 'mwp-al-ext' ) ),
					array( 7002, E_CRITICAL, __( 'Archived site has been unarchived', 'mwp-al-ext' ), __( 'Unarchived the site %SiteName%.', 'mwp-al-ext' ) ),
					array( 7003, E_CRITICAL, __( 'Deactivated site has been activated', 'mwp-al-ext' ), __( 'Activated the site %SiteName%.', 'mwp-al-ext' ) ),
					array( 7004, E_CRITICAL, __( 'Site has been deactivated', 'mwp-al-ext' ), __( 'Deactivated the site %SiteName%.', 'mwp-al-ext' ) ),
					array( 7005, E_CRITICAL, __( 'Existing site deleted from network', 'mwp-al-ext' ), __( 'Deleted the site %SiteName%.', 'mwp-al-ext' ) ),
				),
			),

			/**
			 * Section: Third Party Support
			 */
			__( 'Third Party Plugins', 'mwp-al-ext' ) => array(
				/**
				 * Alerts: BBPress Forum
				 */
				__( 'BBPress Forum', 'mwp-al-ext' ) => array(
					array( 8000, E_CRITICAL, __( 'User created new forum', 'mwp-al-ext' ), __( 'Created new forum %ForumName%. Forum URL is %ForumURL%.' . ' %EditorLinkForum%.', 'mwp-al-ext' ) ),
					array( 8001, E_NOTICE, __( 'User changed status of a forum', 'mwp-al-ext' ), __( 'Changed the status of the forum %ForumName% from %OldStatus% to %NewStatus%.' . ' %EditorLinkForum%.', 'mwp-al-ext' ) ),
					array( 8002, E_NOTICE, __( 'User changed visibility of a forum', 'mwp-al-ext' ), __( 'Changed the visibility of the forum %ForumName% from %OldVisibility% to %NewVisibility%.' . ' %EditorLinkForum%.', 'mwp-al-ext' ) ),
					array( 8003, E_CRITICAL, __( 'User changed the URL of a forum', 'mwp-al-ext' ), __( 'Changed the URL of the forum %ForumName% from %OldUrl% to %NewUrl%.' . ' %EditorLinkForum%.', 'mwp-al-ext' ) ),
					array( 8004, E_NOTICE, __( 'User changed order of a forum', 'mwp-al-ext' ), __( 'Changed the order of the forum %ForumName% from %OldOrder% to %NewOrder%.' . ' %EditorLinkForum%.', 'mwp-al-ext' ) ),
					array( 8005, E_CRITICAL, __( 'User moved forum to trash', 'mwp-al-ext' ), __( 'Moved the forum %ForumName% to trash.', 'mwp-al-ext' ) ),
					array( 8006, E_WARNING, __( 'User permanently deleted forum', 'mwp-al-ext' ), __( 'Permanently deleted the forum %ForumName%.', 'mwp-al-ext' ) ),
					array( 8007, E_WARNING, __( 'User restored forum from trash', 'mwp-al-ext' ), __( 'Restored the forum %ForumName% from trash.' . ' %EditorLinkForum%.', 'mwp-al-ext' ) ),
					array( 8008, E_NOTICE, __( 'User changed the parent of a forum', 'mwp-al-ext' ), __( 'Changed the parent of the forum %ForumName% from %OldParent% to %NewParent%.' . ' %EditorLinkForum%.', 'mwp-al-ext' ) ),
					array( 8011, E_NOTICE, __( 'User changed type of a forum', 'mwp-al-ext' ), __( 'Changed the type of the forum %ForumName% from %OldType% to %NewType%.' . ' %EditorLinkForum%.', 'mwp-al-ext' ) ),
					array( 8009, E_WARNING, __( 'User changed forum\'s role', 'mwp-al-ext' ), __( 'Changed the forum\'s auto role from %OldRole% to %NewRole%.', 'mwp-al-ext' ) ),
					array( 8010, E_WARNING, __( 'User changed option of a forum', 'mwp-al-ext' ), __( '%Status% the option for anonymous posting on forum.', 'mwp-al-ext' ) ),
					array( 8012, E_NOTICE, __( 'User changed time to disallow post editing', 'mwp-al-ext' ), __( 'Changed the time to disallow post editing from %OldTime% to %NewTime% minutes in the forums.', 'mwp-al-ext' ) ),
					array( 8013, E_WARNING, __( 'User changed the forum setting posting throttle time', 'mwp-al-ext' ), __( 'Changed the posting throttle time from %OldTime% to %NewTime% seconds in the forums.', 'mwp-al-ext' ) ),
					array( 8014, E_NOTICE, __( 'User created new topic', 'mwp-al-ext' ), __( 'Created a new topic %TopicName%.' . ' %EditorLinkTopic%.', 'mwp-al-ext' ) ),
					array( 8015, E_NOTICE, __( 'User changed status of a topic', 'mwp-al-ext' ), __( 'Changed the status of the topic %TopicName% from %OldStatus% to %NewStatus%.' . ' %EditorLinkTopic%.', 'mwp-al-ext' ) ),
					array( 8016, E_NOTICE, __( 'User changed type of a topic', 'mwp-al-ext' ), __( 'Changed the type of the topic %TopicName% from %OldType% to %NewType%.' . ' %EditorLinkTopic%.', 'mwp-al-ext' ) ),
					array( 8017, E_CRITICAL, __( 'User changed URL of a topic', 'mwp-al-ext' ), __( 'Changed the URL of the topic %TopicName% from %OldUrl% to %NewUrl%.', 'mwp-al-ext' ) ),
					array( 8018, E_NOTICE, __( 'User changed the forum of a topic', 'mwp-al-ext' ), __( 'Changed the forum of the topic %TopicName% from %OldForum% to %NewForum%.' . ' %EditorLinkTopic%.', 'mwp-al-ext' ) ),
					array( 8019, E_CRITICAL, __( 'User moved topic to trash', 'mwp-al-ext' ), __( 'Moved the topic %TopicName% to trash.', 'mwp-al-ext' ) ),
					array( 8020, E_WARNING, __( 'User permanently deleted topic', 'mwp-al-ext' ), __( 'Permanently deleted the topic %TopicName%.', 'mwp-al-ext' ) ),
					array( 8021, E_WARNING, __( 'User restored topic from trash', 'mwp-al-ext' ), __( 'Restored the topic %TopicName% from trash.' . ' %EditorLinkTopic%.', 'mwp-al-ext' ) ),
					array( 8022, E_NOTICE, __( 'User changed visibility of a topic', 'mwp-al-ext' ), __( 'Changed the visibility of the topic %TopicName% from %OldVisibility% to %NewVisibility%.' . ' %EditorLinkTopic%.', 'mwp-al-ext' ) ),
				),

				/**
				 * Alerts: WooCommerce Products
				 */
				__( 'WooCommerce Products', 'mwp-al-ext' ) => array(
					array( 9000, E_NOTICE, __( 'User created a new product', 'mwp-al-ext' ), __( 'Created a new product called %ProductTitle% and saved it as draft. View the product: %EditorLinkProduct%.', 'mwp-al-ext' ) ),
					array( 9001, E_NOTICE, __( 'User published a product', 'mwp-al-ext' ), __( 'Published a product called %ProductTitle%. Product URL is %ProductUrl%. View the product: %EditorLinkProduct%.', 'mwp-al-ext' ) ),
					array( 9003, E_NOTICE, __( 'User changed the category of a product', 'mwp-al-ext' ), __( 'Changed the category of the product %ProductTitle% from %OldCategories% to %NewCategories%. View the product: %EditorLinkProduct%.', 'mwp-al-ext' ) ),
					array( 9004, E_NOTICE, __( 'User modified the short description of a product', 'mwp-al-ext' ), __( 'Modified the short description of the product %ProductTitle%.%ChangeText% View the product: %EditorLinkProduct%.', 'mwp-al-ext' ) ),
					array( 9005, E_NOTICE, __( 'User modified the text of a product', 'mwp-al-ext' ), __( 'Modified the text of the product %ProductTitle%. View the product: %EditorLinkProduct%.', 'mwp-al-ext' ) ),
					array( 9006, E_NOTICE, __( 'User changed the URL of a product', 'mwp-al-ext' ), __( 'Changed the URL of the product %ProductTitle%%ReportText%.%ChangeText% View the product: %EditorLinkProduct%.', 'mwp-al-ext' ) ),
					array( 9008, E_NOTICE, __( 'User changed the date of a product', 'mwp-al-ext' ), __( 'Changed the date of the product %ProductTitle% from %OldDate% to %NewDate%. View the product: %EditorLinkProduct%.', 'mwp-al-ext' ) ),
					array( 9009, E_NOTICE, __( 'User changed the visibility of a product', 'mwp-al-ext' ), __( 'Changed the visibility of the product %ProductTitle% from %OldVisibility% to %NewVisibility%. View the product: %EditorLinkProduct%.', 'mwp-al-ext' ) ),
					array( 9010, E_NOTICE, __( 'User modified the published product', 'mwp-al-ext' ), __( 'Modified the published product %ProductTitle%. Product URL is %ProductUrl%. View the product: %EditorLinkProduct%.', 'mwp-al-ext' ) ),
					array( 9011, E_NOTICE, __( 'User modified the draft product', 'mwp-al-ext' ), __( 'Modified the draft product %ProductTitle%. View the product: %EditorLinkProduct%.', 'mwp-al-ext' ) ),
					array( 9012, E_WARNING, __( 'User moved a product to trash', 'mwp-al-ext' ), __( 'Moved the product %ProductTitle% to trash. Product URL was %ProductUrl%.', 'mwp-al-ext' ) ),
					array( 9013, E_WARNING, __( 'User permanently deleted a product', 'mwp-al-ext' ), __( 'Permanently deleted the product %ProductTitle%.', 'mwp-al-ext' ) ),
					array( 9014, E_CRITICAL, __( 'User restored a product from the trash', 'mwp-al-ext' ), __( 'Product %ProductTitle% has been restored from trash. View product: %EditorLinkProduct%.', 'mwp-al-ext' ) ),
					array( 9015, E_NOTICE, __( 'User changed status of a product', 'mwp-al-ext' ), __( 'Changed the status of the product %ProductTitle% from %OldStatus% to %NewStatus%. View the product: %EditorLinkProduct%.', 'mwp-al-ext' ) ),
					array( 9072, E_NOTICE, __( 'User opened a product in the editor', 'mwp-al-ext' ), __( 'Opened the %ProductStatus% product page %ProductTitle% in editor. View the product: %EditorLinkProduct%.', 'mwp-al-ext' ) ),
					array( 9073, E_NOTICE, __( 'User viewed a product', 'mwp-al-ext' ), __( 'Viewed the %ProductStatus% product page %ProductTitle%. View the product: %EditorLinkProduct%.', 'mwp-al-ext' ) ),
					array( 9007, E_NOTICE, __( 'User changed the Product Data of a product', 'mwp-al-ext' ), __( 'Changed the Product Data of the product %ProductTitle%. View the product: %EditorLinkProduct%.', 'mwp-al-ext' ) ),
					array( 9016, E_WARNING, __( 'User changed type of a price', 'mwp-al-ext' ), __( 'Changed the %PriceType% of the product %ProductTitle% from %OldPrice% to %NewPrice%. View the product: %EditorLinkProduct%.', 'mwp-al-ext' ) ),
					array( 9017, E_WARNING, __( 'User changed the SKU of a product', 'mwp-al-ext' ), __( 'Changed the SKU of the product %ProductTitle% from %OldSku% to %NewSku%. View the product: %EditorLinkProduct%.', 'mwp-al-ext' ) ),
					array( 9018, E_CRITICAL, __( 'User changed the stock status of a product', 'mwp-al-ext' ), __( 'Changed the stock status of the product %ProductTitle% from %OldStatus% to %NewStatus%. View the product: %EditorLinkProduct%.', 'mwp-al-ext' ) ),
					array( 9019, E_WARNING, __( 'User changed the stock quantity', 'mwp-al-ext' ), __( 'Changed the stock quantity of the product %ProductTitle% from %OldValue% to %NewValue%. View the product: %EditorLinkProduct%', 'mwp-al-ext' ) ),
					array( 9020, E_WARNING, __( 'User set a product type', 'mwp-al-ext' ), __( 'Set the product %ProductTitle% as %Type%. View the product: %EditorLinkProduct%.', 'mwp-al-ext' ) ),
					array( 9021, E_WARNING, __( 'User changed the weight of a product', 'mwp-al-ext' ), __( 'Changed the weight of the product %ProductTitle% from %OldWeight% to %NewWeight%. View the product: %EditorLinkProduct%.', 'mwp-al-ext' ) ),
					array( 9022, E_WARNING, __( 'User changed the dimensions of a product', 'mwp-al-ext' ), __( 'Changed the %DimensionType% dimensions of the product %ProductTitle% from %OldDimension% to %NewDimension%. View the product: %EditorLinkProduct%.', 'mwp-al-ext' ) ),
					array( 9023, E_WARNING, __( 'User added the Downloadable File to a product', 'mwp-al-ext' ), __( 'Added the Downloadable File %FileName% with File URL %FileUrl% to the product %ProductTitle%. View the product: %EditorLinkProduct%.', 'mwp-al-ext' ) ),
					array( 9024, E_WARNING, __( 'User Removed the Downloadable File from a product', 'mwp-al-ext' ), __( 'Removed the Downloadable File %FileName% with File URL %FileUrl% from the product %ProductTitle%. View the product: %EditorLinkProduct%.', 'mwp-al-ext' ) ),
					array( 9025, E_WARNING, __( 'User changed the name of a Downloadable File in a product', 'mwp-al-ext' ), __( 'Changed the name of a Downloadable File from %OldName% to %NewName% in product %ProductTitle%. View the product: %EditorLinkProduct%.', 'mwp-al-ext' ) ),
					array( 9026, E_WARNING, __( 'User changed the URL of the Downloadable File in a product', 'mwp-al-ext' ), __( 'Changed the URL of the Downloadable File %FileName% from %OldUrl% to %NewUrl% in product %ProductTitle%. View the product: %EditorLinkProduct%.', 'mwp-al-ext' ) ),
				),

				/**
				 * Alerts: WooCommerce
				 */
				__( 'WooCommerce', 'mwp-al-ext' ) => array(
					array( 9027, E_WARNING, __( 'User changed the Weight Unit', 'mwp-al-ext' ), __( 'Changed the Weight Unit from %OldUnit% to %NewUnit% in WooCommerce.', 'mwp-al-ext' ) ),
					array( 9028, E_WARNING, __( 'User changed the Dimensions Unit', 'mwp-al-ext' ), __( 'Changed the Dimensions Unit from %OldUnit% to %NewUnit% in WooCommerce.', 'mwp-al-ext' ) ),
					array( 9029, E_CRITICAL, __( 'User changed the Base Location', 'mwp-al-ext' ), __( 'Changed the Base Location from %OldLocation% to %NewLocation% in WooCommerce.', 'mwp-al-ext' ) ),
					array( 9030, E_CRITICAL, __( 'User Enabled/Disabled taxes', 'mwp-al-ext' ), __( '%Status% taxes in the WooCommerce store.', 'mwp-al-ext' ) ),
					array( 9031, E_CRITICAL, __( 'User changed the currency', 'mwp-al-ext' ), __( 'Changed the currency from %OldCurrency% to %NewCurrency% in WooCommerce.', 'mwp-al-ext' ) ),
					array( 9032, E_CRITICAL, __( 'User Enabled/Disabled the use of coupons during checkout', 'mwp-al-ext' ), __( '%Status% the use of coupons during checkout in WooCommerce.', 'mwp-al-ext' ) ),
					array( 9033, E_CRITICAL, __( 'User Enabled/Disabled guest checkout', 'mwp-al-ext' ), __( '%Status% guest checkout in WooCommerce.', 'mwp-al-ext' ) ),
					array( 9034, E_CRITICAL, __( 'User Enabled/Disabled cash on delivery', 'mwp-al-ext' ), __( '%Status% the option Enable cash on delivery in WooCommerce.', 'mwp-al-ext' ) ),
					array( 9002, E_NOTICE, __( 'User created a new product category', 'mwp-al-ext' ), __( 'Created a new product category called %CategoryName% in WooCommerce. Product category slug is %Slug%.', 'mwp-al-ext' ) ),
				),

				/**
				 * Alerts: Yoast SEO
				 */
				__( 'Yoast SEO', 'mwp-al-ext' ) => array(
					array( 8801, E_NOTICE, __( 'User changed title of a SEO post', 'mwp-al-ext' ), __( 'Changed the SEO title of the %PostStatus% %PostType%%ReportText%.%ChangeText% %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 8802, E_NOTICE, __( 'User changed the meta description of a SEO post', 'mwp-al-ext' ), __( 'Changed the Meta description of the %PostStatus% %PostType% titled %PostTitle%%ReportText%.%ChangeText% %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 8803, E_NOTICE, __( 'User changed setting to allow search engines to show post in search results of a SEO post', 'mwp-al-ext' ), __( 'Changed the setting to allow search engines to show post in search results from %OldStatus% to %NewStatus% in the %PostStatus% %PostType% titled %PostTitle%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 8804, E_NOTICE, __( 'User Enabled/Disabled the option for search engine to follow links of a SEO post', 'mwp-al-ext' ), __( '%NewStatus% the option for search engine to follow links in the %PostType% titled %PostTitle%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 8805, E_NOTICE, __( 'User set the meta robots advanced setting of a SEO post', 'mwp-al-ext' ), __( 'Set the Meta Robots Advanced setting to %NewStatus%  in the %PostStatus% %PostType% titled %PostTitle%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 8806, E_NOTICE, __( 'User changed the canonical URL of a SEO post', 'mwp-al-ext' ), __( 'Changed the Canonical URL of the %PostStatus% %PostType% titled %PostTitle%%ReportText%.%ChangeText% %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 8807, E_NOTICE, __( 'User changed the focus keyword of a SEO post', 'mwp-al-ext' ), __( 'Changed the focus keyword of the %PostStatus% %PostType% titled %PostTitle% from %old_keywords% to %new_keywords%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 8808, E_NOTICE, __( 'User Enabled/Disabled the option Cornerston Content of a SEO post', 'mwp-al-ext' ), __( '%Status% the option Cornerston Content on the %PostStatus% %PostType% titled %PostTitle%. %EditorLinkPost%.', 'mwp-al-ext' ) ),
					array( 8809, E_WARNING, __( 'User changed the Title Separator setting', 'mwp-al-ext' ), __( 'Changed the Title Separator from %old% to %new% in the Yoast SEO plugin settings.', 'mwp-al-ext' ) ),
					array( 8810, E_WARNING, __( 'User changed the Homepage Title setting', 'mwp-al-ext' ), __( 'Changed the Homepage Title%ReportText% in the Yoast SEO plugin settings.%ChangeText%', 'mwp-al-ext' ) ),
					array( 8811, E_WARNING, __( 'User changed the Homepage Meta description setting', 'mwp-al-ext' ), __( 'Changed the Homepage Meta description%ReportText% in the Yoast SEO plugin settings.%ChangeText%', 'mwp-al-ext' ) ),
					array( 8812, E_WARNING, __( 'User changed the Company or Person setting', 'mwp-al-ext' ), __( 'Changed the Company or Person setting from %old% to %new% in the YOAST SEO plugin settings.', 'mwp-al-ext' ) ),
					array( 8813, E_WARNING, __( 'User Enabled/Disabled the option Show Posts/Pages in Search Results in the Yoast SEO plugin settings', 'mwp-al-ext' ), __( '%Status% the option Show %SEOPostType% in Search Results in the Yoast SEO plugin settings.', 'mwp-al-ext' ) ),
					array( 8814, E_WARNING, __( 'User changed the Posts/Pages title template in the Yoast SEO plugin settings', 'mwp-al-ext' ), __( 'Changed the %SEOPostType% title template from %old% to %new% in the Yoast SEO plugin settings.', 'mwp-al-ext' ) ),
					array( 8815, E_WARNING, __( 'User Enabled/Disabled SEO analysis in the Yoast SEO plugin settings', 'mwp-al-ext' ), __( '%Status% SEO analysis in the Yoast SEO plugin settings.', 'mwp-al-ext' ) ),
					array( 8816, E_WARNING, __( 'User Enabled/Disabled readability analysis in the Yoast SEO plugin settings', 'mwp-al-ext' ), __( '%Status% Readability analysis in the Yoast SEO plugin settings.', 'mwp-al-ext' ) ),
					array( 8817, E_WARNING, __( 'User Enabled/Disabled cornerstone content in the Yoast SEO plugin settings', 'mwp-al-ext' ), __( '%Status% Cornerstone content in the Yoast SEO plugin settings.', 'mwp-al-ext' ) ),
					array( 8818, E_WARNING, __( 'User Enabled/Disabled the text link counter in the Yoast SEO plugin settings', 'mwp-al-ext' ), __( '%Status% the Text link counter in the Yoast SEO plugin settings.', 'mwp-al-ext' ) ),
					array( 8819, E_WARNING, __( 'User Enabled/Disabled XML sitemaps in the Yoast SEO plugin settings', 'mwp-al-ext' ), __( '%Status% XML Sitemaps in the Yoast SEO plugin settings.', 'mwp-al-ext' ) ),
					array( 8820, E_WARNING, __( 'User Enabled/Disabled ryte integration in the Yoast SEO plugin settings', 'mwp-al-ext' ), __( '%Status% Ryte Integration in the Yoast SEO plugin settings.', 'mwp-al-ext' ) ),
					array( 8821, E_WARNING, __( 'User Enabled/Disabled the admin bar menu in the Yoast SEO plugin settings', 'mwp-al-ext' ), __( '%Status% the Admin bar menu in the Yoast SEO plugin settings.', 'mwp-al-ext' ) ),
					array( 8822, E_WARNING, __( 'User changed the Posts/Pages meta description template in the Yoast SEO plugin settings', 'mwp-al-ext' ), __( 'Changed the %SEOPostType% meta description template from %old% to %new% in the Yoast SEO plugin settings.', 'mwp-al-ext' ) ),
					array( 8823, E_WARNING, __( 'User set the option Date in Snippet Preview for Posts/Pages in the Yoast SEO plugin settings', 'mwp-al-ext' ), __( '%Status% the option Date in Snippet Preview for %SEOPostType% in the Yoast SEO plugin settings.', 'mwp-al-ext' ) ),
					array( 8824, E_WARNING, __( 'User set the option Yoast SEO Meta Box for Posts/Pages in the Yoast SEO plugin settings', 'mwp-al-ext' ), __( '%Status% the option Yoast SEO Meta Box for %SEOPostType% in the Yoast SEO plugin settings.', 'mwp-al-ext' ) ),
					array( 8825, E_WARNING, __( 'User Enabled/Disabled the advanced settings for authors in the Yoast SEO plugin settings', 'mwp-al-ext' ), __( '%Status% the advanced settings for authors in the Yoast SEO settings.', 'mwp-al-ext' ) ),
				),

				/**
				 * Alerts: MainWP
				 */
				__( 'MainWP', 'mwp-al-ext' ) => array(
					array( 7700, E_CRITICAL, __( 'User added the child site', 'mwp-al-ext' ), __( 'Added the child site %friendly_name%.', 'mwp-al-ext' ) ),
					array( 7701, E_CRITICAL, __( 'User removed the child site', 'mwp-al-ext' ), __( 'Removed the child site %friendly_name%.', 'mwp-al-ext' ) ),
					array( 7702, E_WARNING, __( 'User edited the child site', 'mwp-al-ext' ), __( 'Edited the child site %friendly_name%.', 'mwp-al-ext' ) ),
					array( 7703, E_NOTICE, __( 'User synced data with the child site', 'mwp-al-ext' ), __( 'Synced data with the child site %friendly_name%.', 'mwp-al-ext' ) ),
					array( 7704, E_NOTICE, __( 'User synced data with all the child sites', 'mwp-al-ext' ), __( 'Synced data with all the child sites.', 'mwp-al-ext' ) ),
					array( 7705, E_CRITICAL, __( 'User installed the extension', 'mwp-al-ext' ), __( 'Installed the extension %extension_name%.', 'mwp-al-ext' ) ),
					array( 7706, E_CRITICAL, __( 'User activated the extension', 'mwp-al-ext' ), __( 'Activated the extension %extension_name%.', 'mwp-al-ext' ) ),
					array( 7707, E_CRITICAL, __( 'User deactivated the extension', 'mwp-al-ext' ), __( 'Deactivated the extension %extension_name%.', 'mwp-al-ext' ) ),
					array( 7708, E_CRITICAL, __( 'User uninstalled the extension', 'mwp-al-ext' ), __( 'Uninstalled the extension %extension_name%.', 'mwp-al-ext' ) ),
					array( 7709, E_NOTICE, __( 'User added/removed extension to/from the menu', 'mwp-al-ext' ), __( '%action% %extension% %option% the menu.', 'mwp-al-ext' ) ),
					array( 7710, E_NOTICE, __( 'Extension failed to retrieve the activity log of a child site', 'mwp-al-ext' ), __( 'Failed to retrieve the activity log of the child site %friendly_name%.', 'mwp-al-ext' ) ),
					array( 7711, E_NOTICE, __( 'Extension started retrieving activity logs from the child sites', 'mwp-al-ext' ), __( 'Extension started retrieving activity logs from the child sites.', 'mwp-al-ext' ) ),
					array( 7712, E_NOTICE, __( 'Extension is ready retrieving activity logs from the child sites', 'mwp-al-ext' ), __( 'Extension is ready retrieving activity logs from the child sites.', 'mwp-al-ext' ) ),
					array( 7750, E_NOTICE, __( 'User added a monitor for site', 'mwp-al-ext' ), __( 'Added a monitor for site %friendly_name%.', 'mwp-al-ext' ) ),
					array( 7751, E_NOTICE, __( 'User deleted a monitor for site', 'mwp-al-ext' ), __( 'Deleted a monitor for site %friendly_name%.', 'mwp-al-ext' ) ),
					array( 7752, E_NOTICE, __( 'User started the monitor for the site', 'mwp-al-ext' ), __( 'Started the monitor for the site %friendly_name%.', 'mwp-al-ext' ) ),
					array( 7753, E_WARNING, __( 'User stopped the monitor for the site', 'mwp-al-ext' ), __( 'Stopped the monitor for the site %friendly_name%.', 'mwp-al-ext' ) ),
					array( 7754, E_NOTICE, __( 'User created monitors for all child sites', 'mwp-al-ext' ), __( 'Created monitors for all child sites.', 'mwp-al-ext' ) ),
				),
			),
		)
	);
}
add_action( 'mwpal_init', 'mwpal_defaults_init' );
