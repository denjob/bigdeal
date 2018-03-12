<?php
ini_set('memory_limit', '1024M');

echo 'test777';


function get_objects($where,$name=false) {

        global $epsg, $cnt_array;

		$db = pg_connect('host=195.128.137.105 port=5432 user=opengeo password=opengeo dbname=geoserver') 
			or die("Error ".pg_last_error());

        $columns="osm_id, ST_AsGeoJSON(ST_Transform(way,4326)) as way2, name, ward, \"healthcare:speciality\", information, description, social_facility, \"social_facility:for\", capacity, operator, official_name, official_status, phone, website, \"addr:full\", \"addr:city\", \"addr:district\", \"addr:postcode\", opening_hours, \"addr:hamlet\", \"addr:street\", fax, email, allhuman, adulthuman, childhuman, \"healthcare:heart\", \"healthcare:mind\", \"healthcare:maternity_light\", \"healthcare:maternity_hard\", \"healthcare:dtp\", \"ward:speciality_gynaecology\", \"ward:speciality_maternity\", \"ward:speciality_infectious_diseases\", \"ward:speciality_neurology\", \"ward:speciality_paediatrics\", \"ward:speciality_general\", \"ward:speciality_surgery\", \"internet_access:operator\", \"internet_access:speed\", \"wifi_access:ssid\"";
        $query="select ".$columns." from opengeo.test_point where ".$where;

        $result = pg_query($query);
        if (!$result) {
            echo "Проблема с запросом " . $query . "<br/>";
            echo pg_last_error();
            exit();
        }

        $geojson = array(
                         'type'      => 'FeatureCollection',
                         'features'  => array(),
                         'crs' => array(
                                        'type' => 'EPSG',
                                        'properties' => array('code' => '4326')
                         )
        );
        while($myrow = pg_fetch_assoc($result)) {

		$gos18_work = array();
		if($name=="gos18") {
			$query_gos18_work = "select * from opengeo.gos18_work where obj=".$myrow["osm_id"];
			$result_gos18_work = pg_query($query_gos18_work);
			if (!$result_gos18_work) {
				echo "Проблема с запросом " . $query_gos18_work . "<br/>";
				echo pg_last_error();
				exit();
			}
			while($myrow_gos18 = pg_fetch_assoc($result_gos18_work)) {
				$gos18_work[] = array(
					'workyear' => $myrow_gos18["workyear"],
					'worktype' => $myrow_gos18["worktype"],
				);
			}
		}

              $feature = array(
                               'type' => 'Feature',
                               'id' => $myrow["osm_id"],
                               'layer' => $epsg,
                               'geometry' => json_decode($myrow["way2"], true),
                               'geometry_name' => 'way',
                               'properties' => array(
                                                     'name' => $myrow["name"],
                                                     'ward' => $myrow["ward"],
                                                     'healthcarespeciality' => $myrow["healthcare:speciality"],
                                                     'information' => $myrow["information"],
                                                     'description' => $myrow["description"],
                                                     'social_facility' => $myrow["social_facility"],
                                                     'social_facilityfor' => $myrow["social_facility:for"],
                                                     'capacity' => $myrow["capacity"],
                                                     'operator' => $myrow["operator"],
                                                     'official_name' => $myrow["official_name"],
                                                     'official_status' => $myrow["official_status"],
                                                     'phone' => $myrow["phone"],
                                                     'website' => $myrow["website"],
                                                     'addrfull' => $myrow["addr:full"],
                                                     'addrcity' => $myrow["addr:city"],
                                                     'addrdistrict' => $myrow["addr:district"],
                                                     'addrpostcode' => $myrow["addr:postcode"],
                                                     'opening_hours' => $myrow["opening_hours"],
                                                     'addrhamlet' => $myrow["addr:hamlet"],
                                                     'addrstreet' => $myrow["addr:street"],
                                                     'fax' => $myrow["fax"],
                                                     'email' => $myrow["email"],
                                                     'allhuman' => $myrow["allhuman"],
                                                     'adulthuman' => $myrow["adulthuman"],
                                                     'childhuman' => $myrow["childhuman"],
                                                     'healthcareheart' => $myrow["healthcare:heart"],
                                                     'healthcaremind' => $myrow["healthcare:mind"],
                                                     'healthcarematernity_light' => $myrow["healthcare:maternity_light"],
                                                     'healthcarematernity_hard' => $myrow["healthcare:maternity_hard"],
                                                     'healthcaredtp' => $myrow["healthcare:dtp"],
                                                     'wardspeciality_gynaecology' => $myrow["ward:speciality_gynaecology"],
                                                     'wardspeciality_maternity' => $myrow["ward:speciality_maternity"],
                                                     'wardspeciality_infectious_diseases' => $myrow["ward:speciality_infectious_diseases"],
                                                     'wardspeciality_neurology' => $myrow["ward:speciality_neurology"],
                                                     'wardspeciality_general' => $myrow["ward:speciality_general"],
                                                     'wardspeciality_surgery' => $myrow["ward:speciality_surgery"],
                                                     'wardspeciality_paediatrics' => $myrow["ward:speciality_paediatrics"],
                                                     'internet_accessoperator' => $myrow["internet_access:operator"],
                                                     'internet_accessspeed' => $myrow["internet_access:speed"],
                                                     'wifi_accessssid' => $myrow["wifi_access:ssid"],
						     'gos18_work' => $gos18_work,
                               )
              );
              // Add feature array to feature collection array
              array_push($geojson['features'], $feature);

        }

        // Close database connection
        pg_close($db);

        header('Content-type: application/json',true);
        if ($epsg=="cnt") {
          $cnt = array(
             'name' => $name."",
             'count' => count($geojson[features]).""
          );
          array_push($cnt_array, $cnt);
        }else {
          echo json_encode($geojson);
        }

}


?>