<?php
/**
 * Settings
 *
 * @package Strong_Testimonials
 * @since 1.13
 */

$options = get_option( 'wpmtst_options' );
?>
<table class="form-table" cellpadding="0" cellspacing="0">
	<tr valign="top">
		<th scope="row">
			<?php _e( 'Reordering', 'strong-testimonials' ); ?>
		</th>
		<td>
			<label>
				<input type="checkbox" name="wpmtst_options[reorder]" <?php checked( $options['reorder'] ); ?>>
				<?php _e( 'Enable drag-and-drop reordering in the testimonial list. Off by default.', 'strong-testimonials' ); ?>
			</label>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php _e( 'Scroll Top', 'strong-testimonials' ); ?>
		</th>
		<td>
			<label>
				<input type="checkbox" name="wpmtst_options[scrolltop]" <?php checked( $options['scrolltop'] ); ?>>
				<?php _e( 'In paginated Views, scroll to the top when a new page is selected. On by default.', 'strong-testimonials' ); ?>
			</label>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php _e( 'Remove Whitespace', 'strong-testimonials' ); ?>
		</th>
		<td>
			<label>
				<input type="checkbox" name="wpmtst_options[remove_whitespace]" <?php checked( $options['remove_whitespace'] ); ?>>
				<?php _e( 'Remove space between HTML tags in View output to prevent double paragraphs <em>(wpautop)</em>. On by default.', 'strong-testimonials' ); ?>
			</label>
		</td>
	</tr>
</table>
