<?php

class ImageEditPartialCacheBustExtension extends DataExtension
{

    /*
    This is intended to be added to Image as an extension.  When an image is edited  checks are made
    against a configurable list of classes mapped to the image ID field.  If the image ID field value matches
    the  ID of the image being refocussed, the DataObject's LastEdited field is updated.
    */
    public function onAfterWrite()
    {
        $config = Config::inst();
        $sitetreeclasses = $config->get('ImageEditCacheBust', 'SiteTree');
        $dataobjectclasses = $config->get('ImageEditCacheBust', 'DataObject');
        $stages = $config->get('ImageEditCacheBust', 'Stages');

        if ($sitetreeclasses) {
            // deal with SiteTree first
            foreach ($sitetreeclasses as $clazz => $idfield) {
                $instanceofclass = Injector::inst()->create($clazz);
                $objectsWithImage = $instanceofclass::get()->filter($idfield, $this->owner->ID);
                foreach ($objectsWithImage as $objectWithImage) {
                    foreach ($stages as $stage) {
                        $suffix = '_'.$stage;
                        $suffix = str_replace('_Stage', '', $suffix);
                        $sql = "UPDATE `SiteTree{$suffix}` SET LastEdited=NOW() where ID=".$objectWithImage->ID;
                        DB::query($sql);
                    }
                }
            }
        }

        

        if ($dataobjectclasses) {
            // deal with SiteTree first
            foreach ($dataobjectclasses as $clazz => $idfield) {
                $instanceofclass = Injector::inst()->create($clazz);
                $objectsWithImage = $instanceofclass::get()->filter($idfield, $this->owner->ID);
                foreach ($objectsWithImage as $objectWithImage) {
                    $sql = "UPDATE `$clazz` SET LastEdited=NOW() where ID=".$objectWithImage->ID;
                    DB::query($sql);
                }
            }
        }
    }
}
