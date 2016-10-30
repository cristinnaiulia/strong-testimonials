<?php
/**
 * Shortcode functions.
 *
 * @package Strong_Testimonials
 */

/**
 * testimonial_view shortcode
 *
 * @param      $atts
 * @param null $content
 *
 * @return mixed|string|void
 */
function wpmtst_strong_view_shortcode( $atts, $content = null ) {
	$out = shortcode_atts(
		WPMST()->get_view_defaults(),
		normalize_empty_atts( $atts ),
		'testimonial_view'
	);

	return wpmtst_render_view( $out );
}
add_shortcode( 'testimonial_view', 'wpmtst_strong_view_shortcode' );

/**
 * testimonial_view attribute filter
 *
 * @since 1.21.0
 * @param $out
 * @param $pairs
 * @param $atts
 *
 * @return array
 */
function wpmtst_strong_view_shortcode_filter( $out, $pairs, $atts ) {
	return WPMST()->parse_view( $out, $pairs, $atts );
}
add_filter( 'shortcode_atts_testimonial_view', 'wpmtst_strong_view_shortcode_filter', 10, 3 );

/**
 * Render the View.
 *
 * @param $out
 *
 * @return mixed|string|void
 */
function wpmtst_render_view( $out ) {
	// Did we find this view?
	if ( isset( $out['view_not_found'] ) && $out['view_not_found'] ) {
		return '<p style="color:red">' . sprintf( __( 'Strong Testimonials error: View %s not found' ), $out['view'] ) . '</p>';
	}

	// Container class is shared by display and form in templates.
	if ( $out['class'] ) {
		$out['container_class'] .= ' ' . str_replace( ',', ' ', $out['class'] );
	}
	if ( is_rtl() ) {
		$out['container_class'] .= ' rtl';
	}
	WPMST()->set_atts( $out );

	/**
	 * MODE: FORM
	 */
	if ( $out['form'] )
		return wpmtst_form_view( $out );

	/**
	 * MODE: DISPLAY (default)
	 */
	global $view;
	$view = new Strong_View( $out );
	$view->build();
	return $view->output();
}

/**
 * The form.
 *
 * @param $atts
 * @todo Move this into View object
 *
 * @return mixed|string|void
 */
function wpmtst_form_view( $atts ) {
	if ( isset( $_GET['success'] ) ) {
		// Load stylesheet
		do_action( 'wpmtst_form_success', $atts );
		return apply_filters( 'wpmtst_form_success_message', '<div class="testimonial-success">' .  wpmtst_get_form_message( 'submission-success' ) . '</div>' );
	}

	$atts            = normalize_empty_atts( $atts );
	$fields          = wpmtst_get_form_fields( $atts['form_id'] );
	$form_values     = array( 'category' => $atts['category'] );

	foreach ( $fields as $key => $field ) {
		$form_values[ $field['name'] ] = '';
	}

	$previous_values = WPMST()->get_form_values();
	if ( $previous_values ) {
		$form_values = array_merge( $form_values, $previous_values );
	}

	WPMST()->set_form_values( $form_values );

	/**
	 * Add filters here.
	 */

	/**
	 * Load template
	 */
	$template_file = WPMST()->templates->get_template_attr( $atts, 'template' );
	ob_start();
	/** @noinspection PhpIncludeInspection */
	include $template_file;
	$html = ob_get_contents();
	ob_end_clean();

	/**
	 * Remove filters here.
	 */

	do_action( 'wpmtst_form_rendered', $atts );

	$html = apply_filters( 'strong_view_form_html', $html );

	return $html;
}

/**
 * Normalize empty shortcode attributes.
 *
 * Turns atts into tags - brilliant!
 * Thanks http://wordpress.stackexchange.com/a/123073/32076
 */
if ( ! function_exists( 'normalize_empty_atts' ) ) {
	function normalize_empty_atts( $atts ) {
		if ( ! empty( $atts ) ) {
			foreach ( $atts as $attribute => $value ) {
				if ( is_int( $attribute ) ) {
					$atts[ strtolower( $value ) ] = true;
					unset( $atts[ $attribute ] );
				}
			}
			return $atts;
		}
	}
}

/**
 * Honeypot preprocessor
 */
function wpmtst_honeypot_before() {
	if ( isset( $_POST['wpmtst_if_visitor'] ) && ! empty( $_POST['wpmtst_if_visitor'] ) ) {
		do_action( 'honeypot_before_spam_testimonial', $_POST );
		$form_options = get_option( 'wpmtst_form_options' );
		$messages     = $form_options['messages'];
		die( apply_filters( 'wpmtst_l10n', $messages['submission-error']['text'], 'strong-testimonials-form-messages', $messages['submission-error']['description'] ) );
	}
	return;
}

/**
 * Honeypot preprocessor
 */
function wpmtst_honeypot_after() {
	// TODO Something is preventing JS from adding this so the form fails.
	if ( ! isset ( $_POST['wpmtst_after'] ) ) {
		do_action( 'honeypot_after_spam_testimonial', $_POST );
		$form_options = get_option( 'wpmtst_form_options' );
		$messages     = $form_options['messages'];
		die( apply_filters( 'wpmtst_l10n', $messages['submission-error']['text'], 'strong-testimonials-form-messages', $messages['submission-error']['description'] ) );
	}
	return;
}

/**
 * Honeypot
 */
function wpmtst_honeypot_before_script() {
	?>
	<script type="text/javascript">jQuery('#wpmtst_if_visitor').val('');</script>
	<?php
}

/**
 * Honeypot
 */
function wpmtst_honeypot_after_script() {
	?>
	<script type='text/javascript'>
		//<![CDATA[
		( function( $ ) {
			'use strict';
			var forms = "#wpmtst-submission-form";
			$( forms ).submit( function() {
				$( "<input>" ).attr( "type", "hidden" )
					.attr( "name", "wpmtst_after" )
					.attr( "value", "1" )
					.appendTo( forms );
				return true;
			});
		})( jQuery );
		//]]>
	</script>
	<?php
}

/**
 * Remove whitespace between tags. Helps prevent double wpautop in plugins
 * like Posts For Pages and Custom Content Shortcode.
 *
 * @param $html
 * @since 2.3
 *
 * @return mixed
 */
function wpmtst_strong_view_html( $html ) {
	$options = get_option( 'wpmtst_options' );
	if ( $options['remove_whitespace'] ) {
		$html = preg_replace( '~>\s+<~', '><', $html );
		//$html = preg_replace('~[\r\n]+~', '', $html);
	}

	return $html;
}
add_filter( 'strong_view_html', 'wpmtst_strong_view_html' );


/**
 * For testing shortcode field in forms.
 *
 * @param      $atts
 * @param null $content
 *
 * @return string
 */
function wpmtst_hello( $atts, $content = null ) {
	return 'Hello!';
}
add_shortcode( 'wpmtst_hello', 'wpmtst_hello' );
