<div class="col col-xs-12">
	<div class="row tcb-revision-row margin-top-5 margin-bottom-5 margin-left-5 margin-right-5 padding-top-10 padding-bottom-5">
		<div class="col col-xs-2">
			<#= model.get('author')['avatar'] #>
		</div>
		<div class="col col-xs-6">
			<?php echo __( 'Revision made by ', 'thrive-cb' ); ?>
			<strong>
				<#= model.get('author')['name'] #>
			</strong>
			<br>
			<span class="tcb-revision-date-text"><#= model.get('dateShort') #>&nbsp;(<#= model.get('timeAgo') #>)</span>
		</div>
		<div class="col col-xs-4">
			<a class="click tcb-modal-lnk"
			   data-fn="clicked"
			   href="<#= model.get('restoreUrl') #>"><?php echo __( 'Restore Revision', 'thrive-cb' ) ?></a>
		</div>
	</div>
</div>
