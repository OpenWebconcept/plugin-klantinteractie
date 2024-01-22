<?php
/**
 * Template for displaying an error after submitting the contact data.
 *
 * @link       https://www.openwebconcept.nl
 * @since      1.0.0
 *
 * @package    Klantinteractie_Plugin
 * @subpackage Klantinteractie_Plugin/Templates
 */

declare( strict_types=1 );
get_header(); ?>
	<main id="main" class="template-openzaak">
		<div class="container">
			<div class="container-inner">

				<header>
					<h1>
						<?php esc_html_e( 'Something went wrong!', 'klantinteractie' ); ?>
					</h1>
					<div id="readspeaker_button1" class="rs_skip rsbtn rs_preserve">
						<a rel="nofollow" class="rsbtn_play" accesskey="L"
							title="<?php esc_attr_e( 'Let ReadSpeaker webReader read out the text.', 'klantinteractie' ); ?>"
							href="//app-eu.readspeaker.com/cgi-bin/rsent?customerid=8150&amp;lang=nl_nl&amp;readid=readspeaker&amp;url=<?php echo esc_attr( get_permalink() ); ?>">
							<span class="rsbtn_left rsimg rspart">
								<span class="rsbtn_text">
									<span><?php esc_html_e( 'Read aloud', 'klantinteractie' ); ?></span>
								</span>
							</span>
							<span class="rsbtn_right rsimg rsplay rspart"></span>
						</a>
					</div>
				</header>

				<div class="content">
					<div id="readspeaker">
						<?php esc_html_e( 'Updating your data failed.', 'klantinteractie' ); ?>
					</div>
				</div>

			</div>
		</div>
	</main>

<?php
get_footer();
