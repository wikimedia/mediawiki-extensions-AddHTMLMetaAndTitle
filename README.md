MW-MetaTitle
============

An unofficial fork of [Add HTML Meta and Title](http://www.mediawiki.org/wiki/Extension:Add_HTML_Meta_and_Title) extension for MediaWiki.

Usage
=====
Copy Add_HTML_Meta_and_Title.php into 'extensions' directory.
Add following into your LocalSettings.php

```php
$wgSitename = "My Wiki";  
$wgAllowDisplayTitle = true;  
$wgRestrictDisplayTitle = false;  
require_once "$IP/extensions/Add_HTML_Meta_and_Title/Add_HTML_Meta_and_Title.php';
```

Useful hint
===========
Create a template called Seo, insert following wikitext.


    {{DISPLAYTITLE:{{{pagetitle}}} }}
    <seo title={{{pagetitle}}} metakeywords="{{{meta_keywords}}}"/>
Now you can use more Wiki-flavoured style like following.


    {{seo
    |pagetitle=My Main Page
    |meta_keywords=super,long,increase,inches}}

Changelog
=========
08.06.2012 - version 0.5   - MediaWiki v1.19 support
17.05.2015 - version 0.5.2 - using new i18n system
