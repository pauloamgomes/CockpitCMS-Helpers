# Helpers Addon for CMS

This addon combines a set of features that would improve the main functionality of the Cockpit CMS. The idea is to be including new helpers as they are required.

## Helpers

The current implementation provides:

- Password reset CLI command
- Singleton data export/import CLI command
- Collection data export/import CLI command
- Better handling of collection and singletons structure changes
- Addon install CLI command
- JSON Preview for collection entries
- Environment indicator
- Assets in modules menubar
- Quick actions

### Password reset

A CLI command that permits to reset the password of an user, e.g.:

```bash
./cp password --user "admin" --pass "admin"
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
Collection post exported to #storage:exports/singletons/posts.json - 4014 bytes written
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

**Notes:**
The #storage:exports/collections folder is always used for exporting/importing.

### Better handling of collection and singletons structure changes

Cockpit doesn't seem to map any changes in the structure of collections or singletons (e.g. removal of a field) in the data values. So if we remove a field the existing entries will not change and the field value is still returned by the API.

The addon implements two hooks that handle the problem:

- **collections.save.after** - when the entry is saved and if a change is detected (existing data contains fields that are not in the collection structure anymore) the entry is deleted and inserted again (the id will not change). The main reason for delete and delete resides in the fact that Cockpit db update does a merge of the updated data with the existing data.
- **singleton.saveData.before** - for the singletons is a bit more simple, we only need to confirm if the data changed and if so we remove the old fields.

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

### JSON preview of collection entries

A JSON Preview link on each colletion entry sidebar, so we can access the JSON that will be returned from the API directly from the form page, e.g.:

![JSON Preview](https://monosnap.com/image/AirWoZb65N22WNjPkiTyISr4JZxVLZ.png)

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

## Installation

1. Confirm that you have Cockpit CMS (Next branch) installed and working.
2. Download zip and extract to 'your-cockpit-docroot/addons' (e.g. cockpitcms/addons/Helpers)
   (Ensure that the addon folder is named Helpers)

## Copyright and license

Copyright 2018 pauloamgomes under the MIT license.
