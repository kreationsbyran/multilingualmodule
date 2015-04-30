# multilingualmodule
Multilingual module for SS 3.1+


Multilingual Module is expected to be installed on a fresh db. You can however use the migrationtask to migrate a DB to work with MultilingualPages. Dataobjects can not be migrated because they use a completly new set of IDs per Object. You will have to start fresh with your multilingual dataobjects (you might do an own migration thru sql queries however, there is just no bundled task for it). 

Add the module to the root of your site and name it to "multilingual" if not already namned that way.
- Make your Page class extend from MultilingualPage (both class and controller)
- Make SiteConfig extend MultilingualSiteConfig
- Make all Dataobjects you want in multilingual extend from MultilingualDataObject instead of DataObject.
Open up /multilingual/_config.php and set the module up
Do /dev/build/
You can also run a task /dev/tasks/ActivateLanguageTask?en=1 if you want all pages (that extend multilingualpage) to be viewable for english. If you are not starting from a fresh DB you can use the MigrateToMultilingualTask, where all Pages in the DB will be migrated to MultilingualPages. If task not used it might look like you ended up without pages after an dev/build in your site. 
This means that all pages in your site must extend from multilingualpage. This doesnt mean that all pages must be multilingual, but is a prerequisite for the module to work. Dataobject are however optional. Those dataobject that make sense to translate you just extend from MultilingualDataObject. 



Full Documentation: http://multilingual.kreationsbyran.se/


