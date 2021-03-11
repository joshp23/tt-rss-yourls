<?php
class Yourls extends Plugin {
	private $host;

	function init($host) {
		$this->host = $host;
		$host->add_hook($host::HOOK_ARTICLE_BUTTON, $this);
		$host->add_hook($host::HOOK_PREFS_TAB, $this);
		$host->add_hook($host::HOOK_HOTKEY_MAP, $this);
		$host->add_hook($host::HOOK_HOTKEY_INFO, $this);
	}

	function about() {
		return array(
			"2.0.0",
			"Shorten article Link using Yourls",
			"Beun and acaranta, and joshu@unfettered.net");
	}

	function hook_hotkey_map($hotkeys) {
		$hotkeys['s y'] = 'send_to_yourls';

		return $hotkeys;
	}

	function hook_hotkey_info($hotkeys) {
		$hotkeys[__("Article")]["send_to_yourls"] = __("Shorten article link with YOURLS");

		return $hotkeys;
	}

	function save() {
		$yourls_url = db_escape_string($_POST["yourls_url"]);
		$this->host->set($this, "Yourls_URL", $yourls_url);
		echo "Value Yourls URL set to $yourls_url<br/>";
		$yourls_api = db_escape_string($_POST["yourls_api"]);
		$this->host->set($this, "Yourls_API", $yourls_api);
		echo "Value Yourls API set to $yourls_api";
	}

    function api_version() {
            return 2;
    }

	function get_js() {
		return file_get_contents(dirname(__FILE__) . "/yourls.js");
	}

	function hook_article_button($line) {
		$article_id = $line["id"];

		$rv = "<img id=\"yourlsImgId\" src=\"plugins.local/yourls/yourls.png\"
			class='tagsPic' style=\"cursor : pointer\"
			onclick=\"shareArticleToYourls($article_id)\"
			title='".__('Send article to Yourls')."'>";

		return $rv;
	}

	function getInfo() {
		$id = $_REQUEST['id'];
		$sth = $this->pdo->prepare("SELECT title, link 
									FROM ttrss_entries, ttrss_user_entries 
									WHERE id = ? AND ref_id = id  AND owner_uid = ?");
		$sth->execute([$id, $_SESSION['uid']]);
		if ($row = $sth->fetch()) {
		
			$title = truncate_string(strip_tags($row['title']), 100, '...');
			$article_link = $row['link'];
			$yourls_url = $this->host->get($this, "Yourls_URL");
			$yourls_url = $yourls_url . "/yourls-api.php";
			$yourls_api = $this->host->get($this, "Yourls_API");
			$postfields = array(
				'signature' => $yourls_api,
				'action'    => 'shorturl',
				'format'	=> 'simple',
				'url'		=> $article_link,
				'title' 	=> $title,
			);
			
		    $curl_yourls = curl_init();
		    
				curl_setopt($curl_yourls, CURLOPT_URL, $yourls_url);
				curl_setopt($curl_yourls, CURLOPT_HEADER, 0);
				curl_setopt($curl_yourls, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl_yourls, CURLOPT_TIMEOUT, 30);
				curl_setopt($curl_yourls, CURLOPT_POST, 1);
				curl_setopt($curl_yourls, CURLOPT_POSTFIELDS,$postfields);
					
			if (!ini_get('safe_mode') && !ini_get('open_basedir')) {
				curl_setopt($curl_yourls, CURLOPT_FOLLOWLOCATION, true);
			}
			$short_url = curl_exec($curl_yourls) ;
			$status = curl_getinfo($curl_yourls, CURLINFO_HTTP_CODE);
				curl_close($curl_yourls);

			print json_encode(array("status" => $status, "title" => $title, "shorturl" => $short_url));
		} else {
			print json_encode(array( "status" => "Database fail" ));
		}
	}

	function hook_prefs_tab($args) {
		if ($args != "prefPrefs") return;

		print "<div dojoType=\"dijit.layout.AccordionPane\" 
					title=\" <i class='material-icons'>link</i> ".__("Yourls")."\">";
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
