<tr valign="top" id="service_options">
	<th scope="row" class="titledesc"><?php esc_html_e( 'Services', 'woocommerce-shipping-usps' ); ?></th>
	<td class="forminp">
		<table class="usps_services widefat">
			<thead>
				<th class="sort">&nbsp;</th>
				<th><?php esc_html_e( 'Name', 'woocommerce-shipping-usps' ); ?></th>
				<th><?php esc_html_e( 'Service(s)', 'woocommerce-shipping-usps' ); ?></th>
				<th><?php printf( __( 'Price Adjustment (%s)', 'woocommerce-shipping-usps' ), get_woocommerce_currency_symbol() ); ?></th>
				<th><?php esc_html_e( 'Price Adjustment (%)', 'woocommerce-shipping-usps' ); ?></th>
			</thead>
			<tbody>
				<?php
					$sort = 0;
					$this->ordered_services = array();

					foreach ( $this->services as $code => $values ) {

						if ( isset( $this->custom_services[ $code ]['order'] ) ) {
							$sort = $this->custom_services[ $code ]['order'];
						}

						while ( isset( $this->ordered_services[ $sort ] ) )
							$sort++;

						$this->ordered_services[ $sort ] = array( $code, $values );

						$sort++;
					}

					ksort( $this->ordered_services );

					foreach ( $this->ordered_services as $value ) {
						$code   = $value[0];
						$values = $value[1];
						if ( ! isset( $this->custom_services[ $code ] ) )
							$this->custom_services[ $code ] = array();
						?>
						<tr>
							<td class="sort">
								<input type="hidden" class="order" name="usps_service[<?php echo esc_attr( $code ); ?>][order]" value="<?php echo isset( $this->custom_services[ $code ]['order'] ) ? esc_attr( $this->custom_services[ $code ]['order'] ) : ''; ?>" />
							</td>
							<td>
								<input type="text" name="usps_service[<?php echo esc_attr( $code ); ?>][name]" placeholder="<?php echo $values['name']; ?> (<?php echo $this->title; ?>)" value="<?php echo isset( $this->custom_services[ $code ]['name'] ) ? esc_attr( $this->custom_services[ $code ]['name'] ) : ''; ?>" size="35" />
							</td>
							<td>
								<ul class="sub_services" style="font-size: 0.92em; color: #555">
									<?php foreach ( $values['services'] as $key => $name ) :
										if ( 0 === $key ) {
											foreach( $name as $subsub_service_key => $subsub_service ) {
												?>
												<li style="line-height: 23px;">
													<label>
														<input type="checkbox" name="usps_service[<?php echo esc_attr( $code ); ?>][<?php echo esc_attr( $key ); ?>][<?php echo esc_attr( $subsub_service_key ); ?>][enabled]" <?php checked( ( ! isset( $this->custom_services[ $code ][ $key ][ $subsub_service_key ]['enabled'] ) || ! empty( $this->custom_services[ $code ][ $key ][ $subsub_service_key ]['enabled'] ) ), true ); ?> />
														<?php echo $subsub_service; ?>
													</label>
												</li>
												<?php
											}
										} else {
											?>
											<li style="line-height: 23px;">
												<label>
													<input type="checkbox" name="usps_service[<?php echo esc_attr( $code ); ?>][<?php echo esc_attr( $key ); ?>][enabled]" <?php checked( ( ! isset( $this->custom_services[ $code ][ $key ]['enabled'] ) || ! empty( $this->custom_services[ $code ][ $key ]['enabled'] ) ), true ); ?> />
													<?php echo $name; ?>
												</label>
											</li>
											<?php 
										}
									endforeach; ?>
								</ul>
							</td>
							<td>
								<ul class="sub_services" style="font-size: 0.92em; color: #555">
									<?php foreach ( $values['services'] as $key => $name ) :
										if ( 0 === $key ) {
											foreach( $name as $subsub_service_key => $subsub_service ) {
												?>
												<li>
													<?php echo get_woocommerce_currency_symbol(); ?><input type="text" name="usps_service[<?php echo esc_attr( $code ); ?>][<?php echo esc_attr( $key ); ?>][<?php echo esc_attr( $subsub_service_key ); ?>][adjustment]" placeholder="N/A" value="<?php echo isset( $this->custom_services[ $code ][ $key ][ $subsub_service_key ]['adjustment'] ) ? esc_attr( $this->custom_services[ $code ][ $key ][ $subsub_service_key ]['adjustment'] ) : ''; ?>" size="4" />
												</li>
												<?php
											}
										} else {
											?>
											<li>
												<?php echo get_woocommerce_currency_symbol(); ?><input type="text" name="usps_service[<?php echo esc_attr( $code ); ?>][<?php echo esc_attr( $key ); ?>][adjustment]" placeholder="N/A" value="<?php echo isset( $this->custom_services[ $code ][ $key ]['adjustment'] ) ? esc_attr( $this->custom_services[ $code ][ $key ]['adjustment'] ) : ''; ?>" size="4" />
											</li>
											<?php 
										}
									endforeach; ?>
								</ul>
							</td>
							<td>
								<ul class="sub_services" style="font-size: 0.92em; color: #555">
									<?php foreach ( $values['services'] as $key => $name ) :
										if ( 0 === $key ) {
											foreach( $name as $subsub_service_key => $subsub_service ) {
												?>
												<li>
													<input type="text" name="usps_service[<?php echo esc_attr( $code ); ?>][<?php echo esc_attr( $key ); ?>][<?php echo esc_attr( $subsub_service_key ); ?>][adjustment_percent]" placeholder="N/A" value="<?php echo isset( $this->custom_services[ $code ][ $key ][ $subsub_service_key ]['adjustment_percent'] ) ? esc_attr( $this->custom_services[ $code ][ $key ][ $subsub_service_key ]['adjustment_percent'] ) : ''; ?>" size="4" />%
												</li>
												<?php
											}
										} else {
											?>
											<li>
												<input type="text" name="usps_service[<?php echo esc_attr( $code ); ?>][<?php echo esc_attr( $key ); ?>][adjustment_percent]" placeholder="N/A" value="<?php echo isset( $this->custom_services[ $code ][ $key ]['adjustment_percent'] ) ? esc_attr( $this->custom_services[ $code ][ $key ]['adjustment_percent'] ) : ''; ?>" size="4" />%
											</li>		
											<?php 
										}
									endforeach; ?>
								</ul>
							</td>
						</tr>
						<?php
					}
				?>
			</tbody>
		</table>
	</td>
</tr>
