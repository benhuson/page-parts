<?php



class Page_Parts_Admin {
	
	
	
	// --------------------------------------------------
	//                     Properties
	// --------------------------------------------------
	
	
	
	// Plugin
	static $plugin_folder = 'devicive';
	
	// State
	static $current_device = null;
	
	// Settings
	static $plugin_enabled                = false;
	static $desktop_redirect_url          = '';
	static $mobile_redirect_url           = '';
	
	
	
	// ---------------------------------------------------
	//                     Constructor
	// ---------------------------------------------------
	
	
	
	function Page_Parts_Admin() {
	
		// Hooks & Filters
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
		add_filter( 'http_request_args', array( $this, 'http_request_args' ), 5, 2 );
		
	}
	
	function admin_head() {
	
		echo '
			<style>
			#page_parts table img.attachment-thumbnail {
				max-width:40px;max-height:32px;
			}
			</style>
			';
		echo "
<script type=\"text/javascript\">
function sortPageParts() {
}
jQuery(function($) {
	$('#page_parts table.wp-list-table tbody').sortable( {
		accept: 'sortable',
		stop: function(event, ui) {
			var order_count = 0;
			$('#page_parts table.wp-list-table td.order input').each(function(){
				$(this).val(order_count);
				order_count++;
			});
		}
	} );
});
</script>
		";
		
	}
	
	function admin_enqueue_scripts() {
		
		wp_enqueue_script( array("jquery", "jquery-ui-core", "interface", "jquery-ui-sortable", "wp-lists") );
	
	}
	
	
	// ----------------------------------------------------------
	//                     Flash Site Methods
	// ----------------------------------------------------------



	function get_options() {
	
		$options = get_option( 'devicive_settings' );
		
		Devicive::$plugin_enabled       = $options['plugin_enabled'];
		Devicive::$desktop_redirect_url = $options['desktop_redirect_url'];
		Devicive::$mobile_redirect_url  = $options['mobile_redirect_url'];
		
	}



	function enqueue_scripts() {
		
		wp_enqueue_script( 'jquery' );
		
	}



	function handle_device_override() {
		
		if ( isset( $_REQUEST['device'] ) ) {
			$this->current_device = $_REQUEST['device'];
			setcookie( "device", $this->current_device, 0, "/", str_replace( 'http://', '', get_bloginfo( 'url' ) ) );
		} else {
			$this->current_device = $_COOKIE["device"];
		}
		
	}



	function handle_device() {
		
		$options = get_option( 'devicive_settings' );
		
		if ( !Devicive::is_mobile() && !empty( $options['desktop_redirect_url'] ) ) {
			wp_redirect( $options['desktop_redirect_url'] );
		}
		
		if ( Devicive::is_mobile() && !empty( $options['mobile_redirect_url'] ) ) {
			wp_redirect( $options['mobile_redirect_url'] );
		}
		
	}



	function is_mobile() {
		
		$options = get_option( 'devicive_settings' );
		
		// Device Info
		$mobile_browser = false;
		$redirect_url   = false;
		$user_agent     = $_SERVER['HTTP_USER_AGENT'];
		$accept         = $_SERVER['HTTP_ACCEPT'];
		$status         = $_SERVER['HTTP_ACCEPT'];
		
		// Check
		switch ( true ) {
			
			// iPad
			case ( preg_match( '/ipad/i', $user_agent ) || $this->current_device == 'ipad' ):
				$mobile_browser = $options['handle_ipad'] == 'MOBILE';
				$status = 'Apple iPad';
				if ( substr( $options['ipad_redirect_url'], 0, 4 ) == 'http' ) {
					$redirect_url = $options['ipad_redirect_url'];
				}
				break;
			
			// iPod
			case ( preg_match( '/ipod/i', $user_agent ) || preg_match( '/iphone/i', $user_agent ) || $this->current_device == 'iphone' ):
				$mobile_browser = $options['handle_ipod'] == 'MOBILE';
				$status = 'Apple';
				if ( substr( $options['ipod_redirect_url'], 0, 4 ) == 'http' ) {
					$redirect_url = $options['ipod_redirect_url'];
				}
				break;
			
			// Android
			case ( preg_match( '/android/i', $user_agent ) || $this->current_device == 'android' ):
				$mobile_browser = $options['handle_android'] == 'MOBILE';
				$status = 'Android';
				if ( substr( $options['android_redirect_url'], 0, 4 ) == 'http' ) {
					$redirect_url = $options['android_redirect_url'];
				}
				break;
			
			// Opera Mini
			case ( preg_match( '/opera mini/i', $user_agent ) || $this->current_device == 'operamini' ):
				$mobile_browser = $options['handle_operamini'] == 'MOBILE';
				$status = 'Opera';
				if ( substr( $options['operamini_redirect_url'], 0, 4 ) == 'http' ) {
					$redirect_url = $options['operamini_redirect_url'];
				}
				break;
			
			// Blackberry
			case ( preg_match( '/blackberry/i', $user_agent ) || $this->current_device == 'blackberry' ):
				$mobile_browser = $options['handle_blackberry'] == 'MOBILE';
				$status = 'Blackberry';
				if ( substr( $options['blackberry_redirect_url'], 0, 4 ) == 'http' ) {
					$redirect_url = $options['blackberry_redirect_url'];
				}
				break;
			
			// Palm
			case ( preg_match( '/(pre\/|palm os|palm|hiptop|avantgo|plucker|xiino|blazer|elaine)/i', $user_agent ) || $this->current_device == 'palm' ):
				$mobile_browser = $options['handle_palm'] == 'MOBILE';
				$status = 'Palm';
				if ( substr( $options['palm_redirect_url'], 0, 4 ) == 'http' ) {
					$redirect_url = $options['palm_redirect_url'];
				}
				break;
			
			// Windows Mobile
			case ( preg_match( '/(iris|3g_t|windows ce|opera mobi|windows ce; smartphone;|windows ce; iemobile)/i', $user_agent ) || $this->current_device == 'windowsmobile' ):
				$mobile_browser = $options['handle_windowsmobile'] == 'MOBILE';
				$status = 'Windows Smartphone';
				if ( substr( $options['windowsmobile_redirect_url'], 0, 4 ) == 'http' ) {
					$redirect_url = $options['windowsmobile_redirect_url'];
				}
				break;
			
			// Other mobile devices - the i at the end makes it case insensitive
			case ( preg_match( '/(mini 9.5|vx1000|lge |m800|e860|u940|ux840|compal|wireless| mobi|ahong|lg380|lgku|lgu900|lg210|lg47|lg920|lg840|lg370|sam-r|mg50|s55|g83|t66|vx400|mk99|d615|d763|el370|sl900|mp500|samu3|samu4|vx10|xda_|samu5|samu6|samu7|samu9|a615|b832|m881|s920|n210|s700|c-810|_h797|mob-x|sk16d|848b|mowser|s580|r800|471x|v120|rim8|c500foma:|160x|x160|480x|x640|t503|w839|i250|sprint|w398samr810|m5252|c7100|mt126|x225|s5330|s820|htil-g1|fly v71|s302|-x113|novarra|k610i|-three|8325rc|8352rc|sanyo|vx54|c888|nx250|n120|mtk |c5588|s710|t880|c5005|i;458x|p404i|s210|c5100|teleca|s940|c500|s590|foma|samsu|vx8|vx9|a1000|_mms|myx|a700|gu1100|bc831|e300|ems100|me701|me702m-three|sd588|s800|8325rc|ac831|mw200|brew |d88|htc\/|htc_touch|355x|m50|km100|d736|p-9521|telco|sl74|ktouch|m4u\/|me702|8325rc|kddi|phone|lg |sonyericsson|samsung|240x|x320|vx10|nokia|sony cmd|motorola|up.browser|up.link|mmp|symbian|smartphone|midp|wap|vodafone|o2|pocket|kindle|mobile|psp|treo)/i', $user_agent ) ):
				$mobile_browser = true;
				$status = 'Mobile matched on piped preg_match';
				break;
			
			// Possible signs of support for text/vnd.wap.wml or application/vnd.wap.xhtml+xml
			case ( ( strpos( $accept, 'text/vnd.wap.wml' ) > 0 ) || ( strpos( $accept, 'application/vnd.wap.xhtml+xml' ) > 0 ) ):
				$mobile_browser = true;
				$status = 'Mobile matched on content accept header';
				break;
			
			// WAP - the device giving us a HTTP_X_WAP_PROFILE or HTTP_PROFILE header - only mobile devices would do this
			case ( isset( $_SERVER['HTTP_X_WAP_PROFILE'] ) || isset( $_SERVER['HTTP_PROFILE'] ) ):
				$mobile_browser = true;
				$status = 'Mobile matched on profile headers being set';
				break;
			
			// Check against a list of trimmed user agents to see if we find a match
			case ( in_array( strtolower( substr( $user_agent, 0, 4 ) ), array( '1207'=>'1207','3gso'=>'3gso','4thp'=>'4thp','501i'=>'501i','502i'=>'502i','503i'=>'503i','504i'=>'504i','505i'=>'505i','506i'=>'506i','6310'=>'6310','6590'=>'6590','770s'=>'770s','802s'=>'802s','a wa'=>'a wa','acer'=>'acer','acs-'=>'acs-','airn'=>'airn','alav'=>'alav','asus'=>'asus','attw'=>'attw','au-m'=>'au-m','aur '=>'aur ','aus '=>'aus ','abac'=>'abac','acoo'=>'acoo','aiko'=>'aiko','alco'=>'alco','alca'=>'alca','amoi'=>'amoi','anex'=>'anex','anny'=>'anny','anyw'=>'anyw','aptu'=>'aptu','arch'=>'arch','argo'=>'argo','bell'=>'bell','bird'=>'bird','bw-n'=>'bw-n','bw-u'=>'bw-u','beck'=>'beck','benq'=>'benq','bilb'=>'bilb','blac'=>'blac','c55/'=>'c55/','cdm-'=>'cdm-','chtm'=>'chtm','capi'=>'capi','cond'=>'cond','craw'=>'craw','dall'=>'dall','dbte'=>'dbte','dc-s'=>'dc-s','dica'=>'dica','ds-d'=>'ds-d','ds12'=>'ds12','dait'=>'dait','devi'=>'devi','dmob'=>'dmob','doco'=>'doco','dopo'=>'dopo','el49'=>'el49','erk0'=>'erk0','esl8'=>'esl8','ez40'=>'ez40','ez60'=>'ez60','ez70'=>'ez70','ezos'=>'ezos','ezze'=>'ezze','elai'=>'elai','emul'=>'emul','eric'=>'eric','ezwa'=>'ezwa','fake'=>'fake','fly-'=>'fly-','fly_'=>'fly_','g-mo'=>'g-mo','g1 u'=>'g1 u','g560'=>'g560','gf-5'=>'gf-5','grun'=>'grun','gene'=>'gene','go.w'=>'go.w','good'=>'good','grad'=>'grad','hcit'=>'hcit','hd-m'=>'hd-m','hd-p'=>'hd-p','hd-t'=>'hd-t','hei-'=>'hei-','hp i'=>'hp i','hpip'=>'hpip','hs-c'=>'hs-c','htc '=>'htc ','htc-'=>'htc-','htca'=>'htca','htcg'=>'htcg','htcp'=>'htcp','htcs'=>'htcs','htct'=>'htct','htc_'=>'htc_','haie'=>'haie','hita'=>'hita','huaw'=>'huaw','hutc'=>'hutc','i-20'=>'i-20','i-go'=>'i-go','i-ma'=>'i-ma','i230'=>'i230','iac'=>'iac','iac-'=>'iac-','iac/'=>'iac/','ig01'=>'ig01','im1k'=>'im1k','inno'=>'inno','iris'=>'iris','jata'=>'jata','java'=>'java','kddi'=>'kddi','kgt'=>'kgt','kgt/'=>'kgt/','kpt '=>'kpt ','kwc-'=>'kwc-','klon'=>'klon','lexi'=>'lexi','lg g'=>'lg g','lg-a'=>'lg-a','lg-b'=>'lg-b','lg-c'=>'lg-c','lg-d'=>'lg-d','lg-f'=>'lg-f','lg-g'=>'lg-g','lg-k'=>'lg-k','lg-l'=>'lg-l','lg-m'=>'lg-m','lg-o'=>'lg-o','lg-p'=>'lg-p','lg-s'=>'lg-s','lg-t'=>'lg-t','lg-u'=>'lg-u','lg-w'=>'lg-w','lg/k'=>'lg/k','lg/l'=>'lg/l','lg/u'=>'lg/u','lg50'=>'lg50','lg54'=>'lg54','lge-'=>'lge-','lge/'=>'lge/','lynx'=>'lynx','leno'=>'leno','m1-w'=>'m1-w','m3ga'=>'m3ga','m50/'=>'m50/','maui'=>'maui','mc01'=>'mc01','mc21'=>'mc21','mcca'=>'mcca','medi'=>'medi','meri'=>'meri','mio8'=>'mio8','mioa'=>'mioa','mo01'=>'mo01','mo02'=>'mo02','mode'=>'mode','modo'=>'modo','mot '=>'mot ','mot-'=>'mot-','mt50'=>'mt50','mtp1'=>'mtp1','mtv '=>'mtv ','mate'=>'mate','maxo'=>'maxo','merc'=>'merc','mits'=>'mits','mobi'=>'mobi','motv'=>'motv','mozz'=>'mozz','n100'=>'n100','n101'=>'n101','n102'=>'n102','n202'=>'n202','n203'=>'n203','n300'=>'n300','n302'=>'n302','n500'=>'n500','n502'=>'n502','n505'=>'n505','n700'=>'n700','n701'=>'n701','n710'=>'n710','nec-'=>'nec-','nem-'=>'nem-','newg'=>'newg','neon'=>'neon','netf'=>'netf','noki'=>'noki','nzph'=>'nzph','o2 x'=>'o2 x','o2-x'=>'o2-x','opwv'=>'opwv','owg1'=>'owg1','opti'=>'opti','oran'=>'oran','p800'=>'p800','pand'=>'pand','pg-1'=>'pg-1','pg-2'=>'pg-2','pg-3'=>'pg-3','pg-6'=>'pg-6','pg-8'=>'pg-8','pg-c'=>'pg-c','pg13'=>'pg13','phil'=>'phil','pn-2'=>'pn-2','pt-g'=>'pt-g','palm'=>'palm','pana'=>'pana','pire'=>'pire','pock'=>'pock','pose'=>'pose','psio'=>'psio','qa-a'=>'qa-a','qc-2'=>'qc-2','qc-3'=>'qc-3','qc-5'=>'qc-5','qc-7'=>'qc-7','qc07'=>'qc07','qc12'=>'qc12','qc21'=>'qc21','qc32'=>'qc32','qc60'=>'qc60','qci-'=>'qci-','qwap'=>'qwap','qtek'=>'qtek','r380'=>'r380','r600'=>'r600','raks'=>'raks','rim9'=>'rim9','rove'=>'rove','s55/'=>'s55/','sage'=>'sage','sams'=>'sams','sc01'=>'sc01','sch-'=>'sch-','scp-'=>'scp-','sdk/'=>'sdk/','se47'=>'se47','sec-'=>'sec-','sec0'=>'sec0','sec1'=>'sec1','semc'=>'semc','sgh-'=>'sgh-','shar'=>'shar','sie-'=>'sie-','sk-0'=>'sk-0','sl45'=>'sl45','slid'=>'slid','smb3'=>'smb3','smt5'=>'smt5','sp01'=>'sp01','sph-'=>'sph-','spv '=>'spv ','spv-'=>'spv-','sy01'=>'sy01','samm'=>'samm','sany'=>'sany','sava'=>'sava','scoo'=>'scoo','send'=>'send','siem'=>'siem','smar'=>'smar','smit'=>'smit','soft'=>'soft','sony'=>'sony','t-mo'=>'t-mo','t218'=>'t218','t250'=>'t250','t600'=>'t600','t610'=>'t610','t618'=>'t618','tcl-'=>'tcl-','tdg-'=>'tdg-','telm'=>'telm','tim-'=>'tim-','ts70'=>'ts70','tsm-'=>'tsm-','tsm3'=>'tsm3','tsm5'=>'tsm5','tx-9'=>'tx-9','tagt'=>'tagt','talk'=>'talk','teli'=>'teli','topl'=>'topl','hiba'=>'hiba','up.b'=>'up.b','upg1'=>'upg1','utst'=>'utst','v400'=>'v400','v750'=>'v750','veri'=>'veri','vk-v'=>'vk-v','vk40'=>'vk40','vk50'=>'vk50','vk52'=>'vk52','vk53'=>'vk53','vm40'=>'vm40','vx98'=>'vx98','virg'=>'virg','vite'=>'vite','voda'=>'voda','vulc'=>'vulc','w3c '=>'w3c ','w3c-'=>'w3c-','wapj'=>'wapj','wapp'=>'wapp','wapu'=>'wapu','wapm'=>'wapm','wig '=>'wig ','wapi'=>'wapi','wapr'=>'wapr','wapv'=>'wapv','wapy'=>'wapy','wapa'=>'wapa','waps'=>'waps','wapt'=>'wapt','winc'=>'winc','winw'=>'winw','wonu'=>'wonu','x700'=>'x700','xda2'=>'xda2','xdag'=>'xdag','yas-'=>'yas-','your'=>'your','zte-'=>'zte-','zeto'=>'zeto','acs-'=>'acs-','alav'=>'alav','alca'=>'alca','amoi'=>'amoi','aste'=>'aste','audi'=>'audi','avan'=>'avan','benq'=>'benq','bird'=>'bird','blac'=>'blac','blaz'=>'blaz','brew'=>'brew','brvw'=>'brvw','bumb'=>'bumb','ccwa'=>'ccwa','cell'=>'cell','cldc'=>'cldc','cmd-'=>'cmd-','dang'=>'dang','doco'=>'doco','eml2'=>'eml2','eric'=>'eric','fetc'=>'fetc','hipt'=>'hipt','http'=>'http','ibro'=>'ibro','idea'=>'idea','ikom'=>'ikom','inno'=>'inno','ipaq'=>'ipaq','jbro'=>'jbro','jemu'=>'jemu','java'=>'java','jigs'=>'jigs','kddi'=>'kddi','keji'=>'keji','kyoc'=>'kyoc','kyok'=>'kyok','leno'=>'leno','lg-c'=>'lg-c','lg-d'=>'lg-d','lg-g'=>'lg-g','lge-'=>'lge-','libw'=>'libw','m-cr'=>'m-cr','maui'=>'maui','maxo'=>'maxo','midp'=>'midp','mits'=>'mits','mmef'=>'mmef','mobi'=>'mobi','mot-'=>'mot-','moto'=>'moto','mwbp'=>'mwbp','mywa'=>'mywa','nec-'=>'nec-','newt'=>'newt','nok6'=>'nok6','noki'=>'noki','o2im'=>'o2im','opwv'=>'opwv','palm'=>'palm','pana'=>'pana','pant'=>'pant','pdxg'=>'pdxg','phil'=>'phil','play'=>'play','pluc'=>'pluc','port'=>'port','prox'=>'prox','qtek'=>'qtek','qwap'=>'qwap','rozo'=>'rozo','sage'=>'sage','sama'=>'sama','sams'=>'sams','sany'=>'sany','sch-'=>'sch-','sec-'=>'sec-','send'=>'send','seri'=>'seri','sgh-'=>'sgh-','shar'=>'shar','sie-'=>'sie-','siem'=>'siem','smal'=>'smal','smar'=>'smar','sony'=>'sony','sph-'=>'sph-','symb'=>'symb','t-mo'=>'t-mo','teli'=>'teli','tim-'=>'tim-','tosh'=>'tosh','treo'=>'treo','tsm-'=>'tsm-','upg1'=>'upg1','upsi'=>'upsi','vk-v'=>'vk-v','voda'=>'voda','vx52'=>'vx52','vx53'=>'vx53','vx60'=>'vx60','vx61'=>'vx61','vx70'=>'vx70','vx80'=>'vx80','vx81'=>'vx81','vx83'=>'vx83','vx85'=>'vx85','wap-'=>'wap-','wapa'=>'wapa','wapi'=>'wapi','wapp'=>'wapp','wapr'=>'wapr','webc'=>'webc','whit'=>'whit','winw'=>'winw','wmlb'=>'wmlb','xda-'=>'xda-' ) ) ):
				$mobile_browser = true;
				$status = 'Mobile matched on in_array';
				break;
			
			// Check against a list of trimmed user agents to see if we find a match
			case (  $this->current_device == 'mobile' ):
				$mobile_browser = true;
				$status = 'Mobile matched on cookie';
				break;
			
			default:
				$mobile_browser = false;
				$status = 'Desktop / full capability browser';
				break;
			
		}
		
		return $mobile_browser;
		
	}
	
	
	
	function validate_devicive_settings( $input ) {
	
		$input['plugin_enabled']             = $input['plugin_enabled'] == 1 ? true : false;
		$input['desktop_redirect_url']       = esc_url_raw( $input['desktop_redirect_url'], array( 'http' ) );
		$input['mobile_redirect_url']        = esc_url_raw( $input['mobile_redirect_url'], array( 'http' ) );
		
		$input['handle_iphone']              = $input['handle_iphone'];
		$input['iphone_redirect_url']        = esc_url_raw( $input['iphone_redirect_url'], array( 'http' ) );
		$input['handle_ipad']                = $input['handle_ipad'];
		$input['ipad_redirect_url']          = esc_url_raw( $input['ipad_redirect_url'], array( 'http' ) );
		$input['handle_android']             = $input['handle_android'];
		$input['android_redirect_url']       = esc_url_raw( $input['android_redirect_url'], array( 'http' ) );
		$input['handle_operamini']           = $input['handle_operamini'];
		$input['operamini_redirect_url']     = esc_url_raw( $input['operamini_redirect_url'], array( 'http' ) );
		$input['handle_blackberry']          = $input['handle_blackberry'];
		$input['blackberry_redirect_url']    = esc_url_raw( $input['blackberry_redirect_url'], array( 'http' ) );
		$input['handle_palm']                = $input['handle_palm'];
		$input['palm_redirect_url']          = esc_url_raw( $input['palm_redirect_url'], array( 'http' ) );
		$input['handle_windowsmobile']       = $input['handle_windowsmobile'];
		$input['windowsmobile_redirect_url'] = esc_url_raw( $input['windowsmobile_redirect_url'], array( 'http' ) );
		
		return $input;
		
	}
	
	
	
	function enabled() {
		
		return true;
		
	}
	
	
	
	function tools_submenu_page() {
		
		$options = get_option( 'devicive_settings' );
		
		$plugin_enabled                       = $options['plugin_enabled'] == 1 ? ' checked="checked"' : '';
		$handle_iphone_mobile_checked         = $options['handle_iphone'] == 'MOBILE' ? ' checked="checked"' : '';
		$handle_iphone_browser_checked        = $options['handle_iphone'] == 'BROWSER' ? ' checked="checked"' : '';
		$handle_ipad_mobile_checked           = $options['handle_ipad'] == 'MOBILE' ? ' checked="checked"' : '';
		$handle_ipad_browser_checked          = $options['handle_ipad'] == 'BROWSER' ? ' checked="checked"' : '';
		$handle_android_mobile_checked        = $options['handle_android'] == 'MOBILE' ? ' checked="checked"' : '';
		$handle_android_browser_checked       = $options['handle_android'] == 'BROWSER' ? ' checked="checked"' : '';
		$handle_operamini_mobile_checked      = $options['handle_operamini'] == 'MOBILE' ? ' checked="checked"' : '';
		$handle_operamini_browser_checked     = $options['handle_operamini'] == 'BROWSER' ? ' checked="checked"' : '';
		$handle_blackberry_mobile_checked     = $options['handle_blackberry'] == 'MOBILE' ? ' checked="checked"' : '';
		$handle_blackberry_browser_checked    = $options['handle_blackberry'] == 'BROWSER' ? ' checked="checked"' : '';
		$handle_palm_mobile_checked           = $options['handle_palm'] == 'MOBILE' ? ' checked="checked"' : '';
		$handle_palm_browser_checked          = $options['handle_palm'] == 'BROWSER' ? ' checked="checked"' : '';
		$handle_windowsmobile_mobile_checked  = $options['handle_windowsmobile'] == 'MOBILE' ? ' checked="checked"' : '';
		$handle_windowsmobile_browser_checked = $options['handle_windowsmobile'] == 'BROWSER' ? ' checked="checked"' : '';
		
		echo '
<div class="wrap">
	<div id="icon-themes" class="icon32"><br /></div>
	<h2>Devicive</h2>';
		if ( $_GET['updated'] == 'true' ) {
			echo '<div id="message" class="updated fade"><p><strong>Settings saved.</strong></p></div>';
		}
		echo '
	<form method="post" action="options.php">';
		settings_fields( 'DeviciveSettings' );
		echo '
		
		<h3>Devicive Settings</h3>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">Enable Devicive</th>
					<td>
						<fieldset>
							<label for="plugin_enabled"><input name="devicive_settings[plugin_enabled]" type="checkbox" id="plugin_enabled" value="1"' . $plugin_enabled . '> Check this to activate device detection etc.</label>
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>
		
		<h3>Device Handling</h3>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">Desktop Redirect URL</th>
					<td>
						<fieldset>
							<label for="desktop_redirect_url"><input name="devicive_settings[desktop_redirect_url]" type="text" id="desktop_redirect_url" value="' . Devicive::$desktop_redirect_url . '" class="regular-text"></label><br />
							<small>Leaving this blank will not perform any redirection when no mobile device is detected.</small>
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Mobile Redirect URL</th>
					<td>
						<fieldset>
							<label for="mobile_redirect_url"><input name="devicive_settings[mobile_redirect_url]" type="text" id="mobile_redirect_url" value="' . Devicive::$mobile_redirect_url . '" class="regular-text"></label><br />
							<small>Leaving this blank will not perform any redirection when a mobile device is detected.</small>
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">iPhone</th>
					<td>
						<fieldset>
							<label for="handle_iphone_mobile"><input name="devicive_settings[handle_iphone]" type="radio" id="handle_iphone_mobile" value="MOBILE"' . $handle_iphone_mobile_checked . '> Treat iPhones as mobiles</label><br />
							<label for="handle_iphone_browser"><input name="devicive_settings[handle_iphone]" type="radio" id="handle_iphone_browser" value="BROWSER"' . $handle_iphone_browser_checked . '> Treat iPhones like full browsers</label><br />
							<label for="iphone_redirect_url">Redirect iPhones to <input name="devicive_settings[iphone_redirect_url]" type="text" id="iphone_redirect_url" value="' . $options['iphone_redirect_url'] . '" class="regular-text"></label>
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">iPad</th>
					<td>
						<fieldset>
							<label for="handle_ipad_mobile"><input name="devicive_settings[handle_ipad]" type="radio" id="handle_ipad_mobile" value="MOBILE"' . $handle_ipad_mobile_checked . '> Treat iPads as mobiles</label><br />
							<label for="handle_ipad_browser"><input name="devicive_settings[handle_ipad]" type="radio" id="handle_ipad_browser" value="BROWSER"' . $handle_ipad_browser_checked . '> Treat iPads like full browsers</label><br />
							<label for="ipad_redirect_url">Redirect iPads to <input name="devicive_settings[ipad_redirect_url]" type="text" id="ipad_redirect_url" value="' . $options['ipad_redirect_url'] . '" class="regular-text"></label>
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Android</th>
					<td>
						<fieldset>
							<label for="handle_android_mobile"><input name="devicive_settings[handle_android]" type="radio" id="handle_android_mobile" value="MOBILE"' . $handle_android_mobile_checked . '> Treat Androids as mobiles</label><br />
							<label for="handle_android_browser"><input name="devicive_settings[handle_android]" type="radio" id="handle_android_browser" value="BROWSER"' . $handle_android_browser_checked . '> Treat Androids like full browsers</label><br />
							<label for="android_redirect_url">Redirect Androids to <input name="devicive_settings[android_redirect_url]" type="text" id="android_redirect_url" value="' . $options['android_redirect_url'] . '" class="regular-text"></label>
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Opera Mini</th>
					<td>
						<fieldset>
							<label for="handle_operamini_mobile"><input name="devicive_settings[handle_operamini]" type="radio" id="handle_operamini_mobile" value="MOBILE"' . $handle_operamini_mobile_checked . '> Treat Opera Minis as mobiles</label><br />
							<label for="handle_operamini_browser"><input name="devicive_settings[handle_operamini]" type="radio" id="handle_operamini_browser" value="BROWSER"' . $handle_operamini_browser_checked . '> Treat Opera Minis like full browsers</label><br />
							<label for="operamini_redirect_url">Redirect Opera Minis to <input name="devicive_settings[operamini_redirect_url]" type="text" id="operamini_redirect_url" value="' . $options['operamini_redirect_url'] . '" class="regular-text"></label>
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Blackberry</th>
					<td>
						<fieldset>
							<label for="handle_blackberry_mobile"><input name="devicive_settings[handle_blackberry]" type="radio" id="handle_blackberry_mobile" value="MOBILE"' . $handle_blackberry_mobile_checked . '> Treat Blackberrys as mobiles</label><br />
							<label for="handle_blackberry_browser"><input name="devicive_settings[handle_blackberry]" type="radio" id="handle_blackberry_browser" value="BROWSER"' . $handle_blackberry_browser_checked . '> Treat Blackberrys like full browsers</label><br />
							<label for="blackberry_redirect_url">Redirect Blackberrys to <input name="devicive_settings[blackberry_redirect_url]" type="text" id="blackberry_redirect_url" value="' . $options['blackberry_redirect_url'] . '" class="regular-text"></label>
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Palm OS</th>
					<td>
						<fieldset>
							<label for="handle_palm_mobile"><input name="devicive_settings[handle_palm]" type="radio" id="handle_palm_mobile" value="MOBILE"' . $handle_palm_mobile_checked . '> Treat Palm OS as mobiles</label><br />
							<label for="handle_palm_browser"><input name="devicive_settings[handle_palm]" type="radio" id="handle_palm_browser" value="BROWSER"' . $handle_palm_browser_checked . '> Treat Palm OS like full browsers</label><br />
							<label for="palm_redirect_url">Redirect Palm OS to <input name="devicive_settings[palm_redirect_url]" type="text" id="palm_redirect_url" value="' . $options['palm_redirect_url'] . '" class="regular-text"></label>
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Windows Mobiles</th>
					<td>
						<fieldset>
							<label for="handle_windowsmobile_mobile"><input name="devicive_settings[handle_windowsmobile]" type="radio" id="handle_windowsmobile_mobile" value="MOBILE"' . $handle_windowsmobile_mobile_checked . '> Treat Windows Mobiles as mobiles</label><br />
							<label for="handle_windowsmobile_browser"><input name="devicive_settings[handle_windowsmobile]" type="radio" id="handle_windowsmobile_browser" value="BROWSER"' . $handle_windowsmobile_browser_checked . '> Treat Windows Mobiles like full browsers</label><br />
							<label for="windowsmobile_redirect_url">Redirect Windows Mobiles to <input name="devicive_settings[windowsmobile_redirect_url]" type="text" id="windowsmobile_redirect_url" value="' . $options['windowsmobile_redirect_url'] . '" class="regular-text"></label>
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>
		
		<p class="submit"><input type="submit" name="Submit" class="button-primary" value="Save Changes"></p>
		
	</form>
</div>';

	}
	
	
	
	// -------------------------------------------------------
	//                     WordPress Hooks
	// -------------------------------------------------------
	
	
	
	function admin_menu() {
	
		if ( function_exists( 'add_meta_box' ) ) {
			add_meta_box( 'page_parts', 'Page Parts', array( 'Page_Parts_Admin', 'page_parts_meta_box' ), 'page', 'advanced' );
		}
		
	}



	function get_redirect_url() {
		
		global $wpdb, $wp_query, $post;
		
		$hash = '';
		
		return false;
		
	}
	
	
	
	function save_post( $post_id ) {
		
		global $wpdb;
		
		// Verify if this is an auto save routine. If it is our form has not been submitted,
		// so we dont want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		
		if ( empty( $_POST ) || !isset( $_POST['_ajax_nonce-order-page-parts'] ) || !wp_verify_nonce( $_POST['_ajax_nonce-order-page-parts'], 'order_page_parts' ) ) {
			return $post_id;
		}
		
		// OK, we're authenticated: we need to find and save the data
		if ( is_array( $_POST['page_parts_order'] ) ) {
			foreach ( $_POST['page_parts_order'] as $key => $val) {
				if ( absint( $key ) > 0 ) {
					$wpdb->update( $wpdb->posts, array( 'menu_order' => absint( $val ) ), array( 'ID' => absint( $key ) ), array( '%d' ), array( '%d' ) );
				}
			}
		}
		
		return $_POST;
		
	}
	
	
	
	// --------------------------------------------------
	//                     Meta Boxes
	// --------------------------------------------------

	
	function page_parts_meta_box() {
		
		global $post, $wp_query;
		
		$temp_post = clone $post;
		
		echo '<p><a href="post-new.php?post_type=page-part&parent_id=' . $post->ID . '">Add new page part</a></p>';
		
		$temp_query = new WP_Query( array(
			'post_type'      => 'page-part',
			'post_parent'    => $post->ID,
			'post_status'    => 'publish,pending,draft,auto-draft,future,private,trash',
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'posts_per_page' => -1
		) );
		
		echo '<table class="wp-list-table widefat fixed pages" cellspacing="0" style="margin:5px 0;">
			<thead>
				<tr>
					<th scope="col" id="preview" class="manage-column column-title desc" style="width:50px;"></th>
					<th scope="col" id="title" class="manage-column column-title desc" style=""><div style="padding:4px 7px 5px 8px; border-bottom: none;">Title</div></th>
					<th scope="col" id="order" class="manage-column column-author desc" style=""><div style="padding:4px 7px 5px 8px; border-bottom: none;">Order</div></th>
					<th scope="col" id="date" class="manage-column column-date asc" style=""><div style="padding:4px 7px 5px 8px; border-bottom: none;">Status</div></th>
				</tr>
			</thead>
			<tbody id="the-list">';
	
		while ( $temp_query->have_posts() ) : $temp_query->the_post();
			echo '<tr id="post-2" class="sortable alternate author-self status-publish format-default iedit" valign="top">
				<td class="column-icon media-icon" style="padding:5px 8px;border-top: 1px solid #DFDFDF;border-bottom: none;">';
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( array( 80, 60 ) );
			}
			echo '
				</td>
				<td class="post-title page-title column-title" style="padding:5px 8px;border-top: 1px solid #DFDFDF;border-bottom: none;"><strong class="row-title">';
					edit_post_link( get_the_title(), null, null, $post->ID );
					echo '</strong>
				</td>
				<td class="order column-author" style="padding:5px 8px;border-top: 1px solid #DFDFDF;border-bottom: none;">
					<input name="page_parts_order[' . $post->ID . ']" type="text" size="4" id="page_parts_order[' . $post->ID . ']" value="' . $post->menu_order . '">
				</td>
				<td class="date column-date" style="padding:5px 8px;border-top: 1px solid #DFDFDF;border-bottom: none;">' . get_post_status( $post->ID ) . '</td>
			</tr>';
		endwhile;
		
		echo '</tbody></table>';
		
		echo '<input type="submit" name="orderpageparts" id="orderpagepartssub" class="button" value="Order Page Parts">';
		wp_nonce_field( 'order_page_parts', '_ajax_nonce-order-page-parts' );
		
		wp_reset_postdata();
		rewind_posts();
		
		$post = clone $temp_post;
	
	}
	
	
	
	// -------------------------------------------------------------
	//                     Plugin Helper Methods
	// -------------------------------------------------------------
	
	
	
	function plugin_url( $file ) {
		
		global $devicive;
		return WP_PLUGIN_URL . '/' . Devicive::plugin_folder . $file;
		
	}



	function plugin_file( $file ) {
		
		global $devicive;
		return  WP_PLUGIN_DIR . '/' . Devicive::$plugin_folder . $file;
		
	}
	
	
	
	/**
	 * Don't do plugin update notifications
	 * props. Mark Jaquith
	 */
	function http_request_args( $r, $url ) {
	
		if ( 0 !== strpos( $url, 'http://api.wordpress.org/plugins/update-check' ) )
			return $r; // Not a plugin update request. Bail immediately.
		$plugins = unserialize( $r['body']['plugins'] );
		unset( $plugins->plugins[ plugin_basename( __FILE__ ) ] );
		unset( $plugins->active[ array_search( plugin_basename( __FILE__ ), $plugins->active ) ] );
		$r['body']['plugins'] = serialize( $plugins );
		return $r;
		
	}
	
	
	
}
	


?>