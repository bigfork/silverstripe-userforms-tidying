<% if $Tag = 'fieldset' && $Legend %>
	<fieldset>
		<legend class="form__field-label">{$Legend}</legend>
<% end_if %>

	<div class="form__columns">
		<% loop $FieldList %>
			{$FieldHolder}
		<% end_loop %>
	</div>

<% if $Tag = 'fieldset' && $Legend %>
	</fieldset>
<% end_if %>
