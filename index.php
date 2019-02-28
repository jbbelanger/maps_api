<!DOCTYPE html>
<html>
  <head>
    <title>Carte des régions administratives</title>
    <meta name="viewport" content="initial-scale=1.0">
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="http://localhost:8888/maps_api/css/style.css"></link>
    <style>
      /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
       body {
         display: flex;
         width: 100%;
         overflow: hidden;
       }
      @media only screen and (max-width: 736px){
        body {
          flex-direction: column-reverse;
        }
        #map {
          height: 50%;
          width: 100%;
        }
        #info {
          height: 50%;
          width: 100%;
        }
        #info h3 {
          width: calc(54% + 5%);
          margin-top: -15px;
        }
        #info h5 {
          width: calc(54% + 4%);
        }
        ul#députés {
          height: calc(100% - 150px);
        }
      }
      @media only screen and (min-width: 737px){
        body {
          flex-direction: row;
        }
        #map {
          height: 100%;
          width: 80%;
        }
        #info {
          height: 100%;
          width: 20%;
        }
        #info h3 {
          width: calc(94% + 5%);
        }
        #info h5 {
          width: calc(94% + 4%);
        }
      }
      #info {
        font-family: "Guillon";
        background-color: #f3f0ee;
        box-shadow: 0 0 6px 6px rgba(0,0,0,0.2);
        z-index: 100;
        padding: 5px;
      }
      #info h3 {
        text-align: right;
        color: #fff;
        background-color: #28347c;
        font-weight: 300;
        padding: 6px 10px 4px;
        margin-left: -5px;
        box-shadow: 1px 1px 4px 2px rgba(0,0,0,0.1);
      }
      #info h5 {
        text-align: right;
        color: #fff;
        background-color: #17173d;
        font-weight: 300;
        padding: 6px 10px 4px;
        box-shadow: 1px 1px 4px 2px rgba(0,0,0,0.1);
        margin: -18px 0 10px -5px;
        z-index: -1;
      }
      p#polygon_stats {
        margin-top: 0;
        padding: 0 10px 0;
      }
      ul#députés {
        list-style-type: none;
        padding: 0 10px 0;
        height: calc(100% - 200px);
        overflow-y: scroll;
      }
      .depute_nom {
        font-weight: 400;
        font-size: 1.1em;
      }
      .depute_circo {
        font-size: 0.9em;
      }
      ul#députés li{
          margin-bottom: 5px;
          border-left: 12px solid;
          padding-left: 10px;
      }
      ul#députés li.QS{
          border-left-color: #ff5505;
      }
      ul#députés li.CAQ{
          border-left-color: #00a7e7;
      }
      ul#députés li.PLQ{
          border-left-color: #ee1b2e;
      }
      ul#députés li.PQ{
          border-left-color: #004085;
      }
      ul#députés li.PVQ{
          border-left-color: #009430;
      }
      ul#députés li.ADQ{
          border-left-color: #9370db;
      }
      /* Optional: Makes the sample page fill the window. */
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
    </style>
  </head>
  <body>
    <?php include("convert.php"); ?>
    <?php include("db_conn.php");
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset('utf8mb4');
    if (isset($_GET["e"])) {
      $election = $_GET["e"];
    } else {
      $election = 42;
    }
    if (isset($_GET["p"])) {
      $rang = $_GET["p"];
    } else {
      $rang = 1;
    }
    $sql = "select
rg.id id,
rg.regi_nom regi_nom,
count(ci.id) nb_circos,
sum(pc.parc_éi) nb_elects,
replace(group_concat(pe.pers_prenom,\",\",pe.pers_nom,\",\",ci.circ_nom,\",\",pa.part_abb_usuelle order by ci.circ_nom separator \";;\"),\"'\",\"&#39;\") mnas,
rg.regi_geometry regi_geometry
from resultat re
left join circo ci on ci.id = re.resu_id_circo
left join region rg on rg.id = ci.circ_id_region
left join personne pe on pe.id = re.resu_id_personne
left join parti pa on pa.id = re.resu_id_parti
left join participation pc on pc.parc_id_election = re.resu_id_election and pc.parc_id_circo = re.resu_id_circo

where re.resu_id_election = $election and re.resu_rang = $rang

group by rg.id";
    $row;
    $result = $conn->query($sql);
    $regions_geometry_array = [];
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        array_push($regions_geometry_array, [
          "id"=>$row["id"],
          "nom"=>$row["regi_nom"],
          "nb_circos"=>$row["nb_circos"],
          "nb_elects"=>$row["nb_elects"],
          "mnas"=>json_encode(explode(";;",$row["mnas"])),
          "geometry"=>$row["regi_geometry"]
        ]);
      }
    } else {
      echo "<pre><code>\$result->num_rows < 0</code></pre> ou quelque chose d'autre";
    }
    $conn->close();

    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset('utf8mb4');
    $sql_election = "select
el.elec_nom,
sum(pc.parc_éi) parc_ei
from participation pc
left join election el on el.id = pc.parc_id_election
where el.id = $election
group by el.id";
    $result = $conn->query($sql_election);
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $election_name = $row["elec_nom"];
        $nb_elects_nat = $row["parc_ei"];
      }
    } else {

    }

    ?>
    <div id="info">
      <h3 id="polygon_name">Député·e·s par région</h3>
      <h5 id="election_name"><?php echo $election_name; ?></h5>
      <p id="polygon_stats"></p>
      <ul id="députés">Cliquez sur une région pour commencer</ul>
    </div>
    <div id="map"></div>

    <script>

      function makePlural(n,string) {
        if (n > 1) {
          return n + " " + string + "s";
        } else {
          return n + " " + string;
        }
      }
      function spaceForThousands(x) {
          return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
      }
      function calculateArea(a) {
        var areas = [];
        const reducer = (accumulator, currentValue) => accumulator + currentValue;
        for (let path of a.latLngs.j) {
          areas.push(google.maps.geometry.spherical.computeArea(path));
        }
        return Math.round(areas.reduce(reducer)/1000000);
      }
      //e => nb d'électeurs // c => nb de circos
      function ecartMoyenneCirco(e,c,r) {
        moyNat = <?php echo $nb_elects_nat; ?>/125;
        moyReg = e/c;
        console.log("Moyenne " + r + " " + moyReg);
        console.log("Écart " + r + " " + 100*((moyReg-moyNat)/moyNat));
      }

      function showInfoDtails(region) {
        region.addListener("click",function () {
          document.getElementById("députés").innerHTML = "";
          var nb_c = region.nb_circos;
          var nb_e = region.nb_elects;
          ecartMoyenneCirco(nb_e,nb_c,region.name);
          var area = calculateArea(region);
          if (region.id < 10) {
            document.getElementById("polygon_name").innerHTML = "0" + region.id + " - " + region.name;
          } else {
            document.getElementById("polygon_name").innerHTML = region.id + " - " + region.name;
          }
          document.getElementById("polygon_stats").innerHTML = `
            ${spaceForThousands(area)} km<sup>2</sup><br>
            ${spaceForThousands(nb_e)} électeurs<br>
            ${makePlural(nb_c,"circonscription")}`;
          for (let mna of region.mnas) {
            var mna_props = mna.split(",");
            document.getElementById("députés").innerHTML += "<li class=\"" + mna_props[3] + "\"><span class=\"depute_nom\">" + mna_props[0] + " " + mna_props[1] + "</span><br><span class=\"depute_circo\">" + mna_props[2] + "</span></li>";
          }
        });
      }

      function initMap() {
        rgnStrokeColor = "#afa197";
        rgnFillColor = "#d1c2ba";
        regions = [
          <?php
            for ($i = 0, $c = count($regions_geometry_array); $i < $c; $i++) {
              echo "new google.maps.Polygon({
                id: ".$regions_geometry_array[$i]["id"].",
                nb_circos: ".$regions_geometry_array[$i]["nb_circos"].",
                nb_elects: ".$regions_geometry_array[$i]["nb_elects"].",
                name: \"".$regions_geometry_array[$i]["nom"]."\",
                paths: JSON.parse('".$regions_geometry_array[$i]["geometry"]."'),
                mnas: JSON.parse('".$regions_geometry_array[$i]["mnas"]."'),
                strokeColor: rgnStrokeColor,
                strokeOpacity: 0.8,
                strokeWeight: 5,
                fillColor:rgnFillColor,
                fillOpacity: 0,
              }),";
            }
          ?>
        ];
        map = new google.maps.Map(document.getElementById('map'), {
          center: {lat: 47.487692, lng: -75.025446},
          zoom: 7,
          disableDefaultUI: true
        });

        /*var j;
        for (j = 0; j < polygones.length; ++j) {
          circo = new google.maps.Polygon({
            paths: polygones[j].coords,
            strokeColor: polygones[j].color,
            strokeOpacity: 1,
            strokeWeight: 1,
            fillColor: polygones[j].color,
            fillOpacity: 0.45,
            name: polygones[j].nom
            //draggable: true,
            //geodesic: true
          });
          circo.setMap(map);
        }*/

        for (let region of regions) {
          region.setMap(map);
          showInfoDtails(region);
        }
      }

    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDltHXvhv9M7PYZMJmX8cYvMoiqJv8gQ1E&callback=initMap"
    async defer></script>
  </body>
</html>
