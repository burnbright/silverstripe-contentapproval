<h1>$Title</h1>
$Content
$Form
<% if Articles %>
<div id="$ClassName">
	<% include Pagination %>
	<% control Articles %>
	<div class="entry">
		<h3><% if Link %><a href="$Link">$Title</a><% else %>$Title<% end_if %></h3>
		<p class="submitdetails">
			<% if Submitter %><span class="author">submitted by: $Submitter.FirstName $Submitter.Surname</span><% if Created %> - <% end_if %><% end_if %> 
			<% if Created %><span class="dateposted"><span class="day">$Created.DayOfMonth</span> <span class="year">$Created.Format(F)</span> <span class="year">$Created.Year</span></span><% end_if %>
		</p>
	</div>
	<% end_control %>
	<% include Pagination %>
</div>
<% end_if %>
