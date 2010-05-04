<p>Hello Moderator,</p>
<p>Please review the following content that was submitted on the site ($Holder.Title):</p>
<h2>{$Title}:</h2>
<div style="border:1px solid #777777;padding:10px;margin:0 15px;">
	$Content
</div>

<p>Attachments: </p>
<% if Attachments %><% control Attachments %>
<p><strong><a href="$Link">$Title</a></strong></p>
<% end_control %><% end_if %>

<% if Email %>
<p><strong>Submitted by <% if Submitter %>$Submitter.Name<% end_if %>(<a href="mailto:$Email">$Email</a>)</strong></p>
<% end_if %>


<% if PreviewLink %>
<p>
	Preview the article on the site: <a href="$PreviewLink">$PreviewLink</a>
</p>
<% end_if %>
<% if ApproveLink %>
<p>
	You can approve the article immediately by clicking this link: <a href="$ApproveLink">$ApproveLink</a>. You might need to log in.
</p>
<% end_if %>

thanks,<br/>
webmaster