<%--

!!! Do not remove any CSS classes !!!

The userforms JavaScript relies heavily on the specific classes used below

--%>

<% if $Steps.Count > 1 %>
	<div id="userform-progress" class="userform-progress" aria-hidden="true" style="display:none;">
		<div class="progress">
			<div class="progress-bar" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="{$Steps.Count}"></div>
		</div>
		<nav aria-label="Pages in this form">
			<ul class="step-buttons">
				<% loop $Steps %>
					<li class="step-button-wrapper<% if $First %> current<% end_if %>" data-for="{$Name}">
						<button class="step-button-jump" disabled="disabled" data-step="{$Pos}">{$Title}</button>
					</li>
				<% end_loop %>
			</ul>
		</nav>
	</div>
	<h2 class="progress-title"></h2>
<% end_if %>
