<?php
class MigrateToMultilingualTask extends BuildTask {
	protected $title = 'Migrate to Multilingual module';
	protected $description = 'MigrateTask. Run this after first dev/build after adding the Multilingual module.';
	protected $enabled = true;

	function run($request) {
		$this->Migrate();		
		DataObject::flush_and_destroy_cache();
	}
	function Migrate(){
		if(true){
			
			$pages=DB::query("SELECT ID, Title FROM SiteTree WHERE ClassName != 'ErrorPage'");
			
			foreach($pages as $page){
				
				DB::query("INSERT INTO MultilingualPage (ID, LangActive) VALUES (".$page["ID"].",1) ON DUPLICATE KEY UPDATE LangActive=1");				
			}
			
			$pages=DB::query("SELECT ID, Title FROM SiteTree_Live WHERE ClassName != 'ErrorPage'");
			
			foreach($pages as $page){
				
				DB::query("INSERT INTO MultilingualPage_Live (ID, LangActive) VALUES (".$page["ID"].",1) ON DUPLICATE KEY UPDATE LangActive=1");
				DB::alteration_message("Migrated Page #".$page["ID"]." (".$page["Title"].")","changed");
			}			
		}
		DB::alteration_message("Done","created");
	}	
}