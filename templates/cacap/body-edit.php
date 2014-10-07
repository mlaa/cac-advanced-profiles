<div id="cacap-body">

	<div class="cacap-row" id="cacap-user-widget-new">
		<div id="cacap-user-widget-new-content">
			<h2><?php _e( 'Add New Section', 'cacap' ) ?></h2>

			<ul id="cacap-new-widget-types">
			<?php foreach ( cacap_widget_types() as $widget_type ) : ?>
				<?php

				if ( ! $widget_type->allow_new ) {
					continue;
				}

				$css_classes = array();
				if ( cacap_widget_type_is_disabled_for_user( $widget_type ) ) {
					$css_classes[] = 'cacap-has-max';
				}

				if ( ! $widget_type->allow_multiple ) {
					$css_classes[] = 'disable-multiple';
				}

				?>

				<li class="<?php echo implode( ' ', $css_classes ) ?>" id="cacap-new-widget-<?php echo esc_attr( $widget_type->slug ) ?>">
					<a href="#cacap-user-widget-new-content?type=<?php echo esc_attr( $widget_type->slug ) ?>">
						<span class="cacap-plus">+</span>
						<span class="cacap-widget-type-name"><?php echo $widget_type->name ?></span>
						<?php if ( ! empty( $disabled ) ) : ?>
							<span class="cacap-has-max-tooltip"><?php _e( 'You already have a widget of this type.', 'cacap' ) ?></span>
						<?php endif ?>
					</a>
				</li>
			<?php endforeach ?>
			</ul>

			<div id="cacap-widget-prototypes">
			<?php foreach ( cacap_widget_types() as $widget_type ) : ?>
				<?php $wi_prototype = new CACAP_Widget_Instance( array( 'widget_type' => $widget_type, 'key' => 'newwidgetkey' ) ) ?>
				<div id="cacap-widget-prototype-<?php echo esc_attr( $widget_type->slug ) ?>">
					<div class="cacap-drag-handle"></div>
					<div class="cacap-widget-title <?php if ( $widget_type->allow_custom_title ) : ?>cacap-widget-section-editable <?php endif ?>cacap-click-to-edit"><?php echo $wi_prototype->edit_title() ?></div>
					<div class="cacap-widget-content cacap-widget-section-editable cacap-click-to-edit <?php echo $wi_prototype->widget_type->content_type; ?>"><?php echo $wi_prototype->edit_content() ?> <div class="cacap-error"></div> </div>

					<input type="hidden" value="<?php echo esc_attr( $wi_prototype->widget_type->slug ) ?>" name="<?php echo esc_attr( $wi_prototype->css_id ) ?>[widget_type]" />
					<a href="#" class="cacap-widget-remove button confirm"><?php _e( 'Remove', 'cacap' ) ?></a>

					<!-- Visibility Stuff! --> 	

				<?php do_action( 'bp_custom_profile_edit_fields_pre_visibility' ); ?>

				<?php if ( bp_current_user_can( 'bp_xprofile_change_field_visibility' ) ) : ?>

					<?php $xprofile_field_id = 0; // placeholder ID ?> 

					<p class="field-visibility-settings-toggle" id="field-visibility-settings-toggle-<?php echo $xprofile_field_id; ?>">

						<?php $visibility_level = 'public'; // default to public ?> 
						<?php $fields = bp_xprofile_get_visibility_levels(); ?> 
						<?php $visibility_level_label = $fields[$visibility_level]['label'] ?> 
						<?php printf( __( 'This field can be seen by: <span class="current-visibility-level">%s</span>', 'buddypress' ), $visibility_level_label ); ?> <a href="#" class="visibility-toggle-link"><?php _e( 'Change', 'buddypress' ); ?></a>

					</p>

					<div class="field-visibility-settings" id="field-visibility-settings-<?php echo $xprofile_field_id; ?>">
						<fieldset>
							<legend><?php _e( 'Who can see this field?', 'buddypress' ) ?></legend>

						<ul class="radio">
									<?php if ( bp_current_user_can( 'bp_xprofile_change_field_visibility' ) ) : ?>

										<?php foreach( $fields as $level ) : ?>

											<?php printf( '<li class="%s">', esc_attr( $level['id'] ) ); ?>

												<input type="radio" id="<?php echo esc_attr( 'see-field_' . $xprofile_field_id . '_' . $level['id'] ); ?>" name="<?php echo esc_attr( $widget_instance->css_id . '[visibility]' ); ?>" value="<?php echo esc_attr( $level['id'] ); ?>" <?php checked( $level['id'], $visibility_level ); ?> />
											<label for="<?php echo esc_attr( 'see-field_' . $xprofile_field_id . '_' . $level['id'] ); ?>">
												<span class="field-visibility-text"><?php echo esc_html( $level['label'] ); ?></span>
											</label>

											<?php echo '</li>'; ?>

										<?php endforeach; ?>

									<?php endif; ?> 

						</ul> 
						</fieldset>
						<a class="field-visibility-settings-close" href="#"><?php _e( 'Close', 'buddypress' ) ?></a>
					</div>
				<?php else : ?>
					<div class="field-visibility-settings-notoggle" id="field-visibility-settings-toggle-<?php echo $xprofile_field_id; ?>">
						<?php printf( __( 'This field can be seen by: <span class="current-visibility-level">%s</span>', 'buddypress' ), bp_get_the_profile_field_visibility_level_label() ) ?>
					</div>
				<?php endif ?>
					
				<!-- End Visibility Stuff --> 

				</div>

			<?php endforeach ?>
			</div>
		</div>
	</div>

	<div class="cacap-row cacap-widgets cacap-widgets-edit">
		<ul id="cacap-widget-list">
		<?php foreach ( cacap_user_widget_instances() as $widget_instance ) : ?>
                        <?php if ( ! $widget_instance->widget_type->allow_edit ) continue ?>

			<li id="cacap-widget-<?php echo esc_attr( $widget_instance->css_id ) ?>" class="cacap-widget-<?php echo esc_attr( $widget_instance->widget_type->slug ) ?>">
				<div class="cacap-drag-handle"></div>
				<div class="cacap-widget-title <?php if ( $widget_instance->widget_type->allow_custom_title ) : ?>cacap-widget-section-editable <?php endif ?>cacap-click-to-edit" id="<?php echo esc_attr( $widget_instance->css_id ) ?>-title"><?php echo $widget_instance->edit_title() ?></div>
				<div class="cacap-widget-content cacap-widget-section-editable cacap-click-to-edit <?php echo $widget_instance->widget_type->content_type ?>" id="<?php echo esc_attr( $widget_instance->css_id ) ?>-content"><?php echo $widget_instance->edit_content() ?>

					<div class="cacap-error"></div>
				</div>
				<input type="hidden" value="<?php echo esc_attr( $widget_instance->widget_type->slug ) ?>" name="<?php echo esc_attr( $widget_instance->css_id ) ?>[widget_type]" />

				<a href="#" class="cacap-widget-remove button confirm"><?php _e( 'Remove', 'cacap' ) ?></a>

				<!-- Visibility Stuff --> 

				<?php do_action( 'bp_custom_profile_edit_fields_pre_visibility' ); ?>

				<?php $xprofile_field_id = xprofile_get_field_id_from_name( $widget_instance->widget_type->name ); ?> 


				<?php if ( bp_current_user_can( 'bp_xprofile_change_field_visibility' ) ) : ?>
					<p class="field-visibility-settings-toggle" id="field-visibility-settings-toggle-<?php echo $xprofile_field_id; ?>">


						<?php if ( ! $xprofile_field_id ) { // this is probably a Text widget with the visibility stored in user meta
							$visibility_level = $widget_instance->value['visibility']; 
							$xprofile_field_id = 0; // placeholder ID
						} else { // this is probably an oridinary widget with visibility stored in xprofile tables
							$visibility_level = xprofile_get_field_visibility_level( $xprofile_field_id, bp_displayed_user_id() ); 
						} 
						?> 

						<?php $fields = bp_xprofile_get_visibility_levels(); ?> 
						<?php $visibility_level_label = $fields[$visibility_level]['label'] ?> 
						<?php printf( __( 'This field can be seen by: <span class="current-visibility-level">%s</span>', 'buddypress' ), $visibility_level_label ); ?> <a href="#" class="visibility-toggle-link"><?php _e( 'Change', 'buddypress' ); ?></a>

					</p>

					<div class="field-visibility-settings" id="field-visibility-settings-<?php echo $xprofile_field_id; ?>">
						<fieldset>
							<legend><?php _e( 'Who can see this field?', 'buddypress' ) ?></legend>

							<?php $radio_args = array( 'field_id' => $xprofile_field_id, ); ?> 


						<?php echo '<ul class="radio">'; ?> 
									<?php if ( bp_current_user_can( 'bp_xprofile_change_field_visibility' ) ) : ?>

										<?php foreach( $fields as $level ) : ?>

											<?php printf( '<li class="%s">', esc_attr( $level['id'] ) ); ?>

												<input type="radio" id="<?php echo esc_attr( 'see-field_' . $xprofile_field_id . '_' . $level['id'] ); ?>" name="<?php echo esc_attr( $widget_instance->css_id . '[visibility]' ); ?>" value="<?php echo esc_attr( $level['id'] ); ?>" <?php checked( $level['id'], $visibility_level ); ?> />
											<label for="<?php echo esc_attr( 'see-field_' . $xprofile_field_id . '_' . $level['id'] ); ?>">
												<span class="field-visibility-text"><?php echo esc_html( $level['label'] ); ?></span>
											</label>

											<?php echo '</li>'; ?>

										<?php endforeach; ?>

									<?php endif; ?> 

						<?php echo '</ul>'; ?> 

						</fieldset>
						<a class="field-visibility-settings-close" href="#"><?php _e( 'Close', 'buddypress' ) ?></a>
					</div>
				<?php else : ?>
					<div class="field-visibility-settings-notoggle" id="field-visibility-settings-toggle-<?php echo $xprofile_field_id; ?>">
						<?php printf( __( 'This field can be seen by: <span class="current-visibility-level">%s</span>', 'buddypress' ), bp_get_the_profile_field_visibility_level_label() ) ?>
					</div>
				<?php endif ?>
					
				<!-- End Visibility Stuff --> 
			</li>
		<?php endforeach; ?>
		</ul>
	</div>

	<input type="hidden" name="cacap-widget-order" id="cacap-widget-order" value="<?php echo cacap_widget_order() ?>" />

	<?php if ( bp_is_my_profile() ) : ?>
		<div class="cacap-edit-buttons cacap-edit-buttons-bottom">
		<?php if ( bp_is_user_profile_edit() ) : ?>
			<input type="submit" value="<?php _e( 'Save Changes', 'cacap' ) ?>" class="cacap-edit-submit" />
			<a href="<?php echo bp_displayed_user_domain() . buddypress()->profile->slug ?>/" class="cacap-edit-cancel button"><?php _e( 'Cancel', 'cacap' ) ?></a>
		<?php else : ?>
			<a href="<?php echo bp_displayed_user_domain() ?>/<?php echo buddypress()->profile->slug ?>/edit/" class="cacap-edit-button button"><?php _e( 'Edit', 'cacap' ) ?></a>
		<?php endif ?>
		</div>
	<?php endif ?>
</div>

