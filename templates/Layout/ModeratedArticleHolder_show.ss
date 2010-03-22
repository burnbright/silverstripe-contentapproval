<h1>$Title</h1>
<% if Content %>
<div id="$ClassName">
	<% control Content %>
	<div class="entry">
		<div class="submitdetails">
			<% if Submitter %>Submitted by: <span class="author">$Submitter.FirstName $Submitter.Surname</span><% if Created %> - <% end_if %><% end_if %> 
			<% if Created %><span class="dateposted">$Created.Long</span><% end_if %>
		</div>
		<div class="content">$Content</div>
		<% if Attachment %>
			<ul>
			<% control Attachment %>
				<li><a href="$Link">$Title</a></li>			
			<% end_control %>
			</ul>
		<% end_if %>
	</div>
	<% end_control %>
</div>
<% end_if %>
