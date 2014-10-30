<!DOCTYPE html>
<html>
<?php
/**
 * The top-level template for CAC Advanced Profiles
 *
 * We use a top-level template because CACAP does not use the standard WP
 * header/sidebar/footer. However, in the future, we might refactor the
 * plugin so that the header/sidebar/footer are dynamically removed from an
 * existing top-level template
 */
?>

<?php locate_template( 'templates/parts/header-head.php', true ); ?> 

<body <?php body_class() ?>>

<!-- required by Remodal plugin so that we can make modal dialogs 
     with this content --> 
<div class="remodal-bg"> 

	<?php do_action( 'cacap_before_content' ) ?>

	<div id="cacap-content">
		<?php if ( bp_is_user_profile_edit() ) : ?>

			<div class="remodal" data-remodal-id="modal">
				<h1>Welcome to Portfolios</h1>
				<p>The Portfolios profile system allows you to enter information about yourself and your career, so that you might better connect with other users of the Commons. Read more about Portfolios on <a href="http://howtouse.commons.mla.org/category/how-to-do-things-with-mla-commons/portfolios">the help blog</a>.</p>
				<br>
				<!-- <a class="remodal-cancel" href="#">Cancel</a> --> 
				<a class="remodal-confirm" href="#">Get Started</a>
			</div>

			<form action="" method="post" id="cacap-edit-form">
				<div id="cacap-header">
					<?php bp_get_template_part( 'cacap/header-edit' ) ?>
				</div>

				<div id="cacap-edit">
					<?php bp_get_template_part( 'cacap/body-edit' ) ?>
				</div>
			</form>
		<?php else : ?>
			<div id="cacap-header">
				<?php bp_get_template_part( 'cacap/header' ) ?>
			</div>

			<div id="cacap-body">
				<?php bp_get_template_part( 'cacap/body' ) ?>
			</div>
		<?php endif ?>
	</div>

	<?php get_footer( 'cacap' ) ?>

</div> <!-- end .remodal-bg --> 
</body>
</html>
