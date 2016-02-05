<?php
/*
* Extension homepage is at  http://www.mediawiki.org/wiki/Extension:Add_HTML_Meta_and_Title
*
 */   

/** 
 * Protect against register_globals vulnerabilities. 
 * This line must be present before any global variable is referenced. 
 */
if (!defined('MEDIAWIKI')) {
   echo <<<EOT
To install my extension, put the following line in LocalSettings.php:
require_once "\$IP/extensions/Add_HTML_Meta_and_Title/Add_HTML_Meta_and_Title.php" ); 
EOT;
   exit( 1 );
}

# Credits
$wgExtensionCredits['parserhook'][] = array(
	'path' 			=> __FILE__,
    'name'			=> 'Add_HTML_Meta_and_Title',
    'author'		=> 	array('Vladimir Radulovski - vladradulov&lt;at&gt;gmail.com',
						'Jim Wilson - wilson.jim.r&lt;at&gt;gmail.com',
						'Dennis Roczek - dennisroczek&lt;at&gt;gmail.com'),
    'url'			=> 'http://www.mediawiki.org/wiki/Extension:Add_HTML_Meta_and_Title',
    'descriptionmsg'	=> 'addhtmlmetaandtitle-desc',
    'version'		=> '0.6',
	'license-name'	=> 'MIT'
);

# Add Extension Function
$wgExtensionFunctions[] = 'setupSEOParserHooks';
$wgMessagesDirs['Add_HTML_Meta_and_Title'] = __DIR__ . '/i18n';

/**
 * Sets up the MetaKeywordsTag Parser hook and system messages
 */
function setupSEOParserHooks() {
	global $wgParser, $wgMessageCache;
	# meta if empty
	$wgParser->setHook( 'seo', 'renderSEO' );
}

function paramEncode( $param_text, &$parser, $frame ){
  $expanded_param =$parser->recursiveTagParse( $param_text, $frame );
  return base64_encode( $expanded_param );
}

/**
 * Renders the <keywords> tag.
 * @param String $text Incomming text - should always be null or empty (passed by value).
 * @param Array $params Attributes specified for tag - must contain 'content' (passed by value).
 * @param Parser $parser Reference to currently running parser (passed by reference).
 * @return String Always empty.
 */
function renderSEO( $text, $params = array(), $parser, $frame ) {
    # Short-circuit with error message if content is not specified.
	$emt="";
    if  ( (isset($params['title'])) 					||
		  (isset($params['metak'])) 					||
		  (isset($params['metad'])) 					||
		  (isset($params['metakeywords'])) 				||
		  (isset($params['metadescription']))			||
		  (isset($params['google-site-verification']))
		)
	{
		    if  (isset($params['title']))           {$emt .= "<!-- ADDTITLE ".paramEncode($params['title'], $parser, $frame)." -->";}
			if  (isset($params['metak']))           {$emt .= "<!-- ADDMETAK ".paramEncode($params['metak'], $parser, $frame)." -->";}
			if  (isset($params['metakeywords']))    {$emt .= "<!-- ADDMETAK ".paramEncode($params['metakeywords'], $parser, $frame)." -->";}
			if  (isset($params['metad']))           {$emt .= "<!-- ADDMETAD ".paramEncode($params['metad'], $parser, $frame)." -->";}
			if  (isset($params['metadescription'])) {$emt .= "<!-- ADDMETAD ".paramEncode($params['metadescription'], $parser, $frame)." -->";}
			if  (isset($params['google-site-verification'])) {$emt .= "<!-- ADDMETAGOOGLESITEVERIFICATION ".paramEncode($params['google-site-verification'], $parser, $frame)." -->";}
     
			return $emt; //$encoded_metas_and_title;
	 
    }
    else
	{return
            '<div class="errorbox">'.
            wfMessage('addhtmlmetaandtitle-empty-attr')->inContentLanguage()->text().
            '</div>';
	}

}

# Attach post-parser hook to extract metadata and alter headers
$wgHooks['OutputPageBeforeHTML'][] = 'insertMeta';
$wgHooks['BeforePageDisplay'][] = 'insertTitle';

/**
 * Adds the <meta> keywords to document head.
 * Usage: $wgHooks['OutputPageBeforeHTML'][] = 'insertMetaKeywords';
 * @param OutputPage $out Handle to an OutputPage object - presumably $wgOut (passed by reference).
 * @param String $text Output text.
 * @return Boolean Always true to allow other extensions to continue processing.
 */
function insertTitle($out){
     # Extract meta keywords
	if (preg_match_all(
        '/<!-- ADDTITLE ([0-9a-zA-Z\\+\\/]+=*) -->/m', 
        $out->mBodytext, 
        $matches)===false
    ) return true;
    $data = $matches[1];
    # Merge keyword data into OutputPage as meta tags
    foreach ($data as $item) {
        $content = @base64_decode($item);
	$content = htmlspecialchars($content, ENT_QUOTES);
        if ($content) {
		// Set page title
		global $wgSitename;
		$new_title = "$content - $wgSitename";
		$out->setHTMLTitle( $new_title );
	}
    }
	return true;
}

function insertMeta($out, $text){
    # Extract meta keywords
    if (preg_match_all(
        '/<!-- ADDMETAK ([0-9a-zA-Z\\+\\/]+=*) -->/m', 
        $text, 
        $matches)===false
    ) return true;
    $data = $matches[1];    
    # Merge keyword data into OutputPage as meta tags
    foreach ($data AS $item) {
        $content = @base64_decode($item);
		$content = htmlspecialchars($content, ENT_QUOTES);
		
        if ($content) {
			$out->addMeta( 'keywords', $content );
		}
		
    }

    # Extract meta description
    if (preg_match_all(
        '/<!-- ADDMETAD ([0-9a-zA-Z\\+\\/]+=*) -->/m', 
        $text, 
        $matches)===false
    ) return true;
    $data = $matches[1];
    # Merge description data into OutputPage as meta tags
    foreach ($data AS $item) {
        $content = @base64_decode($item);
		$content = htmlspecialchars($content, ENT_QUOTES);
		
        if ($content) {
			$out->addMeta( 'description', $content );
		}
		
    }
    # Extract google-site-verification
    if (preg_match_all(
        '/<!-- ADDMETAGOOGLESITEVERIFICATION ([0-9a-zA-Z\\+\\/]+=*) -->/m', 
        $text, 
        $matches)===false
    ) return true;
    $data = $matches[1];
    
    # Merge google-site-verification data into OutputPage as meta tags
    foreach ($data AS $item) {
        $content = @base64_decode($item);
		$content = htmlspecialchars($content, ENT_QUOTES);

        if ($content) {
		$out->addMeta( 'google-site-verification', $content );
		}
    }
    return true;
}
?>
