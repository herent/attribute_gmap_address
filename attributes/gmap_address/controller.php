<?php

namespace Concrete\Package\AttributeGmapAddress\Attribute\GmapAddress;

use Database;
use View;
use Config;
use \Concrete\Core\Attribute\Controller as AttributeTypeController;
use Concrete\Core\Attribute\FontAwesomeIconFormatter;

class Controller extends AttributeTypeController
{
    public $helpers = ['form'];

    protected $searchIndexFieldDefinition = [
        'latitude' => [
            'type' => 'string',
            'options' => ['length' => '255', 'default' => '', 'notnull' => false],
        ],
        'longitude' => [
            'type' => 'string',
            'options' => ['length' => '255', 'default' => '', 'notnull' => false],
        ],
        'address' => [
            'type' => 'text',
            'options' => ['default' => null, 'notnull' => false],
        ],
    ];

    public function getSearchIndexValue()
    {
        $v = $this->getVariablesValue();
        $args = [];
        $args['address'] = $v['address'];
        $args['latitude'] = $v['latitude'];
        $args['longitude'] = $v['longitude'];
        return $args;
    }

    public function searchKeywords($keywords, $queryBuilder)
    {
        $h = $this->attributeKey->getAttributeKeyHandle();

        return $queryBuilder->expr()->orX(
            $queryBuilder->expr()->like("ak_{$h}_address", ':keywords')
        );
    }

    public function getIconFormatter()
    {
        return new FontAwesomeIconFormatter('map-marker');
    }

    public function getTypeValue()
    {
        $value = [];
        $value['apiKey'] = Config::get('app.api_keys.google.maps');
        return $value;
    }


    /**
     * Returns an array containing all dynamic variables for the attribute
     * @return array
     */
    public function getVariablesValue()
    {
        $db = Database::connection();
        $rawData = $db->GetRow('SELECT * FROM atGmapAddress WHERE avID = ?', [$this->getAttributeValueID()]);
        $ret = [];
        $ret['latitude'] = $rawData['latitude'];
        $ret['longitude'] = $rawData['longitude'];
        $ret['address'] = $rawData['address'];
        return($ret);
    }

    public function getValue()
    {
        $values = $this->getVariablesValue();
        $outputList = "<ul><li>Lat: " . $values['latitude'] .  "</li><li>Lng: " . $values['longitude'] . "</li></ul>";
        return $values;
    }

    /**
     * Shows the attribute configuration form
     */
    public function type_form()
    {
        $typeValues = $this->getTypeValue();
        $this->set('apiKey', $typeValues['apiKey']);
    }

    /**
     * Saves the attribute configuration
     * @param array $data
     */
    public function saveKey($data)
    {
        Config::save('app.api_keys.google.maps', trim($data['apiKey']));
    }

    /**
     * Shows the value, the HTML text in the form
     */
    public function form()
    {
        $this->addHeaderItem('<script src="https://maps.googleapis.com/maps/api/js?key=' . \Config::get('app.api_keys.google.maps') . '&libraries=places&callback=initMap"></script>');
        $this->set('values', $this->getVariablesValue());
    }

    /**
     * Called when we're searching using an attribute.
     * @param $list
     */
    public function searchForm($list)
    {
        $akHandle = $this->attributeKey->getAttributeKeyHandle();
        $address = $this->request("address");
        if ($address){
            $list->filter("ak_" . $akHandle . "_address", "%" . $address . "%", "like");
        }
        return $list;
    }

    public function search() {
        $this->form();
        $v = $this->getView();
        $v->render(new \Concrete\Core\Attribute\Context\BasicFormContext());
    }

    /**
     * Called when we're saving the attribute from the frontend.
     * @param $data
     */
    public function saveForm($data)
    {
        $db = Database::connection();

        $db->Replace(
            'atGmapAddress',
            [
                'avID' => $this->getAttributeValueID(),
                'data' => json_encode($data),
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'address' => $data['address']
            ],
            'avID',
            true
        );
    }

    /**
     * Called when saving an attribute programmatically through setAttribute.
     * @param $data array
     */
    public function saveValue($data)
    {
        $this->saveForm($data);
    }

    /**
     * Called when the attribute is edited in the composer.
     */
    public function composer()
    {
        $this->form();
    }

    public function deleteKey()
    {
        $db = Database::connection();
        $arr = $this->attributeKey->getAttributeValueIDList();
        foreach ($arr as $id) {
            $db->Execute('DELETE FROM atGmapAddress WHERE avID = ?', [$id]);
        }
        $db->Execute('delete from atGmapAddressSettings where akID = ?', array($this->attributeKey->getAttributeKeyID()));
    }

    public function deleteValue()
    {
        $db = Database::connection();
        $db->Execute('DELETE FROM atGmapAddress WHERE avID = ?', [$this->getAttributeValueID()]);
    }

}