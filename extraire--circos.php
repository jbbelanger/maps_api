<table>
<?php

$input = dirname(__FILE__) . '/' . 'carte_2017_003.kml';

$rawData = @file_get_contents($input);

$data = new SimpleXMLElement($rawData);

$circos = [];

foreach ($data->{'Document'}->{'Folder'}->{'Placemark'} as $placemark) {
    $circo_name = $placemark->{'name'};
    $circo_id = $placemark->{'description'};
    echo "<tr><td>$circo_name ($circo_id)</td>";
    $circo_polygones = [];

    //debug
    //echo "<h3>" . $placemark->{'description'} . " - ";
    //echo $placemark->{'name'} . "<br><br></h3>";

    foreach ($placemark->{'MultiGeometry'}->{'Polygon'} as $Polygon) {
        $spaced_data = [];
        $arrayed_data = [];

        $spaced_data = array_map('floatval', explode(" ", str_replace(",", " ", $Polygon->{'outerBoundaryIs'}->{'LinearRing'}->{'coordinates'})));

        for ($i = 0; $i < count($spaced_data) ; $i+=2) {
            array_push($arrayed_data, ["lat" => $spaced_data[$i+1], "lng" => $spaced_data[$i]]);
        }

        array_push($circo_polygones, $arrayed_data);

    }

    $Polygon = $placemark->{'Polygon'};
    $spaced_data_s = [];
    $arrayed_data_s = [];

    $spaced_data_s = array_map('floatval', explode(" ", str_replace(",", " ", $Polygon->{'outerBoundaryIs'}->{'LinearRing'}->{'coordinates'})));

    for ($i = 0; $i < count($spaced_data_s) ; $i+=2) {
        array_push($arrayed_data_s, ["lat" => $spaced_data_s[$i+1], "lng" => $spaced_data_s[$i]]);
    }

    if ($circo_polygones === []) {
        array_push($circo_polygones, $spaced_data_s);
    }

    //var_dump($arrayed_data_s);
    array_push($circos, array("id" => (int) $circo_id, "polygones" => $circo_polygones));
    echo "<td>" . json_encode($circo_polygones) ."</td></tr>";
}

//debug
//var_dump($circo_polygones);
error_get_last();
//echo json_encode($circos);

?>
</table>
<script>
    var JSONdata = JSON.parse('<?php echo json_encode($circos); ?>');
</script>
