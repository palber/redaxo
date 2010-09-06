<?php

/**
 * Page Content Modules Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$mypage = 'modules';

if ($REX['REDAXO'])
{
  // $REX['ADDON']['rxid'][$mypage] = '62';
  $REX['ADDON']['name'][$mypage] = $I18N->msg('modules');
  $REX['ADDON']['perm'][$mypage] = 'isAdmin';
  $REX['ADDON']['version'][$mypage] = "1.3";
  $REX['ADDON']['author'][$mypage] = "Markus Staab";
  $REX['ADDON']['supportpage'][$mypage] = 'forum.redaxo.de';
  $REX['ADDON']['navigation'][$mypage] = array('block'=>'system');

  $I18N->appendFile(dirname(__FILE__) .'/lang');
}


if($REX['REDAXO'])
{
	rex_register_extension('PAGE_CHECKED', 'rex_subpage_modules');
	
	function rex_subpage_modules($params)
	{
		global $REX,$I18N;
	
	    $modules = new rex_be_page($I18N->msg('modules'), array('page'=>'modules', 'subpage' => ''));
	    $modules->setRequiredPermissions('isAdmin');
	    $modules->setHref('index.php?page=modules&subpage=');
	
	    $actions = new rex_be_page($I18N->msg('actions'), array('page'=>'modules', 'subpage' => 'actions'));
	    $actions->setRequiredPermissions('isAdmin');
	    $actions->setHref('index.php?page=modules&subpage=actions');
	
		$REX['PAGES']['modules']->page->addSubPage($modules);
		$REX['PAGES']['modules']->page->addSubPage($actions);
	
	}

}
