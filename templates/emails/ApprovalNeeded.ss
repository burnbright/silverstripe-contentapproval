<p>Hello Moderator,</p>
<p>Please review the following content that was submitted on the site ($Holder.Title):</p>
<h2>{$Title}:</h2>
<div style="border:1px solid #777777;padding:10px;margin:0 15px;">
	$Content
</div>
<% if Attachment %><% control Attachment %>
<p>Attachment: <strong><a href="$Link">$Title</a></strong></p>
<% end_control %><% end_if %>

<% if Submitter %><% control Submitter %>
<p><strong>Submitted by $Name (<a href="mailto:$Email">$Email</a>)</strong></p>
<% end_control %><% end_if %>
<% if PreviewLink %>
<p>
	Preview the article on the site: <a href="$PreviewLink">$PreviewLink</a>
</p>
<% end_if %>
<% if ApproveLink %>
<p>
	Or approve the article immediately by clicking this link: <a href="$ApproveLink">$ApproveLink</a>
</p>
<% end_if %>

thanks,<br/>
webmaster