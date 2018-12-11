function shareArticleToYourls(id) {
	try {
		var query = "?op=pluginhandler&plugin=yourls&method=getInfo&id=" + encodeURIComponent(id);
		var d = new Date();
	    var ts = d.getTime();

		new Ajax.Request("backend.php",	{
			parameters: query,
			onSuccess: function(transport) {
				var ti = JSON.parse(transport.responseText);
				var share_url_query = "?signature=" + ti.yourlsapi + "&action=shorturl&format=simple&url=" + encodeURIComponent(ti.link) + "&title=" + encodeURIComponent(ti.title);
				dialog = new dijit.Dialog({
					id: "YourlsShortLinkDlg"+ts,
					title: __("Youlrs Shortened URL"),
					style: "width: 200px",
					content: '<p align=center>' + ti.shorturl + '<br/><a target="_blank" href="http://twitter.com/share?_=' + ts + '&text=' + encodeURIComponent(ti.title) + '&url=' + encodeURIComponent(ti.shorturl) + '"><img src="/plugins.local/yourls/tweetshare.png"/></a><br/><a target="_blank" href="http://www.facebook.com/sharer.php?u=' + encodeURIComponent(ti.shorturl) + '"><img src="/plugins.local/yourls/fbshare.png" border=0/></a></p>',
					});
				dialog.show();
			} });

	} catch (e) {
		exception_error("yourlsArticle", e);
	}
}
