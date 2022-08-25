<?php

/***************************************************************************\
 *  SPIP, Système de publication pour l'internet                           *
 *                                                                         *
 *  Copyright © avec tendresse depuis 2001                                 *
 *  Arnaud Martin, Antoine Pitrou, Philippe Rivière, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribué sous licence GNU/GPL.     *
 *  Pour plus de détails voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Sanitization du HTML via la librairie Purifier
 * @param string $t
 * @return false|string
 */
function inc_safehtml_dist(string $t): string {
	static $purifier;

	if (!isset($purifier)) {
		require_spip('lib/htmlpurifier/HTMLPurifier.standalone');
		require_spip('inc/HTMLPurifier.extended');
		require_spip('inc/HTMLPurifier_HTML5.loader');

		$config = HTMLPurifier_HTML5Config::createDefault();

		$config->set('HTML.Forms', true);

		if ($iniFile = find_in_path('safehtml/htmlpurifier.ini')) {
			$config->loadIni($iniFile);
		} else {

			$config->set('Attr.EnableID', true);
			$config->set('HTML.TidyLevel', 'none');
			$config->set('Cache.SerializerPath', rtrim(realpath(sous_repertoire(_DIR_CACHE, 'html_purifier')), '/'));

			// trop severe pour une utilisation generique ?
			#$config->set('HTML.SafeIframe', true);
			#$config->set('URI.SafeIframeRegexp', '%^http[s]?://[a-z0-9\.]*' . $_SERVER['HTTP_HOST'] . '/%iS');

			$config->set('Attr.AllowedFrameTargets', ['_blank']);
			$config->set('Attr.AllowedRel', 'nofollow,print,external,bookmark');

			$config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'ftp' => true, 'nntp' => true, 'news' => true, 'tel' => true, 'tcp' => true, 'udp' => true, 'ssh' => true,]);
		}

		$purifier = new HTMLPurifier($config);
	}

	// HTML Purifier prefere l'utf-8
	$charset = (empty($GLOBALS['meta']['charset']) ? _DEFAULT_CHARSET : $GLOBALS['meta']['charset']);
	if ($charset === 'utf-8') {
		$t = $purifier->purify($t);
	} else {
		$t = unicode2charset($purifier->purify(charset2unicode($t)));
	}

	return $t;
}
