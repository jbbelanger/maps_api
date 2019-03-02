<?php
$input_line = "BVA5: SV no 49, 61, 74 à 76";

$bva_bvo = [];
preg_match('/(BVA([\d]{1,3}): SV no )(.+)/', $input_line, $output_array);
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
    var_dump($matches); echo "<br>";
    foreach (range((int) $matches[0][0],(int) $matches[0][1]) as $bvo) {
      array_push($bva_bvo,["bvo"=>$bvo,"bva"=>$output_array[2]]);
    }
  } else {
    array_push($bva_bvo,["bvo"=>(int) $sv_group,"bva"=>$output_array[2]]);
  }
}


var_dump($input_line);
 ?>
<h3>Correspondance</h3>
<table>
  <tr>
    <th>BVO</th>
    <th>BVA</th>
  </tr>
  <?php foreach ($bva_bvo as $correspondance): ?>
    <tr>
      <td><?php echo $correspondance["bvo"] ?></td>
      <td><?php echo $correspondance["bva"] ?></td>
    </tr>
  <?php endforeach; ?>
</table>
