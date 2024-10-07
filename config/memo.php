<?php

return [
    'pagination_length' => 15,

    "members_models"=>[App\User::class],
    "department_model"=>App\Models\Department::class,
    "name" =>['full_name'],
    "members_models_filters"=>null //[["type"=>"staff"]]
    
    
];
