<?php
/**
 * The top-matter of the user header, displayed both in Edit and non-Edit mode
 */
?>

<?php bp_locate_template( 'templates/parts/header-banner.php' ); ?>

<div class="cacap-row cacap-hero-row<?php if ( cacap_is_commons_profile() ) : ?> stuck<?php endif ?>">
	<div class="cacap-sticky-dummy">
		<div class="cacap-hero">

			<div class="cacap-avatar-buttons">
				<?php if ( bp_is_active( 'friends' ) ) : ?>
					<?php bp_add_friend_button( bp_displayed_user_id() ) ?>
				<?php endif ?>

				<?php if ( bp_is_active( 'messages' ) ) : ?>
					<?php bp_send_private_message_button( bp_displayed_user_id() ) ?>
				<?php endif ?>

				<?php if ( bp_is_active( 'activity' ) ) : ?>
					<?php bp_send_public_message_button( bp_displayed_user_id() ) ?>
				<?php endif ?>

				<?php do_action( 'cacap_avatar_actions' ) ?>
			</div>

			<div class="cacap-avatar">
				<?php if ( bp_is_my_profile() ) : ?>
					<a href="<?php echo bp_displayed_user_domain() ?>profile/change-avatar">
					<?php bp_displayed_user_avatar( array(
						'type' => 'full',
						'width' => '250px',
						'height' => '250px',
					) ) ?>
					</a>
				<?php else: ?> 
					<?php bp_displayed_user_avatar( array(
						'type' => 'full',
						'width' => '250px',
						'height' => '250px',
					) ) ?>
					</a>
				<?php endif ?>

			</div>
			<h1>
				<a href="<?php echo bp_displayed_user_domain() ?>"><?php echo xprofile_get_field_data( 1, bp_displayed_user_id() ) ?></a>
			</h1>

			<div class="activity">
				<?php bp_last_activity( bp_displayed_user_id() ) ?>. <a href="<?php echo bp_displayed_user_domain() . _('activity') ?>">View this member's Commons activity.</a>
			</div>

			<?php $bd_field = cacap_get_brief_descriptor_field() ?>
			<?php if ( cacap_field_is_visible_for_user( $bd_field ) ) : ?>
				<h4 class="cacap-short-aboutme"><?php echo xprofile_get_field_data( $bd_field, bp_displayed_user_id() ) ?></h4>
			<?php endif ?>

			<?php $ay_field = cacap_get_about_you_field() ?>
			<?php if ( cacap_field_is_visible_for_user( $ay_field ) ) : ?>
				<div class="cacap-long-aboutme">
					<?php echo wpautop( bp_create_excerpt( html_entity_decode( xprofile_get_field_data( $ay_field, bp_displayed_user_id() ) ), 355 ) ) ?>
				</div>
			<?php endif ?>
		</div>

	</div>

	<?php if ( bp_is_my_profile() ) : ?>
		<div class="cacap-edit-buttons">
		<?php if ( bp_is_user_profile_edit() ) : ?>
			<input type="submit" value="<?php _e( 'Save Changes', 'cacap' ) ?>" class="cacap-edit-submit" />
			<a href="<?php echo bp_displayed_user_domain() . buddypress()->profile->slug ?>/" class="cacap-edit-cancel button"><?php _e( 'Cancel', 'cacap' ) ?></a>
		<?php else : ?>
			<a href="<?php echo bp_displayed_user_domain() ?><?php echo buddypress()->profile->slug ?>/edit/" class="cacap-edit-button button"><?php _e( 'Edit', 'cacap' ) ?></a>
		<?php endif ?>
		</div>
	<?php endif ?>
</div>
<div style="clear:both"> </div>
