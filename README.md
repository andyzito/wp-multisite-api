# Multisite API #
**Contributors:** Andrew Zito, Lafayette College ITS  
**Requires at least:** 4.7  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

This plugin extends the REST API to allow management of sites in a multisite network.

## Description ##

This plugin extends the REST API to allow management of sites in a multisite network.

## Installation ##

1. Upload the `multisite-api` folder to `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

## API ##

This plugin supports all of the same commands as the WP-CLI site command, with the exception of switch-language.

### `activate` ###

| parameter | type   | default | description                  |
|-----------|--------|---------|------------------------------|
| id        | int    | -       | ID of the site to activate   |
| slug      | string | -       | Slug of the site to activate |

Activates a site.

### `archive` ###

| parameter | type   | default | description                 |
|-----------|--------|---------|-----------------------------|
| id        | int    | -       | ID of the site to archive   |
| slug      | string | -       | Slug of the site to archive |

Archives a site.

### `create` ###

| parameter | type       | default | description                                    |
|-----------|------------|---------|------------------------------------------------|
| slug      | string     | -       | Slug of the site (used for path)               |
| title     | string     | -       | Title of the new site                          |
| admin     | int|string | 1       | ID or login name of the new site administrator |

Creates a new site.

### `deactivate` ###

| parameter | type   | default | description                    |
|-----------|--------|---------|--------------------------------|
| id        | int    | -       | ID of the site to deactivate   |
| slug      | string | -       | Slug of the site to deactivate |

Deactivates a site.

### `archive` ###

| parameter | type   | default | description                 |
|-----------|--------|---------|-----------------------------|
| id        | int    | -       | ID of the site to archive   |
| slug      | string | -       | Slug of the site to archive |

Archives a site.

### `delete` ###

| parameter   | type   | default | description                              |
|-------------|--------|---------|------------------------------------------|
| id          | int    | -       | ID of the site to delete                 |
| slug        | string | -       | Slug of the site to delete               |
| keep-tables | bool   | false   | Delete site but preserve database tables |

Deletes a site.

### `list` ###

| parameter | type   | default | description                                                                                |
|-----------|--------|---------|--------------------------------------------------------------------------------------------|
| fields    | string | -       | Comma-separated list of fields to return. Specify a single field will return a flat array. |
| filter    | string | -       | Filter in the format [key]=[val].                                                          |

Lists the sites in a multisite.

The filter system is currently extremely simplistic -- it accepts only one key/value pair, and only does full matching. An example filter would be '`blog_id=45`'.

You can also specify fields to return. For example, '`blog_id,siteurl`' will return an array of objects with the `blog_id` and `siteurl` properties. Specifying a single field will return a flat array.

### `mature` ###

| parameter | type   | default | description                        |
|-----------|--------|---------|------------------------------------|
| id        | int    | -       | ID of the site to mark as mature   |
| slug      | string | -       | Slug of the site to mark as mature |

Marks a site as mature.

### `private` ###

| parameter | type   | default | description                         |
|-----------|--------|---------|-------------------------------------|
| id        | int    | -       | ID of the site to mark as private   |
| slug      | string | -       | Slug of the site to mark as private |

Marks a site as private.

### `public` ###

| parameter | type   | default | description                        |
|-----------|--------|---------|------------------------------------|
| id        | int    | -       | ID of the site to mark as public   |
| slug      | string | -       | Slug of the site to mark as public |

Marks a site as public.

### `spam` ###

| parameter | type   | default | description                      |
|-----------|--------|---------|----------------------------------|
| id        | int    | -       | ID of the site to mark as spam   |
| slug      | string | -       | Slug of the site to mark as spam |

Marks a site as spam.

### `unarchive` ###

| parameter | type   | default | description                   |
|-----------|--------|---------|-------------------------------|
| id        | int    | -       | ID of the site to unarchive   |
| slug      | string | -       | Slug of the site to unarchive |

Unarchives a site.

### `unmature` ###

| parameter | type   | default | description                          |
|-----------|--------|---------|--------------------------------------|
| id        | int    | -       | ID of the site to mark as unmature   |
| slug      | string | -       | Slug of the site to mark as unmature |

Marks a site as unmature.

### `unspam` ###

| parameter | type   | default | description                        |
|-----------|--------|---------|------------------------------------|
| id        | int    | -       | ID of the site to mark as unspam   |
| slug      | string | -       | Slug of the site to mark as unspam |

Marks a site as unspam.