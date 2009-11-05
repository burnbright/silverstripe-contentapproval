$Content
$Form
<% if Articles %>
<div id="$ClassName">
	<% control Articles %>
	<div class="entry">
		<h2><% if Link %><a href="$Link">$Title</a><% else %>$Title<% end_if %></h2>
		<div class="summary">$Content.Summary</div>
		<p class="submitdetails">
			<% if Submitter %>Submitted by: <span class="author">$Submitter.FirstName $Submitter.Surname</span><% if Created %> - <% end_if %><% end_if %> 
			<% if Created %><span class="dateposted">$Created.Long</span><% end_if %>
		</p>
	</div>
	<% end_control %>
</div>
<% end_if %>
