# silverstripe-shortlist

[![Build Status](https://scrutinizer-ci.com/g/salted-herring/silverstripe-shortlist/badges/build.png?b=master)](https://scrutinizer-ci.com/g/salted-herring/silverstripe-shortlist/build-status/master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/salted-herring/silverstripe-shortlist/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/salted-herring/silverstripe-shortlist/?branch=master) [![Build Status](https://travis-ci.org/salted-herring/silverstripe-shortlist.svg?branch=master)](https://travis-ci.org/salted-herring/silverstripe-shortlist)

Session based shortlist module for SilverStripe

This module allows for front end users to create shortlists of pages or dataobjects (which must have associated controllers & Links) which exist within the site. The shortlist is stored against the users' session (so adding/removing items only occurs during the session), but a unique URL for the particular shortlist may be shared.

## Install

The module can be installed via composer:

```bash
composer require saltedherring/silverstripe-shortlist
```

Afterwards run a `dev/build`

## Confiuration Options

_config/_config.yml:

```yaml
---
Name: shortlist
After: 'framework/*','cms/*'
---
ShortList:
  URLSegment: '/shortlist/'
  PaginationCount: 12
```

The base URL segment can be modified here, as can the pagination count (for the page that displays the actual list of items).

- [ ] Ensure that URL Segment can dynamically generate the route.

## Front-end

The shortlist may be added to any template by including the supplied `ShortListLinks` ss include:

```
<% include ShortListLinks %>
```

This will provide a mechanism to allow objects to be added & removed from the shortlist.

The other templates provided are:

- templates/Layout/ShortList.ss *(provides the template for the actual shortlist, including pagination controls)*
- templates/Layout/ShortList_empty.ss *(for displaying empty shortlist messages)*

- [ ] Supply AJAX example use of pagination & add/remove links.
 
## TO DO

- [ ] Ensure that URL Segment can dynamically generate the route.
- [ ] Supply AJAX example use of pagination & add/remove links.
- [ ] Fix unit tests so they work in travis.

## Coding Standards

The code has been written to PSR1.
