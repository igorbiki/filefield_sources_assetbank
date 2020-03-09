---------------------------
FileField Sources Asset Bank
---------------------------

Description
---------------------------
FileField Sources Asset Bank is a module that extends FileField Sources module
to allow Asset Bank image integration in Drupal. Make sure to enable the module
and to configure image filed to use Asset Bank integration.

Requirements
---------------------------
1) Drupal 8 (8.8.2) version was used in development and FileField Sources 8.x-1.0

Installation
---------------------------
1) (recommended) Use composer to add module by running
    "composer require drupal/filefield_sources_assetbank"

2) Enable the module within your Drupal site.

3) Add or configure an existing file or image field. To configure a typical node
   field, visit Manage -> Structure -> Content types and click
   "Manage form display" on a type you'd like to modify. Add a new file field or
   edit an existing one.

   While editing the file or image field, you'll have new options available
   under a "File sources" details. You can enable the desired sources for that
   particular field.

4) Create a piece of content that uses your file and try it out.

Support
---------------------------
Please file bug reports in the FileField Sources Aseetbank issue queue.
Do not use the Drupal.org forums or send bug reports via e-mail.
http://drupal.org/project/issues/filefield_sources?categories=All
