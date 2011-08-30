<h1 class="pagetitle">$Title</h1>
<% if Content %>
<div id="$ClassName" class="moderatedarticle">
	
	<div class="entry">
		<div class="submitdetails">
			<% if Submitter %>
				Submitted by: <span class="author">$Submitter.FirstName $Submitter.Surname</span><% if Created %> - <% end_if %>
				<% end_if %> 
			<% if Created %><span class="dateposted">$Created.Long</span><% end_if %>
		</div>
		<div class="content">$Content</div>
		<% if Attachments %>
			<ul>
			<% control Attachments %>
				<li class="attachment"><a href="$Link" title="$FileType">$Title</a> <span class="details">[$Extension: $Size]</span></li>			
			<% end_control %>
			</ul>
		<% end_if %>
		<% if ArticleHolder %><a href="$ArticleHolder.Link">All <span class="pluralname"><% if ArticleHolder.ItemPlural %>$ArticleHolder.ItemPlural<% else %>items<% end_if %></span></a><% end_if %>
	</div>
	
</div>
<% end_if %>