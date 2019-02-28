<!DOCTYPE html>
<html lang="fr" dir="ltr">
  <head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
     <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.grey-deep_orange.min.css" />
  <script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
    <title>Liste des fichiers KML</title>
  </head>
  <body>
    <div class="mdl-grid">
      <div class="mdl-cell mdl-cell--1-col"></div>
      <div class="mdl-cell mdl-cell--4-col mdl-shadow--2dp">
        <h3 style="text-align:center; margin-bottom: 0;">Fichiers à traiter</h4>
        <h6 style="text-align:center; margin-top: 0;">Polygones simples (<?php $simple_ite =  new GlobIterator('simple-geo/*.kml'); echo $simple_ite->count(); ?>)</h6>
        <ul class=" mdl-list">
          <?php foreach (glob('simple-geo/*.kml') as $input): ?>
            <?php if (strpos(@file_get_contents($input), 'MultiGeometry') !== false): ?>
              <li class="mdl-list__item mdl-list__item--two-line">
                <span class="mdl-list__item-primary-content">
                  <i class="material-icons mdl-list__item-icon">code</i>
                  <a href="<?php echo $input; ?>">
                    <?php
                    $rawData = @file_get_contents($input);
                    $data = new SimpleXMLElement($rawData);
                    echo $data->{'Document'}->{'Folder'}->{'Placemark'}->{'name'};
                     ?>
                   </a>
                  <span class="mdl-list__item-sub-title"><?php echo $input; ?></span>
                </span>
                <span class="mdl-list__item-secondary-content">
                  <span class="mdl-list__item-secondary-info">{Polygon}</span>
                  <a class="mdl-list__item-secondary-action" href="#"><i class="material-icons">filter_1</i></a>
                </span>
              </li>
              <?php else: ?>
              <li class="mdl-list__item mdl-list__item--two-line">
                <span class="mdl-list__item-primary-content">
                  <i class="material-icons mdl-list__item-icon">code</i>
                  <a href="<?php echo $input; ?>">
                    <?php
                    $rawData = @file_get_contents($input);
                    $data = new SimpleXMLElement($rawData);
                    echo $data->{'Document'}->{'Folder'}->{'Placemark'}->{'name'};
                     ?>
                   </a>
                  <span class="mdl-list__item-sub-title"><?php echo $input; ?></span>
                </span>
                <span class="mdl-list__item-secondary-content">
                  <span class="mdl-list__item-secondary-info">{MultiGeometry}->{Polygon}</span>
                  <a class="mdl-list__item-secondary-action" href="#"><i class="material-icons">filter_2</i></a>
                </span>
              </li>
            <?php endif; ?>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="mdl-cell mdl-cell--2-col"></div>
      <div class="mdl-cell mdl-cell--4-col mdl-shadow--2dp">
        <h3 style="text-align:center; margin-bottom: 0;">Fichiers à traiter</h4>
        <h6 style="text-align:center; margin-top: 0;">Polygones multiples (<?php $simple_ite =  new GlobIterator('multi-geo/*.kml'); echo $simple_ite->count(); ?>)</h6>
        <ul class=" mdl-list">
          <?php foreach (glob('multi-geo/*.kml') as $input): ?>
            <?php if (strpos(@file_get_contents($input), 'MultiGeometry') !== false): ?>
              <li class="mdl-list__item mdl-list__item--two-line">
                <span class="mdl-list__item-primary-content">
                  <i class="material-icons mdl-list__item-icon">code</i>
                  <a href="<?php echo $input; ?>">
                    <?php
                    $rawData = @file_get_contents($input);
                    $data = new SimpleXMLElement($rawData);
                    echo $data->{'Document'}->{'Folder'}->{'Placemark'}->{'name'};
                     ?>
                   </a>
                  <span class="mdl-list__item-sub-title"><?php echo $input; ?></span>
                </span>
                <span class="mdl-list__item-secondary-content">
                  <span class="mdl-list__item-secondary-info">{Polygon}</span>
                  <a class="mdl-list__item-secondary-action" href="#"><i class="material-icons">filter_1</i></a>
                </span>
              </li>
              <?php else: ?>
              <li class="mdl-list__item mdl-list__item--two-line">
                <span class="mdl-list__item-primary-content">
                  <i class="material-icons mdl-list__item-icon">code</i>
                  <a href="<?php echo $input; ?>">
                    <?php
                    $rawData = @file_get_contents($input);
                    $data = new SimpleXMLElement($rawData);
                    echo $data->{'Document'}->{'Folder'}->{'Placemark'}->{'name'};
                     ?>
                   </a>
                  <span class="mdl-list__item-sub-title"><?php echo $input; ?></span>
                </span>
                <span class="mdl-list__item-secondary-content">
                  <span class="mdl-list__item-secondary-info">{MultiGeometry}->{Polygon}</span>
                  <a class="mdl-list__item-secondary-action" href="#"><i class="material-icons">filter_2</i></a>
                </span>
              </li>
            <?php endif; ?>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </body>
</html>
