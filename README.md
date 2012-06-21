### twoStepGallery

Another image gallery as Admin-Tool for the Content Management Systems [WebsiteBaker] [1] and [LEPTON CMS] [2].

The twoStepGallery enables to show an album of multiple galleries at one time - specify the start image of the album and add multiple galleries. twoStepGallery will show the start images of each gallery, at click the image will zoom with Lytebox and enable stepping through the gallery a.s.o. 

#### Requirements

* minimum PHP 5.2.x
* using [WebsiteBaker] [1] _or_ using [LEPTON CMS] [2]
* [dbConnect_LE] [7] installed 
* [Dwoo] [8] installed
* [kitTools] [9] installed
* [wblib] [10] installed
* [LibraryAdmin] [11] installed

#### Installation

* download the actual [twoStepGallery] [3] installation archive
* in CMS backend select the file from "Add-ons" -> "Modules" -> "Install module"

#### First Steps

Go to Admin-Tools --> twoStepGallery. 

* Type in a name for the gallery, i.e. `gallery`
* describe the gallery
* select the title image for the gallery
* add multiple images to the gallery
* add the next gallery, select the title image, add further images a.s.o.
* save the gallery
* edit the page where to show the gallery
* insert the droplet for the twoStepGallery

        [[ts_gallery?name=gallery]] 
    
* save the page and look at the result.    

Please visit the [phpManufaktur] [5] to get more informatios about the **twoStepGallery** and join the [Addons Support Group] [6].

[1]: http://websitebaker2.org "WebsiteBaker Content Management System"
[2]: http://lepton-cms.org "LEPTON CMS"
[3]: https://addons.phpmanufaktur.de/download.php?file=twoStepGallery
[4]: http://www.pchart.net/
[5]: http://phpmanufaktur.de
[6]: http://phpmanufaktur.de/support
[7]: https://addons.phpmanufaktur.de/download.php?file=dbConnect_LE
[8]: https://addons.phpmanufaktur.de/download.php?file=Dwoo
[9]: https://addons.phpmanufaktur.de/download.php?file=kitTools
[10]: https://github.com/webbird/wblib/downloads
[11]: http://jquery.lepton-cms.org/modules/download_gallery/dlc.php?file=75&id=1318585713