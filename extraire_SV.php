<table style="font-family:'Guillon'">
<?php

//$input = dirname(__FILE__) . '/sv/' . 'sv--2018.kml';

foreach (glob('/*.kml') as $input) {

  $rawData = @file_get_contents($input);

  $data = new SimpleXMLElement($rawData);

  foreach ($data->{'Document'}->{'Folder'}->{'Placemark'} as $placemark) {
      $sevo_id = (int) $placemark->{'description'};

      echo "<tr><td>" . $placemark->{'name'} . "</td>";
      echo "<td>$sevo_id</td>";

      $sevo_poylgone = [];
      $spaced_data = [];
      $arrayed_data = [];

      $spaced_data = array_map('floatval', explode(" ", str_replace(",", " ", $placemark/*->{'MultiGeometry'}*/->{'Polygon'}->{'outerBoundaryIs'}->{'LinearRing'}->{'coordinates'})));

      for ($i = 0; $i < count($spaced_data) ; $i+=2) {
          array_push($arrayed_data, ["lat" => $spaced_data[$i+1], "lng" => $spaced_data[$i]]);
      }

      array_push($sevo_poylgone, $arrayed_data);


      //MySQL
      $servername = "hp187.hostpapa.com";
      $username = "jbeno958_admin";
      $password = "vibe2192";
      $dbname = "jbeno958_election";

      $conn = new mysqli($servername, $username, $password, $dbname);
      $conn->set_charset('utf8mb4');

      $sql = "UPDATE `section_vote`
  SET `sevo_polygone` = '" . str_replace('\"','\"',(string) json_encode($sevo_poylgone)) . "'
  WHERE `id` = $sevo_id
  ";

  if ($conn->query($sql) === TRUE) {
      echo "<td>Record updated successfully</td></tr>";
  } else {
      echo "<td>Error updating record: " . $conn->error . "</td></tr";
  }

  $conn->close();
  }

}

?>
</table>
