<% if Content %>
<div id="$ClassName">
	<% control Content %>
	<div class="entry">
		<div class="submitdetails">
			<% if Submitter %>Submitted by: <span class="author">$Submitter.FirstName $Submitter.Surname</span><% if Created %> - <% end_if %><% end_if %> 
			<% if Created %><span class="dateposted">$Created.Long</span><% end_if %>
		</div>
		<div class="summary">$Content</div>
	</div>
	<% end_control %>
</div>
<% end_if %>
