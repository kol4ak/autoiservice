<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package aReview
 */
?>

	</div><!-- #content -->
	<?php if ( is_active_sidebar( 'sidebar-4' ) || is_active_sidebar( 'sidebar-5' ) || is_active_sidebar( 'sidebar-6' ) ) : ?>
		<?php get_sidebar('footer'); ?>
	<?php endif; ?>

	<footer id="colophon" class="site-footer" role="contentinfo">
		<div class="decoration-bar"></div>
		<div class="site-info container">
			<p>© 2015 Журнал "Авто и Сервис" - твой главный помощник в мире авто и сервиса.
				Все права защищены. Использование любых материалов, размещённых на сайте,
				разрешается при условии ссылки на сайт <span><a href="autoiservice.org"> www.autoiservice.org</a></span> </p>
		</div><!-- .site-info -->
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
