function shareArticleToYourls(id) {
    try {
		Notify.progress("Saving to YOURLS …", true);
		xhr.json("backend.php",	
				{
					'op': 'pluginhandler',
					'plugin': 'yourls',
					'method': 'getInfo',
					'id': encodeURIComponent(id)
				},
				(reply) => {
					if (reply.status) {
						if (reply.status=="200") {
							var d = new Date();
							var ts = d.getTime();
							
							dialog = new dijit.Dialog({
								id: "YourlsShortLinkDlg"+ts,
								title: __("Youlrs Shortened URL"),
								style: "width: 200px",
								content: '<p align=center>' + reply.shorturl + '<br/><a target="_blank" href="https://twitter.com/share?_=' + ts + '&text=' + encodeURIComponent(reply.title) + '&url=' + encodeURIComponent(reply.shorturl) + '"><img src="/plugins.local/yourls/tweetshare.png"/></a><br/><a target="_blank" href="https://www.facebook.com/sharer.php?u=' + encodeURIComponent(reply.shorturl) + '"><img src="/plugins.local/yourls/fbshare.png" border=0/></a></p>',
							});
								
							dialog.show();
							Notify.info("Saved to YOURLS");
						} else {
							Notify.error("<strong>Error: "+reply.status+" encountered while saving to YOURLS!</strong>", true);
						}
					}  else {
						Notify.error("The YOURLS plugin needs to be configured. See the README for help", true);
					}
				});
    } catch (e) {
	Notify.error("yourlsArticle", e);
    }
}

require(['dojo/_base/kernel', 'dojo/ready'], function (dojo, ready) {
	ready(function () {
		PluginHost.register(PluginHost.HOOK_INIT_COMPLETE, () => {
			App.hotkey_actions["send_to_yourls"]  = function() {
			  if (Article.getActive()) {
				shareArticleToYourls(Article.getActive());
				return;
			  }
			};
		});
	});
});
