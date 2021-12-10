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

// Controle la presence de la lib safehtml et cree la fonction
// de transformation du texte qui l'exploite
// https://code.spip.net/@inc_safehtml_dist
function inc_safehtml_dist($t) {
	static $process, $test;

	if (!$test) {
		$process = false;
		if ($f = find_in_path('lib/safehtml/classes')) {
			define('XML_HTMLSAX3', $f . '/');
			require_once XML_HTMLSAX3 . 'safehtml.php';
			$process = new safehtml();
			$process->deleteTags[] = 'param'; // sinon bug Firefox
		}
		if ($process) {
			$test = 1;
		} # ok
		else {
			$test = -1;
		} # se rabattre sur une fonction de securite basique
	}

	if ($test > 0) {
		# reset ($process->clear() ne vide que _xhtml...),
		# on doit pouvoir programmer ca plus propremement
		$process->_counter = [];
		$process->_stack = [];
		$process->_dcCounter = [];
		$process->_dcStack = [];
		$process->_listScope = 0;
		$process->_liStack = [];
#		$process->parse(''); # cas particulier ?
		$process->clear();
		$t = $process->parse($t);
	} else {
		$t = entites_html($t);
	} // tres laid, en cas d'erreur

	// supprimer un <li></li> provenant d'un <li> ouvrant seul+safehtml
	// cf https://core.spip.net/issues/2201
	$t = str_replace('<li></li>', '', $t);

	return $t;
}
