# plg_jem_demo
JEM Demo Plugin. This plugin will (re)create a set of data to demonstrate JEM. WARNING! Please DO NOT USE this plugin on production sites, It will delete all data every day!

Important: The data/demo.sql has an event creator with id=832.
Because the admin of each joomla site has a different id, it's better to replace all "832" in sql before installing or at least before trigger.

How to procede with ftp:
1. install the plugin
2. open YOURSITE/plugins/jem/demo/data/data.sql and replace all "832" with the id of your admin, Save 
3. open the plugin
4. Choose RESET ALL DATA = YES, save
3. Activate the plugin
5. Go to Components/JEM/Control Panel/Housekeeping and click on "Trigger autoarchive" (ATTENTION: This destroys all existing JEM events and locations)
6. Control, if there are events in Components/JEM/Events!
7. open the plugin
8. Choose RESET ALL DATA = NO, save
