	function shareArticleToYourls(id) {
	try {
		var query = "?op=pluginhandler&plugin=yourls&method=getInfo&id=" + param_escape(id);

		var d = new Date();
	        var ts = d.getTime();


		new Ajax.Request("backend.php",	{
			parameters: query,
			onSuccess: function(transport) {
				var ti = JSON.parse(transport.responseText);
				var share_url_query = "?signature=" + ti.yourlsapi + "&action=shorturl&format=simple&url=" + param_escape(ti.link) + "&title=" + param_escape(ti.title);
				dialog = new dijit.Dialog({
					id: "YourlsShortLinkDlg"+ts,
					title: __("Youlrs Shortened URL"),
					style: "width: 200px",
					//content: "<iframe src='"+ti.yourlsurl + "/yourls-api.php" + share_url_query+"' frameborder='0' allowtransparency='true' scrolling='no' height='40px' width='190px'></iframe>",
					content: '<p align=center>' + ti.shorturl + '<br/><a targer="_blank" href="http://twitter.com/share?_=' + ts + '&text=' + param_escape(ti.title) +
                                        '&url=' + param_escape(ti.shorturl) + '"><img src="plugins/yourls/tweetshare.png"/></a><br/><a target="_blank" href="http://www.facebook.com/sharer.php?u=' + param_escape(ti.shorturl) + '"><img src="plugins/yourls/fbshare.png" border=0/></a><br/><a target="_blank" href="http://hootsuite.com/hootlet/load?address='+param_escape(ti.shorturl)+'&title='+param_escape(ti.title)+'"><img src="http://s1.static.hootsuite.com/33797-5102/images/static/socialshare/share-btn-med.png"/></a></p>',
					});
				dialog.show();
			} });

	} catch (e) {
		exception_error("yourlsArticle", e);
	}
	}

