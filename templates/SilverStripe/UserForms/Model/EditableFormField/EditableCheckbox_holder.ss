<div class="form__field-group<% if $extraClass %> {$extraClass}<% end_if %>" id="{$HolderID}">
	<div class="form__field-holder<% if not $Title %> form__field-holder--no-label<% end_if %>">
		<div class="form__field form-check">
			{$Field}
            <% if $Title %><label class="form-check-label" for="{$ID}">{$Title}</label><% end_if %>
		</div>
        <% if $Description %><p class="form__field-description">{$Description}</p><% end_if %>
        <% if $Message %><p class="alert alert--{$MessageType}" role="alert">{$Message}</p><% end_if %>
	</div>
</div>
