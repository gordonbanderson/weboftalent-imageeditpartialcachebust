##Partial Cache Busting After Image Editing
This module enables the updating of the LastEdited value of DataObjects associated with Images being edited.  This simplifies partial caching of for example the rendering of a folder of pages which are each represented by an image.  If the cache key is based on the LastEdited value of the DataObjects in the folder,  then when an image is edited (e.g. refocused with the FocusPoint module) the change will not show, as the cache key knows nothing about the LastEdited field of the images of child pages.  

The options to ensure image updates show in the above scenario when using partial caching are these:
* Use max(LastEdited) of _all_ images - inefficient
* Have a parital cache query around each child page checking for the LastEdited field of the page's image.  This is also inefficient as one database query is made per child page rendered.
* Add a method in the folder object to calculate the cache key based on a database join query.

At the expense of a slightly more expensive write, we can use the LastEdited date of the child pages as a cache key that will also take into account image edits.

##Configuration
The classes to check for image IDs are configured as follows, in an arbitrarily named .yml file under any module's _config directory, e.g. imageeditpartialcachebust.yml.  There are three keys under 'ImageEditCacheBust'
* Stages: the stages configured in your default configuration, the default being Stage and Live
* SiteTree: a nested array of ClassName mapped to the field containing the image ID.
* DataObject: a nested array of ClassName mapped to the field containing the image ID.

In the example below every time an image is refocussed every PageWithImage data object will have it's LastEdited field updated to now (thus busting fragment caches) if the value of MainImageID matches the ID of the image being edited.  Similarly with both SlidePage and Staff, except this time checking the PhotoID field.
```
---
Name: imageeditpartialcachebuster
After: framework/routes#coreroutes
---

ImageEditCacheBust:
  Stages: ['Stage','Live']
  SiteTree:
    PageWithImage : 'MainImageID'
    Product : 'ImageID'
    SlidePage : 'PhotoID'
    Staff : 'PhotoID'
  DataObject:
    GalleryImage : 'ImageID'
```
##Template Example
Using the module mentioned in the related modules section below, an example template for a folder containing several PageWithImage pages rendering their images looks like this:

```
<div class="row">
<div class="small-12 columns">
$BreadCrumbs
<h1>$Title</h1>
$Content
<% cached ID, LastEdited,Locale,$CacheKey('folderofpagewithimages','ChildPage') %>
<ul class="small-block-grid-2 medium-block-grid-3">
<% loop AllChildren %>
<li><div class="captionedImage">
<h3>$Title</h3>
<a href="$Link"><img class="shadowbox sliderImage" data-interchange="[$PortletImage.CroppedFocusedImage(390,260).URL, (default)],[$PortletImage.CroppedFocusedImage(236,157).URL, (small)],[$PortletImage.CroppedFocusedImage(236,157).URL, (medium)],[$PortletImage.CroppedFocusedImage(390,260).URL, (large)]"/>
<noscript><img src="$PortletImage.SetWidth(640).URL"></noscript></a>
</div>
</li>
<% end_loop %>
</ul>
<% end_cached %>
$Form
$PageComments
</div>
<% include TopAndLike %>
</div>
```

#Related Modules
The cachkey helper module, https://github.com/gordonbanderson/weboftalent-cachekey-helper, enables all partial cache key values to be accessed in a single database query, vastly reducing the number of queries performed when checking for partial cache validity.

