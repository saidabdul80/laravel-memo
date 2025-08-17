<?php

return [
    'pagination_length' => 15,
    "members_models"=>[App\User::class],
    "office_model"=>[App\Office::class],
    "name" =>['full_name'],
    "department_model"=>App\Models\Department::class,
    "role_column_name"=>'role_id',
    "user_department_id_column"=>'department_id',
    "user_office_id_column"=>'office_id',
    "members_models_filters"=>null, //[  ["type"=>"staff"]  ]
    'load_routes' => true,
];
