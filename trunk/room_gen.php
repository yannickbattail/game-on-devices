<?php

$rooms = array(
			array(
				"name" => "bathroom",
				"actions" => array(
								array(
									"id" => 1,
									"verb" => 'cut',
									"nouns" => array(
												array("id" => 1, "value" => "tree"),
												array("id" => 2, "value" => "power")
											   )
								)
							 )
			),
			array(
				"name" => "outside",
				"actions" => array(
								array(
									"id" => 1,
									"verb" => 'plant',
									"nouns" => array(
												array("id" => 1, "value" => 'tree')
											   )
								)
							)
			)
);

file_put_contents('rooms_generated.json', json_encode($rooms));