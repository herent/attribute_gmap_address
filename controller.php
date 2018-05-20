<?php

namespace Concrete\Package\AttributeGmapAddress;

use Concrete\Core\Backup\ContentImporter;
use Package;
use Concrete\Core\View\View;

class Controller extends Package
{
    protected $pkgHandle = 'attribute_gmap_address';
    protected $appVersionRequired = '5.7.5';
    protected $pkgVersion = '0.9.1';

    public function getPackageName()
    {
        return t('Google Maps Attribute');
    }

    public function getPackageDescription()
    {
        return t('Installs an attribute that uses google maps to geocode an address.');
    }

    public function on_start(){
        $view = View::getInstance();
        //$view->addFooterItem('<script src="https://maps.googleapis.com/maps/api/js?key=' . \Config::get('app.api_keys.google.maps') . '&libraries=places"></script>');
    }


    protected function installXmlContent()
    {
        $pkg = Package::getByHandle($this->pkgHandle);

        $ci = new ContentImporter();
        $ci->importContentFile($pkg->getPackagePath() . '/install.xml');
    }

    public function install()
    {
        parent::install();

        $this->installXmlContent();
    }

    public function upgrade()
    {
        parent::upgrade();

        $this->installXmlContent();
    }

}