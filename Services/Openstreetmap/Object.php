<?php
/**
 * Object.php
 * 26-Apr-2011
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_Openstreemap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     Object.php
 */

/**
 * Services_Openstreetmap_Object
 *
 * @category Services
 * @package  Services_Openstreemap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Object.php
 */
class Services_Openstreetmap_Object
{
    protected $xml = null;

    protected $tags = array();

    protected $id = null;

    protected $type = null;

    protected $obj = null;

    protected $dirty = false;

    protected $action = null;

    protected $changeset_id = null;

    /**
     * getXml
     *
     * @return string
     */
    public function getXml()
    {
        return $this->xml;
    }

    /**
     * setXml
     *
     * @param mixed $xml OSM XML
     *
     * @return void
     */
    public function setXml($xml)
    {
        $this->xml = $xml;
        $cxml = simplexml_load_string($xml);
        $obj = $cxml->xpath('//' . $this->type);
        foreach ($obj[0]->children() as $child) {
            $key = (string) $child->attributes()->k;
            if ($key != '') {
                $this->tags[$key] = (string) $child->attributes()->v;
            }
        }
        $this->obj = $obj;
    }

    /**
     * set the Changeset Id for this object
     *
     * @param integer $id Changeset Id (numeric)
     *
     * @return void
     */
    public function setChangesetId($id)
    {
        $this->changeset_id = $id;
    }

    /**
     * getOsmChangeXML
     *
     * @return void
     */
    public function getOsmChangeXML()
    {
        if ($this->dirty) {
            $version = $this->getVersion();
            $version++;
            $domd = new DomDocument();
            $domd->loadXML($this->getXML());
            $xpath = new DomXPath($domd);
            $nodelist = $xpath->query("//{$this->type}");
            $nodelist->item(0)->setAttribute("action", "modify");

            if ($this->changeset_id !== null) {
                $nodelist->item(0)->setAttribute("changeset", $this->changeset_id);
            }
            $tags = $xpath->query("//{$this->type}/tag");

            $set = array();
            for ($i = 0; $i < $tags->length; $i++) {
                $key = $tags->item($i)->getAttribute("k");
                $val = $tags->item($i)->getAttribute("v");
                $set[$key] = $val;
            }

            $diff = array_diff($this->getTags(), $set);

            // Remove existing tags
            for ($i = 0; $i < $tags->length; $i++) {
                $rkey = $tags->item($i)->getAttribute("k");
                if (isset($diff[$rkey])) {
                    $nodelist->item(0)->removeChild($tags->item($i));
                }
            }

            foreach ($diff as $key=>$value) {
                $new = $domd->createElement("tag");
                $new->setAttribute("k", $key);
                $new->setAttribute("v", $value);
                $nodelist->item(0)->appendChild($new);
            }

            $xml = $domd->saveXML($nodelist->item(0));
            return "<{$this->action}>{$xml}</{$this->action}>";

        } else {
            return null;
        }
    }

    /**
     * Retrieve the id of the object in question
     *
     * @return string id of the object
     */
    public function getId()
    {
        $attribs = $this->getAttributes();
        if ($attribs !== null) {
            return (integer) $attribs->id;
        } else {
            return null;
        }
    }

    /**
     * Retrieve the uid of the object in question
     *
     * @return string uid of the object
     */
    public function getUid()
    {
        $attribs = $this->getAttributes();
        if ($attribs !== null) {
            return (integer) $attribs->uid;
        } else {
            return null;
        }
    }

    /**
     * Retrieve the user (creator/editor) of the object in question
     *
     * @return string user of the object
     */
    public function getUser()
    {
        $attribs = $this->getAttributes();
        if ($attribs !== null) {
            return (string) $attribs->user;
        } else {
            return null;
        }
    }

    /**
     * Retrieve the version of the object in question
     *
     * @return string version of the object
     */
    public function getVersion()
    {
        $attribs = $this->getAttributes();
        if ($attribs !== null) {
            return (integer) $attribs->version;
        } else {
            return null;
        }
    }

    /**
     * getAttributes()
     *
     * @return string getAttributes()
     */
    public function getAttributes()
    {

        if ($this->obj[0] === null) {
            return null;
        }
        return $this->obj[0]->attributes();
    }

    /**
     * tags
     *
     * @return string tags
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * setTag
     *
     * @param mixed $key   key
     * @param mixed $value value
     *
     * @return void
     */
    public function setTag($key, $value)
    {
        if ($this->action == null) {
            if ($this->getId() < 0) {
                $this->action = 'create';
            } else {
                $this->action = 'modify';
            }
        }
        $this->dirty = true;
        $this->tags[$key] = $value;
        return $this;
    }

    /**
     * delete
     *
     * @return void
     */
    public function delete()
    {
        $this->action = 'delete';
        return $this;
    }

    /**
     * Display type and id of the object.
     *
     * @return void
     */
    public function getHistory()
    {
        echo "type: ", $this->type, "\n";
        echo "id: ", $this->getId(), "\n";
    }

}

// vim:set et ts=4 sw=4:
?>
