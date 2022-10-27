<% loop $Options %>
	<div class="{$Class}">
		<input id="{$ID}" class="checkbox" name="{$Name}" type="checkbox" value="{$Value.ATT}"
            <% if $isChecked %>checked<% end_if %>
            <% if $isDisabled %>disabled<% end_if %> />
		<label for="{$ID}">{$Title}</label>
	</div>
<% end_loop %>
