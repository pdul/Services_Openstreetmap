# Services_OpenStreetMap
OpenStreetMap is a global project with an aim of collaboratively collecting map
data. This package aims to make communicating with the OSM API intuitive.

## Usage

### Initialisation

Simply require and initialize the Services_OpenStreetMap class:

    require_once 'Services/OpenStreetMap.php';
    $osm = new Services_OpenStreetMap();

### Downloading Data, saving to an OSM file

    $osm->get(-8.3564758, 52.821022799999994, -7.7330017, 53.0428644);
    file_put_contents("area_covered.osm", $osm->getXml());


### Search for a specific POI, in a saved OSM file
    $osm = new Services_OpenStreetMap();

    $osm->loadXml("./osm.osm");
    $results = $osm->search(array("amenity" => "pharmacy"));
    echo "List of Pharmacies\n";
    echo "==================\n\n";

    foreach ($results as $result) {
        $name = null;
        $addr_street = null;
        $addr_city = null;
        $addr_country = null;
        $addr_housename = null;
        $addr_housenumber = null;
        $opening_hours = null;
        $phone = null;

        extract($result);
        $line1 = ($addr_housenumber) ? $addr_housenumber : $addr_housename;
        if ($line1 != null) {
            $line1 .= ', ';
        }
        echo  "$name\n{$line1}{$addr_street}\n$phone\n$opening_hours\n\n";
    }

### Get a specific Node
    require_once 'Services/OpenStreetMap.php';

    $osm = new Services_OpenStreetMap();

    var_dump($osm->getNode(52245107));

    Getting specific changesets, ways etc follow the same pattern.


### Updating a way, or several.
    require_once 'Services/OpenStreetMap.php';

    // A password file, is a colon delimited file.
    // Eg. fred@example.com:yabbadabbado
    $config = array('passwordfile' => './credentials');
    $osm = new Services_OpenStreetMap($config);

    $changeset = $osm->createChangeset();
    $changeset->begin("These ways are lit");
    $ways = $osm->getWays($wayId, $way2Id);
    foreach ($ways as $way) {
        $way->setTag('highway', 'residential');
        $way->setTag('lit', 'yes');
        $changeset->add($way);
    }
    $changeset->commit();

### Creating a node.

    /*
     * If you are going to connect to the live API server to run a quick
     * test that adds new data, such as POIS, with test/imaginary values
     * please be responsible and delete them afterwards.
     */

    require_once 'Services/OpenStreetMap.php';

    $config = array(
        // A password file, is a colon delimited file.
        // Eg. fred@example.com:yabbadabbado
        'passwordfile' => './credentials',
        // The live API server is api.openstreetmap.org
        'server'       => 'http://api06.dev.openstreetmap.org/',
        );
    $osm = new Services_OpenStreetMap($config);

    $changeset = $osm->createChangeset();
    $changeset->begin("Added Acme Vets.");
    // The latitude and longitude values here are intentionally invalid, see
    // note above.
    $lat = 182.8638729;
    $lon = -188.1983611;
    $node = $osm->createNode($lat, $lon, array(
        'name' => 'Acme Vets',
        'building' => 'yes',
        'amenity' => 'vet')
    );
    $changeset->add($node);
    $changeset->commit();

### Working with user information.

The getUser() method retrieves information for the current user.

    $config = array(
        'user' => 'fred@example.com',
        'password' => 'w1lma4evah'
    );

    $osm = new Services_OpenStreetMap($config);
    $user = $osm->getUser();

    echo 'My OSM Mugshot is at ', $user->getImage(), "\n";

The getUserById() method retrieves information for the specified user.

    $osm = new Services_OpenStreetMap();
    $user = $osm->getUserById(1);
