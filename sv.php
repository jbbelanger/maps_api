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
      /* Optional: Makes the sample page fill the window. */
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
    </style>
  </head>
  <body>
    <?php include("db_conn.php");
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset('utf8mb4');
    if (isset($_GET["e"])) {
      $election = $_GET["e"];
    } else {
      $election = 42;
    }
    if (isset($_GET["p"])) {
      $parti = "and r.resv_id_parti = ".$_GET["p"];
    }
    if (isset($_GET["c"])) {
      $circo = "and cc.id = ".$_GET["c"];
    }
    $sql = "select
ci.id id,
ci.sevo_description sevo_description,
mu.muni_nom muni_nom,
sum(distinct pc.pasv_ei) nb_elects,
replace(group_concat(pe.pers_prenom,\",\",pe.pers_nom,\",\",ci.sevo_description,\",\",pa.part_couleur,\",\",pa.part_abb_usuelle,\",\",re.resv_bv,\",\",(re.resv_bv/pc.pasv_bv) order by re.resv_rang separator \";;\"),\"'\",\"&#39;\") mnas,
(select
    p.part_couleur couleur_parti

    from resultat_sv r
    left join parti p on p.id = r.resv_id_parti

    where
    r.resv_id_election = $election and
    r.resv_id_sv = re.resv_id_sv $parti

    group by r.resv_id_parti

    order by sum(r.resv_bv) desc
    limit 0,1
) couleur_parti,
(select
    r.resv_bv / p.pasv_bv

    from resultat_sv r
	left join participation_sv p on p.pasv_id_election = r.resv_id_election and p.pasv_id_sv = r.resv_id_sv

    where
    r.resv_id_election = $election and
    r.resv_id_sv = re.resv_id_sv $parti

    group by r.resv_id_parti

    order by sum(r.resv_bv) desc
    limit 0,1
) pourcentage_parti,
ci.sevo_polygone regi_geometry
from resultat_sv re
left join section_vote ci on ci.id = re.resv_id_sv
left join municipalite mu on mu.id = ci.sevo_id_municipalite
left join circo cc on cc.id = ci.sevo_id_circo
left join region rg on rg.id = cc.circ_id_region
left join personne pe on pe.id = re.resv_id_personne
left join parti pa on pa.id = re.resv_id_parti
left join participation_sv pc on pc.pasv_id_election = re.resv_id_election and pc.pasv_id_sv = re.resv_id_sv

where
re.resv_id_election = $election and
ci.sevo_polygone is not null

group by ci.id
order by re.resv_bv;";

    $row;
    $result = $conn->query($sql);
    $regions_geometry_array = [];
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        array_push($regions_geometry_array, [
          "id"=>$row["id"],
          "sevo_description"=>$row["sevo_description"],
          "muni_nom"=>$row["muni_nom"],
          "nb_elects"=>$row["nb_elects"],
          "couleur"=>$row["couleur_parti"],
          "pourcentage_parti"=>$row["pourcentage_parti"],
          "mnas"=>json_encode(explode(";;",$row["mnas"])),
          "geometry"=>$row["regi_geometry"]
        ]);
      }
    } else {
      echo "<pre><code>\$result->num_rows < 0</code></pre> ou quelque chose d'autre<br><pre><code>$sql<code></pre>";
    }
    $conn->close();

    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset('utf8mb4');
    $sql_election = "select
elec_nom
from election
where el.id = $election";
    $result = $conn->query($sql_election);
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $election_name = $row["elec_nom"];
        $nb_elects_nat = $row["pasv_ei"];
      }
    } else {

    }

    ?>
    <div id="info">
      <h3 id="polygon_name">Résultats par section de vote</h3>
      <h5 id="election_name"><?php echo $election_name; ?></h5>
      <p id="polygon_stats"></p>
      <ul id="députés">Cliquez sur une région pour commencer</ul>
    </div>
    <div id="map"></div>

    <script>
      function drawMap(poly) {
        new google.maps.Polygon({paths:JSON.parse(poly)}).setMap(map)
      }
      function LightenDarkenColor(col,amt) {
        var usePound = false;
        if ( col[0] == "#" ) {
            col = col.slice(1);
            usePound = true;
        }

        var num = parseInt(col,16);

        var r = (num >> 16) + amt;

        if ( r > 255 ) r = 255;
        else if  (r < 0) r = 0;

        var b = ((num >> 8) & 0x00FF) + amt;

        if ( b > 255 ) b = 255;
        else if  (b < 0) b = 0;

        var g = (num & 0x0000FF) + amt;

        if ( g > 255 ) g = 255;
        else if  ( g < 0 ) g = 0;

        return (usePound?"#":"") + (g | (b << 8) | (r << 16)).toString(16);
      }
      function formatPercent(n) {
        return +(Math.round(100* n + "e+2")  + "e-2").toString().replace(".", ",")+" %";
      }
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
      /*function ecartMoyenneCirco(e,c,r) {
        moyNat = <?php echo $nb_elects_nat; ?>/125;
        moyReg = e/c;
        console.log("Moyenne " + r + " " + moyReg);
        console.log("Écart " + r + " " + 100*((moyReg-moyNat)/moyNat));
      }*/
      const urlPersonnes = `http://jbenoit-belanger.com/2018/?ctx=110&pe=`;
      function showInfoDtails(region) {
        region.addListener("click",function () {
          document.getElementById("députés").innerHTML = "";
          var nb_c = region.nb_circos;
          var nb_e = region.nb_elects;
          //ecartMoyenneCirco(nb_e,nb_c,region.name);
          var area = calculateArea(region);
          document.getElementById("polygon_name").innerHTML = region.name;
          document.getElementById("polygon_stats").innerHTML = `
            ${spaceForThousands(area)} km<sup>2</sup><br>
            ${spaceForThousands(nb_e)} électeurs`;
          for (let mna of region.mnas) {
            var mna_props = mna.split(",");
            document.getElementById("députés").innerHTML +=
            `<li style="border-left-color: #${mna_props[3]}">
              <span class="depute_nom"><href="${urlPersonnes}${region.pers_id}">${mna_props[0]} ${mna_props[1]}</a> (${mna_props[4]})</span><br>
              <span class="depute_circo">${spaceForThousands(mna_props[5])} votes (${formatPercent(mna_props[6])})</span>
            </li>`
            //"<li class=\"" + mna_props[3] + "\"><span class=\"depute_nom\">" + mna_props[0] + " " + mna_props[1] + "</span><br><span class=\"depute_circo\">" + mna_props[2] + "</span></li>";
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
                nb_elects: ".$regions_geometry_array[$i]["nb_elects"].",
                name: \"SV ".$regions_geometry_array[$i]["sevo_description"]." - ".$regions_geometry_array[$i]["muni_nom"]."\",
                yolovar: JSON.parse('".$regions_geometry_array[$i]["geometry"]."'),
                paths: JSON.parse('".$regions_geometry_array[$i]["geometry"]."'),
                mnas: JSON.parse('".$regions_geometry_array[$i]["mnas"]."'),
                strokeColor: \"#".$regions_geometry_array[$i]["couleur"]."\",
                strokeOpacity: 1,
                strokeWeight: 1,
                pourcentage_gagnant: ".$regions_geometry_array[$i]["pourcentage_parti"].",
                DynamicFillColor: LightenDarkenColor(\"#".$regions_geometry_array[$i]["couleur"]."\",(0.5-".$regions_geometry_array[$i]["pourcentage_parti"].")*500),
                fillColor: \"#".$regions_geometry_array[$i]["couleur"]."\",
                fillOpacity: 0.5,
              }),";
            }
          ?>
        ];
        map = new google.maps.Map(document.getElementById('map'), {
          center: {lat: 47.487692, lng: -75.025446},
          zoom: 7,
          disableDefaultUI: true,
          mapTypeId: "roadmap",
          styles: [
            {
              "featureType": "administrative",
              "stylers": [{"visibility": "off"}]
            },{
              "featureType": "landscape",
              "stylers": [{"color": "#fdfdfd"}]
            },{
              "featureType": "landscape",
              "elementType": "labels",
              "stylers": [{"visibility": "off"}]
            },{
              "featureType": "poi",
              "stylers": [{"visibility": "off"}]
            },{
              "featureType": "road",
              "elementType": "labels.icon",
              "stylers": [{"visibility": "off"}]
            },{
              "featureType": "road",
              "elementType": "geometry",
              "stylers": [{"color": "#fbddb5"}]
            },{
              "featureType": "administrative.locality",
              "elementType": "labels.text",
              "stylers": [{"visibility": "on","color": "#404040"}]
            },{
              "featureType": "water",
              "stylers": [{"color": "#e7e9f7"}]
            }
          ]
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
