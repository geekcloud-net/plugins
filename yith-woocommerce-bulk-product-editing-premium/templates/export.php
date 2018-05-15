<?php
/** Load WordPress Bootstrap */
require_once( '../../../../wp-load.php' );
require_once( '../../../../wp-admin/admin.php' );

if ( !defined( 'YITH_WCBEP' ) ) {
    exit;
} // Exit if accessed directly

if ( !current_user_can( 'export' ) )
    wp_die( __( 'You do not have sufficient permissions to export the content of this site.' ) );

if ( isset( $_POST[ 'export_ids' ] ) ) {
    $ids = json_decode( $_POST[ 'export_ids' ] );
    $exporter = new YITH_WCBEP_Exporter();
    $exporter->export_products( $ids );
}
?>
