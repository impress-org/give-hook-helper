<span style="display:none;<?php echo ( isset( $this->deprecated_hooks[ $args['ID'] ] ) ) ? 'background:orange!important;' : ''; ?>" class="ghh-hook ghh-hook-<?php echo $args['type'] ?><?php echo ( $nested_hooks ) ? ' ghh-hook-has-hooks' : ''; ?>">

	<?php if ( 'action' == $args['type'] ) : ?>
		<span class="ghh-hook-type"><?php _e( 'A', 'give-hook-helper' ); ?></span>
	<?php elseif ( 'filter' == $args['type'] ) : ?>
		<span class="ghh-hook-type"><?php _e( 'F', 'give-hook-helper' ); ?></span>
	<?php endif; ?>

	<?php
	// Main - Write the action hook name.
	//echo esc_html( $args['ID'] );
	echo $args['ID'];

	// @TODO - Caller function testing.
	if ( isset( $extra_data[1] ) && false !== $extra_data[1] ) {
		foreach ( $extra_data as $extra_data_key => $extra_data_value ) {
			echo '<br />';
			echo $extra_data_value['function'];
		}
	}

	// Write the count number if any function are hooked.
	if ( $nested_hooks_count ) : ?>
		<span class="ghh-hook-count">
			<?php echo $nested_hooks_count ?>
		</span>
		<?php
	endif;

	// Write out list of all the function hooked to an action.
	if ( isset( $wp_filter[ $args['ID'] ] ) ):

		$nested_hooks = $wp_filter[ $args['ID'] ];

		if ( $nested_hooks ): ?>
			<ul class="ghh-hook-dropdown">
				<li class="ghh-hook-heading">
					<strong><?php echo $args['type'] ?>:</strong>&nbsp;<?php echo $args['ID']; ?>

					<?php if ( isset( $this->new_hooks[ $args['ID'] ] ) ) : ?>
						<br><strong><?php _e( 'deprecated hook name', 'give-hook-helper' ) ?>
							:</strong>&nbsp;<?php echo $this->new_hooks[ $args['ID'] ]; ?>
					<?php elseif ( isset( $this->deprecated_hooks[ $args['ID'] ] ) ) : ?>
						<br><strong><?php _e( 'new hook name', 'give-hook-helper' ) ?>
							:</strong>&nbsp;<?php echo $this->deprecated_hooks[ $args['ID'] ]; ?>
					<?php endif; ?>
				</li>

				<?php foreach ( $nested_hooks as $nested_key => $nested_value ) : ?>
					<?php // Show the priority number if the following hooked functions ?>

					<li class="ghh-priority">
						<span class="ghh-priority-label"><strong><?php echo 'Priority:'; /* _e('Priority', 'give-hook-helper') */ ?></strong> <?php echo $nested_key ?></span>
					</li>

					<?php foreach ( $nested_value as $nested_inner_key => $nested_inner_value ) : ?>
						<?php // Show all teh functions hooked to this priority of this hook?>

						<li>
							<?php if ( $nested_inner_value['function'] && is_array( $nested_inner_value['function'] ) && count( $nested_inner_value['function'] ) > 1 ) : ?>

								<?php // Hooked function ( of type object->method() )?>
								<span class="ghh-function-string">
									<?php
									$classname = false;

									if ( is_object( $nested_inner_value['function'][0] ) || is_string( $nested_inner_value['function'][0] ) ) {
										if ( is_object( $nested_inner_value['function'][0] ) ) {
											$classname = get_class( $nested_inner_value['function'][0] );
										}

										if ( is_string( $nested_inner_value['function'][0] ) ) {
											$classname = $nested_inner_value['function'][0];
										}

										if ( $classname ) {
											echo "$classname&ndash;&gt;";
										}
									}
									?>

									<?php echo $nested_inner_value['function'][1] ?>
								</span>
							<?php else : ?>
								<?php // Hooked function ( of type function() ) ?>
								<span class="ghh-function-string">
									<?php echo $nested_inner_key ?>
								</span>
							<?php endif; ?>
						</li>

					<?php endforeach; ?>
				<?php endforeach; ?>

			</ul>
		<?php endif; ?>
	<?php endif; ?>
</span>