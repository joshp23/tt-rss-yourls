<?php
class Yourls extends Plugin {
	private $host;
	private $curl_yourls;

	function init($host) {
		$this->host = $host;
		$this->curl_yourls = $curl_yourls;
                $this->curl_yourls = curl_init() ;
                curl_setopt($this->curl_yourls, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($this->curl_yourls, CURLOPT_FOLLOWLOCATION, true);


		$host->add_hook($host::HOOK_ARTICLE_BUTTON, $this);
		$host->add_hook($host::HOOK_PREFS_TAB, $this);
	}

	function about() {
		return array(1.0,
				"Shorten article Link using Yourls",
				"Beun and acaranta");
	}
	function save() {
		$yourls_url = db_escape_string($_POST["yourls_url"]);
		$this->host->set($this, "Yourls_URL", $yourls_url);
		echo "Value Yourls URL set to $yourls_url<br/>";
		$yourls_api = db_escape_string($_POST["yourls_api"]);
		$this->host->set($this, "Yourls_API", $yourls_api);
		echo "Value Yourls API set to $yourls_api";
	}
	function get_js() {
		return file_get_contents(dirname(__FILE__) . "/yourls.js");
	}

	function hook_article_button($line) {
		$article_id = $line["id"];

		$rv = "<img src=\"plugins/yourls/yourls.png\"
			class='tagsPic' style=\"cursor : pointer\"
			onclick=\"shareArticleToYourls($article_id)\"
			title='".__('Send article to Yourls')."'>";

		return $rv;
	}

	function getInfoOld() {
		$id = db_escape_string($_REQUEST['id']);

		$result = db_query("SELECT title, link
				FROM ttrss_entries, ttrss_user_entries
				WHERE id = '$id' AND ref_id = id AND owner_uid = " .$_SESSION['uid']);

		if (db_num_rows($result) != 0) {
			$title = truncate_string(strip_tags(db_fetch_result($result, 0, 'title')),
					100, '...');
			$article_link = db_fetch_result($result, 0, 'link');
		}
	
		$yourls_url = $this->host->get($this, "Yourls_URL");
		$yourls_api = $this->host->get($this, "Yourls_API");

		print json_encode(array("title" => $title, "link" => $article_link,
					"id" => $id, "yourlsurl" => $yourls_url, "yourlsapi" => $yourls_api));		
	}

	function getInfo() {
		$id = db_escape_string($_REQUEST['id']);

		$result = db_query("SELECT title, link
				FROM ttrss_entries, ttrss_user_entries
				WHERE id = '$id' AND ref_id = id AND owner_uid = " .$_SESSION['uid']);

		if (db_num_rows($result) != 0) {
			$title = truncate_string(strip_tags(db_fetch_result($result, 0, 'title')),
					100, '...');
			$article_link = db_fetch_result($result, 0, 'link');
		}
	
		$yourls_url = $this->host->get($this, "Yourls_URL");
		$yourls_api = $this->host->get($this, "Yourls_API");
/*		$curl_yourls = curl_init() ;
		curl_setopt($curl_yourls, CURLOPT_URL, "$yourls_url/yourls-api.php?signature=$yourls_api&action=shorturl&format=simple&url=".urlencode($article_link)."&title=".urlencode($title)) ;
		curl_setopt($curl_yourls, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl_yourls, CURLOPT_FOLLOWLOCATION, true);
		$short_url = curl_exec($curl_yourls) ;
		curl_close($curl_yourls) ;*/ 
                curl_setopt($this->curl_yourls, CURLOPT_URL, "$yourls_url/yourls-api.php?signature=$yourls_api&action=shorturl&format=simple&url=".urlencode($article_link)."&title=".urlencode($title)) ;
                $short_url = curl_exec($this->curl_yourls) ;

		print json_encode(array("title" => $title, "link" => $article_link,
					"id" => $id, "yourlsurl" => $yourls_url, "yourlsapi" => $yourls_api, "shorturl" => $short_url));		
	}

	function hook_prefs_tab($args) {
		if ($args != "prefPrefs") return;

		print "<div dojoType=\"dijit.layout.AccordionPane\" title=\"".__("Yourls")."\">";

		print "<br/>";

		$yourls_url = $this->host->get($this, "Yourls_URL");
		$yourls_api = $this->host->get($this, "Yourls_API");
		print "<form dojoType=\"dijit.form.Form\">";

		print "<script type=\"dojo/method\" event=\"onSubmit\" args=\"evt\">
			evt.preventDefault();
		if (this.validate()) {
			console.log(dojo.objectToQuery(this.getValues()));
			new Ajax.Request('backend.php', {
parameters: dojo.objectToQuery(this.getValues()),
onComplete: function(transport) {
notify_info(transport.responseText);
}
});
}
</script>";

print "<input dojoType=\"dijit.form.TextBox\" style=\"display : none\" name=\"op\" value=\"pluginhandler\">";
print "<input dojoType=\"dijit.form.TextBox\" style=\"display : none\" name=\"method\" value=\"save\">";
print "<input dojoType=\"dijit.form.TextBox\" style=\"display : none\" name=\"plugin\" value=\"yourls\">";
print "<table width=\"100%\" class=\"prefPrefsList\">";
print "<tr><td width=\"40%\">".__("Yourls base URL")."</td>";
print "<td class=\"prefValue\"><input dojoType=\"dijit.form.ValidationTextBox\" required=\"1\" name=\"yourls_url\" regExp='^(http|https)://.*' value=\"$yourls_url\"></td></tr>";
	print "<tr><td width=\"40%\">".__("Yourls API Key")."</td>";
	print "<td class=\"prefValue\"><input dojoType=\"dijit.form.ValidationTextBox\" required=\"1\" name=\"yourls_api\" value=\"$yourls_api\"></td></tr>";
	print "</table>";
	print "<p><button dojoType=\"dijit.form.Button\" type=\"submit\">".__("Save")."</button>";

	print "</form>";

	print "</div>"; #pane

	}

}
?>
