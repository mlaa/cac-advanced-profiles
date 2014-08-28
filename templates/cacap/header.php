<?php
/**
 * Because of the way bp_locate_template() works in the admin, we have to do a
 * check to see whether the template needs to be loaded manually
 */
$template = bp_get_template_part( 'cacap/header-top' );
if ( $template ) {
	load_template( $template );
}
?>

<?php if ( ! cacap_is_commons_profile() ) : ?>
	<div class="cacap-row cacap-vitals-row">
		<dl id="cacap-vitals">
		<?php foreach ( cacap_vitals() as $vital ) : ?>
			<dt class="<?php echo esc_attr( $vital->css_class ) ?>"><?php echo cacap_sanitize_content( $vital->title ) ?></dt>

			<?php /* Don't escape content, because it may contain HTML */ ?>
			<dd class="<?php echo esc_attr( $vital->css_class ) ?>"><?php echo $vital->content ?></dd>

			<div class="cleardiv"></div>

		<?php endforeach ?>
		</dl>
	</div>
<?php endif ?>

<?php do_action( 'cacap_after_header' ) ?>
