<?php $directory = 'simple-geo/*.kml'; ?>
<!DOCTYPE html>
<html lang="fr" dir="ltr">
  <head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.grey-deep_orange.min.css" />
    <script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
    <title>Extraction des sections de vote</title>
  </head>
  <body>
    <div class="mdl-grid">
      <div class="mdl-cell mdl-cell--1-col"></div>
      <div class="mdl-cell mdl-cell--4-col mdl-shadow--2dp">
        <h3 style="text-align:center; margin-bottom: 0;">Fichiers à traiter</h4>
        <h6 style="text-align:center; margin-top: 0;"><?php $simple_ite =  new GlobIterator($directory); echo $simple_ite->count(); ?> circonscription(s)</h6>
        <ul class=" mdl-list">
    <?php

    //$input = dirname(__FILE__) . '/sv/' . 'sv--2018.kml';

    foreach (glob($directory) as $input) {

      $rawData = @file_get_contents($input);

      $data = new SimpleXMLElement($rawData);

      foreach ($data->{'Document'}->{'Folder'}->{'Placemark'} as $placemark) {
          $sevo_id = (int) $placemark->{'description'};
          ?><li class="mdl-list__item mdl-list__item--two-line">
            <span class="mdl-list__item-primary-content">
              <i class="material-icons mdl-list__item-icon">add_location</i>
              <?php echo $sevo_id; ?>
              <span class="mdl-list__item-sub-title"><?php echo $placemark->{'name'}; ?></span>
            </span><?php

          $sevo_poylgone = [];
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
        ?>
        <span class="mdl-list__item-secondary-content">
          <span class="mdl-list__item-secondary-info">L'entrée a été mise à jour avec succès</span>
          <a class="mdl-list__item-secondary-action" href="#"><i class="material-icons">check</i></a>
        </span><?php
      } else {
        ?>
        <span class="mdl-list__item-secondary-content">
          <span class="mdl-list__item-secondary-info">Erreur: <?php echo $conn->error ?></span>
          <a class="mdl-list__item-secondary-action" href="#"><i class="material-icons">error</i></a>
        </span><?php
      }

      $conn->close();
      }

    }


    ?>
      </ul>
    </div>
    </div>
  </body>
</html>
