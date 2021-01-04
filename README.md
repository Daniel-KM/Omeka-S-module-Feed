Feed (module for Omeka S)
=========================

> __New versions of this module and support for Omeka S version 3.0 and above
> are available on [GitLab], which seems to respect users and privacy better
> than the previous repository.__

[Feed] is a module for [Omeka S] that provides a RSS feed to users from selected
pages and resources.


Installation
------------

The optional module [Generic] may be installed first.

The module uses external libraries, so use the release zip to install it, or use
and init the source.

See general end user documentation for [installing a module].

* From the zip

Download the last release [Feed.zip] from the list of releases (the master does
not contain the dependency), and uncompress it in the `modules` directory.

* From the source and for development

If the module was installed from the source, rename the name of the folder of
the module to `Feed`, go to the root of the module, and run:

```sh
composer install --no-dev
```


Usage
-----

Simply fill the form to list the selected pages and resources in site settings,
and they will be available as a rss feed at "https://example.org/s/my-site/feed"
(or "https://example.org/feed" if you skip the main site).

You can add "rss" or "atom" at the end of the url to force one type: "https://example.org/s/my-site/feed/atom".
Note: atom is currently not working with diacritics and other html entities.

Options available in the site settings are:
- Media type, that can be the standard one or the generic xml one.
- Content disposition, that can be Attachment, Inline, or Undefined.


Warning
-------

Use it at your own risk.

It’s always recommended to backup your files and your databases and to check
your archives regularly so you can roll back if needed.


Troubleshooting
---------------

See online issues on the [module issues] page on GitLab.


License
-------

This module is published under the [CeCILL v2.1] license, compatible with
[GNU/GPL] and approved by [FSF] and [OSI].

In consideration of access to the source code and the rights to copy, modify and
redistribute granted by the license, users are provided only with a limited
warranty and the software’s author, the holder of the economic rights, and the
successive licensors only have limited liability.

In this respect, the risks associated with loading, using, modifying and/or
developing or reproducing the software by the user are brought to the user’s
attention, given its Free Software status, which may make it complicated to use,
with the result that its use is reserved for developers and experienced
professionals having in-depth computer knowledge. Users are therefore encouraged
to load and test the suitability of the software as regards their requirements
in conditions enabling the security of their systems and/or data to be ensured
and, more generally, to use and operate it in the same conditions of security.
This Agreement may be freely reproduced and published, provided it is not
altered, and that no provisions are either added or removed herefrom.


Copyright
---------

* Copyright Daniel Berthereau, 2019-2021 (see [Daniel-KM] on GitLab)

First version of this module was built by Daniel Berthereau for [Fondation Maison de Salins].


[Omeka S]: https://omeka.org/s
[Feed]: https://gitlab.com/Daniel-KM/Omeka-S-module-Feed
[Generic]: https://gitlab.com/Daniel-KM/Omeka-S-module-Generic
[Feed.zip]: https://gitlab.com/Daniel-KM/Omeka-S-module-Feed/-/releases
[Installing a module]: http://dev.omeka.org/docs/s/user-manual/modules/#installing-modules
[module issues]: https://gitlab.com/Daniel-KM/Omeka-S-module-Feed/-/issues
[CeCILL v2.1]: https://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html
[GNU/GPL]: https://www.gnu.org/licenses/gpl-3.0.html
[FSF]: https://www.fsf.org
[OSI]: http://opensource.org
[Fondation Maison de Salins]: https://www.collections.maison-salins.fr
[GitLab]: https://gitlab.com/Daniel-KM
[Daniel-KM]: https://gitlab.com/Daniel-KM "Daniel Berthereau"
