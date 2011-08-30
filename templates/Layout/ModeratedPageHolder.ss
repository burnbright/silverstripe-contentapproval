<h1 class="pagetitle">$Title</h1>
$Content

<% if ApprovedPages %>
<div id="$ClassName">
	<% include ApprovedPagePagination %>
	<% control ApprovedPages %>
	<div class="entry">
		<h3><% if Link %><a href="$Link">$Title</a><% else %>$Title<% end_if %></h3>
		<p class="submitdetails">
			<% if Submitter %><span class="author">submitted by: $Submitter.FirstName $Submitter.Surname</span><% if Created %> - <% end_if %><% end_if %> 
			<% if Created %><span class="dateposted"><span class="day">$Created.DayOfMonth</span> <span class="year">$Created.Format(F)</span> <span class="year">$Created.Year</span></span><% end_if %>
		</p>
	</div>
	<% end_control %>
	<% include ApprovedPagePagination %>
</div>
<% end_if %>

$Form