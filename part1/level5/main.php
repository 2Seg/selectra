<?php
/**
 * Created by PhpStorm.
 * User: eliottdes
 * Date: 18/12/17
 * Time: 13:32
 */

function outputGeneration ($jsonFile) {

    // Making sure the file is a json file
    if (pathinfo($jsonFile, PATHINFO_EXTENSION) != "json") {
        return "This file is not a json file.";
    }

    // Converting the json file in a workable array
    $data = json_decode(file_get_contents($jsonFile), true);

    $providers = $data['providers'];
    $users = $data['users'];
    $contract_modifications = $data['contract_modifications'];
    $contracts = $data['contracts'];


    $output = array("bills" => array());

    for ($i = 0; $i < count($contracts); $i++) {
        for ($j = 0; $j < count($providers); $j++) {
            for ($k = 0; $k < count($users); $k++) {

                if ($contracts[$i]['provider_id'] == $providers[$j]['id'] && $contracts[$i]['user_id'] == $users[$k]['id']) {

                    if ($contracts[$i]['green'] == 1) {
                        $rawBill = $providers[$j]['price_per_kwh'] * ($users[$k]['yearly_consumption'] - $users[$k]['yearly_consumption'] * 0.05);
                    } else {
                        $rawBill = $providers[$j]['price_per_kwh'] * $users[$k]['yearly_consumption'];
                    }


                    if ($contracts[$i]['contract_length'] <= 1) {
                        $billedAmount = $rawBill - $rawBill * 0.1;
                    } elseif ($contracts[$i]['contract_length'] <= 3) {
                        $billedAmount = $rawBill - $rawBill * 0.2;
                    } else {
                        $billedAmount = $rawBill - $rawBill * 0.25;
                    }

                    // date management
                    $start_date = new DateTime($contracts[$i]['start_date']);
                    $end_date = new DateTime($contracts[$i]['end_date']);
                    $contract_length = (int) $end_date->diff($start_date)->format("%Y");

                    $insurance_fee = ($contract_length * 365 * 0.05);

                    $provider_fee = $billedAmount - $insurance_fee;

                    $selectra_fee = round($provider_fee * (12.5 / 100), 2);

                    $output["bills"][$k]["commission"]['insurance_fee'] = $insurance_fee;
                    $output["bills"][$k]["commission"]['provider_fee'] = $provider_fee;
                    $output["bills"][$k]["commission"]['selectra_fee'] = $selectra_fee;

                    $output["bills"][$k]['id'] = $k + 1;
                    $output["bills"][$k]['price'] = $billedAmount;
                    $output["bills"][$k]['user_id'] = $users[$k]['id'];
                }
            }
        }
    }

    // Converting the array in json file
    $jsonOutput = json_encode($output);

    return $jsonOutput;

}



// Test :
echo outputGeneration("data.json");