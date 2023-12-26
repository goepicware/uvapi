<?php
if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}

class Foodpanda
{
    protected $ci;

    public $api_base_url;

    public $x_api_key;

    function __construct()
    {
        /*initialize the CI super-object*/
        $this->ci = &get_instance();

        $this->api_base_url = "";

        $this->x_api_key = "";

        $this->table = "clients";
        $this->ftable = "client_settings";
    }

    public function auth_token($url, $key, $password)
    {

        $url = $url . "/v2/login";

        $postvalue = [
            "username" => $key,

            "password" => $password,

            "grant_type" => "client_credentials",
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => [
                "Content-type: application/x-www-form-urlencoded",
            ],
            CURLOPT_POSTFIELDS => http_build_query($postvalue),
        ]);

        $response = curl_exec($curl);

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $information = curl_getinfo($curl);
        $err = curl_error($curl);
        curl_close($curl);

        $decode_value = json_decode($response);

        if ($err) {
            return json_decode($err);
        } else {
            $decode_value = json_decode($response);
        }
        return $decode_value;
    }
    
    public function getCategoryList($outletId, $app_id)
    {
        $categories = $this->getCategoryReccords($outletId, $app_id,$compid);

        if (!empty($categories)) {
            foreach ($categories as $cat) {
                $catlist = (object) [
                    "id" => $cat["pro_cate_id"],

                    "type" => "Category",

                    "title" => (object) [
                        "default" => $cat["menu_custom_title"],
                    ],

                    "description" => (object) [
                        "default" => $cat["menu_custom_title"],
                    ],

                    "products" => $this->getProductList(
                        $outletId,
                        $app_id,
                        $cat["pro_cate_id"]
                    ),
                ];
            }
        }

        return $catlist;
    }

    public function getProductLists($outletId, $app_id,$compid)
    {
        $productList = [];

        $aval = "634E6FA8-8DAF-4046-A494-FFC1FCF8BD11";
        $categories = $this->getCategoryReccords($outletId, $app_id,$compid);
        if (!empty($categories)) {
            foreach ($categories as $cat) {
                $catId = $cat["pro_cate_id"];

                $products_list = $this->foodpanda_products_list_get(
                    $app_id,
                    $catId,
                    $status = null,
                    $response = null,
                    $outletId,
                    $applyOutletId = "yes",
                    $aval
                );

                if (!empty($products_list)) {
                    foreach ($products_list as $value) {
                        foreach ($value["product_list"] as $produ) {
                            $productList[$produ["product_id"]] = [
                                "id" => $produ["product_id"],

                                "type" => "Product",
                            ];
                        }
                    }
                }
            }
        }
        return $productList;
    }
    public function getProductList($outletId, $app_id, $catId)
    {
        $productList = [];

        $aval = "634E6FA8-8DAF-4046-A494-FFC1FCF8BD11";

        $products_list = $this->foodpanda_products_list_get(
            $app_id,
            $catId,
            $status = null,
            $response = null,
            $outletId,
            $applyOutletId = "yes",
            $aval
        );

        if (!empty($products_list)) {
            foreach ($products_list as $value) {
                foreach ($value["product_list"] as $produ) {
                    $productList[$produ["product_id"]] = [
                        "id" => $produ["product_id"],
                        "type" => "Product",
                    ];
                }
            }
        }

        return $productList;
    }

    public function getModifierProductList($outletId, $app_id, $catId)
    {
        $aval = "634E6FA8-8DAF-4046-A494-FFC1FCF8BD11";

        $products_list = $this->foodpanda_products_list_get(
            $app_id,
            $catId,
            $status = null,
            $response = null,
            $outletId,
            $applyOutletId = "yes",
            $aval
        );
        if (!empty($products_list)) {
            $productList = [];

            foreach ($products_list as $value) {
                foreach ($value["product_list"] as $produ) {
                    if (!empty($produ["set_menu_component"])) {
                        foreach ($produ["set_menu_component"] as $setcom) {
                            if (!empty($setcom["modifiers"])) {
                                foreach ($setcom["modifiers"] as $modifier) {
                                    $productList[
                                        "topping" . $modifier["id"]
                                    ] = [
                                        "id" => "topping" . $modifier["id"],

                                        "type" => "Product",
                                    ];
                                }
                            }
                        }
                    } elseif (!empty($produ["modifiers"])) {
                        foreach ($produ["modifiers"] as $mod) {
                            if (!empty($mod["modifiers"])) {
                                foreach ($mod["modifiers"] as $modifiers) {
                                    $productList[
                                        "topping" . $modifiers["id"]
                                    ] = [
                                        "id" => "topping" . $modifiers["id"],
                                        "type" => "Product",
                                    ];
                                }
                            }
                        }
                    } 
                }
            }
        }

        return $productList;
    }

    public function getMainProductList($outletId, $app_id, $catId)
    {
        $aval = "634E6FA8-8DAF-4046-A494-FFC1FCF8BD11";

        $products_list = $this->foodpanda_products_list_get(
            $app_id,
            $catId,
            $status = null,
            $response = null,
            $outletId,
            $applyOutletId = "yes",
            $aval
        );
        $productList = [];
        if (!empty($products_list)) {
            

            foreach ($products_list as $value) {
                foreach ($value["product_list"] as $produ) {
                    $productList["pd" . $produ["product_id"]] = [
                        "id" => "pd" . $produ["product_id"],

                        "type" => "Product",
                    ];
                }
            }
        }

        return $productList;
    }
    public function getMainProductLists($outletId, $app_id,$compid)
    {
        $productList = [];
        $categories = $this->getCategoryReccords($outletId, $app_id,$compid);

        if (!empty($categories)) {
            foreach ($categories as $cat) {
                $catId = $cat["pro_cate_id"];

                $aval = "634E6FA8-8DAF-4046-A494-FFC1FCF8BD11";

                $products_list = $this->foodpanda_products_list_get(
                    $app_id,
                    $catId,
                    $status = null,
                    $response = null,
                    $outletId,
                    $applyOutletId = "yes",
                    $aval
                );

                if (!empty($products_list)) {
                    foreach ($products_list as $value) {
                        foreach ($value["product_list"] as $produ) {
                            $productList["pd" . $produ["product_id"]] = [
                                "id" => "pd" . $produ["product_id"],

                                "type" => "Product",
                            ];
                        }
                    }
                }
            }
        }
        return $productList;
    }
    public function getCategoryReccords($outletId, $app_id,$compid)
    {
        
        $catWhere = [
            "pro_cate_app_id" => $app_id,
            "pro_cate_company_id" => $compid,
            "pro_cate_status" => "A",
           
        ];

        $join = [];

        $join[0]["select"] = "";

        $join[0]["table"] = "category_assigned_outlets";

        $join[0]["condition"] =
            "pro_cate_primary_id=pao_category_primary_id AND pao_outlet_id='" .
            $outletId .
            "'";

        $join[0]["type"] = "INNER";

        $join[1]["select"] = "menu_custom_title";

        $join[1]["table"] = "menu_navigation";

        $join[1]["condition"] =
            "pro_cate_id=menu_category_id AND menu_type='main'";

        $join[1]["type"] = "INNER";

        $join[2]["select"] =
            "category_availability.cate_availability_id as cat_availability_id";

        $join[2]["table"] =
            "product_category_availability as category_availability";

        $join[2]["condition"] =
            "category_availability.cate_availability_category_primary_id = product_categories.pro_cate_primary_id AND cate_availability_type = 'Category' ";

        $join[2]["type"] = "INNER";

        $categories = $this->ci->Mydb->get_all_records(
            "pro_cate_id, menu_order, pro_cate_name, pro_cate_short_description, pro_cate_status",
            "product_categories",
            $catWhere,
            null,
            null,
            "menu_order",
            null,
            "pro_cate_primary_id",
            $join
        );

        return $categories;
    }

    public function productWithImageList($outletId, $app_id,$compid)
    {
        $aval = "634E6FA8-8DAF-4046-A494-FFC1FCF8BD11";
        $list = [];
        $categories = $this->getCategoryReccords($outletId, $app_id,$compid);
        if (!empty($categories)) {
            $catimgId = 1;

            foreach ($categories as $cat) {
                $catId = $cat["pro_cate_id"];
                $products_list = $this->foodpanda_products_list_get(
                    $app_id,
                    $catId,
                    $status = null,
                    $response = null,
                    $outletId,
                    $applyOutletId = "yes",
                    $aval
                );
                if (!empty($products_list)) {
                    foreach ($products_list as $value) {
                        $imgId = 1;

                        foreach ($value["product_list"] as $produ) {
                            if (empty($produ["set_menu_component"])) {
                                if (!empty($produ["product_thumbnail"])) {
                                    $productImage = base_url() ."media/dev_team/products/main-image/" .$produ["product_thumbnail"];
                                } else {

                                    $productImage = base_url() ."media/dev_team/products/main-image/" .$produ["product_thumbnail"];



                                    //$productImage = "https://assets.grab.com/wp-content/uploads/sites/8/2019/03/12120151/mcdo-fillet-o-fish-grabfood-delivery-700x700.jpg";
                                }

                                (object) [
                                    ($list[$produ["product_id"]] = (object) [
                                        "id" => $produ["product_id"],

                                        "type" => "Product",

                                        "title" => [
                                            "default" => ($produ["product_alias"] !== "" ? $produ["product_alias"] : $produ["product_name"]),
                                        ],

                                        "description" => (object) [
                                            "default" =>  ($produ["product_short_description"] !== ""?  strip_tags($produ["product_short_description"]) : $produ["product_name"]),
                                        ],

                                        "price" => $produ["product_price"],

                                        "active" => true,

                                        "isPrepackedItem" => false,

                                        "isExpressItem" => false,

                                        "excludeDishInformation" => false,

                                        "images" => (object) [
                                            "image-" .
                                            $catimgId .
                                            $imgId => (object) [
                                                "id" =>
                                                    "image-" .
                                                    $catimgId .
                                                    $imgId,

                                                "type" => "Image",
                                            ],
                                        ],
                                    ]),
                                ];

                                $list[
                                    "image-" . $catimgId . $imgId
                                ] = (object) [
                                    "url" => $productImage,
                                       /// "https://assets.grab.com/wp-content/uploads/sites/8/2019/03/12120151/mcdo-fillet-o-fish-grabfood-delivery-700x700.jpg",
                                    //'url' => $produ['product_thumbnail'],
                                    "id" => "image-" . $catimgId . $imgId,

                                    "type" => "Image",
                                ];

                                $imgId++;
                            } else {
                                foreach (
                                    $produ["set_menu_component"]
                                    as $compent
                                ) {
                                    (object) [
                                        ($list[
                                            $produ["product_id"]
                                        ] = (object) [
                                            "id" => $produ["product_id"],

                                            "type" => "Product",

                                            "title" => [
                                                "default" =>
                                                    ($produ["product_alias"] !=="" ? $produ["product_alias"] : $produ["product_name"]),
                                            ],

                                            "description" => (object) [
                                                "default" =>
                                                    strip_tags($produ["product_short_description"]),
                                            ],

                                            "price" => $produ["product_price"],

                                            "active" => true,

                                            "isPrepackedItem" => false,

                                            "isExpressItem" => false,

                                            "excludeDishInformation" => false,
                                        ]),
                                    ];
                                }
                            }
                        }
                    }
                }
                $catimgId++;
            }
        }
        return $list;
    }
    public function getModifierProduct($outletId, $app_id, $catId,$compid)
    {
        $aval = "634E6FA8-8DAF-4046-A494-FFC1FCF8BD11";
        $list = [];
        $categories = $this->getCategoryReccords($outletId, $app_id,$compid);

        if (!empty($categories)) {
            $catimgId = 1;

            foreach ($categories as $cat) {
                $catId = $cat["pro_cate_id"];
                $products_list = $this->foodpanda_products_list_get(
                    $app_id,
                    $catId,
                    $status = null,
                    $response = null,
                    $outletId,
                    $applyOutletId = "yes",
                    $aval
                );
                if (!empty($products_list)) {
                    foreach ($products_list as $value) {
                        foreach ($value["product_list"] as $produ) {
                            if (!empty($produ["set_menu_component"])) {
                                foreach (
                                    $produ["set_menu_component"]
                                    as $setcom
                                ) {
                                    if (!empty($setcom["modifiers"])) {
                                        foreach (
                                            $setcom["modifiers"]
                                            as $modifier
                                        ) {
                                            (object) [
                                                ($list[
                                                    "topping" . $modifier["id"]
                                                ] = (object) [
                                                    "id" =>
                                                        "topping" .
                                                        $modifier["id"],

                                                    "type" => "Product",

                                                    "title" => [
                                                        "default" =>
                                                            $modifier["name"],
                                                    ],

                                                    "description" => (object) [
                                                        "default" =>
                                                            $modifier["name"],
                                                    ],

                                                    "price" => number_format(
                                                        $modifier["price"],
                                                        2
                                                    ),

                                                    "active" => true,

                                                    "isPrepackedItem" => false,

                                                    "isExpressItem" => false,

                                                    "excludeDishInformation" => false,
                                                ]),
                                            ];
                                        }
                                    }
                                }
                            } elseif (!empty($produ["modifiers"])) {
                                if (!empty($produ["modifiers"])) {
                                    foreach ($produ["modifiers"] as $mad) {
                                        if (!empty($mad["modifiers"])) {
                                            foreach (
                                                $mad["modifiers"]
                                                as $modifiers
                                            ) {
                                                (object) [
                                                    ($list[
                                                        "topping" .
                                                            $modifiers["id"]
                                                    ] = (object) [
                                                        "id" =>
                                                            "topping" .
                                                            $modifiers["id"],

                                                        "type" => "Product",

                                                        "title" => [
                                                            "default" =>
                                                                $modifiers[
                                                                    "name"
                                                                ],
                                                        ],

                                                        "description" => (object) [
                                                            "default" =>
                                                                $modifiers[
                                                                    "name"
                                                                ],
                                                        ],

                                                        "price" => number_format(
                                                            $modifiers["price"],
                                                            2
                                                        ),

                                                        "active" => true,

                                                        "isPrepackedItem" => false,

                                                        "isExpressItem" => false,

                                                        "excludeDishInformation" => false,
                                                    ]),
                                                ];
                                            }
                                        }
                                    }
                                }
                            }

                            // else{

                            //         $list['pd' .$produ['product_id']] =

                            //         array(

                            //             'id' => 'pd' .$produ['product_id'],

                            //             'type' => 'Product',

                            //         );

                            // }
                        }
                    }
                }
            }
        }
        return $list;
    }

    public function getSubProductByID(
        $outletId,
        $app_id,
        $catId,
        $productid,
        $product_alias_enabled,
        $product_parent_id,
        $product_menu_set_component_id,
        $product_is_combo
    ) {
        $modifier_array = "";

        $modifier_array = $this->foodpanda_product_modifiers(
            $app_id,
            $productid,
            "callback",
            $product_alias_enabled,
            $product_parent_id
        );

        $res["modifiers"] = $modifier_array;

        $menu_components = [];

        $menu_components = $this->foodpanda_product_combo(
            $app_id,
            $product_menu_set_component_id,
            "callback",
            $product_is_combo,
            $productid
        );

        $res["set_menu_component"] = $menu_components;
        $subproduct = [];
        if (!empty($modifier_array)) {
            foreach ($modifier_array as $mr) {
                $subproduct["var" . $mr["id"]] = [
                    "id" => "var" . $mr["id"],
                    "type" => "Product",
                ];
            }
        } elseif (!empty($menu_components)) {
            foreach ($menu_components as $mc) {
                $subproduct["var" . $mc["id"]] = [
                    "id" => "var" . $mc["id"],
                    "type" => "Product",
                ];
            }
        }

        return $subproduct;
    }

    public function getModifierMofierval($app_id, $product_id, $pro_modifier_id)
    {
        $modval_select = [
            "pro_modifier_value_id",

            "pro_modifier_value_name",

            "pro_modifier_value_price",

            "pro_modifier_value_sequence",

            "pro_modifier_value_description",
        ];

        /* join tabel... */

        $mod_val_join[0]["select"] = "";

        $mod_val_join[0]["table"] = "pos_product_assigned_alias";

        $mod_val_join[0]["condition"] =
            "pos_product_assigned_alias.alias_modifier_value_id = product_modifier_values.pro_modifier_value_id ";

        $mod_val_join[0]["type"] = "inner";

        /* apply availability options */

        $where_in = "";
        $modifier_values = $this->ci->Mydb->get_all_records(
            $modval_select,
            "product_modifier_values",
            [
                "pro_modifier_value_app_id" => addslashes($app_id),

                "pro_modifier_value_status" => "A",

                "alias_product_parent_id" => addslashes($product_id),

                "alias_modifier_id" => $pro_modifier_id,
            ],
            "",
            "",
            [
                "pro_modifier_value_sequence" => "ASC",
            ],
            "",
            "pro_modifier_value_id",
            $mod_val_join,
            $where_in
        );

        $yesrecord = $news = $new_array = [];
        if (!empty($modifier_values)) {
            foreach ($modifier_values as $mod_values) {
                $mod_val_id = $mod_values["pro_modifier_value_id"];

                $new_array[$mod_val_id] = [
                    "id" => $mod_values["pro_modifier_value_id"],

                    "name" => $mod_values["pro_modifier_value_name"],

                    "sequence" => $mod_values["pro_modifier_value_sequence"],

                    "availableStatus" => "AVAILABLE",

                    "price" =>
                        (int) $mod_values["pro_modifier_value_price"] * 100,
                ];
            }
        }
        $news = array_values($new_array);

        $modvalues["modifiers"] = $news;

        $first_mod_values["modifiers"] = $news;

        $result[] = $first_mod_values;

        $subproduct = [];

        if (!empty($result)) {
            foreach ($result as $mr) {
                if (!empty($mr["modifiers"])) {
                    foreach ($mr["modifiers"] as $mrmod) {
                        $subproduct["topping" . $mrmod["id"]] = [
                            "id" => "topping" . $mrmod["id"],
                            "type" => "Product",
                            "price" => number_format($mrmod["price"], 2),
                        ];
                    }
                }
            }
        }

        return $subproduct;
    }

    public function getComboModifier($subcatid, $app_id)
    {
        /* get combo product details.. */

        $product_list = $this->ci->Mydb->get_all_records(
            "pro_product_id,pro_is_default",
            "combo_products",
            [
                "pro_combo_id" => $subcatid,

                "pro_product_id !=" => "",
            ],
            "",
            "",
            [
                "pro_combo_primary_id" => "ASC",
            ]
        );

        $grp_join[0]["select"] =
            "product_groups.pro_group_id,product_groups.pro_group_name";

        $grp_join[0]["table"] = "product_groups as product_groups";

        $grp_join[0]["condition"] =
            " combo_products.pro_group_id = product_groups.pro_group_id";

        $grp_join[0]["type"] = "INNER";

        $grp_join[1]["select"] =
            "product_groups_details.group_detail_product_id as pro_product_id";

        $grp_join[1]["table"] =
            "product_groups_details as product_groups_details";

        $grp_join[1]["condition"] =
            "product_groups_details.group_detail_group_id = product_groups.pro_group_primary_id ";

        $grp_join[1]["type"] = "INNER";

        $group_list = $this->ci->Mydb->get_all_records(
            "pos_combo_products.pro_is_default",
            "pos_combo_products as pos_combo_products",
            [
                "pos_combo_products.pro_combo_id" => $subcatid,
                "pos_combo_products.pro_group_id !=" => "",
            ],
            "",
            "",
            "",
            "",
            "",
            $grp_join
        );

        $menu_items = array_merge($product_list, $group_list);

        $product_details = [];
        $output_result = [];
        if (!empty($menu_items)) {
            foreach ($menu_items as $items) {
                $product_details_new = $this->products_list_get(
                    $app_id,
                    $items["pro_product_id"],
                    "A",
                    "callback"
                );

                if (!empty($product_details_new)) {
                    $product_details[] = $product_details_new;
                }
            }

            $combo_list_arr["modifiers"] = $product_details;

            $output_result[] = $combo_list_arr;
        }

        $subproduct = [];
        if (!empty($output_result)) {
            foreach ($output_result as $mc) {
                if (!empty($mc["modifiers"])) {
                    foreach ($mc["modifiers"] as $mcmod) {
                        $subproduct["topping" . $mcmod["id"]] = [
                            "id" => "topping" . $mcmod["id"],
                            "type" => "Product",
                            "price" => number_format($mcmod["price"], 2),
                        ];
                    }
                }
            }
        }
        return $subproduct;
    }

    public function getModifierbySubCatId(
        $subcatid,
        $outletId,
        $app_id,
        $catId,
        $productid,
        $product_alias_enabled,
        $product_parent_id,
        $product_menu_set_component_id,
        $product_is_combo
    ) {
        $modifier_array = "";

        $modifier_array = $this->getModifierMofierval(
            $app_id,
            $productid,
            $subcatid
        );

        $res["modifiers"] = $modifier_array;

        $menu_components = [];

        $menu_components = $this->getComboModifier($subcatid, $app_id);
        $res["set_menu_component"] = $menu_components;
        $subproduct = [];

        if (!empty($modifier_array)) {
            foreach ($modifier_array as $mr) {
                if (!empty($mr["modifiers"])) {
                    foreach ($mr["modifiers"] as $mrmod) {
                        $subproduct["tt" . $mrmod["id"]] = [
                            "id" => "tt" . $mrmod["id"],
                            "type" => "Topping",
                        ];
                    }
                }
            }
        } elseif (!empty($menu_components)) {
            foreach ($menu_components as $mc) {
                if (!empty($mc["modifiers"])) {
                    foreach ($mc["modifiers"] as $mcmod) {
                        $subproduct["tt" . $mcmod["id"]] = [
                            "id" => "tt" . $mcmod["id"],
                            "type" => "Topping",
                        ];
                    }
                }
            }
        }

        return $subproduct;
    }
    public function getComplexItems($outletId, $app_id,$compid)
    {
        $itemlist = [];
        $itemslist = [];
        $productList = [];
        $schedule = [];
        $catId = 0;
        $aval = "634E6FA8-8DAF-4046-A494-FFC1FCF8BD11";

        $categories = $this->getCategoryReccords($outletId, $app_id,$compid);
        $total_product = $this->getModifierProduct($outletId, $app_id, $catId,$compid);
        $scheduletime = $this->getscheduletime($outletId, $app_id, $aval);

        $itemlist["items"] = $total_product;
        if (!empty($total_product)) {
            if (!empty($categories)) {
                foreach ($categories as $cat) {
                    $simple_combo = 0;

                    $catId = $cat["pro_cate_id"];

                    $products_list = $this->foodpanda_products_list_get(
                        $app_id,
                        $catId,
                        $status = null,
                        $response = null,
                        $outletId,
                        $applyOutletId = "yes",
                        $aval
                    );
                    if (!empty($products_list)) {
                        foreach ($products_list as $pl) {
                            foreach ($pl["product_list"] as $produ) {
                                if (
                                    $produ["product_alias_enabled"] == "Yes" ||
                                    $produ["product_is_combo"] == "Yes"
                                ) {
                                    $simple_combo = 1;
                                }
                            }
                        }
                    }

                    if ($simple_combo == 1) {
                        $itemlist["items"][$cat["pro_cate_id"]] = [
                            "id" => $cat["pro_cate_id"],
                            "type" => "Category",
                            "title" => [
                                "default" => $cat["pro_cate_name"],
                            ],
                            "description" => [
                                "default" => $cat["pro_cate_short_description"],
                            ],
                            "products" => $this->getModifierProductList(
                                $outletId,
                                $app_id,
                                $cat["pro_cate_id"]
                            ),
                        ];
                    }
                }
            }

            if (!empty($categories)) {
                $catimgid = 1;

                foreach ($categories as $cat) {
                    $catId = $cat["pro_cate_id"];

                    $aval = "634E6FA8-8DAF-4046-A494-FFC1FCF8BD11";
                    $products_list = $this->foodpanda_products_list_get(
                        $app_id,
                        $catId,
                        $status = null,
                        $response = null,
                        $outletId,
                        $applyOutletId = "yes",
                        $aval
                    );

                    if (!empty($products_list)) {
                        $imgid = 1;

                        foreach ($products_list as $value) {
                            foreach ($value["product_list"] as $produ) {

                                if (!empty($produ["product_thumbnail"])) {
                                    $productImage = base_url() . "media/dev_team/products/main-image/" .$produ["product_thumbnail"];
                                } else {
                                    $productImage = base_url() . "media/dev_team/products/main-image/" .$produ["product_thumbnail"]; //"https://assets.grab.com/wp-content/uploads/sites/8/2019/03/12120151/mcdo-fillet-o-fish-grabfood-delivery-700x700.jpg";
                                }
                                if (
                                    $produ["product_alias_enabled"] == "Yes" ||
                                    $produ["product_is_combo"] == "Yes"
                                ) {
                                    if (
                                        !empty($produ["set_menu_component"]) ||
                                        !empty($produ["modifiers"])
                                    ) {
                                        $itemlist["items"][
                                            "pd" . $produ["product_id"]
                                        ] = [
                                            "id" => "pd" . $produ["product_id"],
                                            "type" => "Product",
                                            "title" => [
                                                "default" =>
                                                    ($produ["product_alias"] !=="" ? $produ["product_alias"] : $produ["product_name"]),
                                            ],
                                            "description" => [

                                                "default" => strip_tags($produ["product_short_description"])
                                            ],
                                            "price" => $produ["product_price"],
                                            "active" => true,
                                            "isPrepackedItem" => false,
                                            "isExpressItem" => false,
                                            "excludeDishInformation" => false,

                                            "variants" => $this->getSubProductByID(
                                                $outletId,
                                                $app_id,
                                                $catId,
                                                $produ["product_id"],
                                                $produ["product_alias_enabled"],
                                                $produ["product_parent_id"],
                                                $produ[
                                                    "product_menu_set_component_id"
                                                ],
                                                $produ["product_is_combo"]
                                            ),
                                            "images" => [
                                                "image-" . $imgid => [
                                                    "id" => "image-" . $imgid,
                                                    "type" => "Image",
                                                ],
                                            ],
                                        ];
                                        if (
                                            !empty($produ["set_menu_component"])
                                        ) {
                                            foreach (
                                                $produ["set_menu_component"]
                                                as $setcom
                                            ) {
                                                $itemlist["items"][
                                                    "var" . $setcom["id"]
                                                ] = [
                                                    "id" =>
                                                        "var" . $setcom["id"],

                                                    "title" => [
                                                        "default" =>
                                                            $setcom["name"],
                                                    ],
                                                    "type" => "Product",
                                                    "price" =>
                                                        $produ["product_price"],
                                                    "parent" => [
                                                        "id" =>
                                                            "pd" .
                                                            $produ[
                                                                "product_id"
                                                            ],
                                                        "type" => "Product",
                                                    ],
                                                    "active" => true,
                                                    "isPrepackedItem" => false,
                                                    "isExpressItem" => false,
                                                    "excludeDishInformation" => false,
                                                    // "toppings" => $this->getComboModifier($setcom['id'], $app_id)
                                                    "toppings" => [
                                                        "tt" .
                                                        $setcom["id"] => [
                                                            "id" =>
                                                                "tt" .
                                                                $setcom["id"],
                                                            "type" => "Topping",
                                                        ],
                                                    ],
                                                ];
                                            }
                                        } else {
                                            foreach (
                                                $produ["modifiers"]
                                                as $mod
                                            ) {
                                                $itemlist["items"][
                                                    "var" . $mod["id"]
                                                ] = [
                                                    "id" => "var" . $mod["id"],

                                                    "title" => [
                                                        "default" =>
                                                            $mod["name"],
                                                    ],
                                                    "type" => "Product",
                                                    "price" =>
                                                        $produ["product_price"],
                                                    "parent" => [
                                                        "id" =>
                                                            "pd" .
                                                            $produ[
                                                                "product_id"
                                                            ],
                                                        "type" => "Product",
                                                    ],
                                                    "active" => true,
                                                    "isPrepackedItem" => false,
                                                    "isExpressItem" => false,
                                                    "excludeDishInformation" => false,
                                                    "toppings" => [
                                                        "tt" . $mod["id"] => [
                                                            "id" =>
                                                                "tt" .
                                                                $mod["id"],
                                                            "type" => "Topping",
                                                        ],
                                                    ],
                                                ];
                                            }
                                        }
                                        $itemlist["items"][
                                            "image-" . $imgid
                                        ] = [
                                             "url"=>$productImage,
                                            "id" => "image-" . $imgid,
                                            "type" => "Image",
                                        ];
                                    }
                                } else {
                                    $itemlist["items"][
                                        "pd" . $produ["product_id"]
                                    ] = [
                                        "id" => "pd" . $produ["product_id"],
                                        "type" => "Product",
                                        "title" => [
                                            "default" => ($produ["product_alias"] !=="" ? $produ["product_alias"] : $produ["product_name"]),
                                        ],
                                        "description" => [
                                            "default" =>  strip_tags($produ["product_short_description"])
                                        ],
                                        "price" => $produ["product_price"],
                                        "active" => true,
                                        "isPrepackedItem" => false,
                                        "isExpressItem" => false,
                                        "excludeDishInformation" => false,

                                        "images" => [
                                            "image-" . $catimgid . $imgid => [
                                                "id" =>
                                                    "image-" .
                                                    $catimgid .
                                                    $imgid,
                                                "type" => "Image",
                                            ],
                                        ],
                                    ];
                                    $itemlist["items"][
                                        "image-" . $catimgid . $imgid
                                    ] = [
                                        "url"=>$productImage,
                                        "id" => "image-" . $catimgid . $imgid,
                                        "type" => "Image",
                                    ];
                                }
                                $imgid++;
                            }
                        }
                    }

                    $catimgid++;
                }

                if (!empty($categories)) {
                    $catimgid = 1;

                    foreach ($categories as $cat) {
                        $aval = "634E6FA8-8DAF-4046-A494-FFC1FCF8BD11";
                        $catId = $cat["pro_cate_id"];

                        $products_list = $this->foodpanda_products_list_get(
                            $app_id,
                            $catId,
                            $status = null,
                            $response = null,
                            $outletId,
                            $applyOutletId = "yes",
                            $aval
                        );

                        if (!empty($products_list)) {
                            $productList = [];

                            foreach ($products_list as $value) {
                                foreach ($value["product_list"] as $produ) {
                                    if (!empty($produ["set_menu_component"])) {
                                        foreach (
                                            $produ["set_menu_component"]
                                            as $setcom
                                        ) {
                                            $itemlist["items"][
                                                "tt" . $setcom["id"]
                                            ] = [
                                                "id" => "tt" . $setcom["id"],

                                                "type" => "Topping",
                                                "title" => [
                                                    "default" =>
                                                        $setcom["name"],
                                                ],
                                                "quantity" => [
                                                    "maximum" =>
                                                        $setcom[
                                                            "selectionRangeMax"
                                                        ],
                                                    "minimum" =>
                                                        $setcom[
                                                            "selectionRangeMin"
                                                        ],
                                                ],
                                                "products" => $this->getComboModifier(
                                                    $setcom["id"],
                                                    $app_id
                                                ),
                                            ];
                                        }
                                    } elseif (!empty($produ["modifiers"])) {
                                        foreach ($produ["modifiers"] as $mod) {
                                            $itemlist["items"][
                                                "tt" . $mod["id"]
                                            ] = [
                                                "id" => "tt" . $mod["id"],

                                                "type" => "Topping",
                                                "title" => [
                                                    "default" => $mod["name"],
                                                ],
                                                "quantity" => [
                                                    "maximum" =>
                                                        $mod[
                                                            "selectionRangeMax"
                                                        ],
                                                    "minimum" =>
                                                        $mod[
                                                            "selectionRangeMin" 
                                                        ],
                                                ],
                                                "products" => $this->getModifierMofierval(
                                                    $app_id,
                                                    $produ["product_id"],
                                                    $mod["id"]
                                                ),
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($categories)) {
                $catimgid = 1;

                foreach ($categories as $cat) {
                   $product_list =  $this->getMainProductList(
                        $outletId,
                        $app_id,
                        $cat["pro_cate_id"]
                   );
                   if(!empty($product_list)) {
                    (object) [
                        ($itemlist["items"][$cat["pro_cate_id"] . "02"] = [
                            "id" => $cat["pro_cate_id"],
                            "type" => "Category",
                            "title" => [
                                "default" => $cat["pro_cate_name"],
                            ],

                            "description" => [
                                "default" => $cat["pro_cate_short_description"],
                            ],

                            "products" => $this->getMainProductList(
                                $outletId,
                                $app_id,
                                $cat["pro_cate_id"]
                            ),
                        ]),
                    ];
                }
                }
            }


             $scheduletime = $this->getscheduletime($outletId, $app_id, $aval);

            if (!empty($scheduletime)) {
                $days =
                    $scheduletime[0][
                        "delivery_time_setting_day_availablie_day"
                    ];
                $starttime =
                    $scheduletime[0][
                        "delivery_time_setting_time_pickup_slot_start_time"
                    ];
                $endtime =
                    $scheduletime[0][
                        "delivery_time_setting_time_pickup_slot_end_time"
                    ];
            }
            $itemlist["items"]["schedule-001"] = [
                "id" => "schedule-001",
                "type" => "ScheduleEntry",
                "startTime" => "00:00:00",
                "endTime" => "23:50:00",
                //"weekDays" => [$days],
            ];
            $itemlist["items"]["Menu#SCN01"] = [
                "id" => "Menu#SCN01",

                "title" => [
                    "default" => "Scenario 01",
                ],

                "description" => [
                    "default" => "Menu contains on Ala Carte Item",
                ],

                "type" => "Menu",

                "menuType" => "DELIVERY",

                "schedule" => [
                    "schedule-001" => [
                        "id" => "schedule-001",

                        "type" => "ScheduleEntry",
                    ],
                ],

                "products" => $this->getMainProductLists($outletId, $app_id,$compid),
            ];
        }
        return $itemlist;
    }

    public function getItems($outletId, $app_id,$compid)
    {
        $ite = [];
        $categories = $this->getCategoryReccords($outletId, $app_id,$compid);

        $total_product = $this->productWithImageList($outletId, $app_id,$compid);
        $itemlist["items"] = $total_product;
        if (!empty($total_product)) {
            if (!empty($categories)) {
                foreach ($categories as $cat) {
                    $catId = $cat["pro_cate_id"];
                    $productsList =$this->getProductList(
                        $outletId,
                        $app_id,
                        $cat["pro_cate_id"]
                    );
                   
                    if(!empty($productsList)) {
                    (object) [
                        ($itemlist["items"][$catId] = [
                            "id" => $cat["pro_cate_id"],

                            "type" => "Category",

                            "title" => [
                                "default" => $cat["pro_cate_name"],
                            ],

                            "description" => [
                                "default" => $cat["pro_cate_short_description"],
                            ],

                            "products" => $this->getProductList(
                                $outletId,
                                $app_id,
                                $cat["pro_cate_id"]
                            ),
                        ]),
                    ];
                }
                }
            }

            $scheduletime = $this->getscheduletime($outletId, $app_id, $aval);
            if (!empty($scheduletime)) {
                $days =
                    $scheduletime[0][
                        "delivery_time_setting_day_availablie_day"
                    ];
                $starttime =
                    $scheduletime[0][
                        "delivery_time_setting_time_pickup_slot_start_time"
                    ];
                $endtime =
                    $scheduletime[0][
                        "delivery_time_setting_time_pickup_slot_end_time"
                    ];
            }
            $itemlist["items"]["schedule-001"] = [
                "id" => "schedule-001",

                "type" => "ScheduleEntry",

                "startTime" => $starttime,

                "endTime" => $endtime,
            ];
            $itemlist["items"]["Menu#SCN01"] = [
                "id" => "Menu#SCN01",

                "title" => [
                    "default" => "Scenario 01",
                ],

                "description" => [
                    "default" => "Menu contains on Ala Carte Item",
                ],

                "type" => "Menu",

                "menuType" => "DELIVERY",

                "schedule" => [
                    "schedule-001" => [
                        "id" => "schedule-001",

                        "type" => "ScheduleEntry",
                    ],
                ],

                "products" => $this->getProductLists($outletId, $app_id,$compid),
            ];
            //$itemlist=$this->getCategoryList($outletId,$app_id);
        }
        return $itemlist;
    }

    public function dispatch_menu($app_id= null, $compid = null, $food_panda_outlet = null)
    {
        $jsonStr = [];
        $output="Outlet is empty";

        $outlet_foodpanda_code = $this->ci->Mydb->get_record( "outlet_id, outlet_foodpanda_code", "outlet_management", array("outlet_app_id" => $app_id,
               "outlet_id" => $food_panda_outlet,
                "outlet_availability" => "1",
            ));


       if(!empty($outlet_foodpanda_code)){

        $outletInfo = $this->ci->Mydb->get_all_records(
            "outlet_id, outlet_foodpanda_code,outlet_company_id, outlet_name, outlet_email, outlet_phone, outlet_unit_number1, outlet_unit_number2, outlet_address_line1, outlet_address_line2, outlet_postal_code, outlet_marker_latitude, outlet_marker_longitude,outlet_app_id",
            "outlet_management",
            [
                "outlet_app_id" => $app_id,
               "outlet_foodpanda_code" => $outlet_foodpanda_code['outlet_foodpanda_code'],
                "outlet_availability" => "1",
            ]
        );

        $get_settings = $this->ci->Mydb->get_all_records(
            "setting_key, setting_value",
            "client_settings",
            ["client_id" => $compid]
        );

        $aval = "634E6FA8-8DAF-4046-A494-FFC1FCF8BD11";

        $foodpanda_url = "";
        $foodpanda_api_key = "";
        $foodpanda_api_secret = "";
        $foodpanda_chaincode = "";
        if (!empty($get_settings)) {
            foreach ($get_settings as $gt) {
                if ($gt["setting_key"] == "client_foodpanda_url") {
                    $foodpanda_url = $gt["setting_value"];
                }
                if ($gt["setting_key"] == "client_foodpanda_api_key") {
                    $foodpanda_api_key = $gt["setting_value"];
                }
                if ($gt["setting_key"] == "client_foodpanda_api_secret") {
                    $foodpanda_api_secret = $gt["setting_value"];
                }
                if ($gt["setting_key"] == "client_foodpanda_chaincode") {
                    $foodpanda_chaincode = $gt["setting_value"];
                }
            }
        }
        $acessTokan = $this->auth_token(
            $foodpanda_url,
            $foodpanda_api_key,
            $foodpanda_api_secret
        );

        $chaincodee = $foodpanda_chaincode;

        $url = $this->api_base_url .$foodpanda_url."/v2/chains/" .$chaincodee ."/catalog";
        if (!empty($outletInfo)) {

            foreach ($outletInfo as $outlet) {

                if($outlet["outlet_foodpanda_code"]!=''){

                $categories = $this->getCategoryReccords(
                    $outlet["outlet_id"],
                    $app_id,$compid
                );

                $simple_combo = 0;

                if (!empty($categories)) {

                    foreach ($categories as $cat) {
                        $catId = $cat["pro_cate_id"];

                        $products_list = $this->foodpanda_products_list_get(
                            $app_id,
                            $catId,
                            $status = null,
                            $response = null,
                            $outlet["outlet_id"],
                            $applyOutletId = "yes",
                            $aval
                        );
             
                        if (!empty($products_list)) {
                            foreach ($products_list as $pl) {
                                foreach ($pl["product_list"] as $produ) {
                                    if (
                                        $produ["product_alias_enabled"] ==
                                            "Yes" ||
                                        $produ["product_is_combo"] == "Yes"
                                    ) {
                                        $simple_combo = 1;
                                    }
                                }
                            }
                        }
                    }
                }
               
                if ($simple_combo == 1) {
                    $total_items = $this->getComplexItems(
                        $outlet["outlet_id"],
                        $app_id,$compid
                    );
                    if (!empty($total_items)) {
                        if (!empty($total_items["items"])) {
                          
                            $jsonStr["catalog"] = $total_items;

                            $jsonStr["vendors"] = [
                                0 => $outlet["outlet_foodpanda_code"],
                            ];

                            $jsonStr["callbackUrl"] =
                                "https://webhook.site/3ed0611f-8a83-482b-816c-51c479c19a2e";
                        }
                    }
                } else {
                    $total_items = $this->getItems(
                        $outlet["outlet_id"],
                        $app_id,$compid
                    );

                    if (!empty($total_items)) {
                        if (!empty($total_items["items"])) {
                            $jsonStr = [];
                            $jsonStr["catalog"] = $total_items;
                            $jsonStr["vendors"] = [
                                0 => $outlet["outlet_foodpanda_code"],
                            ];

                            $jsonStr["callbackUrl"] =
                                "https://webhook.site/3ed0611f-8a83-482b-816c-51c479c19a2e";
                        }
                    }
                }

                
                $curl = curl_init($url);
                curl_setopt_array($curl, [
                    CURLOPT_URL => $url,
        
                    CURLOPT_RETURNTRANSFER => true,
        
                    CURLOPT_ENCODING => "",
        
                    CURLOPT_MAXREDIRS => 10,
        
                    CURLOPT_TIMEOUT => 30,
        
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        
                    CURLOPT_CUSTOMREQUEST => "PUT",
        
                    CURLOPT_POSTFIELDS => json_encode($jsonStr),
        
                    CURLOPT_HTTPHEADER => [
                        "Content-Type:application/json",
                        "Authorization: Bearer" . $acessTokan->access_token,
                    ],
                ]);

                $output = curl_exec($curl);

                curl_close($curl);
                
            }
            }
        }
        return $output;
      }
       
        
    }

    public function foodpanda_products_list_get(
        $app_id = "",
        $category_id = "",
        $status = null,
        $response = null,
        $outlet_id = null,
        $applyOutletId = null,
        $availability = null
    ) {
        $company = app_validation($app_id); /* validate app */

        $app_id = $company["client_app_id"];

        $product_tabel = "products";

        $cate_tabel = "pos_product_categories";

        $subcate_tabel = "pos_product_subcategories";

        $category_id = $category_id;

        $availability = $availability;

        $common = $availability_arr = [];

        /*-------------sub category params-------------------*/

        $joins = [];

        /* joining the subcategory availability */

        $joins[0]["select"] =
            "subcategory_availability.cate_availability_id as subcat_availability_id";

        $joins[0]["table"] =
            "product_category_availability as subcategory_availability";

        $joins[0]["condition"] =
            "subcategory_availability.cate_availability_category_primary_id = product_subcategories.pro_subcate_primary_id";

        $joins[0]["type"] = "left";

        $order_by = [
            "pro_subcate_sequence" => "ASC",
        ];

        $like = $limit = $offset = "";

        $groupby = [];

        $where = [
            "pro_subcate_status" => "A",
            "pro_subcate_app_id" => $app_id,
        ];

        if ($availability != "") {
            $where[
                "subcategory_availability.cate_availability_id"
            ] = $availability;
        }

        $select_array = [
            "pro_subcate_id",
            "pro_subcate_slug ",
            "pro_subcate_name",
            "pro_subcate_active_image",
            "pro_subcate_default_image",
            "pro_subcate_short_description",
            "pro_subcate_image",
        ];

        /*-----------------Category Loop----------------------*/

        $pro_cate_id = $category_id;

        /*---------Get subcategories corresponding sub categories  -----*/

        $where["pro_subcate_category_id"] = $pro_cate_id;

        $subcat_res = $this->ci->Mydb->get_all_records(
            $select_array,
            "product_subcategories",
            $where,
            $limit,
            $offset,
            $order_by,
            $like,
            $groupby,
            $joins
        );

        $return_data = [];
        if (!empty($subcat_res)) {
            foreach ($subcat_res as $subcate_val) {
                $product_list = $this->lastest_products_list(
                    $app_id,
                    "",
                    "A",
                    "listonly",
                    $availability,
                    $subcate_val["pro_subcate_id"],
                    $category_id,
                    "",
                    $outlet_id
                );

                $subcate_val["product_list"] = $product_list;

                if (!empty($product_list)) {
                    $return_data[] = $subcate_val;
                }
            }
        }

        return $return_data;
    }

    public function get_product_image($app_id, $product_id)
    {
        $productImage = $this->ci->Mydb->get_all_records(
            "CONCAT('" .
                base_url() .
                "media/dev_team/products/gallery-image/" .
                "',pro_gallery_image) AS pro_gallery_image",
            "product_gallery",
            [
                "pro_gallery_company_app_id" => $app_id,
                "pro_gallery_product_primary_id" => $product_id,
            ]
        );

        $product_Image = [];

        if (!empty($productImage)) {
            $product_Image = array_column($productImage, "pro_gallery_image");
        }

        return $product_Image;
    }

    public function foodpanda_product_modifiers(
        $app_id = "",
        $product_id = "",
        $response,
        $alias_enabled
    ) {
        $company = app_validation($app_id); /* validate app */

        $product_id = $product_id;

        $alias_enabled = $alias_enabled;

        $app_id = $company["client_app_id"];

        $result = $modvalues = [];

        if ($alias_enabled == "No") {
            /* old version modifier and all modifiet values */

            /*get modifiers  */

            $mod_select = [
                "pro_modifier_id",

                "pro_modifier_name",

                "pro_modifier_sequence",

                "pro_modifier_short_description",

                "pro_modifier_description",

                "pro_modifier_max_select",

                "pro_modifier_min_select",

                "pro_modifier_image",
            ];

            /* join tabel... */

            $mod_join[0]["select"] = $mod_select;

            $mod_join[0]["table"] = "pos_product_modifiers";

            $mod_join[0]["condition"] =
                "pos_product_modifiers.pro_modifier_id = product_assigned_modifiers.psm_modifier_id ";

            $mod_join[0]["type"] = "inner";

            $all_modifeirs = $this->ci->Mydb->get_all_records(
                "psm_product_id",
                "product_assigned_modifiers",
                [
                    "psm_product_id" => addslashes($product_id),

                    "psm_company_app_id" => addslashes($app_id),

                    "pro_modifier_status" => "A",
                ],
                "",
                "",
                [
                    "pro_modifier_sequence" => "ASC",
                ],
                "",
                "pro_modifier_id",
                $mod_join
            );

            $first_mod_values = [];

            if (!empty($all_modifeirs)) {
                foreach ($all_modifeirs as $modvalues) {
                    /* get modifier values */

                    $modval_select = [
                        "pro_modifier_value_id",

                        "pro_modifier_value_modifier_id",

                        "pro_modifier_value_name",

                        "pro_modifier_value_price",

                        "pro_modifier_value_sequence",

                        "pro_modifier_value_short_description",

                        "pro_modifier_value_description",
                    ];

                    $first_mod_values = [
                        "id" => $modvalues["pro_modifier_id"],

                        "name" => $modvalues["pro_modifier_name"],

                        "sequence" => 1,

                        "availableStatus" => "AVAILABLE",

                        "selectionRangeMin" =>
                            (int) $modvalues["pro_modifier_min_select"],

                        "selectionRangeMax" =>
                            (int) $modvalues["pro_modifier_max_select"],
                    ];

                    $modifier_values = $this->ci->Mydb->get_all_records(
                        $modval_select,
                        "product_modifier_values",
                        [
                            "pro_modifier_value_app_id" => addslashes($app_id),

                            "pro_modifier_value_status" => "A",

                            "pro_modifier_value_modifier_id" =>
                                $modvalues["pro_modifier_id"],
                        ],
                        "",
                        "",
                        [
                            "pro_modifier_value_sequence" => "ASC",
                        ],
                        ""
                    );

                    $yesrecord = $news = $new_array = [];

                    if (!empty($modifier_values)) {
                        foreach ($modifier_values as $mod_values) {
                            $mod_val_id = $mod_values["pro_modifier_value_id"];

                            $new_array[$mod_val_id] = [
                                "id" => $mod_values["pro_modifier_value_id"],

                                "name" =>
                                    $mod_values["pro_modifier_value_name"],

                                "sequence" =>
                                    $mod_values["pro_modifier_value_sequence"],

                                "availableStatus" => "AVAILABLE",

                                "price" =>
                                    (int) $mod_values[
                                        "pro_modifier_value_price"
                                    ] * 100,
                            ];
                        }

                        $news = array_values($new_array);

                        $first_mod_values["modifiers"] = $news;

                        $result[] = $first_mod_values;
                    }
                }

                return $result;
            }
        } elseif ($alias_enabled == "extra") {
            /* get modifiers */

            $mod_select = [
                "pro_modifier_id",

                "pro_modifier_name",

                "pro_modifier_sequence",

                "pro_modifier_short_description",

                "pro_modifier_description",

                "pro_modifier_max_select",

                "pro_modifier_min_select",

                "pro_modifier_image",
            ];

            /* join tabel... */

            $mod_join[0]["select"] = $mod_select;

            $mod_join[0]["table"] = "pos_product_modifiers";

            $mod_join[0]["condition"] =
                "pos_product_modifiers.pro_modifier_id = product_category_modifiers.pcm_modifier_id ";

            $mod_join[0]["type"] = "inner";

            $all_modifeirs = $this->ci->Mydb->get_all_records(
                "pcm_modifier_id",
                "product_category_modifiers",
                [
                    "pcm_category_id" => addslashes($product_id),

                    "pcm_company_app_id" => addslashes($app_id),

                    "pro_modifier_status" => "A",
                ],
                "",
                "",
                [
                    "pro_modifier_sequence" => "ASC",
                ],
                "",
                "pcm_modifier_id",
                $mod_join
            );

            $first_mod_values = [];

            if (!empty($all_modifeirs)) {
                foreach ($all_modifeirs as $modvalues) {
                    /* get modifier values */

                    $modval_select = [
                        "pro_modifier_value_id",

                        "pro_modifier_value_modifier_id",

                        "pro_modifier_value_name",

                        "pro_modifier_value_price",

                        "pro_modifier_value_sequence",

                        "pro_modifier_value_short_description",

                        "pro_modifier_value_description",
                    ];

                    $first_mod_values = [
                        "id" => $modvalues["pro_modifier_id"],

                        "name" => $modvalues["pro_modifier_name"],

                        "sequence" => 1,

                        "availableStatus" => "AVAILABLE",

                        "selectionRangeMin" =>
                            (int) $modvalues["pro_modifier_min_select"],

                        "selectionRangeMax" =>
                            (int) $modvalues["pro_modifier_max_select"],
                    ];

                    $modifier_values = $this->ci->Mydb->get_all_records(
                        $modval_select,
                        "product_modifier_values",
                        [
                            "pro_modifier_value_app_id" => addslashes($app_id),

                            "pro_modifier_value_status" => "A",

                            "pro_modifier_value_modifier_id" =>
                                $modvalues["pro_modifier_id"],
                        ],
                        "",
                        "",
                        [
                            "pro_modifier_value_sequence" => "ASC",
                        ],
                        "",
                        "pro_modifier_value_id"
                    );

                    $yesrecord = $news = $new_array = [];

                    if (!empty($modifier_values)) {
                        foreach ($modifier_values as $mod_values) {
                            $mod_val_id = $mod_values["pro_modifier_value_id"];

                            $new_array[$mod_val_id] = [
                                "id" => $mod_values["pro_modifier_value_id"],

                                "name" =>
                                    $mod_values["pro_modifier_value_name"],

                                "sequence" =>
                                    $mod_values["pro_modifier_value_sequence"],

                                "availableStatus" => "AVAILABLE",

                                "price" =>
                                    (int) $mod_values[
                                        "pro_modifier_value_price"
                                    ] * 100,
                            ];
                        }

                        $news = array_values($new_array);

                        $first_mod_values["modifiers"] = $news;

                        $result[] = $first_mod_values;
                    }
                }

                return $result;
            }
        } else {
            $mod_select = [
                "pro_modifier_id",

                "pro_modifier_name",

                "pro_modifier_sequence",

                "pro_modifier_short_description",

                "pro_modifier_description",

                "pro_modifier_max_select",

                "pro_modifier_min_select",
            ];

            /* join tabel... */

            $mod_join[0]["select"] = $mod_select;

            $mod_join[0]["table"] = "pos_product_modifiers";

            $mod_join[0]["condition"] =
                "pos_product_modifiers.pro_modifier_id = pos_product_assigned_alias.alias_modifier_id ";

            $mod_join[0]["type"] = "inner";

            $all_modifeirs = $this->ci->Mydb->get_all_records(
                "alias_modifier_id",
                "pos_product_assigned_alias",
                [
                    "alias_product_parent_id" => addslashes($product_id),

                    "alias_company_app_id" => addslashes($app_id),

                    "pro_modifier_status" => "A",
                ],
                "",
                "",
                [
                    "pro_modifier_sequence" => "ASC",
                ],
                "",
                "pro_modifier_id",
                $mod_join
            );

            $modifier_values = $news = [];

            $first_mod_values = [];

            if (!empty($all_modifeirs)) {
                foreach ($all_modifeirs as $modvalues) {
                    /* get modifier values */

                    $modval_select = [
                        "pro_modifier_value_id",

                        "pro_modifier_value_name",

                        "pro_modifier_value_price",

                        "pro_modifier_value_sequence",

                        "pro_modifier_value_description",
                    ];

                    /* join tabel... */

                    $mod_val_join[0]["select"] = "";

                    $mod_val_join[0]["table"] = "pos_product_assigned_alias";

                    $mod_val_join[0]["condition"] =
                        "pos_product_assigned_alias.alias_modifier_value_id = product_modifier_values.pro_modifier_value_id ";

                    $mod_val_join[0]["type"] = "inner";

                    /* apply availability options */

                    $where_in = "";

                    $first_mod_values = [
                        "id" => $modvalues["pro_modifier_id"],

                        "name" => $modvalues["pro_modifier_name"],

                        "sequence" => 1,

                        "availableStatus" => "AVAILABLE",

                        "selectionRangeMin" =>
                            (int) $modvalues["pro_modifier_min_select"],

                        "selectionRangeMax" =>
                            (int) $modvalues["pro_modifier_max_select"],
                    ];

                    $modifier_values = $this->ci->Mydb->get_all_records(
                        $modval_select,
                        "product_modifier_values",
                        [
                            "pro_modifier_value_app_id" => addslashes($app_id),

                            "pro_modifier_value_status" => "A",

                            "alias_product_parent_id" => addslashes(
                                $product_id
                            ),

                            "alias_modifier_id" =>
                                $modvalues["pro_modifier_id"],
                        ],
                        "",
                        "",
                        [
                            "pro_modifier_value_sequence" => "ASC",
                        ],
                        "",
                        "pro_modifier_value_id",
                        $mod_val_join,
                        $where_in
                    );
                    $yesrecord = $news = $new_array = [];

                    if (!empty($modifier_values)) {
                        foreach ($modifier_values as $mod_values) {
                            $mod_val_id = $mod_values["pro_modifier_value_id"];

                            $new_array[$mod_val_id] = [
                                "id" => $mod_values["pro_modifier_value_id"],

                                "name" =>
                                    $mod_values["pro_modifier_value_name"],

                                "sequence" =>
                                    $mod_values["pro_modifier_value_sequence"],

                                "availableStatus" => "AVAILABLE",

                                "price" =>
                                    (int) $mod_values[
                                        "pro_modifier_value_price"
                                    ] * 100,
                            ];
                        }

                        $news = array_values($new_array);

                        $modvalues["modifiers"] = $news;

                        $first_mod_values["modifiers"] = $news;

                        $result[] = $first_mod_values;
                    }
                }

                return $result;
            }
        }
    }

    public function foodpanda_product_combo(
        $app_id = "",
        $set_component = null,
        $response = null,
        $is_combo = null,
        $product_id = null
    ) {
        $result = $output_result = [];

        if ($is_combo == "No" && $set_component != "") {
            /* join table */

            $join[0]["select"] = "menu_set_component_menu_component_id";

            $join[0]["table"] = "product_menu_set_component_items";

            $join[0]["condition"] = "set_component_id = menu_set_component_id ";

            $join[0]["type"] = "INNER";

            $join[1]["select"] =
                "pro_component_id,pro_component_name,pro_component_sequence,pro_component_status,pro_component_min_select,pro_component_max_select,pro_component_default_product_id";

            $join[1]["table"] = "product_menu_component";

            $join[1]["condition"] =
                "menu_set_component_menu_component_id = pro_component_id";

            $join[1]["type"] = "INNER";

            $com_set = $this->ci->Mydb->get_all_records(
                "set_component_name,set_component_id",
                "product_menu_set_component",
                [
                    "set_component_id" => $set_component_id,

                    "set_component_app_id" => $app_id,

                    "set_component_status" => "A",

                    "pro_component_status" => "A",
                ],
                "",
                "",
                [
                    "pro_component_sequence" => "ASC",
                ],
                "",
                "",
                $join
            );

            /* get componenet items and full details */

            $set_value = [];

            $combo_list_arr = [];
            if (!empty($com_set)) {
                foreach ($com_set as $set) {
                    $set_value["menu_set_component_id"] = "";

                    $set_value["menu_set_component_name"] = "";

                    $set_value["menu_component_id"] = $set["pro_component_id"];

                    $set_value["menu_component_name"] =
                        $set["pro_component_name"];

                    $set_value["menu_component_sequence"] =
                        $set["pro_component_sequence"];

                    $set_value["menu_component_min_select"] =
                        $set["pro_component_min_select"];

                    $set_value["menu_component_max_select"] =
                        $set["pro_component_max_select"];

                    $set_value["menu_component_default_select"] =
                        $set["pro_component_default_product_id"];

                    $set_value["menu_component_apply_price"] = "";

                    $set_value["menu_component_modifier_apply"] = "";

                    $combo_list_arr = [
                        "id" => $set["combo_id"],

                        "name" => $set["combo_name"],

                        "sequence" => 1,

                        "availableStatus" => "AVAILABLE",

                        "selectionRangeMin" => (int) $set["combo_min_select"],

                        "selectionRangeMax" => (int) $set["combo_max_select"],
                    ];

                    /* get product and modifier details..*/

                    $menu_items = $this->ci->Mydb->get_all_records(
                        "menu_component_category_id,menu_component_subcategory_id,menu_component_product_id",
                        "product_menu_component_items",
                        [
                            "menu_component_id" => $set["pro_component_id"],
                        ]
                    );

                    $product_details = [];

                    if (!empty($menu_items)) {
                        foreach ($menu_items as $items) {
                            $product_details_new = $this->products_list_get(
                                $app_id,
                                $items["menu_component_product_id"],
                                "A",
                                "callback"
                            );

                            if (!empty($product_details_new)) {
                                $product_details[] = $product_details_new;
                            }
                        }

                        $combo_list_arr["product_details"] = $product_details;

                        $output_result[] = $combo_list_arr;
                    }
                }
            }
        } elseif ($is_combo == "Yes") {
            $com_set = $this->ci->Mydb->get_all_records(
                "combo_id,combo_name,combo_max_select,combo_min_select,combo_pieces_count,combo_price_apply,combo_qty,combo_is_saving,combo_modifier_apply",
                "product_combos",
                [
                    "combo_product_id" => $product_id,
                ],
                "",
                "",
                [
                    "combo_sort_order" => "ASC",
                ]
            );

            $set_value = $product_list = $group_list = [];

            $combo_list_arr = [];

            if (!empty($com_set)) {
                foreach ($com_set as $set) {
                    $set_value["menu_set_component_id"] = $set["combo_id"];

                    $set_value["menu_set_component_name"] = $set["combo_name"];

                    $set_value["menu_component_id"] = $set["combo_id"];

                    $set_value["menu_component_name"] = $set["combo_name"];

                    $set_value["menu_component_sequence"] = "";

                    $set_value["menu_component_min_select"] =
                        $set["combo_min_select"];

                    $set_value["menu_component_max_select"] =
                        $set["combo_max_select"];

                    $set_value["menu_component_apply_price"] =
                        $set["combo_price_apply"];

                    $set_value["menu_component_modifier_apply"] =
                        $set["combo_modifier_apply"];

                    $set_value["menu_component_default_select"] = "";

                    $set_value["menu_component_pieces_count"] =
                        $set["combo_pieces_count"];

                    $combo_list_arr = [
                        "id" => $set["combo_id"],

                        "name" => $set["combo_name"],

                        "sequence" => 1,

                        "availableStatus" => "AVAILABLE",

                        "selectionRangeMin" => (int) $set["combo_min_select"],

                        "selectionRangeMax" => (int) $set["combo_max_select"],
                    ];

                    /* get combo product details.. */

                    $product_list = $this->ci->Mydb->get_all_records(
                        "pro_product_id,pro_is_default",
                        "combo_products",
                        [
                            "pro_combo_id" => $set["combo_id"],

                            "pro_product_id !=" => "",
                        ],
                        "",
                        "",
                        [
                            "pro_combo_primary_id" => "ASC",
                        ]
                    );

                    $grp_join[0]["select"] =
                        "product_groups.pro_group_id,product_groups.pro_group_name";

                    $grp_join[0]["table"] = "product_groups as product_groups";

                    $grp_join[0]["condition"] =
                        " combo_products.pro_group_id = product_groups.pro_group_id";

                    $grp_join[0]["type"] = "INNER";

                    $grp_join[1]["select"] =
                        "product_groups_details.group_detail_product_id as pro_product_id";

                    $grp_join[1]["table"] =
                        "product_groups_details as product_groups_details";

                    $grp_join[1]["condition"] =
                        "product_groups_details.group_detail_group_id = product_groups.pro_group_primary_id ";

                    $grp_join[1]["type"] = "INNER";

                    $group_list = $this->ci->Mydb->get_all_records(
                        "pos_combo_products.pro_is_default",
                        "pos_combo_products as pos_combo_products",
                        [
                            "pos_combo_products.pro_combo_id" =>
                                $set["combo_id"],
                            "pos_combo_products.pro_group_id !=" => "",
                        ],
                        "",
                        "",
                        "",
                        "",
                        "",
                        $grp_join
                    );

                    $menu_items = array_merge($product_list, $group_list);

                    $product_details = [];

                    if (!empty($menu_items)) {
                        foreach ($menu_items as $items) {
                            $product_details_new = $this->products_list_get(
                                $app_id,
                                $items["pro_product_id"],
                                "A",
                                "callback"
                            );

                            if (!empty($product_details_new)) {
                                $product_details[] = $product_details_new;
                            }
                        }

                        $combo_list_arr["modifiers"] = $product_details;

                        $output_result[] = $combo_list_arr;
                    }
                }
            }
        }

        return $output_result;
    }

    public function lastest_products_list(
        $app_id = "",
        $product_id = "",
        $status = null,
        $response = null,
        $availability = null,
        $subcategory_id = null,
        $category_id = null,
        $tagResponse = null,
        $selected_outlet = null
    ) {
        $company = app_validation($app_id); /* validate app */

        $app_id = $company["client_app_id"];

        $tabel = "products";

        /* Default values */

        $common = $where_in = $ouput = [];

        $applyOutletId = "yes";

        $select_array = [
            "REPLACE(product_name,'(Trio)','') as product_name" /* don't add before any  filed */,

            "product_type",

            "product_id",

            "product_primary_id",

            "product_company_id",

            "product_company_app_id",

            "product_category_id",

            "product_subcategory_id",

            "product_short_description",

            "product_long_description",

            "product_sequence",

            "product_thumbnail",

            "product_parent_id",

            "product_status",

            "product_cost",

            "product_price",

            "product_alias_enabled",

            "product_menu_set_component_id",

            "product_is_combo",

            "product_stock",

            "product_sku",

            "product_alias"
        ];

        $validate_array = $select_array;

        $validate_array[0] = "product_name";

        $where = [
            "product_company_app_id" => $app_id,
        ];

        if ($response != "callback") {
            $where = array_merge($where, [
                "product_price !=" => 0,

                "product_parent_id" => "",

                "product_parent_primary_id" => "",
            ]);
        }

        $order_by = [
            "pro_subcate_sequence" => "ASC",

            "product_sequence" => "ASC",
        ];

        $limit = $like = $next = $previous = "";

        /* get single subcategory details */

        $subcategory_id = $subcategory_id != "" ? $subcategory_id : "";

        if ($subcategory_id != "" && $response != "callback") {
            $where = array_merge(
                [
                    "product_subcategory_id" => addslashes($subcategory_id),
                ],
                $where
            );
        }

        /* get single category details $category_id */

        $category_id = $category_id != "" ? $category_id : "";

        if ($category_id != "" && $response != "callback") {
            $where = array_merge(
                [
                    "product_category_id" => addslashes($category_id),
                ],
                $where
            );
        }

        /* get single product slug */

        $product_id = $product_id != "" ? $product_id : "";

        if ($product_id != "") {
            $where = array_merge(
                [
                    "product_id" => addslashes($product_id),
                ],
                $where
            );
        }

        /* apply status */

        $status = $status != "" ? $status : "";

        if (in_array($status, ["A", "I"])) {
            $where = array_merge(
                [
                    "product_status" => addslashes($status),
                ],
                $where
            );
        }

        /* apply availability options */

        $availability = $availability != "" ? $availability : "";

        if ($availability != "" && $response != "callback") {
        }

        /* Apply limit option */

        $get_limit = "0";

        if ((int) $get_limit != 0) {
            $limit = (int) $get_limit;
        }

        /* join tables - Main category table */

        $join[0]["select"] =
            "pro_cate_sequence as catgory_sequence ,pro_cate_name as catgory_name,pro_cate_short_description as catgory_short_description ,pro_cate_description  as catgory_description,pro_cate_image  as catgory_image";

        $join[0]["table"] = "pos_product_categories";

        $join[0]["condition"] =
            "product_category_id = pro_cate_id AND pro_cate_status = 'A'";

        $join[0]["type"] = "INNER";

        /* join tables - sub category table */

        $join[1]["select"] =
            "pro_subcate_sequence as subcatgory_sequence,pro_subcate_name as subcatgory_name,pro_subcate_short_description as subcatgory_short_description,pro_subcate_description as subcatgory_description,,pro_subcate_image as subcatgory_image";

        $join[1]["table"] = "pos_product_subcategories";

        $join[1]["condition"] =
            "product_subcategory_id = pro_subcate_id AND pro_subcate_status = 'A'";

        $join[1]["type"] = "INNER";

        $additional_where_in = [];

        /* apply outlet filter options */

        $selected_outlet =
            $selected_outlet != "" ? urldecode($selected_outlet) : "";

        $applyOutletId = "yes";

        $applyOutletId =
            $applyOutletId != "" ? urldecode($applyOutletId) : $applyOutletId;

        if (
            $selected_outlet != "" &&
            $response != "callback" &&
            $selected_outlet != "undefined" &&
            ($app_id == "5E845FE9-C95D-4FFB-8A70-B14935C1455C" ||
                $applyOutletId == "yes")
        ) {
            $outlet_arr = explode(";", $selected_outlet);

            $join_count = count($join);

            $join[$join_count]["select"] = "pao_outlet_id";

            $join[$join_count]["table"] = "product_assigned_outlets";

            $join[$join_count]["condition"] = "product_id = pao_product_id";

            $join[$join_count]["type"] = "INNER";

            $additional_where_in = [
                "field" => "pao_outlet_id",

                "where_in" => $outlet_arr,
            ];
        }

        $post_offset = 0;

        /* get total rows */

        $offset = $post_offset != 0 ? $post_offset * $limit : 0;

        $result = $this->ci->Mydb->get_all_records(
            $select_array,
            $tabel,
            $where,
            $limit,
            $offset,
            $order_by,
            $like,
            "product_id",
            $join,
            $where_in
        );

        //echo $this->ci->db->last_query();

        // echo "<pre>";

        // print_r($result);

        $output = [];

        if (!empty($result)) {
            foreach ($result as $res) {
                $productImage = $this->get_product_image(
                    $app_id,
                    $res["product_primary_id"]
                );

                $res["productImage"] = $productImage;

                $modifier_array = "";

                $modifier_array = $this->foodpanda_product_modifiers(
                    $app_id,
                    $res["product_id"],
                    "callback",
                    $res["product_alias_enabled"],
                    $res["product_parent_id"]
                );

                $res["modifiers"] = $modifier_array;

                $menu_components = [];

                $menu_components = $this->foodpanda_product_combo(
                    $app_id,
                    $res["product_menu_set_component_id"],
                    "callback",
                    $res["product_is_combo"],
                    $res["product_id"]
                );

                $res["set_menu_component"] = $menu_components;

                $output[] = $res;
            }
        }
        return $output;
    }

    public function dispatch_order($url, $remote_id, $order_id, $app_id)
    {
        $order_details = $this->ci->Mydb->get_record("*", "orders", [
            "order_id" => $order_id,
            "order_company_app_id" => $app_id,
        ]);

        $order_customer = $this->ci->Mydb->get_record(
            "*",
            "orders_customer_details",
            [
                "order_customer_order_primary_id" =>
                    $order_details["order_primary_id"],
            ]
        );

        $order_item = $this->ci->Mydb->get_all_records(
            "item_id,item_product_id,item_name,item_qty,item_total_amount,item_specification",
            "order_items",
            [
                "item_order_primary_id" => $order_details["order_primary_id"],
            ]
        );

        $outlet_details = $this->ci->Mydb->get_record(
            "outlet_name, outlet_phone, outlet_address_line1, outlet_address_line2, outlet_postal_code, outlet_unit_number1, outlet_unit_number2, outlet_marker_latitude, outlet_marker_longitude",
            "outlet_management",
            [
                "outlet_id" => $order_details["order_outlet_id"],
            ]
        );

        $customer = (object) [
            "email" => $order_customer["order_customer_email"],

            "firstName" => $order_customer["order_customer_fname"],

            "lastName" => $order_customer["order_customer_lname"],

            "mobilePhone" => $order_customer["order_customer_mobile_no"],
        ];

        $delivery_address = (object) [
            "postcode" => $order_customer["order_customer_postal_code"],

            "city" => $order_customer["order_customer_city"],

            "street" => $order_customer["order_customer_address_line1"],

            "number" => $order_customer["order_customer_unit_no1"],
        ];

        //

        $delivery = (object) [
            "address" => $order_customer["order_customer_email"],

            "expectedDeliveryTime" =>
                date("Y-m-d\TH:i:s", strtotime($order_details["order_date"])) .
                ".000Z",

            "expressDelivery" => false,

            "riderPickupTime" => "",
        ];

        $discounts = [
            "name" => "",
            "amount" => "",
            "type" => "",
        ];

        $expeditionType = "";

        if ($order_details["order_availability_name"] == "Pickup") {
            $expeditionType = "pickup";
        } else {
            $expeditionType = "delivery";
        }

        $localInfo = (object) [
            "countryCode" => "SG",

            "currencySymbol" => "$",

            "platform" => "Foodpanda",

            "platformKey" => "",
        ];

        $payment_status = "";

        $payment_type = "";

        if ($order_details["order_payment_mode"] == "3") {
            $payment_status = "paid";

            $payment_type = "online";
        } else {
            $payment_status = "pending";

            $payment_type = "offline";
        }

        $payment = (object) [
            "status" => $payment_status,

            "type" => $payment_type,
        ];

        $platformRestaurant = (object) [
            "id" => "",
        ];

        $price = (object) [
            "deliveryFees" => [
                "name" => "",
                "value" => "",
            ],

            "grandTotal" => $order_details["order_total_amount"],

            "minimumDeliveryValue" => "Foodpanda",

            "subTotal" => $order_details["order_sub_total"],

            "vatTotal" => "",
        ];

        if (!empty($order_item)) {
            $i = 0;

            foreach ($order_item as $items) {
                /* get modifiers */

                $modifier_array = $extra_modifier_array = [];

                $modifier_array = $this->product_modifiers_get(
                    $order_id,
                    $items["item_id"],
                    "Modifier",
                    "order_item_id",
                    "callback"
                );

                $order_item[$i]["modifiers"] = $modifier_array;

                /* get set_menu_component */

                $menu_components = [];

                $menu_components = $this->product_menu_component_get(
                    $order_id,
                    $items["item_id"],
                    "MenuSetComponent",
                    "order_menu_primary_id",
                    "callback"
                );

                $order_item[$i]["set_menu_component"] = $menu_components;

                $i++;
            }
        }

        foreach ($order_item as $prod) {
            $get_product_details = $this->ci->Mydb->get_record(
                "product_category_id",
                "products",
                [
                    "product_id" => $prod["item_product_id"],
                    "product_company_app_id" =>
                        $order_details["order_company_app_id"],
                ]
            );

            $get_category_name = $this->ci->Mydb->get_record(
                "pro_cate_name",
                "product_categories",
                [
                    "pro_cate_id" =>
                        $get_product_details["product_category_id"],
                    "pro_cate_app_id" => $order_details["order_company_app_id"],
                ]
            );

            $modifiers_arr = $prod["modifiers"];

            $menu_set_component = $prod["set_menu_component"];

            $selectedToppings = [];

            if (count($modifiers_arr) > 0) {
                foreach ($modifiers_arr as $mod) {
                    if (!empty($mod["modifiers_values"])) {
                        foreach ($mod["modifiers_values"] as $modvals) {
                            $selectedToppings[] = [
                                "children" => [],
                                "name" => "",
                                "price" => "",
                                "quantity" => "",
                                "remoteCode" => null,
                            ];
                        }
                    }
                }
            } elseif (count($menu_set_component) > 0) {
                foreach ($menu_set_component as $menu_set) {
                    if (
                        isset($menu_set["product_details"]) &&
                        !empty($menu_set["product_details"])
                    ) {
                        foreach ($menu_set["product_details"] as $pro) {
                            $selectedToppings[] = [
                                "children" => [],
                                "name" => $pro["menu_product_name"],
                                "price" => $pro["menu_product_price"],
                                "quantity" => $pro["menu_product_qty"],
                                "remoteCode" => null,
                            ];
                        }
                    }
                }
            }

            $products[] = [
                "categoryName" => $get_category_name["pro_cate_name"],

                "name" => $prod["item_name"],

                "paidPrice" => $prod["item_total_amount"],

                "quantity" => $prod["item_qty"],

                "remoteCode" => null,

                "selectedToppings" => $selectedToppings,

                "unitPrice" => "",

                "comment" => "",

                "description" => "",
            ];
        }

        $order_source_web = false;

        $order_mobile_source = false;

        if ($order_details["order_source"] == "Web") {
            $order_source_web = true;
        } else {
            $order_mobile_source = true;
        }

        $url = $url . "order/" . $remote_id;

        $params = [
            "token" => "",

            "code" => "",

            "comments" => (object) [
                "customerComment" => "",
                "vendorComment" => "",
            ],

            "createdAt" =>
                date(
                    "Y-m-d\TH:i:s",
                    strtotime($order_details["order_created_on"])
                ) . ".000Z",

            "customer" => $customer,

            "delivery" => $delivery,

            "discounts" => $discounts,

            "expeditionType" => $expeditionType,

            "expiryDate" =>
                date("Y-m-d\TH:i:s", strtotime($order_details["order_date"])) .
                ".000Z",

            "localInfo" => $localInfo,

            "payment" => $payment,

            "test" => false,

            "preOrder" => false,

            "pickup" => null,

            "platformRestaurant" => $platformRestaurant,

            "price" => $price,

            "products" => $products,

            "corporateOrder" => false,

            "mobileOrder" => $order_mobile_source,

            "webOrder" => $order_source_web,
        ];

        echo "<pre>";

        print_r($params);

        exit();

        $curl = curl_init($url);

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_ENCODING => "",

            CURLOPT_MAXREDIRS => 10,

            CURLOPT_TIMEOUT => 30,

            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

            CURLOPT_CUSTOMREQUEST => "PUT",

            CURLOPT_POSTFIELDS => json_encode($params),

            CURLOPT_HTTPHEADER => ["Content-Type:application/json"],
        ]);

        $output = curl_exec($curl);

        curl_close($curl);

        return $output;
    }

    public function update_order_status(
        $url,
        $remote_id,
        $remoteOrderId,
        $status,
        $remarks
    ) {
        $url =
            $url .
            "{" .
            $remote_id .
            "}" .
            "/remoteOrder/{" .
            $remoteOrderId .
            "}/posOrderStatus";

        $params = [
            "status" => $status,
            "message" => $remarks,
        ];

        $curl = curl_init($url);

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_ENCODING => "",

            CURLOPT_MAXREDIRS => 10,

            CURLOPT_TIMEOUT => 30,

            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

            CURLOPT_CUSTOMREQUEST => "PUT",

            CURLOPT_POSTFIELDS => json_encode($params),

            CURLOPT_HTTPHEADER => ["Content-Type:application/json"],
        ]);

        $output = curl_exec($curl);

        curl_close($curl);

        return $output;
    }

    public function product_modifiers_get(
        $order_id = "",
        $item_id = "",
        $type,
        $field,
        $response = null
    ) {
        $result = [];

        $modifiers = $this->ci->Mydb->get_all_records(
            "order_modifier_id,order_modifier_name",
            "order_modifiers",
            [
                "order_modifier_type" => $type,
                $field => $item_id,
                "order_modifier_parent" => "",
            ]
        );

        if (!empty($modifiers)) {
            foreach ($modifiers as $modvalues) {
                /* get modifier values */

                $modifier_values = $this->ci->Mydb->get_all_records(
                    [
                        "order_modifier_id",
                        "order_modifier_name",
                        "order_modifier_qty",
                        "order_modifier_price",
                    ],
                    "order_modifiers",
                    [
                        "order_modifier_type" => $type,
                        $field => $item_id,
                        "order_modifier_parent" =>
                            $modvalues["order_modifier_id"],
                    ]
                );

                if (!empty($modifier_values)) {
                    $modvalues["modifiers_values"] = $modifier_values;

                    $result[] = $modvalues;
                }
            }
        }

        return $result;
    }

    /* this function used to product menu component items */

    public function product_menu_component_get(
        $order_id = "",
        $item_id = "",
        $type,
        $field,
        $response = null
    ) {
        $result = $output_result = [];

        $com_set = $this->ci->Mydb->get_all_records(
            ["menu_menu_component_id", "menu_menu_component_name"],
            "order_menu_set_components",
            [
                "menu_item_id" => $item_id,
            ],
            "",
            "",
            "",
            "",
            "menu_menu_component_id"
        );

        $set_value = [];

        if (!empty($com_set)) {
            foreach ($com_set as $set) {
                $set_value["menu_component_id"] =
                    $set["menu_menu_component_id"];

                $set_value["menu_component_name"] =
                    $set["menu_menu_component_name"];

                /* get prodict details */

                $menu_items = $this->ci->Mydb->get_all_records(
                    [
                        "menu_primary_id",
                        "menu_product_id",
                        "menu_product_name",
                        "menu_product_sku",
                        "menu_product_price",
                        "menu_product_qty",
                    ],
                    "order_menu_set_components",
                    [
                        "menu_item_id" => $item_id,
                        "menu_menu_component_id" =>
                            $set["menu_menu_component_id"],
                    ]
                );

                $product_details = [];

                if (!empty($menu_items)) {
                    foreach ($menu_items as $items) {
                        $items["modifiers"] = $this->product_modifiers_get(
                            $order_id,
                            $items["menu_primary_id"],
                            "MenuSetComponent",
                            $field,
                            "callback"
                        );

                        $product_details[] = $items;
                    }

                    $set_value["product_details"] = $product_details;

                    $output_result[] = $set_value;
                }
            }
        }

        return $output_result;
    }

    public function load_post_curl($url, $params, $method)
    {
        $curl = curl_init($url);

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_ENCODING => "",

            CURLOPT_MAXREDIRS => 10,

            CURLOPT_TIMEOUT => 30,

            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

            CURLOPT_CUSTOMREQUEST => $method,

            CURLOPT_POSTFIELDS => $params,

            CURLOPT_HTTPHEADER => [
                "Content-Type:application/json",

                "X-Api-Key:" . $this->x_api_key,
            ],
        ]);

        $output = curl_exec($curl);

        curl_close($curl);

        return $output;
    }

    public function get_quotations($order_id, $caronly, $remarks, $order_date)
    {
        $order_details = $this->ci->Mydb->get_record("*", "orders", [
            "order_id" => $order_id,
        ]);

        $order_customer = $this->ci->Mydb->get_record(
            "*",
            "orders_customer_details",
            [
                "order_customer_order_primary_id" =>
                    $order_details["order_primary_id"],
            ]
        );

        $outlet_details = $this->ci->Mydb->get_record(
            "outlet_name, outlet_phone, outlet_address_line1, outlet_address_line2, outlet_postal_code, outlet_unit_number1, outlet_unit_number2, outlet_marker_latitude, outlet_marker_longitude",
            "outlet_management",
            [
                "outlet_id" => $order_details["order_outlet_id"],
            ]
        );

        $customerAddr = "";

        $outletAddr = "";

        $unitNumTxt = "";

        $address = "";

        if ($order_customer["order_customer_address_line1"] != "") {
            $customerAddr = $this->getAddressFmt(
                $order_customer["order_customer_address_line1"],
                $order_customer["order_customer_postal_code"]
            );
        }

        if ($order_customer["order_customer_unit_no1"] != "") {
            $unitNumTxt =
                $order_customer["order_customer_unit_no2"] != ""
                    ? "#" .
                        $order_customer["order_customer_unit_no1"] .
                        "-" .
                        $order_customer["order_customer_unit_no2"]
                    : $order_customer["order_customer_unit_no1"];
        } elseif ($order_customer["order_customer_unit_no2"] != "") {
            $unitNumTxt = "#" . $order_customer["order_customer_unit_no2"];
        }

        $userLatLangArr = $this->getUserLatLang(
            $order_customer["order_customer_postal_code"]
        );

        $order_destinations = [
            [
                "delivery_address" => $customerAddr,

                "delivery_lat" => $userLatLangArr["latitude"],

                "delivery_lng" => $userLatLangArr["longitude"],

                "delivery_address_details" => $unitNumTxt,

                "customer_phone_number" => "+6597775663",

                "note" => $remarks,

                "distance" => "5 km",

                "duration" => "10 min",

                "delivery_proof" => null,
            ],
        ];

        $unitNumTxt = "";

        $outletAddr = $this->getAddressFmt(
            $outlet_details["outlet_address_line1"],
            $outlet_details["outlet_postal_code"]
        );

        if ($outlet_details["outlet_unit_number1"] != "") {
            $unitNumTxt =
                $outlet_details["outlet_unit_number2"] != ""
                    ? "#" .
                        $outlet_details["outlet_unit_number1"] .
                        "-" .
                        $outlet_details["outlet_unit_number2"]
                    : $outlet_details["outlet_unit_number1"];
        } elseif ($outlet_details["outlet_unit_number2"] != "") {
            $unitNumTxt = "#" . $outlet_details["outlet_unit_number2"];
        }

        $outletAddr = $unitNumTxt . $outletAddr;

        $post_data = json_encode([
            "pick_up_address" => $outletAddr,

            "pick_up_lat" => $outlet_details["outlet_marker_latitude"],

            "pick_up_lng" => $outlet_details["outlet_marker_longitude"],

            "order_destinations" => json_encode($order_destinations),

            "car_only" => $caronly,

            /* "scheduled_time"=> $order_date,*/
        ]);

        /* API URL */

        $url = $this->api_base_url . "orders";

        $curl_response = $this->load_post_curl($url, $post_data, "POST");

        $decoded_response = json_decode($curl_response, true);

        if (array_key_exists("error", $decoded_response)) {
            $response = [
                "response" => "error",

                "status_code" => 400,

                "error_code" => $decoded_response["code"],

                "error_message" => $decoded_response["error"],
            ];
        } else {
            $this->ci->Mydb->update(
                "orders",
                [
                    "order_primary_id" => $order_details["order_primary_id"],
                ],
                [
                    "order_milkrun_status" => $decoded_response["status"],
                    "order_delivary_type" => "Milkrun",
                    "order_milkrun_quote_id" => $decoded_response["uuid"],
                ]
            );

            $this->ci->Mydb->insert("pos_milkrun_order_details", [
                "milkrun_api_req_user_name" => $outlet_details["outlet_name"],

                "milkrun_api_req_user_contactno" =>
                    $outlet_details["outlet_phone"],

                "milkrun_api_delivery_user_name" =>
                    $order_customer["order_customer_fname"] .
                    " " .
                    $order_customer["order_customer_lname"],

                "milkrun_api_delivery_user_contactno" =>
                    $order_customer["order_customer_mobile_no"],

                "milkrun_api_schedule_date" => $order_date,

                "milkrun_api_service_type" => $caronly == true ? "Car" : "Bike",

                "milkrun_api_pickup_location" => $outletAddr,

                "milkrun_api_fee_price" => $decoded_response["delivery_fee"],

                "milkrun_api_order_id" => $order_details["order_id"],

                "milkrun_api_ref_id" => $decoded_response["uuid"],

                "milkrun_api_primary_order_id" =>
                    $order_details["order_primary_id"],
            ]);

            // echo $this->ci->db->last_query();
            //exit;

            $response = [
                "response" => "success",

                "status_code" => 200,

                "message" => "Order placed successfully!",

                "data" => $decoded_response,
            ];
        }

        return $response;
    }

    public function submit_order($order_id)
    {
        /* API URL */

        $url = $this->api_base_url . "orders/" . $order_id . "/submit";

        $data = [
            "uuid" => $order_id,

            // "scheduled_time"     => $scheduled_time,
        ];

        $order_data = json_encode($data);

        $curl_response = $this->load_post_curl($url, $order_data, "POST");

        $decoded_response = json_decode($curl_response, true);

        if (array_key_exists("error", $decoded_response)) {
            $response = [
                "response" => "error",

                "status_code" => 400,

                "error_code" => $decoded_response["code"],

                "error_message" => $decoded_response["error"],
            ];
        } else {
            $this->ci->Mydb->update(
                "orders",
                [
                    "order_milkrun_quote_id" => $order_id,
                ],
                [
                    "order_milkrun_id" => $decoded_response["uuid"],
                    "order_milkrun_status" => $decoded_response["status"],
                ]
            );

            $response = [
                "response" => "success",

                "status_code" => 200,

                "message" => "Order placed successfully!",

                "data" => $decoded_response,
            ];
        }

        return $response;
    }

    public function get_order_details($order_id)
    {
        /* API URL */

        $url = $this->api_base_url . "orders/" . $order_id;

        $data = [
            "uuid" => $order_id,

            // "scheduled_time"     => $scheduled_time,
        ];

        $order_data = json_encode($data);

        $curl_response = $this->load_post_curl($url, $order_data, "GET");

        $decoded_response = json_decode($curl_response, true);

        if (array_key_exists("error", $decoded_response)) {
            $response = [
                "response" => "error",

                "status_code" => 400,

                "error_code" => $decoded_response["code"],

                "error_message" => $decoded_response["error"],
            ];
        } else {
            $response = [
                "response" => "success",

                "status_code" => 200,

                "message" => "Order placed successfully!",

                "data" => $decoded_response,
            ];
        }

        return $response;
    }

    public function cancel_order($order_id)
    {
        /* API URL */

        $url = $this->api_base_url . "orders/" . $order_id;

        $data = [
            "uuid" => $order_id,

            // "scheduled_time"     => $scheduled_time,
        ];

        $order_data = json_encode($data);

        $curl_response = $this->load_post_curl($url, $order_data, "DELETE");

        $decoded_response = json_decode($curl_response, true);

        if (array_key_exists("error", $decoded_response)) {
            $response = [
                "response" => "error",

                "status_code" => 400,

                "error_code" => $decoded_response["code"],

                "error_message" => $decoded_response["error"],
            ];
        } else {
            $response = [
                "response" => "success",

                "status_code" => 200,

                "message" => "Order placed successfully!",

                "data" => $decoded_response,
            ];
        }

        return $response;
    }

    public function getUserLatLang($postal_code)
    {
        $userLatLangArr = [];

        $zip_resut = $this->ci->Mydb->get_record(
            ["zip_latitude", "zip_longitude", "zip_id"],
            "zipcodes",
            [
                "zip_code" => $postal_code,
            ]
        );

        $zip_status = "";

        if (
            !empty($zip_resut) &&
            $zip_resut["zip_latitude"] != "" &&
            $zip_resut["zip_longitude"] != ""
        ) {
            $userLatLangArr["latitude"] = $zip_resut["zip_latitude"];

            $userLatLangArr["longitude"] = $zip_resut["zip_longitude"];
        } else {
            $this->ci->load->helper("curl");

            $url = MAPAPI_LINK . $postal_code;

            $zip_resut = getCURLresult($url);

            if (!empty($zip_resut) && !empty($zip_resut->results)) {
                $zip_status = "OK";

                $latitude = $zip_resut->results[0]->LATITUDE;

                $longitude = $zip_resut->results[0]->LONGITUDE;
            }
        }

        return $userLatLangArr;
    }

    public function getAddressFmt($addressline1, $postal_code)
    {
        $customerAddr = "";

        $custAddrLine = $addressline1;

        if (strpos(strtolower($custAddrLine), "singapore") !== false) {
            $custAddrLine = str_replace(
                "singapore",
                "",
                strtolower($custAddrLine)
            );
        }

        $custAddrLine = trim($custAddrLine);

        $custAddrLine = str_replace(",", "", $custAddrLine);

        $customerAddr = $custAddrLine . ", Singapore " . $postal_code;

        return $customerAddr;
    }

    public function products_list_get(
        $app_id = "",
        $product_id = "",
        $status = null,
        $response = null
    ) {
        $result = [];

        $products_list = $this->ci->Mydb->get_all_records(
            "product_id, product_primary_id, product_parent_revel_id, product_company_app_id,product_category_id, product_name, product_long_description, product_sequence, product_thumbnail, product_status, product_menu_set_component_id, product_is_combo,  product_price, product_alias_enabled, product_alias",
            "products",
            [
                "product_company_app_id" => $app_id,
                "product_id" => $product_id,
               
                "product_status" => "A",
            ]
        );

        foreach ($products_list as $value) {
            $result = [
                "id" => $product_id,

                "name" => ($value["product_alias"] !=="" ? $value["product_alias"] : $value["product_alias"]),

                "sequence" => (int) $value["product_sequence"],

                "availableStatus" => "AVAILABLE",

                "price" => (int) $value["product_price"] * 100,
            ];
        }

        return $result;
    }

    public function update_order_statusUpdat($foodpanda_acceptancetime,$foodpanda_order_code,$foodpanda_order_token,$status_name,$compid)
    {
        $get_settings = $this->ci->Mydb->get_all_records(
            "setting_key, setting_value",
            "client_settings",
            ["client_id" => $compid]
        );

        $aval = "634E6FA8-8DAF-4046-A494-FFC1FCF8BD11";

        $foodpanda_url = "";
        $foodpanda_api_key = "";
        $foodpanda_api_secret = "";
        if (!empty($get_settings)) {
            foreach ($get_settings as $gt) {
                if ($gt["setting_key"] == "client_foodpanda_url") {
                    $foodpanda_url = $gt["setting_value"];
                }
                if ($gt["setting_key"] == "client_foodpanda_api_key") {
                    $foodpanda_api_key = $gt["setting_value"];
                }
                if ($gt["setting_key"] == "client_foodpanda_api_secret") {
                    $foodpanda_api_secret = $gt["setting_value"];
                }
            }
        }
        $acceptanceTime = $foodpanda_acceptancetime;
        $remoteOrderId = $foodpanda_order_code;
        $status = $status_name;

        $data = [
            "acceptanceTime" => $acceptanceTime,
            "remoteOrderId" => $remoteOrderId,
            "status" => $status,
        ];

        $acessTokan = $this->auth_token(
            $foodpanda_url,
            $foodpanda_api_key,
            $foodpanda_api_secret
        );

        $url = $this->api_base_url.$foodpanda_url."/v2/order/status/$foodpanda_order_token";
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_ENCODING => "",

            CURLOPT_MAXREDIRS => 10,

            CURLOPT_TIMEOUT => 30,

            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

            CURLOPT_CUSTOMREQUEST => "POST",

            CURLOPT_POSTFIELDS => json_encode($data),

            CURLOPT_HTTPHEADER => [
                "Content-Type:application/json",

                "Authorization: Bearer " . $acessTokan->access_token,
            ],
        ]);

        $response = curl_exec($curl);

        $err = curl_error($curl);

        curl_close($curl);


if($status=="order_accepted")
{
    $url = $this->api_base_url.$foodpanda_url."/v2/orders/$foodpanda_order_token/preparation-completed";
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,

        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_ENCODING => "",

        CURLOPT_MAXREDIRS => 10,

        CURLOPT_TIMEOUT => 30,

        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

        CURLOPT_CUSTOMREQUEST => "POST",


        CURLOPT_HTTPHEADER => [
            "Content-Type:application/json",

            "Authorization: Bearer " . $acessTokan->access_token,
        ],
    ]);

    $prepare_response = curl_exec($curl);

    $err = curl_error($curl);

    curl_close($curl);

}


        $decoded_response = json_decode($response, true);
        return $decoded_response;

    }

    public function getscheduletime($outlet_id, $app_id, $aval)
    {
        $join = [];
        $join[0]["select"] = "delivery_time_setting_day_availablie_day";
        $join[0]["table"] = "advanced_delivery_time_settings_days";
        $join[0]["condition"] =
            "delivery_time_setting_id=delivery_time_setting_day_time_setting_primary_id";
        $join[0]["type"] = "INNER";

        $join[1]["select"] =
            "delivery_time_setting_time_pickup_slot_start_time, delivery_time_setting_time_pickup_slot_end_time";
        $join[1]["table"] = "advanced_delivery_time_settings_time";
        $join[1]["condition"] =
            "delivery_time_setting_id=delivery_time_setting_time_time_setting_primary_id AND delivery_time_setting_day_id=delivery_time_setting_time_pickup_days_id";
        $join[1]["type"] = "INNER";

        $timeWhere =
            "delivery_time_setting_outlet_id='" .
            $outlet_id .
            "' AND delivery_time_setting_company_app_id='" .
            $app_id .
            "' AND delivery_time_setting_availability_id='" .
            $aval .
            
            "' AND delivery_time_setting_status='A'";

        $deliveryTime = $this->ci->Mydb->get_all_records(
            "",
            "advanced_delivery_time_settings",
            $timeWhere,
            null,
            null,
            null,
            null,
            ["delivery_time_setting_outlet_id", "delivery_time_setting_day_id"],
            $join
        );
        return $deliveryTime;
    }
}
