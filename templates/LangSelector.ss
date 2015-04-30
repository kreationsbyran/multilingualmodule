<% if LangSelector(true).Count > 1 %>
<div id="lang">
	<ul class="language-selector">
		<% loop LangSelector(true) %>
			<li><a href="$Link" title="$LangNice" class="flag-$LangCode $Selected"><img src="$ImgURL" height="15" alt="$LangNice" /><span>$LangNice</span></a></li>
		<% end_loop %>
	</ul>				  
</div>
<% end_if %>