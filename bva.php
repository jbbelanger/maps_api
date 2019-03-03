<table>
  <tr>
    <th>BVO</th>
    <th>Id BD BVO</th>
    <th>BVA</th>
    <th>Id BD BVA</th>
    <th>SQL</th>
    <th>Retour</th>
  </tr>
<?php

include("db_conn.php");
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset('utf8mb4');
$sql = "select
raw.`Circonscription` raw_circ_nom,
ci.id circ_id,
raw.`Nom_des_municipalités` raw_bva,
left(raw.`Nom_des_municipalités`,locate(\":\",raw.`Nom_des_municipalités`)-1) sv_bva_description,
sv.id sv_id

from raw_sv raw
left join (select id, circ_nom from circo where circ_id_carte = 4) ci on ci.circ_nom = raw.`Circonscription`
left join section_vote sv on sv.sevo_id_circo = ci.id and sv.sevo_description = left(raw.`Nom_des_municipalités`,locate(\":\",raw.`Nom_des_municipalités`)-1)

where `Nom_des_municipalités` like \"BVA%\"
order by ci.id";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $bva_bvo = [];
    preg_match('/(BVA([\d]{1,3}): SV no )(.+)/', $row["raw_bva"], $output_array);
    //$output_array[2] ==> 12 <== le # de bva
    //$output_array[3] ==> 59 à 60, 92, 101 à 102, 105 à 106 <== les groupes de SV

    $array_of_sv = explode(",",$output_array[3]);
    //$array_of_sv[0] ==> 59 à 60
    //$array_of_sv[1] ==> 92
    //$array_of_sv[2] ==>  101 à 102
    //$array_of_sv[3] ==>  105 à 106

    foreach ($array_of_sv as $sv_group) {
      if (strpos($sv_group,"à")) {
        preg_match_all('!\d+!', $sv_group, $matches);
        //var_dump($matches); echo "<br>";
        foreach (range((int) $matches[0][0],(int) $matches[0][1]) as $bvo) {
          array_push($bva_bvo,["bvo"=>$bvo,"bva"=>$output_array[2]]);
        }
      } else {
        array_push($bva_bvo,["bvo"=>(int) $sv_group,"bva"=>$output_array[2]]);
      }
    }
    $circ_id = $row["circ_id"];
    $bva_sv_id = $row["sv_id"];
    $bvo_sv_id;

    foreach ($bva_bvo as $correspondance):
      /*$bvo = $correspondance["bvo"];
      $conn = new mysqli($servername, $username, $password, $dbname);
      $conn->set_charset('utf8mb4');
      $sql = "SELECT sv.id
      FROM resultat_sv rs
      LEFT JOIN section_vote sv ON sv.id = rs.resv_id_sv
      WHERE sv.sevo_id_circo = $circ_id and sv.sevo_description+0 = $bvo";
      $result = $conn->query($sql);
      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          $bvo_sv_id = $row["id"];
        }
      }*/
      ?>
        <tr>
          <td><?php echo $correspondance["bvo"] ?></td>
          <td><?php echo $bvo_sv_id ?></td>
          <td><?php echo $correspondance["bva"] ?></td>
          <td><?php echo $bva_sv_id ?></td>

      <?php
      $bvo = $correspondance["bvo"];
      $servername = "hp187.hostpapa.com";
      $username = "jbeno958_admin";
      $password = "vibe2192";
      $dbname = "jbeno958_election";
      $conn = new mysqli($servername, $username, $password, $dbname);
      $conn->set_charset('utf8mb4');
      $update_sql = "UPDATE section_vote sv
      SET sv.sevo_regr_bva = $bva_sv_id
      WHERE sv.sevo_id_circo = $circ_id and sv.sevo_description+0 = $bvo";

      if ($conn->query($update_sql) === TRUE) {
        echo "<td>" . $update_sql . "</td>";
        echo "<td>L'entrée a été mise à jour</td>";
      } else {
        echo "<td>" . $update_sql . "</td>";
        echo "<td>Erreur</td>";
      }
      echo "</tr>";
    endforeach;
  }
} else {
  echo "<pre><code>\$result->num_rows < 0</code></pre> ou quelque chose d'autre<br><pre><code>$sql<code></pre>";
}

//$input_line = "BVA5: SV no 49, 61, 74 à 76";

?>
</table>
