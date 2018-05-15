<script type="text/javascript">
	if ( window.parent && window.parent.TVE && window.parent.TVE.CONST ) {
		if ( window.parent.TVE.CONST.userkey === <?php echo json_encode( $data['userkey'] ) ?> ) {
			window.parent.TVE.handle_login( <?php echo json_encode( $data ) ?> );
		}
	}
</script>
