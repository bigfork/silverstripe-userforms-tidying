<form {$AttributesHTML}>
    <% include SilverStripe\\UserForms\\Form\\UserFormProgress %>
    <% include SilverStripe\\UserForms\\Form\\UserFormStepErrors %>

	<% if $Message %>
		<p id="{$FormName}_error" class="alert alert--{$MessageType}">{$Message}</p>
	<% end_if %>

	<% if $Legend %>
		<fieldset>
			<legend>{$Legend}</legend>
			<div class="fieldset">
				<% include SilverStripe\\UserForms\\Form\\UserFormFields %>
			</div>
		</fieldset>
	<% else %>
		<div class="fieldset">
			<% include SilverStripe\\UserForms\\Form\\UserFormFields %>
		</div>
	<% end_if %>

    <% if $Steps.Count > 1 %>
        <% include SilverStripe\\UserForms\\Form\\UserFormStepNav %>
    <% else %>
        <% include SilverStripe\\UserForms\\Form\\UserFormActionNav %>
    <% end_if %>
</form>
