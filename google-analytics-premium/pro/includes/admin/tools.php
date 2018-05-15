<?php
/**
 * Tools class.
 *
 * @since 6.0.0
 *
 * @package MonsterInsights
 * @subpackage Tools
 * @author  Chris Christoff
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function monsterinsights_tools_url_builder() {
	ob_start();?>
	<h2><?php echo esc_html__( 'Generate custom campaign parameters for your advertising URLS.', 'ga-premium' );?></h2>
	<p><?php echo  esc_html__( 'The URL builder helps you add parameters to your URLs you use in custom web-based or email ad campaigns. A custom campaign is any ad campaign not using the AdWords auto-tagging feature. When users click one of the custom links, the unique parameters are sent to your Analytics account, so you can identify the urls that are the most effective in attracting users to your content.', 'ga-premium' ); ?> </p>
	<p><?php echo esc_html__('Fill out the required fields (marked with *) in the form below, and as you make changes the full campaign URL will be generated for you.', 'ga-premium' ); ?></p>
	<br />
	<form id="monsterinsights-url-builder" action="javascript:void(0);">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="monsterinsights-url-builer-domain">
							<?php echo esc_html__( 'Website URL', 'ga-premium' );?><span class="monsterinsights-required-indicator">*</span>
						</label>
					</th>
					<td>
						<input type="url" name="domain" id="monsterinsights-url-builer-domain" value="" />
						<p class="description"><?php echo sprintf( esc_html__( 'The full website URL (e.g. %1$s)', 'ga-premium' ), home_url() );?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="monsterinsights-url-builer-source">
							<?php echo esc_html__( 'Campaign Source', 'ga-premium' );?><span class="monsterinsights-required-indicator">*</span>
						</label>
					</th>
					<td>
						<input type="text" name="source" id="monsterinsights-url-builer-source" value="" />
						<p class="description"><?php echo sprintf( esc_html__( 'Enter a referrer (e.g. %1$s, %2$s, %3$s)', 'ga-premium' ), '<code>facebook</code>', '<code>newsletter</code>', '<code>google</code>' );?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="monsterinsights-url-builer-medium">
							<?php echo esc_html__( 'Campaign Medium', 'ga-premium' );?>
						</label>
					</th>
					<td>
						<input type="text" name="medium" id="monsterinsights-url-builer-medium" value="" />
						<p class="description"><?php echo sprintf( esc_html__( 'Enter a marketing medium (e.g. %1$s, %2$s, %3$s)', 'ga-premium' ), '<code>cpc</code>', '<code>banner</code>', '<code>email</code>' );?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="monsterinsights-url-builer-name">
							<?php echo esc_html__( 'Campaign Name', 'ga-premium' );?>
						</label>
					</th>
					<td>
						<input type="text" name="name" id="monsterinsights-url-builer-name" value="" />
						<p class="description"><?php echo sprintf( esc_html__( 'Enter a name to identify the campaign (e.g. %1$s)', 'ga-premium' ), '<code>spring_sale</code>' );?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="monsterinsights-url-builer-term">
							<?php echo esc_html__( 'Campaign Term', 'ga-premium' );?>
						</label>
					</th>
					<td>
						<input type="text" name="term" id="monsterinsights-url-builer-term" value="" />
						<p class="description"><?php echo esc_html__( 'Enter the paid keyword', 'ga-premium' );?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="monsterinsights-url-builer-content">
							<?php echo esc_html__( 'Campaign Content', 'ga-premium' );?>
						</label>
					</th>
					<td>
						<input type="text" name="content" id="monsterinsights-url-builer-content" value="" />
						<p class="description"><?php echo esc_html__( 'Enter something to differentiate ads', 'ga-premium' );?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="monsterinsights-url-builer-fragment">
							<?php echo esc_html__( 'Use Fragment', 'ga-premium' );?>
						</label>
					</th>
					<td>
						<input type="checkbox" name="fragment" id="monsterinsights-url-builer-fragment" value="" />
						<p class="description"><?php echo esc_html__( 'Set the parameters in the fragment portion of the URL (not recommended).', 'ga-premium' );?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="monsterinsights-url-builer-url">
							<?php echo esc_html__( 'URL to use (updates automatically):', 'ga-premium' );?>
						</label>
					</th>
					<td>
						<textarea name="url" id="monsterinsights-url-builer-url" value="" readonly="readonly"></textarea>
						<p>
							<button class="monsterinsights-copy-to-clipboard monsterinsights-action-button button button-action" data-clipboard-target="#monsterinsights-url-builer-url">
								<?php echo esc_html__( 'Copy to clipboard' ,'ga-premium');?>
							</button>
						</p>
					</td>
				</tr>

			</tbody>
		</table>
	</form>
	<h2><?php echo esc_html__( 'More information and examples for each option', 'ga-premium');?></h2>
	<p><?php echo esc_html__( 'The following table gives a detailed explanation and example of each of the campaign parameters.', 'ga-premium');?></p>
	<table class="wp-list-table widefat striped">
	  <tbody>
		<tr>
		  <td>
			<p><strong><?php echo esc_html__( 'Campaign Source', 'ga-premium');?></strong></p>
			<p><code>utm_source</code></p>
		  </td>
		  <td>
			<p><strong><?php echo esc_html__( 'Required.', 'ga-premium');?></strong></p>
			<p><?php echo sprintf( esc_html__( 'Use %1$s to identify a search engine, newsletter name, or other source.', 'ga-premium'),'<code>utm_source</code>');?></p>
			<p><em><?php echo esc_html__( 'Example:', 'ga-premium');?></em> <code>google</code></p>
		  </td>
		</tr>
		<tr>
		  <td>
			<p><strong><?php echo esc_html__( 'Campaign Medium', 'ga-premium');?></strong></p>
			<p><code>utm_medium</code></p>
		  </td>
		  <td>
			<p><?php echo sprintf(esc_html__( 'Use %1$s to identify a medium such as email or cost-per-click.', 'ga-premium'),'<code>utm_medium</code>');?></p>
			<p><em><?php echo esc_html__( 'Example:', 'ga-premium');?></em> <code>cpc</code></p>
		  </td>
		</tr>
		<tr>
		  <td>
			<p><strong><?php echo esc_html__( 'Campaign Name', 'ga-premium');?></strong></p>
			<p><code>utm_campaign</code></p>
		  </td>
		  <td>
			<p><?php echo sprintf(esc_html__( 'Used for keyword analysis. Use %1$s to identify a specific product promotion or strategic campaign.', 'ga-premium'),'<code>utm_campaign</code>');?></p>
			<p><em><?php echo esc_html__( 'Example:', 'ga-premium');?></em> <code>utm_campaign=spring_sale</code></p>
		  </td>
		</tr>
		<tr>
		  <td>
			<p><strong><?php echo esc_html__( 'Campaign Term', 'ga-premium');?></strong></p>
			<p><code>utm_term</code></p>
		  </td>
		  <td>
			<p><?php echo sprintf( esc_html__( 'Used for paid search. Use %1$s to note the keywords for this ad.', 'ga-premium'),'<code>utm_term</code>');?></p>
			<p><em><?php echo esc_html__( 'Example:', 'ga-premium');?></em> <code>running+shoes</code></p>
		  </td>
		</tr>
		<tr>
		  <td>
			<p><strong><?php echo esc_html__( 'Campaign Content', 'ga-premium');?></strong></p>
			<p><code>utm_content</code></p>
		  </td>
		  <td>
			<p><?php echo sprintf(esc_html__( 'Used for A/B testing and content-targeted ads. Use %1$s to differentiate ads or links that point to the same URL.', 'ga-premium'),'<code>utm_content</code>');?></p>
			<p><em><?php echo esc_html__( 'Examples:', 'ga-premium');?></em> <code>logolink</code> <em><?php echo esc_html__( 'or', 'ga-premium');?></em> <code>textlink</code></p>
		  </td>
		</tr>
	  </tbody>
	</table>
	
	<h2 id="monsterinsights-related-resources"><?php echo esc_html__( 'More information:', 'ga-premium');?></h2>

	<ul id="monsterinsights-related-resources-list">
	  <li><a href="https://support.google.com/analytics/answer/1247851"><?php echo esc_html__( 'About Campaigns', 'ga-premium');?></a></li>
	  <li><a href="https://support.google.com/analytics/answer/1033863"><?php echo esc_html__( 'About Custom Campaigns', 'ga-premium');?></a></li>
	  <li><a href="https://support.google.com/analytics/answer/1037445"><?php echo esc_html__( 'Best practices for creating Custom Campaigns', 'ga-premium');?></a></li>
	  <li><a href="https://support.google.com/analytics/answer/1247839"><?php echo esc_html__( 'About the Referral Traffic report', 'ga-premium');?></a></li>
	  <li><a href="https://support.google.com/analytics/answer/1033173"><?php echo esc_html__( 'About traffic source dimensions', 'ga-premium');?></a></li>
	  <li><a href="https://support.google.com/adwords/answer/1752125"><?php echo esc_html__( 'AdWords Auto-Tagging', 'ga-premium');?></a></li>
	</ul>
	<?php
	echo ob_get_clean();
}
add_action( 'monsterinsights_tools_url_builder_tab', 'monsterinsights_tools_url_builder' );