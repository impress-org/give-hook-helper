<?php global $wp_filter, $wp_actions; ?>
<div class="ghh-nested-hooks-block <?php echo ( 'show-filter-hooks' == $this->status ) ? 'ghh-active' : ''; ?> ">
	<?php
	foreach ( $this->all_hooks as $va_nested_value ) {

		if ( false === $this->is_give_plugin_hooks( $va_nested_value ) ) {
			continue;
		}

		if ( 'action' == $va_nested_value['type'] || 'filter' == $va_nested_value['type'] ) {
			$this->render_action( $va_nested_value );
		} else {
			?>
			<div class="ghh-collection-divider">
				<?php echo $va_nested_value['ID'] ?>
			</div>
			<?php
		}

		/*
		?>
		<div class="va-action">
			<?php echo $va_nested_value ?>
		</div>
		<?php
		*/
	}
	?>
</div>