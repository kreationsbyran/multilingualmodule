<?php

class MultilingualGoogleSitemapExtension extends Extension {

	function updateItems($output) {
		
		$newPages = new ArrayList();
		foreach ($output as $page) {
			foreach (Multilingual::multilingual_extra_langs() as $lang) {
				
				$CurrentLangActive="LangActive_".$lang;				
				if ($page->$CurrentLangActive) {					
					$npage = clone $page;
					$npage->ForceLang = $lang;
					$npage->URLSegment = $page->URLSegment;
					$newPages->push($npage);
					
				}
			}
			Multilingual::set_current_lang(Multilingual::default_lang());			
		}
		
		//see if default language is active. If not, then remove from sitemap.xml
		foreach($output as $page){			
			if(!$page->LangActive){
				$output->remove($page);
			}
		}
		
		$output->merge($newPages);
		
	}

}