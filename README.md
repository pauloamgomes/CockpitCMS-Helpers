# Helpers Addon for CMS

This addon combines a set of features that would improve the main functionality of the Cockpit CMS. The idea is to be including new helpers as they are required.

## Helpers

The current implementation provides:

- [Password reset CLI command](#password-reset)
- [Token API keys reset](#token-api-keys-reset)
- [Singleton data export/import CLI command](#singleton-data-exportimport-cli-command)
- [Collection data export/import CLI command](#collection-data-exportimport-cli-command)
- [Form data export/import CLI command](#form-data-exportimport-cli-command)
- [Better handling of collection and singletons structure changes](#better-handling-of-collection-and-singletons-structure-changes)
- [Addon install CLI command](#addon-install-cli-command)
- [JSON Preview/Live edit for collection entries and singletons](#json-previewlive-edit-of-collection-entries-and-singletons-data)
- [Environment indicator](#environment-indicator)
- [Assets in modules menubar](#assets-in-modules-menu)
- [Quick actions](#quick-actions)
- [Collection Entries extra sidebar actions](#collection-entries-extra-sidebar-actions)
- [Content Preview url override](#content-preview-url-override)
- [Collection Select Field](#collection-select-field)
- [Singleton Select Field](#singleton-select-field)
- [Max Revisions](#max-revisions)
- [Unique Fields](#unique-fields)
- [Locks removal](#locks-removal)
- [Cockpit search on collections](#cockpit-search-on-collections)
- [Basic migrations CLI command](#basic-migrations-cli-command)

### Password reset

A CLI command that permits to reset the password of an user, e.g.:

```bash
./cp password --user "admin" --pass "admin"
```

### Token API keys reset

A CLI command that permits to reset the tokens. By Default Cockpit has one "master" and one or more "special" keys.

Set the master key

```bash
./cp reset-api --name master --key 7e4c32f0546317bb8a3ec0166fa63c
  API key master set to 7e4c32f0546317bb8a3ec0166fa63c
```

Generate a new master key

```bash
./cp reset-api --name master
  API key master set to 7e4c32f0546317bb8a3ec0166fa63c
```

Set "special" key at position 1 (first entry of special keys)

```bash
./cp reset-api --name special --number 1
  API key special set to 7e4c32f0546317bb8a3ec0166fa63c
```

Generate a new "special" key for position 1

```bash
./cp reset-api --name special --number 1
  API key special set to 7e4c32f0546317bb8a3ec0166fa63c
```


### Singleton data export/import CLI command

A CLI command that permits to export and import singleton data, e.g.:

```bash
$ ./cp export-singleton --name settings

Exporting data from singleton settings
Singleton settings exported to #storage:exports/singletons/settings.json - 4014 bytes written
```

To import just run the import-singleton command:

```bash
$ ./cp import-singleton --name settings
Singleton settings data imported from #storage:exports/singletons/settings.json
```

**Notes:**
The #storage:exports/singletons folder is always used for exporting/importing.

### Collection data export/import CLI command

A CLI command that permits to export and import collection data, e.g.:

```bash
./cp export-collection --name posts

Exporting collection posts (2 entries) to #storage:exports/collections/posts.json
Collection post exported to #storage:exports/collections/posts.json - 4014 bytes written
```

To import just run the import-collection command:

```bash
$ ./cp import-collection --name posts
  Importing collection posts (2 entries)
Imported 5c12ef4746eee8004a7a7b72 (insert)
Imported 5c14dd4746eee801bc2002c3 (insert)
Collection posts import done. Imported 2 entries
```

If the entries already exists they will be imported as a new revision:

```bash
$ ./cp import-collection --name posts
  Importing collection posts (2 entries)
Imported 5c12ef4746eee8004a7a7b72 (update)
Imported 5c14dd4746eee801bc2002c3 (update)
Collection posts import done. Imported 2 entries
```

### Form data export/import CLI command

A CLI command that permits to export and import form data, e.g.:

```bash
./cp export-form --name submissions

Exporting form posts (2 entries) to #storage:exports/forms/submissions.json
Form post exported to #storage:exports/forms/submissions.json - 4014 bytes written
```

To import just run the import-form command:

```bash
$ ./cp import-form --name submissions
  Importing form submissions (2 entries)
Imported 5c12ef4746eee8004a7a7b72 (insert)
Imported 5c14dd4746eee801bc2002c3 (insert)
Form submissions import done. Imported 2 entries
```

**Notes:**
The #storage:exports/collections folder is always used for exporting/importing.

### Better handling of collection and singletons structure changes

Cockpit doesn't seem to map any changes in the structure of collections or singletons (e.g. removal of a field) in the data values. So if we remove a field the existing entries will not change and the field value is still returned by the API.

The addon implements two hooks that handle the problem:

- **collections.save.after** - when the entry is saved and if a change is detected (existing data contains fields that are not in the collection structure anymore) the entry is deleted and inserted again (the id will not change). The main reason for delete and delete resides in the fact that Cockpit db update does a merge of the updated data with the existing data.
- **singleton.saveData.before** - for the singletons is a bit more simple, we only need to confirm if the data changed and if so we remove the old fields.

That validation only takes place if its defined in the helpers global configuration as below:

```yam
helpers:
  checkSchema: true
```

Since the collection hooks will require a manual save of the collection entries, a CLI command was created to perform the update operation against all entries of a collection:

```bash
$ ./cp update-collection --name posts

Collection 'posts' - Updating fields...
Entry 5c1b8fb6cad42d03f72ab442 updated.
Done! 1 entries updated.
```

### Addon install CLI command

The main idea behind an addon install command is to provide the possibility to include in the addon folder exported data (e.g. a singleton structure, a collection structure and entries, etc..).
Let's imagine we are working in an bunch of core data structures that we need to put in place everytime we reinstall cockpit (or we have someone in the team starting), for example:

* A settings singleton
* A page collection

So we can create an addon (e.g. MySiteBase) that will have an export of above data structures and corresponding data. We can define our structure in the addon folder (e.g. `addons/MySiteBase/install`), in an `addons/MySiteBase/info.yaml` file:

```yaml
name: MySiteBase
description: Provides core features for MySite
version: 0.1
install:
  singletons:
      - name: settings
        source: install/settings.singleton.php
        data: install/settings.json
  collections:
      - name: page
        source: install/page.collection.php
        rules:
          - create: install/rules/page.create.php
          - delete: install/rules/page.delete.php
          - read: install/rules/page.read.php
          - update: install/rules/page.update.php
        data: install/page.json
  customStorage:
      - source: install/myfile1.php
        target: "mycustomfolder/myfile1.php"
      - source: install/myfile2.php
        target: "mycustomfolder/myfile2.php"
```

So using above addon definition we can provide some context to the install CLI command and we can run:

```bash
$ ./cp install --name MySiteBase
```

Above command will create the data structures (Singletons and Collections) and correspondind data (Singleton data and Collection entries):

```bash
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 Cockpit CMS Addon installer
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Processing singletons...
 Installing singleton settings from /var/www/html/addons/MySiteBase/install/settings.singleton.php
* Singleton 'settings' created.
* Singleton 'settings' data imported.
Processing collections...
 Installing collection page from /var/www/html/addons/MySiteBase/install/page.collection.php
* Collection 'page' created.
 Installing collection page rules
* Collection 'page' rule 'create' created.
* Collection 'page' rule 'delete' created.
* Collection 'page' rule 'read' created.
* Collection 'page' rule 'update' created.
 Importing collection page (2 entries)
* Imported collection 'page' entry -> _id:5c12ef4746eee8004a7a7b72 (update)
* Imported collection 'page' entry -> _id:5c14dd4746eee801bc2002c3 (update)
Collection page import done. Imported 2 entries
Processing custom storage...
 Importing file into /var/www/html/storage/mycustomfolder/myfile1.php
* File '/var/www/html/storage/mycustomfolder/myfile1.php' created.
 Importing file into /var/www/html/storage/mycustomfolder/myfile2.php
* File '/var/www/html/storage/mycustomfolder/myfile2.php' created.
```

If the data structures are already installed we can force an overwrite/update by using the --force param:

```bash
$ ./cp install --name MySiteBase --force
```

**@todo: Include in the install process Forms, Assets and custom operations.**

### JSON preview/live edit of collection entries and singletons data

A JSON link on each collection entry and singleton data sidebar, so we can access the JSON that will be returned from the API directly from the form page, e.g.:

![JSON Preview/Live edit](https://monosnap.com/image/O8y6ZtPSN9GbnP5jOc3mpMWaZACSGI)

The JSON can be changed directly in the editor and changes will be reflected in the form.

A view and edit permission is available for non admin users, e.g.:

```yaml
groups:
  editor:
    helpers:
      jsonview: true
      jsonedit: true
```

Cockpit introduced recently in the core an option to inspect the entry json, however it doesn't provide yet editing capabilities.

### Environment indicator

A simple mechanism to provide a visual feedback regarding the environment we are working, this can be useful to prevent doing changes on a live environment by mistake. To enable it just add to the `config.yaml`:

```yaml
helpers:
  environment: local
```

You can use local, dev, stg or prod and the you'll have an indicator in the cockpit site name and a border line in main header, e.g.:

**Local**

![Local Environment](https://monosnap.com/image/LBEIL3eeI6GaHlTnmkJAB047BDDlxA.png)

**Prod**

![Prod Environment](https://monosnap.com/image/hF6MDznnCQ1ahhAEhGdtsiLrW1dPtj.png)

### Assets in modules menu

The modules menu includes core Collections, Singletons and Forms, the Helpers addon makes a menu entry for Assets:

![Assets](https://monosnap.com/image/dq7InK3hqcckwcORLgUwl6Ulvyp6kc.png)

### Quick Actions

A simple UI element that is present in the modules navigation bar and can be configured (`config.yaml`) to display a list of quick actions. Actions can be aggregated in groups, and can be a link to whatever you need, for example:

```yaml
helpers:
  quickactions:
    - group: Create
      actions:
        - label: Post
          path: /collections/entry/post
        - label: Page
          path: /collections/entry/page
        - label: Categories
          path: /collections/entry/category
    - group: Accounts
      actions:
        - label: New
          path: /accounts/create
```

![Quick Action](https://monosnap.com/image/C50GMgiJ54dxNZKkoNfcu6Fma7YriC.png)

### Collection Entries extra sidebar actions

The addon extends the sidebar when viewing a collection entry with 3 handy extra actions:

- Add new entry (create a new entry without leaving the page)
- Duplicate (clone) the existing entry (without leaving the page)
- Delete the existing entry

![Cockpit collection entry extra actions](https://monosnap.com/image/jZt27rZB2rpjDvqrNWivWuaMk04wMa)

### Content Preview url override

By default the Cockpit preview url is tied to the collection structure, and therefore is not possible
to have different urls (e.g. for local environment or dev/stage environments).
The Helpers addon provides a mechanism to override the url in the config.yaml file. For example, if we have a configured url in the collection like below:

![Preview Url](https://monosnap.com/image/IIlcvTPmBMj9gKSynZVlOf6geXudNt.png)

And in the config.yaml we have an entry like:

```yaml
helpers:
  preview:
    url: https://localhost:3003
```

Cockpit will use `https://localhost:3003/preview/page` as preview url.

### Collection Select Field

Cockpit provides the Collection Link field to easily reference collection entries, however for some scenarios (e.g. we have a small and fixed number of items) a select element can be more useful.

The Collection Select field accepts the same parameters in the field definition as the Collection Link field, e.g.:

```json
{
  "link": "page",
  "display": "title",
  "limit": 20
}
```

The field requires an ACL (for non admin users) to be set in the config, e.g:

```yaml
groups:
  editor:
    helpers:
      collectionSelect: true
```

### Singleton Select Field

Cockpit doesn't provide a mechanism to link to singletons, however for some scenarios (e.g. define global data like blocks) it can be useful.

The Singleton Select field accepts the below parameters:

```json
{
  "group": "Blocks",
  "limit": 20
}
```

The field requires an ACL (for non admin users) to be set in the config, e.g:

```yaml
groups:
  editor:
    helpers:
      singletonSelect: true
```


### Max Revisions

Cockpit by default creates revisions for collections and singletons without a limit, so we can end with thousands of revisions for a collection/singleton, mostly when performing batch updates or tests.

The helpers addon provides a mechanism to set a limit per collection/singleton or to all collections or singletons:

* Set max revisions to 10 for all collections and singletons
```yaml
helpers:
  maxRevisions:
    collections: 10
    singletons: 10
```

* Set max revisions to 10 to all collections/singletons and 15 to page collection.
```yaml
helpers:
  maxRevisions:
    collections: 10
    singletons: 10
    page: 15
```


### Unique Fields

Cockpit by default permits to create entries with same values, that can be an issue for example when we want to have a unique title.

The addon provides via configuration the possibility to define per collection the fields that should be unique, e.g.:

```yaml
helpers:
  uniqueFields:
    page:
      - title
    city:
      - title
      - name
```

PLEASE NOTE THAT BY ACTIVATING ABOVE WILL REMOVE THE COCKPIT DUPLICATE FUNCTIONALITY

### Locks Removal

When editing contents cockpit provides a lock editing feature, however that seems to not be expired, and to avoid ending with contents locked for a long time the Helpers provide a cli command to remove the locks (e.g. to be used in a cron command).

```bash
# Remove all locks
$ php cp reset-expiredlocks

# Remove locks older than 6h (21600s)
$ php cp reset-expiredlocks --time 21600
```

### Cockpit search on collections

Cockpit provides a basic global search that can be used to search on pre-defined set of elements like collection types or user accounts. The helpers addons extends that functionality to work also on collection entries, making possible to search directly on any collection type entries.

To active that functionality add to your `config.yaml` the collections and title field name you want to perform search, e.g.:

```yaml
helpers:
  cockpitSearch:
    limit: 10
    collections:
      page: title
      post: title
```

On the above example when hitting the global search cockpit will return results from collections page and post (on the field title):

![Search](https://monosnap.com/image/uhtIkShzmlcGPTNOL5hSn9OQvvqdv7)

### Basic migrations CLI command

On the event you have a running website and you need to change some field structures, it can be helpfull to have a
mechanism to run the changes against the updated contents.
The addon provides a very simple CLI command that works as a convention/wrapper for your migration script, all the logic
behind the migration is still required to be handled by you.

Put your script on your addon inside a migrations folder, e.g.: `<addon_name>/migrations/20190710-update-posts.php` containing
a function name `migration_20190710_update_posts`, contents can be like below:

```php
<?php
// Update post contents field date type.
function migration_20190710_update_posts($app) {
  $collection = $app->module('collections')->collection('posts');
  $entries = $app->storage->find("collections/{$collection['_id']}")->toArray();
  foreach ($entries as $entry) {
    // Perform your changes.
    $entry['field_xpto'] = [...];
    // And save entry.
    $app->module('collections')->save('posts', $entry, ['revision' => TRUE]);
  }
}
```

and just run the command:

```bash
$ php cp --addon <addon_name> --name 20190710-update-posts
```

## Installation

1. Confirm that you have Cockpit CMS (Next branch) installed and working.
2. Download zip and extract to 'your-cockpit-docroot/addons' (e.g. cockpitcms/addons/Helpers)
   (Ensure that the addon folder is named Helpers)

## Copyright and license

Copyright 2018 pauloamgomes under the MIT license.
