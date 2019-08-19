<?php
# Enterprisepayment module, data.
$name = "Enterprisepayment";
$category = "social";

# Icons
$icons = [
	"/app/local/modules/Enterprisepayment/resources/media/library/Enterprisepayment-flat.png",
];

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = [
	'library_id' => $result["library_id"],
	'icon_id' => $result["icon_id"],
	"code" => "enterprisepayment",
	"name" => $name,
	"model" => "Enterprisepayment_Model_Enterprisepayment",
	"desktop_uri" => "enterprisepayment/application/",
	"mobile_uri" => "enterprisepayment/mobile_list/",
	"mobile_view_uri" => "enterprisepayment/mobile_view/",
	"mobile_view_uri_parameter" => "recognition_id",
	"only_once" => 0,
	"is_ajax" => 1,
	"use_my_account" => 1,
	"position" => 1000,
	"social_sharing_is_available" => 1,
];

$option = Siberian_Feature::install($category, $data, ["code"]);

# Layouts
$layout_data = [1];
$slug = "enterprisepayment";

Siberian_Feature::installLayouts($option->getId(), $slug, $layout_data);

# Icons Flat
$icons = [
	"/app/local/modules/Enterprisepayment/resources/media/library/enterprisepayment-flat.png",
];

Siberian_Feature::installIcons("{$name}-flat", $icons);

# Copy assets at install time
Siberian_Assets::copyAssets("/app/local/modules/Enterprisepayment/resources/var/apps/");