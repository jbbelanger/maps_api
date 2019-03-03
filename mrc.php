<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.grey-deep_orange.min.css" />
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/chroma-js/2.0.3/chroma.min.js"></script>
    <title>Résultats par MRC</title>
    <meta name="viewport" content="initial-scale=1.0">
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
          width: 78%;
        }
        #info {
          height: 100%;
          width: 22%;
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
        font-size: 1.1em;
        line-height: 1em;
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
        font-size: 1em;
        line-height: 1em;
      }
      p#polygon_stats {
        margin-top: 0;
        padding: 0 10px 0;
        line-height: 1em;
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
        line-height: 1.1em;
      }
      .depute_circo {
        font-size: 0.9em;
      }
      ul#députés li{
          margin-bottom: 5px;
          border-left: 12px solid;
          padding-left: 10px;
          line-height: 1.4em;
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
mr.id id,
mr.murc_nom murc_nom,
rg.regi_nom,
ps.pasv_bv nb_bv,
ps.pasv_br nb_br,
ps.pasv_ei nb_ei,
replace(group_concat(rs.part_nom_usuel,\",\",rs.part_couleur,\",\",rs.resv_bv,\",\",(rs.resv_bv/ps.pasv_bv) order by rs.resv_bv desc separator \";;\"),\"'\",\"'\") mnas,
ga.part_couleur,
mr.murc_polygone geometrie
from (select
	r.resv_id_election,
	c.id resv_id_mrc,
	c.murc_nom,
	p.part_nom_usuel,
	p.part_couleur,
	sum(resv_bv) resv_bv
	from resultat_sv r
	inner join section_vote s on s.id = r.resv_id_sv
	inner join municipalite m on m.id = s.sevo_id_municipalite
	left join mrc c on c.id = m.muni_id_mrc
	left join parti p on p.id = r.resv_id_parti

	group by p.id, c.id
	order by c.murc_nom, resv_bv desc) rs
left join mrc mr on mr.id = rs.resv_id_mrc
left join region rg on rg.id = mr.murc_id_region
left join (select
	t.pasv_id_election,
	c.id pasv_id_mrc,
	c.murc_nom,
	sum(t.pasv_bv) pasv_bv,
	sum(t.pasv_br) pasv_br,
	sum(t.pasv_ei) pasv_ei
	from participation_sv t
	inner join section_vote s on s.id = t.pasv_id_sv
	inner join municipalite m on m.id = s.sevo_id_municipalite
	left join mrc c on c.id = m.muni_id_mrc

	group by c.id) ps on ps.pasv_id_mrc = rs.resv_id_mrc and ps.pasv_id_election = rs.resv_id_election
left join (select
rsv.mrc_id,
rsv.part_abb_usuelle,
rsv.part_couleur
from (select
	com.id mrc_id,
	com.murc_nom,
	par.part_abb_usuelle,
	par.part_couleur
	from resultat_sv res
	inner join section_vote sec on sec.id = res.resv_id_sv
	inner join municipalite mun on mun.id = sec.sevo_id_municipalite
	left join mrc com on com.id = mun.muni_id_mrc
	left join parti par on par.id = res.resv_id_parti

	group by par.id, com.id
	order by com.murc_nom, sum(res.resv_bv) desc) rsv

group by rsv.mrc_id) ga on ga.mrc_id = rs.resv_id_mrc

where
rs.resv_id_election = 42 and
mr.murc_polygone is not null

group by mr.id
order by rs.resv_bv;";
    $row;
    $result = $conn->query($sql);
    $regions_geometry_array = [];
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        array_push($regions_geometry_array, [
          "id"=>$row["id"],
          "nom"=>$row["murc_nom"],
          "nom_region"=>$row["regi_nom"],
          "nb_bv"=>$row["nb_bv"],
          "nb_br"=>$row["nb_br"],
          "nb_ei"=>$row["nb_ei"],
          "couleur"=>$row["part_couleur"],
          "mnas"=>json_encode(explode(";;",$row["mnas"])),
          "geometry"=>$row["geometrie"]
        ]);
      }
    } else {
      echo "<pre><code>\$result->num_rows < 0</code></pre> ou quelque chose d'autre<br><pre><code>$sql<code></pre>";
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
      <a href="/maps">
        <button class="mdl-button mdl-js-button mdl-button--raised" onclick="navigate()">
          Changer de type de carte
        </button>
      </a>
      <h3 id="polygon_name">Résultats par section de vote</h3>
      <h5 id="municipalite_name"></h5>
      <p id="polygon_stats"></p>
      <ul id="députés">Cliquez sur une section de vote pour voir les résultats</ul>
    </div>
    <div id="map"></div>

    <script>
      function drawMap(poly) {
        new google.maps.Polygon({paths:JSON.parse(poly)}).setMap(map)
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
      function spaceForThousands(xy) {
          return xy.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
      }
      function calculateArea(a) {
        var areas = [];
        const reducer = (accumulator, currentValue) => accumulator + currentValue;
        for (let path of a.latLngs.j) {
          areas.push(google.maps.geometry.spherical.computeArea(path));
        }
        return Math.round(areas.reduce(reducer)/1000000);
      }
      function showInfoDtails(region) {
        region.addListener("click",function () {
          document.getElementById("députés").innerHTML = "";
          var nb_c = region.nb_circos;
          var nb_e = region.nb_ei;
          var area = calculateArea(region);
          document.getElementById("polygon_name").innerHTML = `MRC de ${region.name}`;
          document.getElementById("municipalite_name").innerHTML = region.region;
          document.getElementById("polygon_stats").innerHTML = `
            ${spaceForThousands(area)} km<sup>2</sup><br>
            ${spaceForThousands(nb_e)} électeurs<br>
            ${spaceForThousands(region.nb_bv)} bulletins valides<br>
            ${spaceForThousands(region.nb_br)} bulletins rejetés<br>
            ${formatPercent((region.nb_bv+ region.nb_br)/nb_e)} de participation au jour J`;
          for (let mna of region.mnas) {
            var mna_props = mna.split(",");
            document.getElementById("députés").innerHTML +=
            `<li class="mdl-shadow--2dp" style="border-left-color: #${mna_props[1]}">
              <span class="depute_nom">${mna_props[0]}</span><br>
              <span class="depute_circo">${spaceForThousands(mna_props[2])} votes (${formatPercent(mna_props[3])})</span>
            </li>`;
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
                name: \"".$regions_geometry_array[$i]["nom"]."\",
                region: \"".$regions_geometry_array[$i]["nom_region"]."\",
                nb_bv: ".$regions_geometry_array[$i]["nb_bv"].",
                nb_br: ".$regions_geometry_array[$i]["nb_br"].",
                nb_ei: ".$regions_geometry_array[$i]["nb_ei"].",
                paths: JSON.parse('".$regions_geometry_array[$i]["geometry"]."'),
                mnas: JSON.parse('".$regions_geometry_array[$i]["mnas"]."'),
                strokeColor: \"#".$regions_geometry_array[$i]["couleur"]."\",
                strokeOpacity: 1,
                strokeWeight: 1,
                fillColor: \"#".$regions_geometry_array[$i]["couleur"]."\",
                fillOpacity: 0.5,
              }),";
            }
          ?>
        ];
        map = new google.maps.Map(document.getElementById('map'), {
          center: {lat: 47.487692, lng: -75.025446},
          zoom: 7,
          disableDefaultUI: true
        });

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
