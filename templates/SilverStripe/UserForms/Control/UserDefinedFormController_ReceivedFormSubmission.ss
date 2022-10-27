<div id="uff">
	{$OnCompleteMessage}
</div>

<script type="text/javascript">
	window.dataLayer = window.dataLayer || [];
	window.dataLayer.push({
		'event': 'FormComplete',
		'Form' : '<% if $Title %>{$Title}<% else %>{$Parent.OwnerPage.MenuTitle}<% end_if %>'
	});
</script>
